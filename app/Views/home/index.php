<?php

declare(strict_types=1);

use App\Models\HomePage;

/** @var HomePage $page */
?>
<div class="component-stack">
    <?= $this->component('hero', [
        'kicker' => 'Startseite',
        'title' => $page->headline(),
        'description' => $page->description(),
    ]) ?>

    <section class="panel-grid">
        <?= $this->component('panel', [
            'title' => 'Layout-System',
            'copy' => 'Gemeinsame Seitenstruktur, Metadaten und Styling liegen jetzt zentral im Layout und muessen nicht in jeder View dupliziert werden.',
        ]) ?>

        <?= $this->component('panel', [
            'title' => 'Komponenten',
            'copy' => 'Wiederverwendbare UI-Bloecke wie Hero, Karten oder Listen koennen als eigene Templates gekapselt und in Views kombiniert werden.',
        ]) ?>
    </section>
</div>