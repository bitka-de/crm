<?php

declare(strict_types=1);

/** @var string $kicker */
/** @var string $title */
/** @var string $description */
?>
<section class="hero">
    <p class="hero-kicker"><?= $this->escape($kicker) ?></p>
    <h1 class="hero-title"><?= $this->escape($title) ?></h1>
    <p class="hero-description"><?= $this->escape($description) ?></p>
</section>