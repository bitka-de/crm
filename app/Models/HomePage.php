<?php

declare(strict_types=1);

namespace App\Models;

final class HomePage
{
    public function __construct(
        private string $title,
        private string $headline,
        private string $description
    ) {
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headline(): string
    {
        return $this->headline;
    }

    public function description(): string
    {
        return $this->description;
    }
}