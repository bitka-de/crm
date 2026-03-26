# Changelog

Alle relevanten Aenderungen an diesem Projekt werden hier dokumentiert.

## Unreleased

### Added

### Changed

### Fixed

### Removed

### Security

---

## Version 2 - 2026-03-26

### Added
- Auth-System mit Musterkonto admin / 1234
- Session-Klasse fuer serverseitige Sitzungsverwaltung
- Auth-Klasse mit attempt(), check(), user(), logout() (Passwoerter mit password_hash/password_verify)
- MiddlewareInterface als Vertrag fuer alle Middlewares
- AuthMiddleware leitet nicht eingeloggte Nutzer auf /login um
- AuthController mit showLogin(), login() und logout()
- Login-View mit Hero-Komponente, Fehleranzeige und Formular
- Middleware-Unterstuetzung im Router: get/post/addRoute akzeptieren optionales Middleware-Array
- Login-Routen GET /login, POST /login, POST /logout im Bootstrap
- Formular-CSS in resources/css/app.css

### Changed
- Startseite / ist jetzt durch AuthMiddleware geschuetzt
- Bootstrap startet Session und registriert Auth-Routen
- HomepageRenderingTest authentifiziert Testnutzer in setUp

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
