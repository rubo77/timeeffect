- Undefined variable warnings in user/index.php und groups/index.php wurden durch Initialisierung aller relevanten Variablen aus $_REQUEST behoben.
- Fatal Error bei Theme-Änderung: settings.php ruft nicht existierende Methode escape() auf. settings.php ist redundant, Theme-Einstellung soll in own.php integriert werden.
- settings.php hat kein CSS, ist nicht im Haupt-Flow genutzt.
- Backend-Refaktor: Verhindere Objekt-Erstellung ohne gültige ID (Customer, Project, Effort), entferne Fallbacks, verbessere ACL-Filter und Logging, Regressionstests mit PHPUnit.

## Notes
- settings.php ist redundant und sollte entfernt werden.
- Theme-Einstellung sollte in own.php integriert werden.
- Theme-Einstellung (Darkmode) ist jetzt in own.php integriert, settings.php wurde entfernt.
- Backend-Root-Cause-Fixes:
  - Verhindere Objekt-Erstellung ohne gültige ID (Customer, Project, Effort)
  - Entferne Fallbacks aus ACL/Constructor-Logik
  - Verbessere ACL-Filter und Logging
  - Regressionstests mit PHPUnit
- Für Theme-Setting muss Spalte `theme_preference` in der Auth-Tabelle existieren (DB-Migration notwendig)
- Migration migrate_theme_preference.php erstellt, prüft und fügt Spalte automatisch hinzu
- Automatische Migrationen sollen beim Login geprüft und ggf. ausgeführt werden
- Es soll ein DB-Flag geben, das den Migrationsstand dokumentiert (automatisches Upgrade jeder beliebigen DB auf aktuellen Stand)
- Migrationen müssen nach Laden der Database-Klasse ausgeführt werden (jetzt in database.inc.php)
- Fehler mit fehlendem $_PJ_db_prefix und add_slashes() wurden durch Fallbacks und Umstellung auf addslashes() behoben
- Migration-Dateien sollen im Verzeichnis sql/ liegen (künftig beachten)
- Keine Fallbacks mehr für DB-Prefix: Migration läuft nur, wenn Config geladen ist
- Bug: Fatal error Call to undefined method Auth::giveValue() beim Speichern der Settings-Seite analysiert: Ursache war fehlendes Include von auth.inc.php, jetzt behoben.
- Alle Links von own.php auf settings.php umgestellt (Header, Templates, Migration).
- Redirect-Problem nach own.php nach dem Speichern lag an $GLOBALS['_PJ_own_user_script'] in scripts.inc.php. Zeigt jetzt korrekt auf settings.php.
- Bug: Nach Theme-Update wurde $_PJ_auth fälschlich durch ein Auth-Objekt ersetzt (ohne giveValue). Jetzt bleibt PJAuth erhalten, fetchAdditionalData() wird korrekt verwendet.
- Neue Anforderung: Passwortfelder erst nach Klick auf "Passwort ändern" anzeigen (JavaScript), kein Fehler beim Speichern ohne Passwortfelder.
- Neue Anforderung: Theme-Änderung (Darkmode) zeigt keinen Effekt – CSS-Lade-Reihenfolge und aktive Stylesheets analysieren.
- Theme-Preference wird jetzt als data-theme Attribut am <html> Element gesetzt, damit greift das CSS für Dark- und Light-Mode korrekt.
- Passwortfelder werden jetzt wirklich erst dynamisch per JavaScript ins DOM eingefügt (nicht nur versteckt), um Autofill-Probleme zu verhindern.
- Bug: In den Templates wurde das data-theme Attribut durch JavaScript sofort wieder überschrieben; jetzt ist nur noch PHP maßgeblich, Theme-Umschaltung funktioniert.
- Alle manuellen Theme/CSS/UI-Tests und Demos sind künftig im test-Verzeichnis abzulegen (z.B. test/theme-test-dark.html).
- Theme-Funktion (Dark/Light Mode) funktioniert jetzt korrekt (JavaScript-Override entfernt)
- Tests müssen immer im Unterordner tests/ abgelegt werden (User-Regel)
- Soundausgabe bei sudo-Befehlen ist technisch nicht möglich
- Theme-Debug-Testseite (tests/theme-debug.html) wurde erstellt, um CSS-Variablen und Dark-Mode-Verhalten im Browser zu prüfen. Zeigt die geladenen Variablen und deren Werte zur Laufzeit an.
- Test-Regel dauerhaft gespeichert: Tests dürfen nur im tests/-Verzeichnis liegen.
- Erkenntnis: CSS-Variables für Darkmode werden wegen zu niedriger Spezifität im Haupt-CSS nicht angewendet. Test mit html[data-theme="dark"] und !important funktioniert. Nachhaltige Korrektur im Haupt-CSS erforderlich.
- Haupt-Darkmode-Variables werden jetzt mit html[data-theme="dark"] überschrieben (Spezifitäts-Fix umgesetzt).
- CSS-Datei enthält weiterhin viele Syntaxfehler durch fehlerhafte Media Query/Selector-Mischungen (technische Schuld, separat beheben).
- Darkmode-Styles für Inputs/Textareas/Selectboxen (helle Schrift, dunkler Hintergrund) und Links (helle Farben) ergänzt.
- Darkmode-Styles für Navigation-Links (dunkler) und .FormFieldName (hell) ergänzt.
- Darkmode-Styles für Tabellen, TD.leftNavi und Inventar-spezifische Navigation ergänzt.
- Inventar-Navigation verwendet a.modern-tab statt .modern-tabs a (Root Cause identifiziert, CSS-Fix umgesetzt)
- Reiter (Tabs) in MainNav und Subnav jetzt mit dunklerem Text (Darkmode)
- Darkmode-Styles für Hamburger-Menü (.mobile-main-options), Kunden-Listen (.content, .list, A.list) und weitere mobile Bereiche ergänzt (Root Cause: fehlende Selektoren für mobile und Kunden-spezifische Bereiche, jetzt ergänzt)
- Umfassende Darkmode-Styles für mobile Navigation (.mobile-nav, .nav-item, svg, span) ergänzt, sodass jetzt alle Bereiche (inkl. Kunden im Hamburger-Menü) dunkel sind
- Darkmode-Styles für .modern-nav, .animate-float und a.option ergänzt und getestet (Inventar- und mobile Navigation jetzt überall dunkel, inkl. aller Option-Links)
- Mobile Navigation ist jetzt vollständig dunkel, alle Elemente (inkl. .modern-nav, .animate-float, a.option) wurden korrekt gestylt
- Database Migration System ist jetzt umfassend in [docs/DATABASE_MIGRATIONS.md](docs/DATABASE_MIGRATIONS.md) dokumentiert (Best Practices, Patterns, Checkliste, Beispiele, Troubleshooting für PRs)
- Installationsanleitung (docs/TIMEEFECT Installation Manual.md) ist jetzt modernisiert: PEAR entfernt, Docker empfohlen, aktuelle PHP/MySQL/MariaDB Anforderungen, moderne Setup- und Troubleshooting-Abschnitte
- Installationsanleitung konsolidiert: Docker, Composer, .env, Production Deployment, dev/README.md kann gelöscht werden (alle Infos jetzt zentral in [TIMEEFECT Installation Manual.md](docs/TIMEEFECT Installation Manual.md))
- Alle relevanten Markdown-Dokumentationen sind jetzt im Installation Manual verlinkt (siehe Abschnitt "Additional Documentation").
- Die wichtigsten Development- und Dokumentationsdateien sind im Installation Manual gelistet; Legacy-Skripte und abgeschlossene Migrations-/Analyse-Dateien wurden gelöscht und sind im Manual als nicht mehr relevant markiert.
- dev/ wurde aufgeräumt und alle Legacy-Skripte wurden gelöscht und im Manual dokumentiert.

## Task List

## Current Goal