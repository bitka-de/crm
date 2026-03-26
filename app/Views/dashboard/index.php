<?php

declare(strict_types=1);

/** @var string $user */
?>
<div class="component-stack">
    <?= $this->component('hero', [
        'kicker'      => 'Dashboard',
        'title'       => 'Guten Tag, ' . $this->escape($user) . '.',
        'description' => 'Willkommen im CRM. Hier findet ihr eine Uebersicht aller wichtigen Bereiche.',
    ]) ?>

    <section class="panel-grid">
        <?= $this->component('panel', [
            'title' => 'Kunden',
            'copy'  => 'Verwalte alle Kundenkontakte an einem Ort.',
        ]) ?>

        <?= $this->component('panel', [
            'title' => 'Aufgaben',
            'copy'  => 'Behalte offene Aufgaben und Wiedervorlagen im Blick.',
        ]) ?>

        <?= $this->component('panel', [
            'title' => 'Berichte',
            'copy'  => 'Analysiere Aktivitaeten und exportiere Daten.',
        ]) ?>
    </section>

    <form method="post" action="/logout" style="padding: 8px 0;">
        <button type="submit" class="logout-btn">Abmelden</button>
    </form>
</div>
