<?php


namespace App\Application\Actions\Test;


use App\Application\Actions\Action;
use App\Domain\DomainException\AuthorizationException;
use App\Domain\DomainException\DomainRecordNotFoundException;
use Google\Authenticator\GoogleAuthenticator;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class AuthenticatorAction extends Action {

    private GoogleAuthenticator $authenticator;

    public function __construct(LoggerInterface $logger, Medoo $medoo, GoogleAuthenticator $authenticator) {
        parent::__construct($logger, $medoo);
        $this->authenticator = $authenticator;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    protected function action(): Response {
        // TODO: Implement action() method.

        if ($_SESSION['authorized'] === true) {
            return $this->respondWithData([
                'authorized' => true,
                'user' => $_SESSION['user']
            ]);
        }

        if (empty($_REQUEST['code'])) {
            throw new AuthorizationException('Missing code property!');
        }

        $code = $_REQUEST['code'];

        $possibleSecrets = $this->medoo->select('authenticator_secrets', [
            '[><]users' => [
                'user_id' => 'id'
            ]
        ], [
            'given_name',
            'family_name',
            'value'
        ]);

        foreach ($possibleSecrets as $secretArray) {
            if ($this->authenticator->checkCode($secretArray['value'], $code)) {
                $_SESSION['authorized'] = true;
                $_SESSION['user'] = [
                    'given_name' => $secretArray['given_name'],
                    'family_name' => $secretArray['family_name']
                ];

                return $this->respondWithData([
                    'authorized' => $_SESSION['authorized'],
                    'user' => $_SESSION['user']
                ]);
            }
        }

        return $this->respondWithData([
            'authorized' => false,
        ]);
    }
}