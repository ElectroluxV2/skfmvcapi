<?php declare(strict_types=1);
namespace App\Application\Actions\User;

use App\Domain\DomainException\AuthorizationException;
use App\Domain\DomainException\ParameterException;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteUserAction extends UserAction {

    /**
     * @inheritDoc
     * @throws AuthorizationException
     * @throws ParameterException
     */
    protected function action(): Response {
        $issuerPermission = $_SESSION['user']['permission_level'];

        if ($issuerPermission < 50) throw new AuthorizationException("your permission level is too low!");

        $userId = $this->request->getAttribute('user_id');
        $userToDelete = $this->medoo->get('users', [
            'permission_level'
        ],[
            'id' => $userId
        ]);

        if (empty($userToDelete)) throw new ParameterException("there is no user with id " . $userId);

        if ($userToDelete['permission_level'] >= $issuerPermission) throw new AuthorizationException("your permission level is too low!");

        $this->medoo->delete('users', [
            'id' => $userId
        ]);

        return $this->respondWithData([
            'success'
        ]);
    }
}