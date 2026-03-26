<?php

declare(strict_types=1);

/** @var string $title */
/** @var string $copy */
?>
<article class="panel">
    <h2 class="panel-title"><?= $this->escape($title) ?></h2>
    <p class="panel-copy"><?= $this->escape($copy) ?></p>
</article>