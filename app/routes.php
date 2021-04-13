<?php declare(strict_types=1);

use App\Application\Actions\Authenticate\LoginAction;
use App\Application\Actions\Authenticate\LogoutAction;
use App\Application\Actions\Install\InstallAction;
use App\Application\Actions\Test\TestAction;
use App\Application\Middleware\AuthenticatorMiddleware as AM;
use App\Application\Middleware\PermissionMiddleware as PM;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/test', TestAction::class);
    $app->get('/install', InstallAction::class);

    $app->group('/authenticate', function (Group $group) {
        $group->any('/login', LoginAction::class);
        $group->any('/logout', LogoutAction::class);
    });

    $app->group('/users', function (Group $users) {
        $users->any('/me', function (Group $ownUser) {
            $ownUser->any('', GetUserInfoOwnAction::class);
            $ownUser->group('/secrets', function (Group $ownSecret) {
                $ownSecret->any('', ListSecretsOwnAction::class);
                $ownSecret->any('/create', CreateSecretOwnAction::Class);
                $ownSecret->any('/delete', DeleteSecretOwnAction::Class);
            });
            $ownUser->group('/data', function (Group $ownData) {
                $ownData->any('', GetUserDataOwnAction::class);
                $ownData->any('/update', UpdateUserDataOwnAction::class);
            });
        });

        $users->any('/{user_id}', function (Group $otherUser) {
            $otherUser->any('', GetUserInfoOtherAction::class);
            $otherUser->group('/secrets', function (Group $otherSecret) {
                $otherSecret->any('', ListSecretsOtherAction::class);
                $otherSecret->any('/create', CreateSecretOtherAction::Class);
                $otherSecret->any('/delete', DeleteSecretOtherAction::Class);
            });
            $otherUser->group('/data', function (Group $otherData) {
                $otherData->any('', GetUserDataOtherAction::class);
                $otherData->any('/update', UpdateUserDataOtherAction::class);
            });
        })->addMiddleware(new PM('MANAGE_USER_OTHERS'));

        $users->any('', GetUsersInfoOtherAction::class)->addMiddleware(new PM('MANAGE_USER_OTHERS'));
    })->add(AM::class);

    $app->group('/sailors', function (Group $sailors) {
        $sailors->any('/{sailor_id}', function (Group $sailor) {
            $sailor->any('', GetSailorAction::class);
        });

        $sailors->any('', ListSailorsAction::class);
    });

    $app->group('/regattas', function (Group $regattas) {
        $regattas->any('/{regatta_id}', function (Group $regatta) {
            $regatta->any('', GetRegattaAction::class);
            $regatta->any('/update', UpdateRegattaAction::class)->add(AM::class);
            $regatta->any('/delete', DeleteRegattaAction::class)->add(AM::class);
        });

        $regattas->any('', ListRegattasAction::class);
    });

    $app->group('/calendars', function (Group $calendars) {
       $calendars->any('/{year}', function (Group $calendar) {
           $calendar->any('', GetCalendarAction::class);
           $calendar->any('/update', UpdateRegattaAction::class)->add(AM::class);
           $calendar->any('/delete', DeleteRegattaAction::class)->add(AM::class);
       });

        $calendars->any('', ListCalendarsAction::class);
    });

    $app->group('/cups', function (Group $cups) {
        $cups->any('/{year}', function (Group $cup) {
            $cup->any('', GetCupAction::class);
            $cup->any('/update', UpdateCupAction::class)->add(AM::class);
            $cup->any('/delete', DeleteCupAction::class)->add(AM::class);
        });
    });

    $app->group('/results', function (Group $results) {
        $results->any('/{regatta_id}', function (Group $result) {
            $result->any('', GetResultAction::class);
            $result->any('/update', UpdateResultAction::class);
            $result->any('/create', CreateResultAction::class);
            $result->any('/delete', DeleteResultAction::class);
        });
    })->add(AM::class);

    $app->group('/declarations', function (Group $declarations) {
        $declarations->any('/{id}', function (Group $result) {
            $result->any('', GetDeclarationAction::class);
            $result->any('/update', UpdateDeclarationAction::class);
            $result->any('/create', CreateDeclarationAction::class);
            $result->any('/delete', DeleteDeclarationAction::class);
        });
    })->add(AM::class);
};
