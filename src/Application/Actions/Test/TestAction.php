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
        // TODO: Implement action() method.

        $this->medoo->create('users', [
            'id' => [
               'serial',
               'primary key'
            ],
            'given_name' => [
                'varchar (255)',
                'not null'
            ],
            'family_name' => [
                'varchar (255)',
                'not null'
            ]
        ]);

        $this->medoo->create('authenticator_secrets', [
            'user_id' => [
                'integer',
                'references users (id)'
            ],
            'value' => [
                'varchar (16)',
                'unique',
                'not null'
            ]
        ]);

        /*$this->medoo->insert('users', [
           'given_name' => 'Paweł',
           'family_name' => 'Tarnawski'
        ]);*/

        $secret = $this->authenticator->generateSecret();

        $this->medoo->insert('authenticator_secrets', [
            'user_id' => '5',
            'value' => $secret,
        ]);

        $url = GoogleQrUrl::generate('Paweł Tarnawski', $secret, 'openskiff.pl');

        return $this->respondWithData([
            'secret' => $secret,
            'url' => $url
        ]);
    }
}