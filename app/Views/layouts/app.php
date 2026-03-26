<?php

declare(strict_types=1);

/** @var string $content */
/** @var string $title */
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
<div class="page-shell">
    <header class="page-nav">
        <p class="page-brand">CRM Platform</p>
        <span class="page-badge">MVC mit Layouts und Komponenten</span>
    </header>

    <main class="page-content">
        <?= $content ?>
    </main>
</div>
</body>
</html>