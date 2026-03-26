# Versionslog und einfacher Release-Prozess

Dieses Projekt haelt es bewusst einfach:

- Keine SemVer-Pflicht.
- Releases werden ueber Datum und Inhalt im Changelog dokumentiert.

## Grundregel

- Neue Aenderungen landen zuerst in Unreleased in CHANGELOG.md.
- Beim Release werden die Eintraege unter einen neuen Datumsblock verschoben.

Beispiel fuer einen Datumsblock:

- ## 2026-03-26

## Einfache Release-Checkliste

1. Aenderungen in CHANGELOG.md unter Unreleased sammeln.
2. Tests ausfuehren:
   - composer test
   - composer test:all
3. Build pruefen:
   - ./crm build (oder composer build)
4. In CHANGELOG.md neuen Datumsblock erstellen.
5. Inhalte aus Unreleased in den Datumsblock uebernehmen.
6. Unreleased wieder als leere Vorlage stehen lassen.
7. Commit erstellen und optional einen Git-Tag setzen.

## Optional empfohlene Kategorien

- Added
- Changed
- Fixed
- Removed
- Security

Die Kategorien sind optional und sollen nur helfen, Eintraege schneller zu lesen.
