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
    <div class="clean-system-root">
        <div class="clean-system-layout">
            <aside class="clean-sidebar">
                <div class="clean-sidebar-head">
                    <p class="page-brand">CRM</p>
                    <p class="system-brand-subline">Core System</p>
                </div>

                <nav class="clean-sidebar-nav" aria-label="Hauptnavigation">
                    <?php foreach ($navItems as $item): ?>
                        <a
                            href="<?= $this->escape($item['href']) ?>"
                            class="clean-sidebar-link <?= $currentPath === $item['href'] ? 'is-active' : '' ?>"
                        >
                            <?= $this->escape($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="clean-sidebar-foot">
                    <p class="clean-user"><?= $this->escape((string) (Auth::user() ?? 'User')) ?></p>
                    <form method="post" action="/logout">
                        <button type="submit" class="logout-btn">Log out</button>
                    </form>
                </div>
            </aside>

            <main class="page-content clean-main">
                <?= $content ?>
            </main>
        </div>
    </div>
<?php else: ?>
    <div class="clean-guest-root">
        <header class="page-nav system-topbar">
            <div class="system-brand-block">
                <p class="page-brand">CRM System</p>
                <p class="system-brand-subline">Schrittweise erweiterbare Plattform</p>
            </div>
            <span class="page-badge">PHP MVC</span>
        </header>

        <main class="page-content">
            <?= $content ?>
        </main>
    </div>
<?php endif; ?>
</body>
</html>