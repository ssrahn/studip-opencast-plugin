<?php

class IndexController extends Opencast\Controller
{
    public function index_action()
    {
        Navigation::activateItem('course/opencast/overview');

        // starting point for vue app
    }
}
