<?php declare(strict_types=1);
namespace App\Application\Middleware;

use App\Domain\DomainException\AuthorizationException;
use Medoo\Medoo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class PermissionMiddleware implements MiddlewareInterface {
    private string $permission;

    /**
     * PermissionMiddleware constructor.
     */
    public function __construct(string $permission) {
        $this->permission = $permission;
    }

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $user = $_SESSION['user'];

        $hasPermission = in_array(['*', $this->permission], $user['permissions']);

        if (!$hasPermission) {
            throw new AuthorizationException("Missing permission: $this->permission");
        }

        return $handler->handle($request);
    }
}