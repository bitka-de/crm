<?php

declare(strict_types=1);

use App\Core\Auth;

/** @var string $content */
/** @var string $title */

$isLoggedIn = Auth::check();
$currentPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';

$navItems = [
    ['label' => 'Dashboard', 'href' => '/dashboard'],
    ['label' => 'Company', 'href' => '/company'],
    ['label' => 'Kontakte', 'href' => '/contacts'],
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->escape($title ?? 'CRM') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
<?php if ($isLoggedIn): ?>
    <div class="app-root">
        <nav class="app-topnav" aria-label="Hauptnavigation">
            <a href="/dashboard" class="app-topnav-brand" aria-label="Dashboard">
                <span class="app-topnav-logo">CRM</span>
                <p class="app-topnav-name">Mein Unternehmen</p>
            </a>

            <div class="app-topnav-links">
                <?php foreach ($navItems as $item): ?>
                    <a
                        href="<?= $this->escape($item['href']) ?>"
                        class="app-topnav-link <?= $currentPath === $item['href'] ? 'is-active' : '' ?>"
                    >
                        <?= $this->escape($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="app-topnav-right">
                <span class="app-topnav-user"><?= $this->escape((string) (Auth::user() ?? 'User')) ?></span>
                <form method="post" action="/logout">
                    <button type="submit" class="logout-btn">Log out</button>
                </form>
            </div>
        </nav>

        <main class="app-main page-content">
            <?= $content ?>
        </main>
    </div>
<?php else: ?>
    <div class="guest-root">
        <main class="guest-card">
            <?= $content ?>
        </main>
    </div>
<?php endif; ?>
</body>
</html>