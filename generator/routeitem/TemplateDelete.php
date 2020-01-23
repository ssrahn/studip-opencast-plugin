<?php

namespace Backend\Routes\##UTemplate##;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\Errors\Error;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\##UTemplate##;

class ##UTemplate##Delete extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $##Template## = ##UTemplate##::where('id', $args['id'])->first();
        if ($##Template## == null)
        {
            throw new Error('##Template## not found.', 404);
        }

        if (!$##Template##->delete()) {
            throw new Error('Could not delete ##Template##.', 500);
        }

        return $response->withStatus(204);
    }
}
