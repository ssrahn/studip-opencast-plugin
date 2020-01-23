<?php

class AdminController extends Opencast\Controller
{
    public function index_action()
    {
        Navigation::activateItem('/admin/config/oc-config');

        // starting point for vue app
    }
}
