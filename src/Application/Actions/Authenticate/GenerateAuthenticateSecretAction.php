<?php declare(strict_types=1);
namespace App\Application\Actions\Authenticate;

use Psr\Http\Message\ResponseInterface as Response;

class GenerateAuthenticateSecretAction extends AuthenticateAction {

    /**
     * @inheritDoc
     */
    protected function action(): Response {
        // TODO: Implement action() method.
        return $this->respondWithData([]);
    }
}