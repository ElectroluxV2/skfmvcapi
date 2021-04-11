<?php declare(strict_types=1);

namespace App\Application\Actions\Authenticate;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteAuthenticateSecretAction extends AuthenticateAction {

    /**
     * @inheritDoc
     */
    protected function action(): Response {



        return $this->respondWithData([]);
    }
}