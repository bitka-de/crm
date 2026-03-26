# CRM (PHP, MVC, Asset-Build)

Dieses Projekt ist ein leichtgewichtiges CRM-Grundgeruest auf PHP-Basis mit:

- eigenem Router
- einfachem MVC-Aufbau (Model, View, Controller)
- View-System mit Layouts und Komponenten
- Asset-Build fuer CSS, JavaScript und Bilder

## Voraussetzungen

- PHP 8.1 oder neuer (empfohlen)
- Composer

## Projektstruktur

- app/: Anwendungslogik
- public/: Oeffentlicher Webroot (Front Controller + gebaute Assets)
- resources/: Asset-Quellen (CSS, JS, Bilder)
- build.php: Build-Einstieg fuer Assets
- crm: lokaler CLI-Wrapper fuer Build-Befehle

Wichtige Ordner im Detail:

- app/Controllers: Controller
- app/Models: Models
- app/Views: Views, Layouts, Komponenten
- app/Core/Router.php: Routing
- app/Core/View.php: Rendering (Layouts + Komponenten)
- app/Build/AssetBuilder.php: Asset-Pipeline

## Schnellstart

1. Abhaengigkeiten installieren:

```bash
composer install
```

2. Assets bauen:

```bash
./crm build
```

Alternative:

```bash
composer build
```

3. Lokalen Webserver starten:

```bash
php -S localhost:8000 -t public
```

4. Im Browser aufrufen:

- http://localhost:8000

## Wie man das nutzt

### Routing

Routen werden in app/bootstrap.php registriert.

Beispiel:

```php
$router->get('/', [HomeController::class, 'index']);
$router->post('/kontakt', [ContactController::class, 'store']);
```

### Controller

Controller erzeugen Daten (Model) und geben gerendertes HTML zurueck.

Beispiel:

```php
return (new View())->render('home/index', [
    'title' => 'Startseite',
    'page' => $page,
]);
```

### Views, Layouts, Komponenten

- Standard-Layout: app/Views/layouts/app.php
- Komponenten: app/Views/components/*.php
- Seiten-Views: app/Views/<bereich>/<datei>.php

Im View koennen Komponenten so verwendet werden:

```php
<?= $this->component('panel', [
    'title' => 'Titel',
    'copy' => 'Inhalt',
]) ?>
```

Wenn eine View ohne Layout gerendert werden soll:

```php
return (new View())->render('api/raw', $data, null);
```

### Assets (CSS, JS, Bilder)

Quellen liegen unter:

- resources/css
- resources/js
- resources/images

Build-Ausgabe liegt unter:

- public/assets/css/app.css
- public/assets/js/app.js
- public/assets/images/*

Build starten:

```bash
./crm build
```

Oder:

```bash
composer build
```

## Testumgebung

Das Projekt nutzt PHPUnit fuer Unit- und Integrationstests.

Teststruktur:

- tests/Unit
- tests/Integration
- phpunit.xml

Tests ausfuehren:

```bash
composer test
```

Kompletten Testlauf ausfuehren:

```bash
composer test:all
```

Direkt mit PHPUnit:

```bash
vendor/bin/phpunit --configuration phpunit.xml
```

## Versionslog

- Changelog: CHANGELOG.md
- Einfacher Ablauf fuer Releases: VERSIONING.md

## Was das System kann

- einfache GET/POST-Routen
- Controller-Handler als [ControllerClass::class, 'method']
- 404 bei unbekannten Routen
- 500 bei ungueltigem Route-Handler
- MVC-Grundstruktur fuer serverseitig gerenderte Seiten
- Layout-basiertes Rendering
- wiederverwendbare View-Komponenten
- HTML-Escaping ueber View::escape()
- Asset-Build aus resources nach public/assets
- rekursives Kopieren von Bildern

## Was das System aktuell nicht kann

- keine dynamischen Routenparameter (z. B. /kunden/{id})
- kein benanntes Routing / URL-Generator
- keine Middleware (Auth, CSRF, Logging-Pipeline)
- keine Dependency Injection im Router
- kein Datenbank-Layer / ORM / Migrationen
- kein Formular-Validierungsframework
- keine API-Serialisierung oder Content-Negotiation
- kein Minifying oder Hashing/Fingerprinting fuer Assets
- kein Watch-Mode fuer Assets
- kein global installierter Befehl crm build ohne PATH-Setup

## Hinweise zum Befehl crm build

Im Projekt funktioniert der Build direkt mit:

```bash
./crm build
```

Falls du unbedingt crm build ohne ./ nutzen willst, muss der Befehl in deinem PATH liegen (z. B. ueber Alias oder Symlink).

## Typische Erweiterungen als naechster Schritt

- dynamische Router-Parameter einfuehren
- Middleware-Konzept ergaenzen
- Datenbankanbindung und Repository-/Service-Schicht aufbauen
- Asset-Build um Minify + Dateihashes erweitern
- Watch-Task fuer Entwicklung bereitstellen
