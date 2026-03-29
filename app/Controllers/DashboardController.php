<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;

final class DashboardController
{
    public function index(): string
    {
        $quickActions = [
            [
                'label' => 'Unternehmensdaten',
                'href' => '/company',
                'hint' => 'Stammdaten und Rechtsform pflegen',
            ],
            [
                'label' => 'Kontakte',
                'href' => '/contacts',
                'hint' => 'Kontakte und Status verwalten',
            ],
            [
                'label' => 'Startseite',
                'href' => '/',
                'hint' => 'Zur Gesamtuebersicht wechseln',
            ],
        ];

        $systemModules = [
            [
                'name' => 'Kontaktmanagement',
                'status' => 'Bereit',
                'description' => 'Kontakte erfassen, mit Zusatzfeldern erweitern und Statuslisten flexibel verwalten.',
            ],
            [
                'name' => 'Aufgabensteuerung',
                'status' => 'Bereit',
                'description' => 'Aufgaben, Faelligkeiten und Verantwortlichkeiten transparent planen.',
            ],
            [
                'name' => 'Auswertung',
                'status' => 'Naechster Schritt',
                'description' => 'Kennzahlen, Berichte und Export-Pipelines fuer operative Entscheidungen.',
            ],
            [
                'name' => 'Systemeinstellungen',
                'status' => 'Backlog',
                'description' => 'Mandantenfaehigkeit, Rollen, API-Zugaenge und Integrationen.',
            ],
        ];

        $kpis = [
            ['label' => 'Aktive Module', 'value' => '5'],
            ['label' => 'Erweiterbare Bereiche', 'value' => '4'],
            ['label' => 'Architektur', 'value' => 'MVC'],
        ];

        return (new View())->render('dashboard/index', [
            'title' => 'Dashboard',
            'user'  => Auth::user() ?? 'Unbekannt',
            'quickActions' => $quickActions,
            'systemModules' => $systemModules,
            'kpis' => $kpis,
        ]);
    }
}
