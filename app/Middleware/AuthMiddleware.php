<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\MiddlewareInterface;
use App\Core\Session;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): ?string
    {
        Session::start();

        if (Auth::check()) {
            return null;
        }

        http_response_code(302);
        header('Location: /login');

        return '';
    }
}
