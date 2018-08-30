<pre>
    <?
    $job = new OCJob('71bc715abca4aa721105a9aa8bb340f8');
    $upload_client = new UploadClient();
    $ingest_client = new IngestClient();
    $track_uri = $upload_client->getTrackURI($job->complete_data()['opencast_info']['opencast_job_id']);
    $media_package = $ingest_client->addTrack(
        $job->complete_data()['opencast_info']['media_package'],
        $track_uri,
        $job->complete_data()['opencast_info']['flavor']
    );
    ?>
</pre>

<div id="opencast">
    <h2>
        <?= $_('Festplattenplatz im Tempverzeichnis') ?>
    </h2>

    <?= sprintf($_('Belegt: %s von %s'),
        $memory_space['readable']['used'],
        $memory_space['readable']['total']
    ) ?>
    <br>

    <progress value="<?= $memory_space['bytes']['used'] ?>" max="<?= $memory_space['bytes']['total'] ?>" data-label="test"></progress>

    <br><br>

    <?= $this->render_partial('admin/_job.php',  [
        'caption' => $_('Nicht abgeschlossene / abgebrochene Uploads'),
        'jobs'    => $upload_jobs['unfinished']
    ]) ?>

    <?= $this->render_partial('admin/_job.php',  [
        'caption' => $_('Erfolgreiche Uploads'),
        'jobs'    => $upload_jobs['successful']
    ]) ?>
</div>
