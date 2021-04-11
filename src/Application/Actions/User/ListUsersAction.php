<?php declare(strict_types=1);
namespace App\Application\Actions\User;

use Psr\Http\Message\ResponseInterface as Response;

class ListUsersAction extends UserAction {
    /**
     * {@inheritdoc}
     */
    protected function action(): Response {

        $users = $this->medoo->select('users', [
            'id',
            'given_name',
            'family_name',
        ]);

        foreach ($users as $index => $user) {
            $authenticatorSecrets = $this->medoo->select('authenticator_secrets', [
                'id',
                'created',
                'last_active'
            ], [
                'user_id' => $user['id']
            ]);

            $users[$index]['authenticator_secrets'] = $authenticatorSecrets;
        }

        return $this->respondWithData(['users' => $users]);
    }
}
