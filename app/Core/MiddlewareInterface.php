<?php

declare(strict_types=1);

namespace App\Core;

interface MiddlewareInterface
{
    /**
     * Verarbeitet die Anfrage.
     * Gibt null zurueck, um die naechste Stufe aufzurufen.
     * Gibt einen String zurueck, um die Verarbeitung abzubrechen.
     */
    public function handle(): ?string;
}
