<?php declare(strict_types=1);
namespace App\Application\Actions\User;

use App\Domain\DomainException\AuthorizationException;
use App\Domain\DomainException\ParameterException;
use Psr\Http\Message\ResponseInterface as Response;

class CreateUserAction extends UserAction {

    /**
     * @inheritDoc
     * @throws ParameterException
     * @throws AuthorizationException
     */
    protected function action(): Response {
        $data = $this->request->getParsedBody();
        $issuerPermission = $_SESSION['user']['permission_level'];

        if ($issuerPermission < 50) throw new AuthorizationException("your permission level is too low!");

        if (!isset($data['given_name'])) throw new ParameterException("field given_name was not provided!");
        if (!isset($data['family_name'])) throw new ParameterException("field family_name was not provided!");
        if (!isset($data['permission_level'])) throw new ParameterException("field permission_level was not provided!");

        if (empty($data['given_name'])) throw new ParameterException("field given_name was empty!");
        if (empty($data['family_name'])) throw new ParameterException("field family_name was empty!");
        if (empty($data['permission_level'])) throw new ParameterException("field permission_level was empty!");

        if (strlen($data['given_name']) > 255) throw new ParameterException("field given_name was too long!");
        if (strlen($data['family_name']) > 255) throw new ParameterException("field family_name was too long!");
        if (!is_numeric($data['permission_level'])) throw new ParameterException("field permission_level must be numeric!");

        if ($data['permission_level'] > $issuerPermission) throw new AuthorizationException("your permission level is too low!");

        $this->medoo->insert('users', [
            'given_name' => $data['given_name'],
            'family_name' => $data['family_name'],
            'permission_level' => $data['permission_level']
        ]);

        return $this->respondWithData([
            'user_id' => $this->medoo->id()
        ]);
    }
}