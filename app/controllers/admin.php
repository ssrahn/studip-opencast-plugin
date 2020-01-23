<?php

class AdminController extends Opencast\Controller
{
    /**
     * Constructs the controller and provide translations methods.
     *
     * @param object $dispatcher
     * @see https://stackoverflow.com/a/12583603/982902 if you need to overwrite
     *      the constructor of the controller
     */
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        $this->plugin = $dispatcher->current_plugin;

        // Localization
        $this->_ = function ($string) use ($dispatcher) {
            return call_user_func_array(
                [$dispatcher->current_plugin, '_'],
                func_get_args()
            );
        };

        $this->_n = function ($string0, $tring1, $n) use ($dispatcher) {
            return call_user_func_array(
                [$dispatcher->current_plugin, '_n'],
                func_get_args()
            );
        };
    }

    public function index_action()
    {
        Navigation::activateItem('/admin/config/oc-config');

        /*
        Helpbar::get()->addPlainText('', $this->_('Hier wird die Anbindung zum Opencast System verwaltet.
        Geben Sie die Daten ihres Opencast-Systems ein, um Aufzeichnungen in Ihren Veranstaltungen bereitstellen zu können.
        Optional haben Sie die Möglichkeit, ein zweites Opencast-System im Nur-Lesen-Modus anzubinden.')
        );
        */

        // starting point for vue app
    }
}
