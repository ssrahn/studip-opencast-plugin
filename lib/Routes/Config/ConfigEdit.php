<?php

namespace Backend\Routes\Config;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\Config;

class ConfigEdit extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->getRequestData($request);

        $config = Config::where('id', $args['id'])->first();

        foreach ($json['config'] as $attr => $val) {
            if (isset($config->$attr)) {
                $config->$attr = $val;
            }
        }

        $config->save();

        return $response->withStatus(204);
    }
}
