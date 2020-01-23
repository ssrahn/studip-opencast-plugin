<?php

namespace Backend\Routes\Config;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\Errors\Error;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\Config;

class ConfigList extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $config = Config::all()->toArray();

        if (!empty($config)) {
            return $this->createResponse($config, $response);
        }

        return $this->createResponse([], $response);
    }
}
