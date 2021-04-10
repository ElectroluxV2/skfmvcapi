<?php declare(strict_types=1);
namespace App\Application\Actions\Authenticate;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

class LogoutAction extends Action {

    protected function action(): Response {
        session_unset();

        return $this->respondWithData([
            'authorized' => false,
        ]);
    }
}