<?php

namespace Opencast\Models;

use Opencast\RelationshipTrait;
use Opencast\Models\UPMap;

class Templates extends UPMap
{
    use RelationshipTrait;

    protected static function configure($config = array())
    {
        $config['db_table'] = 'du_templates';

        parent::configure($config);
    }

    public function getRelationships()
    {
        return [];
    }
}
