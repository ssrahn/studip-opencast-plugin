<?php
namespace Opencast\Models;

use Opencast\RelationshipTrait;
use Opencast\Models\UPMap;

class Config extends UPMap
{
    use RelationshipTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'oc_config';

        parent::configure($config);
    }

    public function getRelationships()
    {
        return [];
    }
}
