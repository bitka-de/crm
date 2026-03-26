<?php

declare(strict_types=1);

/** @var string $user */
/** @var array<int, array{label: string, href: string, hint: string}> $quickActions */
/** @var array<int, array{name: string, status: string, description: string}> $systemModules */
/** @var array<int, array{label: string, value: string}> $kpis */
?>
<div class="clean-dashboard">
    <header class="clean-dashboard-head">
        <h1>Dashboard</h1>
        <p>Willkommen, <?= $this->escape($user) ?></p>
    </header>

    <section class="clean-kpis" aria-label="Kennzahlen">
        <?php foreach ($kpis as $kpi): ?>
            <article class="clean-kpi-card">
                <p class="clean-kpi-label"><?= $this->escape($kpi['label']) ?></p>
                <p class="clean-kpi-value"><?= $this->escape($kpi['value']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="clean-columns">
        <article class="clean-card">
            <header class="clean-card-head">
                <h2>Quick Actions</h2>
            </header>
            <div class="clean-action-list">
                <?php foreach ($quickActions as $action): ?>
                    <a href="<?= $this->escape($action['href']) ?>" class="clean-action-item">
                        <strong><?= $this->escape($action['label']) ?></strong>
                        <span><?= $this->escape($action['hint']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="clean-card">
            <header class="clean-card-head">
                <h2>Module</h2>
            </header>
            <ul class="clean-module-list">
                <?php foreach ($systemModules as $module): ?>
                    <li>
                        <div>
                            <p class="clean-module-name"><?= $this->escape($module['name']) ?></p>
                            <p class="clean-module-description"><?= $this->escape($module['description']) ?></p>
                        </div>
                        <span class="clean-status-badge"><?= $this->escape($module['status']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </article>
    </section>
</div>
