<?php declare(strict_types=1);
namespace App\Application\Actions\Authenticate;

use App\Domain\DomainException\AuthorizationException;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;

class LoginAction extends AuthenticateAction {

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    protected function action(): Response {
        // Do not double verify
        if ($_SESSION['authorized'] === true) {
            return $this->respondWithData([
                'authorized' => true,
                'user' => $_SESSION['user']
            ]);
        }

        $data = $this->request->getParsedBody();

        if (empty($data['code'])) {
            throw new AuthorizationException('Missing code property!');
        }

        $code = $data['code'];

        if (!is_numeric($code)) {
            throw new AuthorizationException('Code must be numeric!');
        }

        if (strlen($code) !== 6) {
            throw new AuthorizationException('Code must be 6 digits length!');
        }

        $possibleSecrets = $this->medoo->select('authenticator_secrets', [
            '[><]users' => [
                'user_id' => 'id'
            ]
        ], [
            'user_id',
            'given_name',
            'family_name',
            'value'
        ]);

        // TODO: prevent logging into someones other account
        foreach ($possibleSecrets as $secretArray) {
            if ($this->authenticator->checkCode($secretArray['value'], $code)) {
                $_SESSION['authorized'] = true;
                $_SESSION['user'] = [
                    'id' => $secretArray['user_id'],
                    'given_name' => $secretArray['given_name'],
                    'family_name' => $secretArray['family_name']
                ];

                // Get permissions
                $permissions = $this->medoo->select('permission_types', [
                    '[>]user_permission' => [
                        'id' => 'permission_id'
                    ],
                    '[>]users' => [
                        'user_permission.user_id' => 'id'
                    ]
                ], [
                    'permission_types.name',
                    'permission_types.id'
                ], [
                    'users.id' => 1
                ]);

                $_SESSION['user']['permissions'] = $permissions;

                // Update last active
                $now = new DateTime();
                $this->medoo->update('authenticator_secrets', [
                    'last_active' => $now->format('Y-m-d H:i:s')
                ], [
                    'value' => $secretArray['value']
                ]);

                return $this->respondWithData([
                    'authorized' => $_SESSION['authorized'],
                    'user' => $_SESSION['user']
                ]);
            }
        }

        // No matches
        throw new AuthorizationException('Invalid data!');
    }
}