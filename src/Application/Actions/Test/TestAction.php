<?php


namespace App\Application\Actions\Test;


use App\Application\Actions\Action;
use Google\Authenticator\GoogleAuthenticator;
use Google\Authenticator\GoogleQrUrl;
use JetBrains\PhpStorm\Pure;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class TestAction extends Action {

    private GoogleAuthenticator $authenticator;

    #[Pure] public function __construct(LoggerInterface $logger, Medoo $medoo, GoogleAuthenticator $authenticator) {
        parent::__construct($logger, $medoo);
        $this->authenticator = $authenticator;
    }

    /**
     * @inheritDoc
     */
    protected function action(): Response {
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

        return $this->respondWithData(['perms' => $permissions]);

    }
}