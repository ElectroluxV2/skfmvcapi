<?php declare(strict_types=1);
namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class DeleteUserAction extends UserAction {

    /**
     * @inheritDoc
     */
    protected function action(): Response {
        // TODO: Implement action() method.

        return $this->respondWithData([]);
    }
}