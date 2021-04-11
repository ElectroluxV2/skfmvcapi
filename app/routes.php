<?php declare(strict_types=1);

use App\Application\Actions\Authenticate\DeleteAuthenticateSecretAction;
use App\Application\Actions\Authenticate\GenerateAuthenticateSecretAction;
use App\Application\Actions\Authenticate\LoginAction;
use App\Application\Actions\Authenticate\LogoutAction;
use App\Application\Actions\Install\InstallAction;
use App\Application\Actions\Test\TestAction;
use App\Application\Actions\User\CreateUserAction;
use App\Application\Actions\User\DeleteUserAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\UpdateUserAction;
use App\Application\Middleware\AuthenticatorMiddleware;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/test', TestAction::class);
    $app->get('/install', InstallAction::class);

    $app->group('/authenticate', function (Group $group) {
        $group->any('/login', LoginAction::class);
        $group->any('/logout', LogoutAction::class);
        $group->group('/secrets', function (Group $codeGroup) {
           $codeGroup->any('/generate/{user_id}', GenerateAuthenticateSecretAction::class);
           $codeGroup->any('/delete/{secret_id}', DeleteAuthenticateSecretAction::class);
        })->addMiddleware(new AuthenticatorMiddleware());
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->any('/create', CreateUserAction::class);
        $group->any('/update/{user_id}', UpdateUserAction::class);
        $group->any('/delete/{user_id}', DeleteUserAction::class);
    })->addMiddleware(new AuthenticatorMiddleware());
};
