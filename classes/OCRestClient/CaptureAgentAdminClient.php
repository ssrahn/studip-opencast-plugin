<?php

class CaptureAgentAdminClient extends OCRestClient
{
    public static $me;

    public function __construct($config_id = 1)
    {
        $this->serviceName = 'CaptureAgentAdminClient';

        if ($config = parent::getConfig('capture-admin', $config_id)) {
            parent::__construct($config);
        } else {
            throw new Exception(_("Die Konfiguration wurde nicht korrekt angegeben"));
        }
    }

    /**
     *  getCaptureAgents() - retrieves a representation of all Capture Agents from conntected Opencast-Matterhorn Core
     *
     * @return array string response of connected Capture Agents
     * @throws Exception
     */
    public function getCaptureAgents()
    {
        $service_url = "/agents.json";
        if ($agents = $this->getJSON($service_url)) {
            $sanitzed_agents = $this->sanitizeAgents($agents);
            return $sanitzed_agents;
        } else {
            return false;
        }
    }

    public function getCaptureAgentCapabilities($agent_name)
    {
        $service_url = "/agents/" . $agent_name . "/capabilities.json";
        if ($agent = $this->getJSON($service_url)) {
            $x = 'properties-response';
            return $agent->$x->properties->item;
        } else {
            return false;
        }
    }

    private function sanitizeAgents($agents)
    {
        if (is_array($agents->agents->agent)) {
            $sanitized_agents = $agents->agents->agent;
        } else {
            $sanitized_agents = $agents->agents;
        }

        return $sanitized_agents;
    }
}
