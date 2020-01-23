<?php

class AdminController extends Opencast\Controller
{
    public function index_action()
    {
        Navigation::activateItem('/admin/config/oc-config');

        Helpbar::get()->addPlainText('', $_('Hier wird die Anbindung zum Opencast System verwaltet.
        Geben Sie die Daten ihres Opencast-Systems ein, um Aufzeichnungen in Ihren Veranstaltungen bereitstellen zu können.
        Optional haben Sie die Möglichkeit, ein zweites Opencast-System im Nur-Lesen-Modus anzubinden.')
        );

        // starting point for vue app
    }
}
