<?php

namespace Backend\Routes\##UTemplate##;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\Errors\Error;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\##UTemplate##;

class ##UTemplate##Show extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $##Template## = ##UTemplate##::where('id', $args['id'])->first();

        if ($##Template##) {
            return $this->createResponse(['##Template##' => $##Template##->toArray()], $response);
        }

        throw new Error('##UTemplate## not found', 404);
    }
}
