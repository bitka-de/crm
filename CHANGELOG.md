# Changelog

Alle relevanten Aenderungen an diesem Projekt werden hier dokumentiert.

## Unreleased

### Added

### Changed

### Fixed

### Removed

### Security

### Planned
- Dynamische Routenparameter (z. B. /kunden/{id})
- Middleware-Unterstuetzung
- Asset-Minifying und Hashing
- Watch-Modus fuer Asset-Build

## Version 1 - 2026-03-26

### Added
- Grundstruktur fuer die Web-App mit Front Controller in public/index.php
- Eigener Router mit GET/POST-Unterstuetzung und 404/500-Handling
- Bootstrap-Initialisierung in app/bootstrap.php
- MVC-Basis mit Model, View und Controller fuer die Startseite
- View-System mit Layout-Unterstuetzung, Komponenten-Unterstuetzung und Escape-Helfer
- Asset-Pipeline mit Quellen in resources/css, resources/js, resources/images und Build-Ausgabe nach public/assets
- Build-Einstieg ueber build.php, lokalen CLI-Wrapper crm und Composer-Befehl composer build
- Testumgebung mit PHPUnit, Konfiguration in phpunit.xml, Test-Bootstrap in tests/bootstrap.php, Unit-Tests und Integrationstest
- Projektdokumentation in README.md
- Basis .gitignore fuer Abhaengigkeiten, Build-Artefakte und lokale Dateien

### Changed
- Startseitenausgabe von einfacher Closure auf MVC-Rendering umgestellt
- Layout von Inline-CSS/JS auf gebaute Asset-Dateien unter public/assets umgestellt

### Tested
- Build erfolgreich ueber ./crm build und composer build ausgefuehrt
- PHPUnit-Tests erfolgreich ausgefuehrt (kompletter Lauf)
