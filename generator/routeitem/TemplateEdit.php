<?php

namespace Backend\Routes\##UTemplate##;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Backend\Errors\AuthorizationFailedException;
use Backend\BackendTrait;
use Backend\BackendController;
use Backend\Models\##UTemplate##;

class ##UTemplate##Edit extends BackendController
{
    use BackendTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->getRequestData($request);

        $##Template## = ##UTemplate##::where('id', $args['id'])->first();

        foreach ($json['##Template##'] as $attr => $val) {
            if (isset($##Template##->$attr)) {
                $##Template##->$attr = $val;
            }
        }

        $##Template##->save();

        return $response->withStatus(204);
    }
}
