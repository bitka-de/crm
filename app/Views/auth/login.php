<?php

declare(strict_types=1);
?>
<div class="component-stack">
    <?= $this->component('hero', [
        'kicker'      => 'Anmeldung',
        'title'       => 'Willkommen',
        'description' => 'Melden Sie sich mit Ihren Zugangsdaten an.',
    ]) ?>

    <?php if (!empty($error)): ?>
        <div class="auth-error">
            <?= $this->escape((string) $error) ?>
        </div>
    <?php endif; ?>

    <form class="auth-form" method="post" action="/login">
        <div class="form-field">
            <label for="username">Benutzername</label>
            <input type="text" id="username" name="username"
                   autocomplete="username" required>
        </div>
        <div class="form-field">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password"
                   autocomplete="current-password" required>
        </div>
        <button type="submit">Anmelden</button>
    </form>
</div>
