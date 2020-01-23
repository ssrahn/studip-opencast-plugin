<?php

namespace Backend\Routes\Config;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\Errors\Error;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\Config;

class ConfigDelete extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $config = Config::where('id', $args['id'])->first();
        if ($config == null)
        {
            throw new Error('config not found.', 404);
        }

        if (!$config->delete()) {
            throw new Error('Could not delete config.', 500);
        }

        return $response->withStatus(204);
    }
}
