<?php
declare(strict_types=1);

use App\Application\Actions\Test\AuthenticatorAction;
use App\Application\Actions\Test\TestAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Middleware\AuthenticatorMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/', TestAction::class);
    $app->get('/l', AuthenticatorAction::class);

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    })->addMiddleware(new AuthenticatorMiddleware());
};
