<?php

namespace Opencast\Routes\Config;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Opencast\Errors\AuthorizationFailedException;
use Opencast\OpencastTrait;
use Opencast\OpencastController;
use Opencast\Models\Config;

class ConfigAdd extends OpencastController
{
    use OpencastTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        \SimpleOrMap::expireTableScheme();
        $json = $this->getRequestData($request);

        $config = new Config;

        $config->config = json_encode($json['config']);

        $config->store();

        return $this->createResponse(['config' => $config], $response);
    }
}
