<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\HomePage;

final class HomeController
{
    public function index(): string
    {
        $page = new HomePage(
            'CRM Startseite',
            'Willkommen im CRM',
            'Die Startseite wird jetzt ueber ein einfaches Model-View-Controller-System mit Layout und wiederverwendbaren Komponenten gerendert.'
        );

        return (new View())->render('home/index', [
            'page' => $page,
            'title' => $page->title(),
        ]);
    }
}