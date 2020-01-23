<?

define('BACKEND_PATH', __DIR__ . '/../lib/');
define('FRONTEND_PATH', __DIR__ . '/../vueapp/');

function template($template)
{
    global $tmpl;

    foreach ($tmpl as $name => $text) {
        $template = str_replace('##' . $name . '##', $text, $template);
    }

    return $template;
}

$tmpl = [
    'Template'   => strtolower($argv[1]),
    'UTemplate'  => ucfirst($argv[1]),
    'UUTemplate' => strtoupper($argv[1])
];

$name  = strtolower($argv[1]);
$uname = ucfirst($argv[1]);


// slim routes template
$routes  = "\$this->app->get('/${name}', Routes\\${uname}\\${uname}List::class);\n";
$routes .= "        \$this->app->get('/${name}/{id}', Routes\\${uname}\\${uname}Show::class);\n";
$routes .= "        \$this->app->post('/${name}', Routes\\${uname}\\${uname}Add::class);\n";
$routes .= "        \$this->app->put('/${name}/{id}', Routes\\${uname}\\${uname}Edit::class);\n";
$routes .= "        \$this->app->delete('/${name}/{id}', Routes\\${uname}\\${uname}Delete::class);\n";
$routes .= "\n        ##TEMPLATE##";

// add routes to Slim
file_put_contents(BACKEND_PATH . 'RouteMap.php',
    str_replace('##TEMPLATE##', $routes, file_get_contents(BACKEND_PATH . 'RouteMap.php'))
);

// add route files
$files = ['TemplateAdd.php', 'TemplateDelete.php', 'TemplateEdit.php', 'TemplateList.php', 'TemplateShow.php'];

@mkdir(BACKEND_PATH . 'Routes/'. $uname);

foreach ($files as $file) {
    $filename = str_replace('Template', $uname, $file);

    file_put_contents(BACKEND_PATH . 'Routes/'. $uname . '/'. $filename,
        template(
            file_get_contents(__DIR__ . '/routeitem/'. $file)
        )
    );
}

// add model
$model = "<?php
namespace Opencast\Models;

use Opencast\RelationshipTrait;
use Opencast\Models\UPMap;

class $uname extends UPMap
{
    use RelationshipTrait;

    protected static function configure(\$config = [])
    {
        \$config['db_table'] = 'oc_$name';

        parent::configure(\$config);
    }

    public function getRelationships()
    {
        return [];
    }
}
";

file_put_contents(BACKEND_PATH . 'Models/' . $uname . '.php', $model);

// add frontend store api
file_put_contents(FRONTEND_PATH . 'store/'. $name . '.module.js',
    template(
        file_get_contents(__DIR__ . '/frontend/template.module.js')
    )
);

file_put_contents(FRONTEND_PATH . 'store/mutations.type.js',
    file_get_contents(FRONTEND_PATH . '/store/mutations.type.js') .
    template(
        file_get_contents(__DIR__ . '/frontend/mutations.type.js')
    )
);

file_put_contents(FRONTEND_PATH . 'store/actions.type.js',
    file_get_contents(FRONTEND_PATH . '/store/actions.type.js') .
    template(
        file_get_contents(__DIR__ . '/frontend/actions.type.js')
    )
);
