<?php declare(strict_types=1);

use App\Application\Actions\Authenticate\DeleteAuthenticateCodeAction;
use App\Application\Actions\Authenticate\GenerateAuthenticateCodeAction;
use App\Application\Actions\Authenticate\LoginAction;
use App\Application\Actions\Authenticate\LogoutAction;
use App\Application\Actions\Install\InstallAction;
use App\Application\Actions\Test\TestAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Middleware\AuthenticatorMiddleware;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/test', TestAction::class);
    $app->get('/install', InstallAction::class);

    $app->group('/authenticate', function (Group $group) {
        $group->any('/login', LoginAction::class);
        $group->any('/logout', LogoutAction::class);
        $group->group('/code', function (Group $codeGroup) {
           $codeGroup->any('/generate', GenerateAuthenticateCodeAction::class);
           $codeGroup->any('/delete', DeleteAuthenticateCodeAction::class);
        })->addMiddleware(new AuthenticatorMiddleware());
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
    })->addMiddleware(new AuthenticatorMiddleware());
};
