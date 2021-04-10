<?php declare(strict_types=1);
namespace App\Application\Actions\Authenticate;

use App\Application\Actions\Action;
use Google\Authenticator\GoogleAuthenticator;
use JetBrains\PhpStorm\Pure;
use Medoo\Medoo;
use Psr\Log\LoggerInterface;

abstract class AuthenticateAction extends Action {
    protected GoogleAuthenticator $authenticator;

    #[Pure] public function __construct(LoggerInterface $logger, Medoo $medoo, GoogleAuthenticator $authenticator) {
        parent::__construct($logger, $medoo);
        $this->authenticator = $authenticator;
    }

}
