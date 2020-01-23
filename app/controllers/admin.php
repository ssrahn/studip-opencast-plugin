<?php

class AdminController extends Opencast\Controller
{
    public function index_action()
    {
        Navigation::activateItem('/opencast/admin');
        PageLayout::addStylesheet($this->plugin->getPluginURL().'/assets/css/unterrichtsplanung.css');
        // starting point for vue app
    }
}
