<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Core\View;

final class AuthController
{
    public function showLogin(): string
    {
        return (new View())->render('auth/login', [
            'title' => 'Anmelden',
            'error' => Session::get('auth_error'),
        ]);
    }

    public function login(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($username, $password)) {
            Session::remove('auth_error');
            http_response_code(302);
            header('Location: /dashboard');
            exit;
        }

        Session::set('auth_error', 'Ungueltige Zugangsdaten. Bitte erneut versuchen.');
        http_response_code(302);
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        http_response_code(302);
        header('Location: /login');
        exit;
    }
}
