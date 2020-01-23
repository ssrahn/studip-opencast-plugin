<?php

namespace Backend\Routes\Config;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\Errors\Error;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\Config;

class ConfigShow extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $config = Config::where('id', $args['id'])->first();

        if ($config) {
            return $this->createResponse(['config' => $config->toArray()], $response);
        }

        throw new Error('Config not found', 404);
    }
}
