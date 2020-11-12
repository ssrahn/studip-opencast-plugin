<?php
/*
 * course.php - course controller
 */

use Opencast\Models\OCConfig;
use Opencast\Models\OCSeminarSeries;
use Opencast\Models\OCTos;
use Opencast\Models\OCScheduledRecordings;
use Opencast\LTI\OpencastLTI;

class CourseController extends OpencastController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        PageLayout::setHelpKeyword('Opencast');

        PageLayout::addHeadElement(
            'script',
            [],
            'OC.parameters = ' . json_encode($this->getOCParameters(), JSON_FORCE_OBJECT)
        );
    }

    /**
     * Sets the page title. Page title always includes the course name.
     *
     * @param mixed $title Title of the page (optional)
     */
    private function set_title($title = '')
    {
        $title_parts = func_get_args();

        if (class_exists('Context')) {
            $title_parts[] = Context::getHeaderLine();
        } else {
            $title_parts[] = $GLOBALS['SessSemName']['header_line'];
        }

        $title_parts = array_reverse($title_parts);
        $page_title  = implode(' - ', $title_parts);

        PageLayout::setTitle($page_title);
    }


    /**
     * Common code for all actions: set default layout and page title.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (class_exists('Context')) {
            $this->course_id = Context::getId();
        } else {
            $this->course_id = $GLOBALS['SessionSeminar'];
        }

        $this->config = OCConfig::getConfigForCourse($this->course_id);
        $this->paella = $this->config['paella'] == '0' ? false : true;

        // set the stream context to ignore ssl erros -> get_headers will not work otherwise
        stream_context_set_default([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        // check, if current user is lecturer, force tos if so
        if (Config::get()->OPENCAST_SHOW_TOS
            && !$GLOBALS['perm']->have_studip_perm('admin', $this->course_id)
            && $action != 'tos' && $action != 'access_denied' && $action != 'accept_tos') {
            if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
                if (empty(OCTos::findBySQL('user_id = ? AND seminar_id = ?', [$GLOBALS['user']->id, $this->course_id]))) {
                    $this->redirect('course/tos');
                }
            } else {
                if (empty(OCTos::findBySQL('seminar_id = ?', [$this->course_id]))) {
                    $this->redirect('course/access_denied');
                }
            }
        }
    }

    /**
     * This is the default action of this controller.
     */
    public function index_action($active_id = 'false', $upload_message = false)
    {
        $this->set_title($this->_("Opencast Player"));

        if ($upload_message === 'true') {
            PageLayout::postSuccess($this->_('Die Datei wurde erfolgreich hochgeladen. Je nach Größe der Datei und Auslastung des Opencast-Servers kann es einige Zeit dauern, bis die Aufzeichnung in der Liste sichtbar wird.'));
        } else if ($upload_message === 'false') {
            PageLayout::postError($this->_('Die Datei konnte nicht erfolgreich hochgeladen werden.'));
        }

        $this->mayWatchEpisodes = $GLOBALS['perm']->have_studip_perm('autor', $this->course_id);
        if (!$this->mayWatchEpisodes) {
            PageLayout::postInfo($this->_('Sie sind in dieser Veranstaltung nur Leser*in. Sie können die Aufzeichnungen leider nicht ansehen. Wenden Sie sich eventuell an die Lehrenden dieser Veranstaltung!'));
        }

        $reload = true;

        foreach (OCSeminarSeries::getMissingSeries($this->course_id) as $series) {
            PageLayout::postError(sprintf($this->_(
                'Die verknüpfte Serie mit der ID "%s" konnte nicht in Opencast gefunden werden! ' .
                'Verküpfen sie bitte eine andere Serie, erstellen Sie eine neue oder ' .
                'wenden Sie sich an einen Systemadministrator.'
            ), $series['series_id']));
        }

        $this->connectedSeries = OCSeminarSeries::getSeries($this->course_id);
        $this->wip_episodes    = [];
        $this->instances       = [];
        $this->multiconnected  = false;

        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->course_id) && !empty($this->connectedSeries)) {
            $this->workflow_client = WorkflowClient::getInstance();

            foreach ($this->connectedSeries as $key => $series) {
                if ($series['schedule']) {
                    $this->can_schedule = true;
                }

                $api_client = ApiEventsClient::getInstance(OCConfig::getConfigIdForSeries($series['series_id']));

                $oc_series                   = OCSeriesModel::getSeriesFromOpencast($series);
                $this->connectedSeries[$key] = array_merge($series->toArray(), $oc_series);
                $this->wip_episodes          = array_merge($api_client->getEpisodes($series['series_id']), $this->wip_episodes);
                $this->instances             = array_merge(
                    $this->workflow_client->getRunningInstances($series['series_id']),
                    $this->instances
                );

                // is this series connected to more than one seminar?
                if (count(OCSeminarSeries::findBySeries_id($series['series_id'])) > 1) {
                    $this->multiconnected = true;
                }
            }

            $this->wip_episodes = array_filter($this->wip_episodes, function ($element) {
                return ($element->processing_state == 'RUNNING');
            });

            //workflow
            $occourse         = new OCCourseModel($this->course_id);
            $this->tagged_wfs = $this->workflow_client->getTaggedWorkflowDefinitions();
            $this->schedulewf = $occourse->getWorkflow('schedule');
            $this->uploadwf   = $occourse->getWorkflow('upload');
        }

        if (!empty($this->connectedSeries)) {
            OpencastLTI::updateEpisodeVisibility($this->course_id);
            OpencastLTI::setAcls($this->course_id);
        }

        Navigation::activateItem('course/opencast/overview');
        try {
            $this->search_client = SearchClient::getInstance();

            $occourse = new OCCourseModel($this->course_id);

            $this->coursevis = $occourse->getSeriesVisibility();

            if ($occourse->getSeriesID()) {

                $this->ordered_episode_ids = $this->get_ordered_episode_ids($reload);

                if (!empty($this->ordered_episode_ids)) {
                    PageLayout::setTitle(PageLayout::getTitle() . ' - ' . $this->_('Vorlesungsaufzeichnungen'));
                    if ($this->paella) {
                        $this->video_url = $this->search_client->getBaseURL() . "/paella/ui/watch.html?id=";
                    } else {
                        $this->video_url = $this->search_client->getBaseURL() . "/engage/theodul/ui/core.html?id=";
                    }
                }

                // Upload-Dialog
                $this->date   = date('Y-m-d');
                $this->hour   = date('H');
                $this->minute = date('i');

                // Remove Series
                if ($this->flash['cand_delete']) {
                    $this->flash['delete'] = true;
                }

                $eventsClient = ApiEventsClient::getInstance(1);
                $this->events = $eventsClient->getBySeries($occourse->getSeriesID());
            }
        } catch (Exception $e) {
            $this->flash['error'] = $e->getMessage();
            $this->render_action('_error');
        }

        $this->configs = OCConfig::getBaseServerConf();
    }

    private function get_ordered_episode_ids($reload, $minimum_full_view_perm = 'tutor')
    {
        try {
            $oc_course = new OCCourseModel($this->course_id);
            if ($oc_course->getSeriesID()) {
                $ordered_episode_ids = $oc_course->getEpisodes($reload);
                if (!$GLOBALS['perm']->have_studip_perm($minimum_full_view_perm, $this->course_id)) {
                    $ordered_episode_ids = $oc_course->refineEpisodesForStudents($ordered_episode_ids);
                }
            }

            return $ordered_episode_ids;
        } catch (Exception $e) {
            return false;
        }
    }

    public function tos_action()
    {
        if (!Config::get()->OPENCAST_SHOW_TOS || !$GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            return $this->redirect('course/index');
        }

        $this->set_title($this->_('Opencast - Datenschutzrichtlinien'));
        Navigation::activateItem('course/opencast');
    }

    public function accept_tos_action()
    {
        if (!Config::get()->OPENCAST_SHOW_TOS || !$GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            return $this->redirect('course/index');
        }

        if (empty(OCTos::findBySQL('user_id = ? AND seminar_id = ?', [$GLOBALS['user']->id, $this->course_id]))) {
            $tos = OCTos::create(
                [
                    'user_id'    => $GLOBALS['user']->id,
                    'seminar_id' => $this->course_id
                ]);
            $tos->store();
        }
        $this->redirect('course/index');
    }

    public function withdraw_tos_action()
    {
        if (!Config::get()->OPENCAST_SHOW_TOS || !$GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            return $this->redirect('course/index');
        }

        OCTos::deleteBySQL('seminar_id = ?', [
            $this->course_id
        ]);

        $this->redirect('course/index');
    }

    public function access_denied_action()
    {
        if (!Config::get()->OPENCAST_SHOW_TOS) {
            return $this->redirect('course/index');
        }

        $this->set_title($this->_('Opencast - Zugriff verweigert'));
        Navigation::activateItem('course/opencast');
    }

    public function config_action()
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
        if (Request::isXhr()) {
            $this->set_layout(null);
        }

        if (isset($this->flash['messages'])) {
            $this->message = $this->flash['messages'];
        }

        Navigation::activateItem('course/opencast');
        $navigation = Navigation::getItem('/course/opencast');
        $navigation->setImage(new Icon('../../' . $this->dispatcher->trails_root . '/images/opencast-black.svg'));

        $this->set_title($this->_('Opencast Konfiguration'));
        $this->response->add_header('X-Title', rawurlencode($this->_('Series mit Veranstaltung verknüpfen')));


        $this->configs = OCConfig::getBaseServerConf();

        foreach ($this->configs as $id => $config) {
            $sclient = SearchClient::getInstance($id);
            if ($series = $sclient->getAllSeries($this->course_id)) {
                $this->all_series[$id] = $series;
            }
        }
    }

    public function edit_action($course_id)
    {
        $series = json_decode(Request::get('series'), true);
        OCSeriesModel::setSeriesforCourse(
            $course_id,
            $series['config_id'],
            $series['series_id'],
            'visible',
            0,
            time()
        );
        StudipLog::log('OC_CONNECT_SERIES', null, $course_id, json_encode($series));
        PageLayout::postSuccess(
            $this->_('Änderungen wurden erfolgreich übernommen. Es wurde eine Serie für den Kurs verknüpft.')
        );
        $this->redirect('course/index');
    }

    public function remove_series_action($ticket)
    {
        if (Request::submitted('cancel')) {
            $this->redirect('course/index');
            return;
        }
        OCPerm::check('tutor');
        if (Request::submitted('delete') && check_ticket($ticket)) {
            if (OCSeriesModel::removeSeriesforCourse($this->course_id)) {
                PageLayout::postSuccess($this->_('Die Zuordnung wurde entfernt'));
                StudipLog::log('OC_REMOVE_CONNECTED_SERIES', null, $this->course_id, '');
            } else {
                PageLayout::postError($this->_('Die Zuordnung konnte nicht entfernt werden.'));
            }
        }
        $this->flash['cand_delete'] = true;
        $this->redirect('course/index');
    }


    public function scheduler_action()
    {
        Navigation::activateItem('course/opencast/scheduler');
        $navigation = Navigation::getItem('/course/opencast');
        $navigation->setImage(new Icon('../../' . $this->dispatcher->trails_root . '/images/opencast-black.svg'));

        $this->set_title($this->_('Opencast Aufzeichnungen planen'));

        $this->cseries = OCSeminarSeries::getSeries($this->course_id);

        $course               = new Seminar($this->course_id);
        $selectable_semesters = new SimpleCollection(Semester::getAll());
        $start                = $course->start_time;
        $end                  = $course->duration_time == -1 ? PHP_INT_MAX : $course->end_time;
        $selectable_semesters = $selectable_semesters->findBy('beginn', [$start, $end], '>=<=')->toArray();
        if (count($selectable_semesters) > 1 || (count($selectable_semesters) == 1 && $course->hasDatesOutOfDuration())) {
            $selectable_semesters[] = ['name' => _('Alle Semester'), 'semester_id' => 'all'];
        }
        $this->selectable_semesters = array_reverse($selectable_semesters);
        $current_semester           = reset($this->selectable_semesters);
        $this->semester_filter      = Request::option('semester_filter') ?: $current_semester['semester_id'];

        UrlHelper::bindLinkParam('semester_filter', $this->semester_filter);

        $this->dates           = OCModel::getDatesForSemester($this->course_id, $this->semester_filter);
        $this->all_semester    = Semester::getAll();
        $this->caa_client      = CaptureAgentAdminClient::getInstance();
        $this->workflow_client = WorkflowClient::getInstance();
        $this->tagged_wfs      = $this->workflow_client->getTaggedWorkflowDefinitions();


        $events_client   = ApiEventsClient::getInstance();
        $events = $events_client->getEpisodes($this->cseries[0]['series_id']);

        foreach ($events as $event) {
            $this->events[$event->identifier] = $event;
        }
    }


    public function schedule_action($resource_id, $publishLive, $termin_id)
    {
        if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $scheduler_client = SchedulerClient::getInstance();
            if ($scheduler_client->scheduleEventForSeminar($this->course_id, $resource_id, $publishLive, $termin_id)) {
                PageLayout::postSuccess($publishLive
                    ? $this->_('Livestream mit Aufzeichnung wurde geplant.')
                    : $this->_('Aufzeichnung wurde geplant.')
                );
                $course  = Course::find($this->course_id);
                $members = $course->members;
                $users   = [];
                foreach ($members as $member) {
                    $users[] = $member->user_id;
                }

                $notification = sprintf(
                    $this->_('Die Veranstaltung "%s" wird für Sie mit Bild und Ton automatisiert aufgezeichnet.'),
                    htmlReady($course->name)
                );
                PersonalNotifications::add(
                    $users,
                    $this->url_for('course/index', ['cid' => $this->course_id]),
                    $notification,
                    $this->course_id,
                    Icon::create($this->plugin->getPluginUrl() . '/images/opencast-black.svg')
                );

                StudipLog::log('OC_SCHEDULE_EVENT', $termin_id, $this->course_id);
            } else {
                PageLayout::postError($this->_('Aufzeichnung konnte nicht geplant werden.'));
            }
        } else {
            throw new Exception($this->_('Sie haben leider keine Berechtigungen um diese Aktion durchzuführen'));
        }
        $this->redirect('course/scheduler?semester_filter=' . Request::option('semester_filter'));
    }

    public function unschedule_action($resource_id, $termin_id)
    {
        $this->course_id = Request::get('cid');
        if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $scheduler_client = SchedulerClient::getInstance();
            if ($scheduler_client->deleteEventForSeminar($this->course_id, $resource_id, $termin_id)) {
                PageLayout::postSuccess($this->_('Die geplante Aufzeichnung wurde entfernt'));
                StudipLog::log('OC_CANCEL_SCHEDULED_EVENT', $termin_id, $this->course_id);
            } else {
                PageLayout::postError($this->_('Die geplante Aufzeichnung konnte nicht entfernt werden.'));
            }
        } else {
            throw new Exception($this->_('Sie haben leider keine Berechtigungen um diese Aktion durchzuführen'));
        }
        $this->redirect('course/scheduler?semester_filter=' . Request::option('semester_filter'));
    }

    public function schedule_update_action()
    {
        $event_id = Request::get('event_id');
        $event    = OCScheduledRecordings::find($event_id);

        if ($event && Config::get()->OPENCAST_ALLOW_ALTERNATE_SCHEDULE
            && $GLOBALS['perm']->have_studip_perm('tutor', $event->seminar_id)
        ) {
            $start = Request::get('start');
            $end   = Request::get('end');

            if ($event) {
                $date = $event->date->date;

                $new_start = mktime(
                    floor($start / 60),
                    $start - floor($start / 60) * 60,
                    0,
                    date('n', $date),
                    date('j', $date),
                    date('Y', $date)
                );

                $new_end = mktime(
                    floor($end / 60),
                    $end - floor($end / 60) * 60,
                    0,
                    date('n', $date),
                    date('j', $date),
                    date('Y', $date)
                );

                $event->start = $new_start;
                $event->end   = $new_end;
                $event->store();

                // update event in opencast
                $scheduler_client = SchedulerClient::create($event->seminar_id);
                $scheduler_client->updateEventForSeminar(
                    $event->seminar_id,
                    $event->resource_id,
                    $event->date_id,
                    $event->event_id
                );
            }
        }

        $this->render_nothing();
    }

    public function update_action($resource_id, $termin_id)
    {
        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            $scheduler_client = SchedulerClient::create($this->course_id);
            $scheduled        = OCModel::checkScheduledRecording($this->course_id, $resource_id, $termin_id);

            if ($scheduler_client->updateEventForSeminar($this->course_id, $resource_id, $termin_id, $scheduled['event_id'])) {
                PageLayout::postSuccess($this->_('Die geplante Aufzeichnung wurde aktualisiert.'));
                StudipLog::log('OC_REFRESH_SCHEDULED_EVENT', $termin_id, $this->course_id);
            } else {
                PageLayout::postError($this->_('Die geplante Aufzeichnung konnte nicht aktualisiert werden.'));
            }
        } else {
            throw new Exception($this->_('Sie haben leider keine Berechtigungen um diese Aktion durchzuführen'));
        }

        $this->redirect('course/scheduler?semester_filter=' . Request::option('semester_filter'));
    }

    public function create_series_action()
    {
        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            if (empty(OCSeminarSeries::getSeries($this->course_id))) {
                $this->series_client = SeriesClient::create($this->course_id);
                if ($this->series_client->createSeriesForSeminar($this->course_id)) {
                    PageLayout::postSuccess($this->_('Series wurde angelegt'));
                    StudipLog::log('OC_CREATE_SERIES', $this->course_id);
                    StudipCacheFactory::getCache()->expire('oc_allseries');
                } else {
                    throw new Exception($this->_('Verbindung zum Series-Service konnte nicht hergestellt werden.'));
                }
            }
        } else {
            throw new Exception($this->_('Sie haben leider keine Berechtigungen um diese Aktion durchzuführen'));
        }
        $this->redirect('course/index');
    }

    /**
     * Set the view permissions for the passed episode
     *
     * @param  [type] $episode_id [description]
     * @param  [type] $permission [description]
     *
     * @return [type]             [description]
     */
    public function permission_action($episode_id, $permission)
    {
        $this->user_id = $GLOBALS['user']->id;

        // permissions of live streams cannot be changed
        if ($this->isLive($episode_id)) {
            throw new AccessDeniedException();
        }

        if (!$GLOBALS['perm']->have_studip_perm('admin', $this->course_id)
            && !OCModel::checkPermForEpisode($episode_id, $this->user_id)) {
            throw new AccessDeniedException();
        }

        if (OCModel::setVisibilityForEpisode($this->course_id, $episode_id, $permission)) {
            StudipLog::log(
                'OC_CHANGE_EPISODE_VISIBILITY',
                null,
                $this->course_id, "Episodensichtbarkeit wurde auf {$permission} geschaltet ({$episode_id})"
            );
            $this->set_status('201');
        } else {
            // republishing failed, report error to frontend
            $this->set_status('409');
        }

        $this->render_json(OCModel::getEntry($this->course_id, $episode_id)->toArray());
    }

    /**
     * @deprecated
     */
    public function upload_action()
    {
        if ($this->isStudyGroup() && !$this->isStudentUploadForStudyGroupActivated()) {
            PageLayout::postError(_('Uploads durch Studierende sind momentan verboten.'));
            $this->redirect('course/index/false');
        }

        $this->connectedSeries = OCSeminarSeries::getSeries($this->course_id);
        if (!$this->connectedSeries) {
            throw new Exception('Es ist keine Serie mit dieser Veranstaltung verknüpft!');
        }
        $this->set_title($this->_('Opencast Medienupload'));

        $workflow_client = WorkflowClient::getInstance();

        $workflows = array_filter(
            $workflow_client->getTaggedWorkflowDefinitions(),
            function ($element) {
                return (in_array('schedule', $element['tags']) !== false
                    || in_array('schedule-ng', $element['tags']) !== false)
                    ? $element
                    : false;
            }
        );

        $occourse       = new OCCourseModel($this->course_id);
        $this->workflow = $occourse->getWorkflow('upload');

        if ($this->workflow) {
            foreach ($workflows as $wf) {
                if ($wf['id'] == $this->workflow['workflow_id']) {
                    $this->workflow_text = $wf['title'];
                }
            }
        }

        if (Request::isXhr()) {
            $this->set_layout(null);
        } else {
            Navigation::activateItem('course/opencast/overview');
        }
    }

    public function bulkschedule_action()
    {
        // try to set higher time limit to prevent breaking the bulk update in the middle of it
        set_time_limit(1800);
        $action = Request::get('action');
        if ($GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $dates = Request::getArray('dates');
            foreach ($dates as $termin_id => $resource_id) {
                switch ($action) {
                    case 'create':
                        self::schedule($resource_id, false, $termin_id, $this->course_id);
                        break;
                    case 'live':
                        self::schedule($resource_id, true, $termin_id, $this->course_id);
                        break;
                    case 'update':
                        self::updateschedule($resource_id, $termin_id, $this->course_id);
                        break;
                    case 'delete':
                        self::unschedule($resource_id, $termin_id, $this->course_id);
                        break;
                }
            }
        } else {
            throw new Exception($this->_('Sie haben leider keine Berechtigungen um diese Aktion durchzuführen'));
        }
        $this->redirect('course/scheduler?semester_filter=' . Request::option('semester_filter'));
    }

    public static function schedule($resource_id, $publishLive, $termin_id, $course_id)
    {
        $scheduled = OCModel::checkScheduledRecording($course_id, $resource_id, $termin_id);
        if (!$scheduled) {
            $scheduler_client = SchedulerClient::getInstance(OCConfig::getConfigIdForCourse($course_id));

            if ($scheduler_client->scheduleEventForSeminar($course_id, $resource_id, $publishLive, $termin_id)) {
                StudipLog::log('OC_SCHEDULE_EVENT', $termin_id, $course_id);
                return true;
            } else {
                // TODO FEEDBACK
            }
        }
    }

    public static function updateschedule($resource_id, $termin_id, $course_id)
    {
        $scheduled = OCModel::checkScheduledRecording($course_id, $resource_id, $termin_id);
        if ($scheduled) {
            $scheduler_client = SchedulerClient::getInstance(OCConfig::getConfigIdForCourse($course_id));
            $scheduler_client->updateEventForSeminar($course_id, $resource_id, $termin_id, $scheduled['event_id']);
            StudipLog::log('OC_REFRESH_SCHEDULED_EVENT', $termin_id, $course_id);
        } else {
            self::schedule($resource_id, $termin_id, $course_id);
        }
    }

    public static function unschedule($resource_id, $termin_id, $course_id)
    {
        $scheduled = OCModel::checkScheduledRecording($course_id, $resource_id, $termin_id);
        if ($scheduled) {
            $scheduler_client = SchedulerClient::getInstance(OCConfig::getConfigIdForCourse($course_id));
            if ($scheduler_client->deleteEventForSeminar($course_id, $resource_id, $termin_id)) {
                StudipLog::log('OC_CANCEL_SCHEDULED_EVENT', $termin_id, $course_id);
                return true;
            } else {
                // TODO FEEDBACK
            }
        }
    }

    public function remove_failed_action($workflow_id)
    {
        $workflow_client = WorkflowClient::getInstance();
        if ($workflow_client->removeInstanceComplete($workflow_id)) {
            if (OCModel::removeWorkflowIDforCourse($workflow_id, $this->course_id)) {
                PageLayout::postSuccess($this->_('Die hochgeladenen Daten wurden gelöscht.'));
            } else {
                PageLayout::postError($this->_('Die Referenz in der Datenbank konnte nicht gelöscht werden.'));
            }
        } else {
            PageLayout::postError($this->_('Die hochgeladenen Daten konnten nicht gelöscht werden.'));
        }
        $this->redirect('course/index/');
    }

    public function get_player_action($episode_id = "")
    {
        $occourse        = new OCCourseModel($this->course_id);
        $episodes        = $occourse->getEpisodes();
        $episode         = [];
        $current_preview = '';
        foreach ($episodes as $episode) {
            if ($episode['id'] == $episode_id) {
                $episode['author']      = $episode['author'] != '' ? $episode['author'] : 'Keine Angaben vorhanden';
                $episode['description'] = $episode['description'] != '' ? $episode['description'] : 'Keine Beschreibung vorhanden';
                $episode['start']       = date("d.m.Y H:i", strtotime($episode['start']));
                $cand_episode           = $episode;
            }
        }

        if (Request::isXhr()) {
            $this->set_status('200');
            $active_id           = $episode_id;
            $this->search_client = SearchClient::getInstance();

            if ($this->paella) {
                $video_url = $this->search_client->getBaseURL() . "/paella/ui/embed.html?id=" . $active_id;
            } else {
                $video_url = $this->search_client->getBaseURL() . "/engage/theodul/ui/core.html?id=" . $active_id;
            }

            $perm  = $GLOBALS['perm']->have_studip_perm('dozent', $this->course_id);
            $video = [
                'url'    => $video_url,
                'image'  => $current_preview,
                'circle' => $this->plugin->getPluginURL() . '/images/play.svg'
            ];

            $episode = [
                'active_id'    => $active_id,
                'course_id'    => $this->course_id,
                'paella'       => $this->paella,
                'video'        => $video,
                'perm'         => $perm,
                'episode_data' => $episode
            ];

            $this->render_json($episode);
        } else {
            $this->redirect('course/index/' . $episode_id);
        }
    }

    public function toggle_tab_visibility_action($ticket)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $occourse = new OCCourseModel($this->course_id);
            $occourse->toggleSeriesVisibility();
            $visibility = $occourse->getSeriesVisibility();
            $vis        = ['visible' => 'sichtbar', 'invisible' => 'ausgeblendet'];
            PageLayout::postSuccess(sprintf(
                $this->_("Der Reiter in der Kursnavigation ist jetzt für alle Kursteilnehmer %s."),
                htmlReady($vis[$visibility])
            ));

            StudipLog::log(
                'OC_CHANGE_TAB_VISIBILITY',
                $this->course_id,
                null,
                sprintf($this->_("Reiter ist %s."), htmlReady($vis[$visibility]))
            );
        }
        $this->redirect('course/index/false');
    }

    public function toggle_schedule_action($ticket)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $occourse = new OCCourseModel($this->course_id);
            $occourse->toggleSeriesSchedule();
        }
        $this->redirect('course/index/false');
    }

    public function workflow_action()
    {
        if (Request::isXhr()) {
            $this->set_layout(null);
        }
        PageLayout::setTitle($this->_('Workflow konfigurieren'));
        $this->workflow_client = WorkflowClient::getInstance();
        $this->workflows       = array_filter(
            $this->workflow_client->getTaggedWorkflowDefinitions(),
            function ($element) {
                return (in_array('schedule', $element['tags']) !== false
                    || in_array('schedule-ng', $element['tags']) !== false)
                    ? $element
                    : false;
            }
        );

        $occourse       = new OCCourseModel($this->course_id);
        $this->uploadwf = $occourse->getWorkflow('upload');
    }

    public function setworkflow_action()
    {
        if (check_ticket(Request::get('ticket')) && $GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $occcourse = new OCCourseModel($this->course_id);
            if ($course_workflow = Request::get('oc_course_workflow')) {
                $occcourse->setWorkflow($course_workflow, 'schedule');
            }
            if ($course_uploadworkflow = Request::get('oc_course_uploadworkflow')) {
                $occcourse->setWorkflow($course_uploadworkflow, 'upload');
            }
        }
        $this->redirect('course/index/false');
    }

    public function setworkflowforscheduledepisode_action($termin_id, $workflow_id, $resource_id)
    {
        if (Request::isXhr() && $GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            $occcourse = new OCCourseModel($this->course_id);
            $success   = $occcourse->setWorkflowForDate($termin_id, $workflow_id);
            self::updateschedule($resource_id, $termin_id, $this->course_id);
            $this->render_json($success);

        } else {
            $this->render_nothing();
        }
    }

    public function allow_download_action($ticket)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            CourseConfig::get($this->course_id)->store('OPENCAST_ALLOW_MEDIADOWNLOAD_PER_COURSE', 'yes');
            PageLayout::postInfo($this->_('Teilnehmer dürfen nun Aufzeichnungen herunterladen.'));
        }
        $this->redirect('course/index/false');
    }

    public function disallow_download_action($ticket)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            CourseConfig::get($this->course_id)->store('OPENCAST_ALLOW_MEDIADOWNLOAD_PER_COURSE', 'no');
            PageLayout::postInfo($this->_('Teilnehmer dürfen nun keine Aufzeichnungen mehr herunterladen.'));
        }

        $this->redirect('course/index/false');
    }

    public function isDownloadAllowed()
    {
        $courseConfig = CourseConfig::get($this->course_id)->OPENCAST_ALLOW_MEDIADOWNLOAD_PER_COURSE;
        switch ($courseConfig) {
            case 'yes':
                return true;
            case 'no':
                return false;
            case 'inherit':
                $globalConfig = Config::get()->OPENCAST_ALLOW_MEDIADOWNLOAD;
                return $globalConfig;
        }

        throw new RuntimeException("The course's configuration of OPENCAST_ALLOW_MEDIADOWNLOAD_PER_COURSE contains an unknown value.");
    }

    public function allow_students_upload_action($ticket)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('tutor', $this->course_id) && !$this->isStudyGroup()) {
            $studyGroup = $this->createStudyGroup($this->course_id);
            PageLayout::postInfo($this->_('Teilnehmer dürfen nun Aufzeichnungen hochladen.'));
        }
        $this->redirect('course/index/false');
    }

    public function disallow_students_upload_action($ticket)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('tutor', $this->course_id) && !$this->isStudyGroup()) {
            $this->unlinkStudyGroupAndCourse($this->course_id);
            PageLayout::postInfo($this->_('Teilnehmer dürfen nun keine Aufzeichnungen mehr hochladen.'));
        }
        $this->redirect('course/index/false');
    }

    public function isStudentUploadEnabled()
    {
        $studyGroupId = CourseConfig::get($this->course_id)->OPENCAST_MEDIAUPLOAD_STUDY_GROUP;
        return !empty($studyGroupId);
    }

    public function remove_episode_action($ticket, $episode_id)
    {
        if (check_ticket($ticket) && $GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            $episode = \Opencast\Models\OCSeminarEpisodes::findOneBySQL(
                'seminar_id = ? AND episode_id = ?',
                [$this->course_id, $episode_id]
            );
            if ($episode) {
                // live streams cannot be removed
                if ($this->isLive($episode_id)) {
                    throw new AccessDeniedException();
                }

                if ($this->retractEpisode($episode)) {
                    PageLayout::postSuccess($this->_('Die Episode wurde zum Entfernen markiert.'));
                } else {
                    PageLayout::postError($this->_('Die Episode konnte nicht zum Entfernen markiert werden.'));
                }
            }
        }

        $this->redirect('course/index/false');
    }

    public static function nice_size_text($size, $precision = 1, $conversion_factor = 1000, $display_threshold = 0.5)
    {
        $possible_sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($depth = 0; $depth < count($possible_sizes); $depth++) {
            if (($size / $conversion_factor) > $display_threshold) {
                $size /= $conversion_factor;
            } else {
                return round($size, $precision) . ' ' . $possible_sizes[$depth];
            }
        }

        return $size;
    }

    private function getOCParameters()
    {
        $cid             = Context::getId();
        $connectedSeries = OCSeminarSeries::getSeries($cid);
        $occourse        = new OCCourseModel($cid);
        $uploadwf        = $occourse->getWorkflow('upload');
        if ($uploadwf) {
            $workflow = $uploadwf['workflow_id'];
        } else {
            $workflow = Config::get()->OPENCAST_WORKFLOW_ID;
        }
        return [
            'seriesId'         => empty($connectedSeries) ? null : $connectedSeries[0]['series_id'],
            'uploadWorkflowId' => $workflow
        ];
    }

    private function retractEpisode($episode)
    {
        $workflowClient = ApiWorkflowsClient::getInstance();
        $result         = $workflowClient->retract($episode->episode_id);
        if (!$result) {
            return false;
        }

        $episode->is_retracting = true;
        $episode->store();
        return true;
    }

    private function isLive($episode_id)
    {
        $oc_events = ApiEventsClient::create($this->course_id);
        $events = $oc_events->getEpisodes(OCSeminarSeries::getSeries($this->course_id));

        foreach ($events as $event) {
            if ($event['id'] === $episode_id) {
                return $event->publication_status[0] == 'engage-live';
            }
        }

        return false;
    }

    private function createStudyGroup($courseId)
    {
        if (!empty(CourseConfig::get($courseId)->OPENCAST_MEDIAUPLOAD_STUDY_GROUP)) {
            return false;
        }
        $course = Course::find($courseId);

        $studyGroup = $this->createStudyGroupObject($course);
        $this->copyAvatarToStudyGroup($course, $studyGroup);
        $this->addAllMembersToStudyGroup($course, $studyGroup);
        $this->setupOpencastInStudyGroup($studyGroup);
        $this->linkStudyGroupAndCourse($course, $studyGroup);

        return $studyGroup;
    }

    private function createStudyGroupObject($course)
    {
        $studyGroup_name = $this->_("Studiengruppe:") . " " . $course['name'];
        $current_studyGroup = Course::findOneBySQL('name = :name AND status IN (:studygroup_mode)', [
            ':name'    => $studyGroup_name,
            ':studygroup_mode' => \studygroup_sem_types(),
        ]);
        if (!$current_studyGroup) {
            $studyGroup = new Course();
            $studyGroup['name'] = $studyGroup_name;
            $studyGroup['status'] = array_shift(studygroup_sem_types());
            $studyGroup['start_time'] = $course['start_time'];
            $studyGroup->store();
        } else {
            $studyGroup = $current_studyGroup;
        }

        return $studyGroup;
    }

    private function copyAvatarToStudyGroup($course, $studyGroup)
    {
        $oldAvatar = Avatar::getAvatar($course->getId());
        if ($oldAvatar->is_customized()) {
            $path = $oldAvatar->getCustomAvatarPath(Avatar::ORIGINAL);
            $studyGroupAvatar = Avatar::getAvatar($studyGroup->getId());
            $studyGroupAvatar->createFrom($path);
        }
    }

    private function addAllMembersToStudyGroup($course, $studyGroup)
    {
        foreach ($course->members as $member) {
            if ($studyGroup->members->findOneBy('user_id', $member->user_id)) {
                $currentStudyGroupMember = CourseMember::find([$studyGroup->getId(), $member->user_id]);
                $currentStudyGroupMember['status'] = 'dozent';
                $currentStudyGroupMember->store();
                continue;
            }
            $studyGroupMember = new CourseMember();
            $studyGroupMember['user_id'] = $member->user_id;
            $studyGroupMember['seminar_id'] = $studyGroup->getId();
            $studyGroupMember['status'] = 'dozent';
            $studyGroupMember->store();
        }
    }

    private function setupOpencastInStudyGroup($studyGroup)
    {
        PluginManager::getInstance()->setPluginActivated(
            $this->plugin->getPluginId(),
            $studyGroup->getId(),
            true
        );

        if (empty(OCSeminarSeries::getSeries($studyGroup->getId()))) {
            $this->series_client = SeriesClient::create($studyGroup->getId());
            if ($this->series_client->createSeriesForSeminar($studyGroup->getId())) {
                StudipLog::log('OC_CREATE_SERIES', $studyGroup->getId());
                StudipCacheFactory::getCache()->expire('oc_allseries');
            }
        }
    }

    private function linkStudyGroupAndCourse($course, $studyGroup)
    {
        CourseConfig::get($course->getId())->store('OPENCAST_MEDIAUPLOAD_STUDY_GROUP', $studyGroup->getId());
        CourseConfig::get($studyGroup->getId())->store('OPENCAST_MEDIAUPLOAD_LINKED_COURSE', $course->getId());
    }

    private function unlinkStudyGroupAndCourse($courseId)
    {
        $studyGroupId = CourseConfig::get($courseId)->OPENCAST_MEDIAUPLOAD_STUDY_GROUP;
        if (!empty($studyGroupId)) {
            CourseConfig::get($courseId)->store('OPENCAST_MEDIAUPLOAD_STUDY_GROUP', '');
            CourseConfig::get($studyGroupId)->store('OPENCAST_MEDIAUPLOAD_LINKED_COURSE', '');
            $studyGroup = Course::find($studyGroupId);
            $course = Course::find($courseId);
            $course_dozenten = $course->members->filter(
                function ($member) {
                    return $member['status'] === "dozent";
                }
            )->pluck('user_id');
            foreach ($studyGroup->members as $member) {
                if ( !in_array($member->user_id, array_values($course_dozenten)) ) {
                    $studyGroupMember = CourseMember::find([$studyGroupId, $member->user_id]);
                    $studyGroupMember['status'] = 'tutor';
                    $studyGroupMember->store();
                }
            }
        }
    }

    public function isStudyGroup()
    {
        $course = Seminar::GetInstance($this->course_id);
        return $course->isStudygroup();
    }

    public function isStudentUploadForStudyGroupActivated()
    {
        $linkedCourseId = CourseConfig::get($this->course_id)->OPENCAST_MEDIAUPLOAD_LINKED_COURSE;
        return !empty($linkedCourseId);
    }
}
