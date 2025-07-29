# Notes
- User requested to fix include paths in migrate_theme_preference.php after moving to sql folder (done).
- User requested to remove all border images (e.g., option-es.gif) throughout the whole app.
- Initial script removed many border images, but some (e.g., abstand.gif) still remain and require further refinement.
- User changed strategy: Instead of removing border/spacer images, leave them in place and copy abstand.gif (transparent) over all, except for one specific spacer (between Basisdaten and Tarife), which should be removed.
- User is handling the image copying manually via shell commands.
- User requests to remove the image between Basisdaten and Tarife in the customer inventory subnav.
- User requests to unify the style of this subnav with the other subnavs.
- There is still a gap due to /images/option-sb.gif in the subnav.
- On Android Chrome with dark mode enabled, the app shows light mode but input text is white-on-white (not in stylesheet). User wants to force light or dark mode on Android Chrome.
- CSS fixes have been added to eliminate the gap caused by option-sb.gif and to force proper input colors and color-scheme on Android Chrome, including compatibility fixes for standard properties.
- User reverted previous CSS changes and fixed syntax errors, but dark mode is still not working on Chrome for Android (works on Firefox).
- Added Chrome Android-specific JS and CSS fallbacks for dark mode detection and styling.
- User requests: When saving settings, the theme (dark/light) should be forced to the saved setting, overriding the toggle button state.
- This is now implemented: saving settings forces the theme, overriding the toggle button state and updating UI elements accordingly.
- Previous implementation did not reliably override the toggle; new robust JS event-based fix applied, ensuring theme is always forced after settings save, even if toggled before.
- User reverted the last changes (deleted theme.js, settings.php, migrate_theme_preference.php) and wants to postpone the fix.
- User requests to rebase branch copilot/fix-21 onto current master.
- User is now focusing on making copilot/fix-21 work and reported a fatal error: DatabaseSecurity::buildWhereString() called with too few arguments (expects at least 3, got 2) in security.inc.php:80, called from auth.inc.php:138.
- DatabaseSecurity::buildWhereString() argument error in copilot/fix-21 is now fixed (missing DB connection parameter added).
- New error: Auth_Container_DB::$connection is undefined and NULL is passed as DB connection, causing InvalidArgumentException in security.inc.php. Need to debug/fix DB connection initialization in auth flow.
- DB connection issue in authentication flow fixed by creating a proper mysqli connection for DatabaseSecurity functions.
- Explicit call to $db->connect(...) is required; Link_ID can be 0 if not connected. Fix confirmed working; no fatal DB errors now.
- New fatal error: mysqli_connect('timeeffect', ...) fails due to hostname resolution error (php_network_getaddresses: getaddrinfo for timeeffect failed). Need to debug/fix DB hostname configuration for MySQL connection.
- MySQL hostname resolution error was caused by incorrect parameter order in DB connect() call; now fixed and no errors remain. Next step is to rebase branch.
- User note: Files prohibited by .gitignore can still be viewed using bash commands like `cat` (important for debugging and config access).
- New UI/UX request: Replace icons/stop.gif with 🛑 (emoji) on the right of the word "stop" on the efforts page, and add a stop link with stop icon next to navigation entries for Aufwände in the left navigation.
- stop.gif icon replaced with 🛑 emoji in all relevant effort row templates; stop link with emoji added to Aufwände navigation entry in left navigation.
- New fatal error: Effort access field is null (FATAL ERROR: access field is null - class: Effort, ...). Root cause: Effort::load() did not ensure access field is set when loading from DB. Fixed by assigning default value if missing after DB load.
- After rebase/merge, there are conflicts in: css/modern.css, inventory/efforts.php, templates/inventory/effort/form.ihtml, templates/shared/topnav.ihtml, vendor/composer/installed.json. These must be resolved manually.
- All merge conflicts have now been resolved and committed. Next: verify application functionality after merge.
- Open Efforts navigation improved: Stop button is now visually grouped with each activity link, as a single navigation element.
- Fixed: "Cannot modify header information" warning for 'Stop all activities' by moving header() call before any output.
- Customer selectbox for new efforts now only shows customers where user has 'new' rights in at least one project.
- Project select now uses server-generated <option> elements for all allowed projects, filtered client-side by JavaScript; AJAX endpoint and related debug logging removed for simplicity and reliability.
- Neue UI/UX: Advanced-Felder (Dauer, Tarif, Berechnet, gehört Gruppe, Besitzer darf, Gruppenmitglieder dürfen, Alle Agenten dürfen) werden jetzt hinter einem JS-Button "Erweitert" versteckt.
- Neue UI/UX: Notiz-Feld wird jetzt hinter einem JS-Button "Notiz einfügen" versteckt.
- Neue UI/UX: Textbox "Beschreibung" hat jetzt autofocus und autoselect für "Ohne Beschreibung".
- Bugfix: Nach Speichern eines Aufwands erscheint jetzt eine Bestätigungsmeldung mit Beschreibung, Projekt und Kunde (oder Hinweis, falls Kunde/Projekt fehlt).
- Hinweis: Prüfen, ob Aufwand ohne Projekt gespeichert werden kann, und ggf. Nutzerführung verbessern.
- Wichtiger Debug-Hinweis: Nach dem Speichern erscheint eine weiße Seite, weil der Benutzer beim POST nicht authentifiziert ist und auf die Login-Seite umgeleitet wird. Save() schlägt fehl, daher kein Redirect/Erfolgsmeldung.
- NEU: Authentifizierungs-Check vor dem Speichern eines Aufwands implementiert, leitet bei nicht eingeloggtem User sauber um.
- NEU: Verbesserte Fehlerbehandlung – bei Fehlern beim Speichern wird eine Fehlermeldung mit Rücksprung-Links angezeigt (statt weißer Seite).
- NEU: PHP Notice-Warnings (z.B. zu session_name() in config.inc.php) müssen behoben werden, um Initialisierungsprobleme und weie Seiten zu vermeiden.
- NEU: Debug-Logging am Anfang und entlang des Save-Flows zeigt: POST kommt an, aber der Code erreicht die Save-Logik nicht (if(isset(alted))). Aktuell wird systematisch der Ausfhrungspfad mit Logging eingegrenzt, um die Ursache der weie Seite zu finden.
- NEU: Root Cause gefunden: Die Zugriffsprüfung auf Effort->checkUserAccess('write') wurde auch für neue Aufwände (ohne bestehendes Effort-Objekt) durchgeführt, wodurch die Save-Logik nie erreicht wurde. Fix ist implementiert (Zugriffsprüfung nur für bestehende Efforts).
- NEU: Fix für MySQL-Fehler: Leere project_id Werte werden jetzt zu NULL konvertiert, damit keine Datenbank-Constraint-Verletzung mehr auftritt.
- NEU: Fix in Effort-Klasse: project_id wird jetzt auch beim INSERT und REPLACE korrekt als NULL (ohne Anführungszeichen) gespeichert, wenn leer. Dadurch keine MySQL-Fehler mehr bei leeren Projekten.
- NEU: Fix für MySQL-Fehler: project_id undefined (PHP Warning) und DB-Constraint (project_id NOT NULL) verhindern das Speichern ohne Projekt. User fordert reproduzierbaren Unittest, der sich als admin einloggt, Session merkt und neuen Aufwand ohne Projekt speichert.
- NEU: Datenbankschema verlangt project_id NOT NULL DEFAULT '0'. Fix: Immer '0' statt NULL für project_id verwenden, wenn kein Projekt gewählt ist.
- NEU: Nach Session-Timeout und anschließendem Login kommt man auf efforts.php ohne Parameter, was zu neuen Warnungen führt (undefined array key project_id, header already sent). Ursache: Output vor header() und fehlende Prüfung auf project_id an mehreren Stellen. Muss systematisch behoben werden (ob_start, header vor Output, weitere Checks).
- NEU: Output-Buffering (`ob_start()`) am Anfang von efforts.php aktiviert, um "headers already sent" Fehler zu verhindern. Alle Stellen mit header() Aufruf müssen auf vorherigen Output geprüft werden.
- NEU: Systematische Überprüfung aller header() Aufrufe und Output-Buffering in efforts.php und verwandten Dateien erforderlich, um sicherzustellen, dass keine unerwarteten Ausgaben vor header() Aufrufen erfolgen.
- NEU: Aufwände ohne Projekt (project_id = 0) werden durch INNER JOIN in EffortList nicht angezeigt. Lösung: Query auf LEFT JOIN umstellen, damit auch Aufwände ohne Projekt sichtbar sind.
- NEU: EffortList-Query im else-Block so angepasst, dass jetzt auch Aufwände ohne Projekt (project_id = 0) in der Übersicht erscheinen. Damit ist die Sichtbarkeit von Aufwänden ohne Projekt in der Hauptliste gegeben.
- NEU: Erfolgsmeldung nach dem Speichern eines Aufwands soll die ID des neuen Aufwands enthalten und alle Texte in die Localization verschoben werden (siehe efforts.php:L356-L371, Userwunsch).
- NEU: Erfolgsmeldung nach Aufwand-Save zeigt jetzt die ID des neuen Aufwands und nutzt ausschließlich Lokalisierungs-Strings (de.inc.php). Userwunsch umgesetzt.
- NEU: Fix für fehlende Aufwand-ID nach Save: Die ID wird jetzt nach dem Speichern eines neuen Aufwands korrekt über die verwendete DB-Instanz mit insert_id() ermittelt und angezeigt. Fehlerhafte Nutzung einer neuen DB-Instanz (ohne Verbindung) wurde behoben.
- NEU: Nach ID-Fix: Aufwand erscheint nicht in der Liste; neue PHP-Warnings (undefined $p_id/$c_id in path.ihtml) und Fatal Error bei Project::__construct() nach Auswahl von Projekt/Kunde. Debug erforderlich.
- NEU: Fehler mit Project::__construct() (by reference) und fehlende Variablen $p_id/$c_id im Template path.ihtml wurden behoben.
- NEU: User-Anforderung: Wenn efforts.php ohne pid/cid aufgerufen wird, soll eine zusätzliche Spalte für project_id und ggf. customer erscheinen. Die Query und die Tabelle müssen sortierbar nach pid (und innerhalb pid nach cid) werden.
- NEU: Spalten für project_id und customer werden jetzt dynamisch in der Aufwandsliste angezeigt (list.ihtml/row.ihtml), inklusive dynamischem colspan für Notiz-Zeile. Sortierung nach project_id und customer_id ist in der EffortList-Query umgesetzt.
- NEU: Die dynamische Spaltenanzeige und Sortierung wurde erfolgreich umgesetzt und getestet.
- NEU: Bug: Wenn kein Projekt gewählt wird, weist die Auto-Projekt-Logik trotzdem fälschlich ein Projekt zu. Die Logik in efforts.php muss so angepasst werden, dass wirklich kein Projekt gespeichert wird, wenn der User keins auswählt.
- NEU: Bugfix: Die automatische Projektzuweisung in efforts.php ist jetzt deaktiviert, sodass bei Auswahl "kein Projekt" auch wirklich kein Projekt gespeichert wird. Der Userwunsch ist damit umgesetzt.
- NEU: Note and open task for the new continue-link (cont=1) logic: only show if no other effort with same description is running.
- NEU: Die Logik für den "Fortsetzen"-Link (cont=1) ist jetzt korrekt implementiert und getestet: Der Link wird nur angezeigt, wenn kein anderer Aufwand mit derselben Beschreibung und laufender Zeit (hours=0) existiert. SQL-Injection-Schutz und DB-Initialisierung sind berücksichtigt.
- NEU: Bugfix: Fatal Error wegen falscher Parameter-Reihenfolge bei DatabaseSecurity::escapeString() (row.ihtml) – jetzt wird korrekt ($value, $link) übergeben und die mysqli-Connection aus der Database-Instanz verwendet.
- NEU: Kritischer Bug: SQL-Fehler wegen fehlender Spalte `hours` in der Aufwands-Tabelle (row.ihtml, Fortsetzen-Link). Die Logik muss auf das korrekte Feld für "laufende Aufwände" angepasst werden (z.B. Status, offene Zeit, etc.).
- NEU: Bugfix: SQL-Fehler im Fortsetzen-Link (row.ihtml): Die Spalte `hours` existiert nicht. Die Logik wurde angepasst und prüft jetzt auf `begin = end` als Kriterium für laufende Aufwände (statt nicht existierender Spalte).
- NEU: UX/Design-Wunsch: Topnav oben rechts vereinheitlichen wie bei modernen Plattformen (User-Initial im Kreis als Menü-Opener, dann Play/Stop, ⚙️, ⏏; Reihenfolge und Look modernisieren).
- NEU: Topnav-Modernisierung abgeschlossen: User-Initial im Kreis, moderne Reihenfolge (User, Play, Stop, Theme, Settings, Logout), CSS und JS für User-Avatar implementiert.
- NEU: User-Avatar ersetzt Settings-Gear-Icon, steht ganz rechts, Navigation ist jetzt rechtsbündig (flex-end), Avatar ist Link zu Settings.
- NEU: User-Dropdown: Logout-Link erscheint im Dropdown-Menü, das beim Hover über den User-Avatar angezeigt wird (statt direkt sichtbar).
- NEU: User avatar hover dropdown with logout link: Implementierung und Testing erforderlich.
- [x] User avatar hover dropdown with logout link: Implementierung und Testing erfolgreich.
- [x] Plan ergänzt: Layout-Fix rückgängig machen, Dropdown-Menü für Logout beim Hover auf User-Avatar implementieren.
- [x] Letztes Layout-Fix rückgängig machen, da Topnav zu weit links ist. Besseres Flexbox/Tabellen-Layout für ganz rechts nötigt.
- [x] Dropdown-Menü für Logout beim Hover auf User-Avatar ist jetzt implementiert und getestet. Layout ist wieder ganz rechts.
- NEU: Anzeige von Success-/Info-Messages aus URL-Parameter `message` in customer.php und list.ihtml (grüne Box über Kundenliste).
- [x] Anzeige von Success-/Info-Messages aus URL-Parameter `message` in customer.php und list.ihtml (grüne Box über Kundenliste).
- [x] Migration-Konflikt gelöst: Theme-Preference-Migration ist im MigrationManager integriert (migrations.inc.php), migrate_theme_preference.php entfernt, Syntaxfehler und Merge-Konflikte beseitigt.
- NEU: Plan ergänzt: Merge-Konflikt in include/database.inc.php als nächsten Schritt aufnehmen.
{{ ... }}
- [x] Migration-Konflikt beheben: Theme-Preference-Migration in MigrationManager integrieren (siehe docs/DATABASE_MIGRATIONS.md), migrate_theme_preference.php entfernen
{{ ... }}
Backend/UX Restarbeiten: Header/Output, PHP Notices, Unittest
Migration-Konflikt: Theme-Preference-Migration sauber integrieren

## Task List
- [x] Fix include paths in migrate_theme_preference.php after move
{{ ... }}
- [x] Fehler: $_PJ_auth ist null im register.ihtml – Ursache analysieren und Initialisierung sicherstellen
- [x] Fehler: Falscher Parameter-Typ bei mysqli_real_escape_string() in password_reset.php – DB-Connection korrekt übergeben
- [x] Fehler: Migrationen (z.B. confirmed-Spalte) werden nicht ausgeführt – Ursache analysieren und Migrations-Timing/Trigger reparieren
- [x] Migrations-Trigger und Auth-Initialisierung an Login-Seite orientieren, Register/Create-User robust machen

## Current Goal
Migrationen werden nicht ausgeführt: Migrations-Timing/Trigger debuggen und reparieren
- NEU: Migrations-Trigger und Auth-Initialisierung an Login-Seite orientieren, damit Register-Seite und andere No-Login-Seiten robust funktionieren
- User-Klasse prüft jetzt, ob Migrationsspalten existieren, bevor sie diese verwendet (Fallback auf altes Schema, wenn Migrationen fehlen).
- Register-Seite lädt Gruppen jetzt direkt aus der DB, nicht mehr über $_PJ_auth.
- Nächster Schritt: Migrations-Trigger und Auth-Initialisierung an das Login-Seiten-Schema angleichen, sodass Register/Create-User robust funktionieren – auch wenn Migrationen fehlen.
- NEU: Template-Fehler: $center_template ist in note.ihtml nicht gesetzt (password_reset).
- NEU: Template-Fehler: $_PJ_db_prefix ist in register.ihtml nicht gesetzt (Register-Seite).
- NEU: SQL-Fehler: SELECT ... FROM group ... benötigt Backticks um group.
- NEU: Kritischer Fehler: Register-Seite sucht Tabelle 'te_' statt 'te_group' – Prefix falsch oder leer. Ursache für fehlende Gruppenanzeige und SQL-Fehler identifizieren und beheben.
- [x] Template-Include-Fehler: note.ihtml versucht /templates/password_reset/note.ihtml zu includen, das nicht existiert. Template-Handling für password_reset/note/ihtml anpassen.
- [x] Migrations-Trigger und Auth-Initialisierung an Login-Seite orientieren, Register/Create-User robust machen
- [x] Template-Include-Fehler: note.ihtml erwartet /templates/password_reset/note.ihtml, das nicht existiert – Template-Handling für Info/Success/Fallback-Nachrichten anpassen

## Current Goal
Migrationen werden nicht ausgeführt: Migrations-Timing/Trigger debuggen und reparieren
- NEU: Migrations-Trigger und Auth-Initialisierung an Login-Seite orientieren, damit Register-Seite und andere No-Login-Seiten robust funktionieren
- User-Klasse prüft jetzt, ob Migrationsspalten existieren, bevor sie diese verwendet (Fallback auf altes Schema, wenn Migrationen fehlen).
- Register-Seite lädt Gruppen jetzt direkt aus der DB, nicht mehr über $_PJ_auth.
- Nächster Schritt: Migrations-Trigger und Auth-Initialisierung an das Login-Seiten-Schema angleichen, sodass Register/Create-User robust funktionieren – auch wenn Migrationen fehlen.
- NEU: Template-Fehler: $center_template ist in note.ihtml nicht gesetzt (password_reset).
- NEU: Template-Fehler: $_PJ_db_prefix ist in register.ihtml nicht gesetzt (Register-Seite).
- NEU: SQL-Fehler: SELECT ... FROM group ... benötigt Backticks um group.
- NEU: Kritischer Fehler: Register-Seite sucht Tabelle 'te_' statt 'te_group' – Prefix falsch oder leer. Ursache für fehlende Gruppenanzeige und SQL-Fehler identifizieren und beheben.
- NEU: Template-Include-Fehler: note.ihtml erwartet /templates/password_reset/note.ihtml, das nicht existiert – Template-Handling für Info/Success/Fallback-Nachrichten anpassen.