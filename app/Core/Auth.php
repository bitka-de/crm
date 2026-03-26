<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    /**
     * Demo-Benutzer. In Produktion: Datenbank mit password_hash() und password_verify() verwenden.
     * @var array<string, string>
     */
    private static array $users = [];

    /**
     * @return array<string, string>
     */
    private static function users(): array
    {
        if (empty(self::$users)) {
            self::$users = [
                'admin' => password_hash('1234', PASSWORD_BCRYPT, ['cost' => 4]),
            ];
        }

        return self::$users;
    }

    public static function attempt(string $username, string $password): bool
    {
        $hash = self::users()[$username] ?? null;

        if ($hash === null || !password_verify($password, $hash)) {
            return false;
        }

        Session::set('auth_user', $username);

        return true;
    }

    public static function check(): bool
    {
        return Session::has('auth_user');
    }

    public static function user(): ?string
    {
        return Session::get('auth_user');
    }

    public static function logout(): void
    {
        Session::remove('auth_user');
    }
}
