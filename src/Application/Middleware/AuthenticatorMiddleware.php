<?php


namespace App\Application\Middleware;


use App\Domain\DomainException\AuthorizationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticatorMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     * @throws AuthorizationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        // TODO: Implement process() method.

        if ($_SESSION['authorized'] !== true) {
            throw new AuthorizationException('Not authorized!');
        }

        return $handler->handle($request);
    }
}