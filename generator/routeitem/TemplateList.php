<?php

namespace Backend\Routes\##UTemplate##;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\Errors\Error;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\##UTemplate##;

class ##UTemplate##List extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $##Template## = ##UTemplate##::all()->toArray();

        if (!empty($##Template##)) {
            return $this->createResponse($##Template##, $response);
        }

        return $this->createResponse([], $response);
    }
}
