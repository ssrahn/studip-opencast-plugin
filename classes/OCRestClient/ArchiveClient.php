<?php

class ArchiveClient extends OCRestClient
{
    static $me;
    public $serviceName = "Archive";

    function __construct()
    {
        if ($config = parent::getConfig('archive')) {
            parent::__construct($config);
        } else {
            throw new Exception (_("Die Konfiguration wurde nicht korrekt angegeben"));
        }
    }

    function deleteEvent($eventId) {
        $this->ochandler->set_options([
            CURLOPT_CUSTOMREQUEST => 'DELETE'
        ]);
        $response = $this->getJSON("/".$eventId, array(), false, false);
        return $response;
    }

    function applyWorkflow($workflowDefinitionId, $eventId) {
        $response = $this->getXML("/apply/".$workflowDefinitionId, array('mediaPackageIds' => $eventId), false, true);
        if ($response[1] == 204) {
            return true;
        } else {
            return false;
        }
    }

}
