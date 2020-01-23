<?php

namespace Backend\Routes\Config;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\Config;

class ConfigAdd extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->getRequestData($request);

        $config = new Config;

        foreach ($json['config'] as $attr => $val) {
            $config->$attr = $val;
        }

        $config->save();

        return $this->createResponse(['config' => $config], $response);
    }
}
