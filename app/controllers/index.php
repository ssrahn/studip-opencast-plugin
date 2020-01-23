<?php

class IndexController extends Opencast\Controller
{
    public function index_action()
    {
        Navigation::activateItem('course/opencast/overview');
        PageLayout::addStylesheet($this->plugin->getPluginURL().'/assets/css/unterrichtsplanung.css');

        // starting point for vue app
    }
}
