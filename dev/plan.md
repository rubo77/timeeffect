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
- 31.7. 05:23 "Customer-Save-Fix"
    - Customer-Save übernimmt jetzt alle Felder aus $_REQUEST, unabhängig vom bisherigen DB-Wert (REQUEST hat Vorrang).
    - Column-Count-Bug in customer.inc.php behoben: access-Spalte ist jetzt Teil des $table_fields-Arrays.
    - Secure Defaults werden nur noch bei neuen Datensätzen angewendet (!isset($this->data['id'])).
    - Debug-Logging für Datenübernahme ist temporär aktiv.
    - In project.inc.php wurde Secure Defaults analog gefixt.
    - In db_mysql.inc.php wurden alle @-Operatoren entfernt, Fehler werden nicht mehr unterdrückt.
    - Checkbox-Fix: readforeignefforts wird jetzt immer korrekt gespeichert (auch wenn Checkbox nicht gesetzt, dann Wert '0').
    - Template-Fix: Im customer-Formular wird jetzt isset($readforeignefforts) and $readforeignefforts === '0' verwendet, um das Problem mit !empty('0') zu beheben.
    - Migration-Strategie: Automatische Migrationen werden in migrations.inc.php als PHP-Methoden implementiert und laufen beim Login, manuelle Migrationen (z.B. sql/migration_add_registration_features.sql) müssen explizit ausgeführt werden. Dokumentation in DATABASE_MIGRATIONS.md ist jetzt konsistent und beschreibt beide Wege.
    - Password-Reset: Debug-Logging für Token-Update in password_reset.php aktiviert, um SQL-Fehler (z.B. fehlende Spalten) zu erkennen. Debug-Flag global aktiviert.
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
- NEU: Registrierung: Default-Permissions für neue User sind aktuell nur 'agent', allow_nc=0. Es gibt aber keine explizite Einschränkung, dass User nur eigene Kunden/Projekte sehen dürfen. Berechtigungskonzept für "Besitzer darf", "Gruppenmitglieder dürfen", "Alle Agenten dürfen" etc. muss geklärt und ggf. angepasst werden.
- NEU: Registrierung: Sichere Default-Berechtigungen für neue User müssen implementiert werden, um sicherzustellen, dass neue User nur auf eigene Kunden/Projekte zugreifen können.
- [x] Sichere Default-Berechtigungen für neue User implementieren: Nur eigene Kunden/Projekte sichtbar, keine fremden Aufwände
- [x] Registrierung: Gruppenauswahl bei Registrierung einschränken (nur agent/client), neue User erhalten keine Gruppenzugehörigkeit per Default
- NEU: Es gibt zwei Arten von Gruppen: (1) Systemgruppen (admin, agent, client, accountant), die Berechtigungsrollen definieren, und (2) benutzerdefinierte Gruppen, die von Admins angelegt werden können und eigene IDs haben. IDs und Bedeutungen dürfen nicht verwechselt werden.
- NEU: Bug: Registrierung ohne Gruppe ist aktuell nicht möglich (Fehlermeldung "Bitte wählen Sie mindestens eine Gruppe für diesen Benutzer!"). Außerdem werden bei Auswahl z.B. von 'client' fälschlich IDs von benutzerdefinierten Gruppen zugeordnet. Analyse und Fix erforderlich.
- FIX: Registrierung nutzt jetzt korrekt die gids-Tabelle (benutzerdefinierte Gruppen) für Gruppenzuweisungen, Systemgruppen werden nur für Berechtigungsrollen verwendet. Registrierung ohne Gruppe ist für Nicht-Admins möglich, keine automatische Zuweisung zu falschen Gruppen-IDs mehr. Validierung und UI/UX angepasst.
- NEU: SQL-Syntax-Fehler: Wenn gids leer ist, wird ein ungültiges SQL "gid IN ()" generiert. Das führt zu einem Syntax-Fehler und muss im Query-Building abgefangen werden (kein OR-Block für Gruppen, wenn gids leer ist).
- NEU: Note and task for systematic SQL-Syntax-Fix for gid IN () in all modules
- [x] SQL-Syntax-Fixes systematisch in allen Query-Building-Stellen: Kein "gid IN ()" mehr bei leeren Gruppen (customer.inc.php, effort.inc.php, statistics.inc.php, project.inc.php etc.)
- NEU: DRY-Prinzip: Es wird eine zentrale Funktion zur Generierung des ACL-Query-Teils für Gruppenrechte erstellt (z.B. buildAclGroupQuery($user, $tableAlias = '')), die überall verwendet wird. Alle bisherigen Query-Builds werden darauf umgestellt.
- 7.6. 14:37 "ACL"
Die Umstellung auf eine zentrale ACL-Query-Funktion ist abgeschlossen und in allen relevanten Modulen (customer.inc.php, effort.inc.php, project.inc.php) implementiert. Ein umfassendes Test-Script validiert die Funktionalität, insbesondere die Vermeidung von "gid IN ()" Fehlern und die DRY-Prinzip-Umsetzung.
- 7.6. 06:23 "DEBUG-Global"
Debug-Logging wird jetzt zentral über die globale Variable $GLOBALS['_PJ_debug'] in config.inc.php und config.inc.php.sample gesteuert. Die Funktion debugLog() prüft nur noch dieses Flag. Damit ist Debug-Ausgabe überall zentral aktivierbar/deaktivierbar.
- 7.6. 06:44 "SQL-Injection-Analyse"
Commit f05b0db5 hat SQL-Injection-Schutz in einigen Modulen verbessert, aber das Kernproblem in project.inc.php (Project::load()) nicht gelöst: Dort werden weiterhin ungefilterte Parameter (z.B. pid aus $_REQUEST) direkt in SQL-Queries verwendet. Exploits wie ?pid=' OR 1=1 -- sind daher weiterhin möglich. Task: Systematische Absicherung aller ID-Parameter gegen SQL-Injection, insbesondere in Project::load().
- 7.6. 06:51 "SQL-Injection-Fix"
Alle kritischen load- und lookup-Methoden (Project::load, Effort::load, User, Group) sind jetzt mit DatabaseSecurity::escapeString() und expliziter DB-Verbindung abgesichert. Testskript meldet aber noch DELETE-Statements mit ungefilterten IDs als potenziell verwundbar. Task: Auch alle DELETE-Statements systematisch absichern.
- 7.6. 07:00 "SQL-Injection-Fix count()"
Auch Project::count() ist jetzt mit expliziter DB-Verbindung und EscapeString abgesichert. Alle load- und count-Methoden in project.inc.php sind damit sicher gegen SQL-Injection.
- 7.6. 07:02 "Password-Validation-Fix"
Bug: Die Passwort-Validierung in User::save() war zu strikt und hat auch bei nicht aktivierter Passwort-Änderung ein leeres Passwort verlangt. Fix: Passwort ist nur bei neuen Usern Pflicht, bei bestehenden Usern nur wenn wirklich geändert wird. Logik entsprechend angepasst und getestet.
- 7.6. 07:04 "User-Form-UI-Fix"
Bug: Bei neuen Usern wurde fälschlich der JS-Button zum Anzeigen der Passwortfelder angezeigt. Jetzt erscheinen die Passwortfelder direkt, der Button nur bei bestehenden Usern. Template-Logik in form.ihtml angepasst und getestet.
- 7.6. 07:06 "Auto-Group-Creation"
Neue Anforderung: Bei Neuanlage eines Users wird automatisch eine persönliche Gruppe mit dem Usernamen als Gruppennamen erstellt. Diese Gruppe erscheint direkt im Gruppenzugehörigkeits-Dropdown und ist vorausgewählt.
- 7.6. 07:07 "Auto-Group-Creation abgeschlossen"
Die automatische Gruppenerstellung für neue User ist implementiert: Backend legt Gruppe an, Dropdown zeigt sie direkt an und wählt sie aus. JavaScript aktualisiert die Anzeige dynamisch beim Tippen des Usernamens.
- 7.6. 07:14 "Group-Members-Display"
Neue Anforderung: Beim Bearbeiten einer Gruppe (groups/index.php?edit=1&gid=X) sollen alle zugeordneten Benutzer und Objekte (Kunden, Projekte, Aufwände) angezeigt werden.
- 7.6. 07:15 "Group-Members-Display umgesetzt"
Neue Datei group_assignments.inc.php liefert Methoden zur Anzeige aller zugeordneten Benutzer und Objekte im Gruppen-Edit. Template und Lokalisierung ergänzt, Syntax geprüft.
- [x] Anzeige aller zugeordneten Benutzer und Objekte beim Gruppen-Edit (groups/index.php?edit=1&gid=X)
- 7.6. 07:19 "Group-Display-Bugfixes"
Bug: User-Links im Gruppen-Edit ergänzen (Link auf User-Edit). SQL-Fix: ORDER BY name in Group_getAssignedCustomers() führt zu Fehler, da Spalte evtl. anders heißt (z.B. kundenname). Task: Spaltennamen prüfen und Query anpassen.
- 7.6. 07:21 "Null-Value-Fix"
Fix: htmlspecialchars() deprecated warning durch robustes Null-Handling im Template (?? '' bei allen Werten). PHP-Syntax geprüft, keine weiteren Deprecated-Fehler.
- 7.6. 07:22 "Project-Constructor-Fix"
Bug: Project::__construct() erwartet mindestens 2 Argumente (customer, user), aber in group_assignments.inc.php wird nur ein Array übergeben. Task: Überall, wo Project-Objekte aus DB-Records erzeugt werden, müssen die richtigen Argumente übergeben werden (z.B. Dummy-User oder Customer, falls nicht vorhanden). Siehe group_assignments.inc.php und ähnliche Stellen.
- 7.6. 07:23 "Project-Constructor-Fix umgesetzt"
Fix: Überall, wo Customer, Project, Effort-Objekte aus DB-Records erzeugt werden (group_assignments.inc.php), werden jetzt Dummy-Objekte für user/customer übergeben, damit keine ArgumentCountError mehr auftreten. PHP-Syntax geprüft.
- [x] Anzeige aller zugeordneten Benutzer und Objekte beim Gruppen-Edit (groups/index.php?edit=1&gid=X)
- [x] Project-Constructor-Fix: Überall Dummy-Objekte für user/customer beim Erzeugen aus DB-Records verwenden (group_assignments.inc.php)
- 7.6. 07:25 "Dummy-Objekt-Refactoring-Diskussion"
Hinweis: Die Nutzung von Dummy-Objekten (DummyUser, DummyCustomer) für reine Anzeige in group_assignments.inc.php ist unelegant und ein Designproblem. Die starke Kopplung der Datenklassen an User/Customer-Objekte für ACL sollte langfristig durch eleganteres Design (z.B. optionale ACL-Prüfung, Factory/Display-Objekte) ersetzt werden. Task für Refactoring offen.
- 7.6. 07:26 "Raw-DB-Records-Refactoring abgeschlossen"
Fix: Dummy-Objekte entfernt, alle Anzeige-Funktionen in group_assignments.inc.php nutzen jetzt direkt die DB-Records (Arrays) für die Anzeige. Das Template wurde angepasst, arbeitet jetzt mit Arrays statt Objekten. Keine Kopplung/ACL-Probleme mehr, keine Dummy-Methoden nötig. PHP-Syntax geprüft.
- [x] Raw-DB-Records-Refactoring: Anzeige aller Gruppen-Zuordnungen nutzt jetzt direkt DB-Records (keine Dummy-Objekte mehr)
- 7.6. 07:28 "PHP-Closing-Tag-Fix"
Neue User-Präferenz: Am Ende von PHP-Dateien, die mit PHP-Code enden, immer das abschließende ?> weglassen (PSR/Best Practice). Bereits in scripts.inc.php und group_assignments.inc.php umgesetzt.
- 7.6. 07:28 "$_PJ_project_script-Fix"
Fix: Fehlende Variable $_PJ_project_script in scripts.inc.php ergänzt (Alias auf _PJ_projects_inventory_script), damit Template-Kompatibilität gewährleistet ist.
- 7.6. 07:34 "Project-CID-Auto-Lookup"
Hinweis: In inventory/projects.php soll, falls cid fehlt, diese vor dem ACL-Test aus der DB gelesen werden (Kompatibilität für Links aus Gruppenverwaltung).
- [x] Dummy-Objekt-Refactoring: Dummy-Objekte entfernt, alle Anzeige-Funktionen in group_assignments.inc.php nutzen jetzt direkt die DB-Records (Arrays) für die Anzeige
- [x] Auto-Lookup der cid in inventory/projects.php vor ACL-Test
- 7.6. 08:36 "Password-Handling-Bug"
Bug: Beim Editieren eines bestehenden Users wird das Passwort immer verlangt, weil das hidden uid-Feld fehlt und der Modus nach dem Speichern auf "new" wechselt. Task: Hidden-uid-Feld sicher übergeben und Passwort-Logik/Modus robust prüfen und fixen.
- 7.6. 08:43 "Password-Handling-Refactoring"
Die Dummy-Passwort-Lösung wird entfernt. Stattdessen wird ein Hidden-Feld für den Modus (new/edit) eingeführt und die Passwort-Validierung in User::save() sowie das Template entsprechend angepasst. Dadurch ist die Erkennung des Edit-Modus eindeutig und robust.
- 7.6. 08:51 "Auth-Passwort-Refactoring"
Die Dummy-Passwort-Logik in auth.inc.php wird entfernt und auf die neue Mode-basierte Logik umgestellt. Unit-Test für User-Edit und User-Creation ist als nächster Schritt offen.
- 7.6. 08:53 "Password-Validation-Standalone-Test"
Die Dummy-Passwort-Logik ist jetzt auch in auth.inc.php entfernt. Ein eigenständiger Unittest für die Passwort-Validierungslogik (ohne DB/Web-Abhängigkeit) wurde erstellt und erfolgreich ausgeführt. Die neue Mode-basierte Logik ist damit vollumfänglich getestet und produktionsreif.
- NEU: "switch user" Warning: Cannot modify header information - headers already sent by (output started at /var/www/html/include/pear/PEAR.php:154) in /var/www/html/switch_user.php on line 50. Muss behoben werden.
- NEU: Löschen von Gruppen nur erlauben, wenn keine Benutzer oder Objekte zugeordnet sind.
- NEU: In der Nav "in Bearbeitung" sollen Efforts ohne Project korrekt angezeigt werden.
- 7.6. 09:31 "Switch User Header-Fix"
Fix: Output-Buffering in switch_user.php hinzugefügt, um "headers already sent"-Fehler beim Wechseln des Users zu verhindern.
- 7.6. 09:31 "Group-Delete-Protection"
Fix: Gruppen können jetzt nur gelöscht werden, wenn keine Benutzer oder Objekte zugeordnet sind. Schutz-Logik in groups/index.php implementiert.
- 7.6. 09:31 "Navigation-Efforts-Fix"
Fix: Efforts ohne Project (project_id=0) werden jetzt in der Navigation "in Bearbeitung" korrekt angezeigt (OpenEfforts-Query angepasst).
- NEU: ArgumentCountError: DatabaseSecurity::buildUpdate() in auth.inc.php: Fehlender DB-Link-Parameter muss ergänzt werden (siehe security.inc.php:152). Task für Fix und Testabdeckung aufgenommen.
- 7.6. 11:37 "DatabaseSecurity-buildUpdate-Fix"
Fix: Fehlender DB-Link-Parameter an DatabaseSecurity::buildUpdate() in auth.inc.php ergänzt. Fehler ist behoben, Syntax geprüft.
- NEU: SQL-Syntax-Fehler: Wenn gids leer ist, wird ein ungültiges SQL "gid IN ()" generiert. Das führt zu einem Syntax-Fehler und muss im Query-Building abgefangen werden (kein OR-Block für Gruppen, wenn gids leer ist).
- NEU: Note and task for systematic SQL-Syntax-Fix for gid IN () in all modules
- [x] SQL-Syntax-Fixes systematisch in allen Query-Building-Stellen: Kein "gid IN ()" mehr bei leeren Gruppen (customer.inc.php, effort.inc.php, statistics.inc.php, project.inc.php etc.)
- NEU: Bug: User-Settings werden von normalen Usern nicht gespeichert, weil die ID im settings.php nicht korrekt gesetzt wird. Fix: Immer aktuelle User-ID verwenden, wenn keine ID im Request ist.
- [x] Bugfix: User-Register-Flow: mode-Handling robust machen (kein PHP-Warning)
- [x] Bugfix: Customer-Save: DatabaseSecurity::escapeString() immer mit Link_ID aufrufen
- NEU: Bug: Vor- und Nachname wurden beim Speichern der User-Settings nicht übernommen, weil sie in settings.php nicht aus dem Request gelesen und nicht an save() übergeben wurden. Fix ist implementiert.
- [x] Vor-/Nachname-Fix: User-Settings speichern jetzt auch Vor- und Nachname korrekt (settings.php angepasst).
- NEU: Bug: Firstname wurde trotz Fix nicht gespeichert, weil das Feld im Template durch einen alten Scope-Effekt weiterhin readonly war. Fix: readonly-Status wird jetzt explizit gelöscht, Felder sind wieder editierbar und übertragbar. Debug-Analyse und Lösung dokumentiert.
- NEU: Bug: readonly-Problem für firstname/lastname: readonly-Status wird jetzt explizit gelöscht, Felder sind wieder editierbar und übertragbar. Debug-Analyse und Lösung dokumentiert.
- NEU: Bug: Auth-Layer-Save: In auth.inc.php wurde beim Speichern von User-Settings weiterhin der alte Wert aus $this->giveValue('firstname')/$this->giveValue('lastname') statt der neuen Werte aus $data verwendet. Fix: Jetzt werden die Werte aus $data genommen und korrekt gespeichert. (Root Cause für das Nicht-Speichern trotz POST und Debug-Logs)
- NEU: Bug: Nach erfolgreichem Speichern der User-Settings wurden Änderungen erst nach Reload sichtbar, weil das Auth-Objekt nicht aktualisiert wurde. Fix: Nach save() wird jetzt fetchAdditionalData() aufgerufen, damit Änderungen sofort im UI erscheinen.
- NEU: Bug: Vor- und Nachname wurden beim Speichern der User-Settings nicht übernommen, weil sie in settings.php nicht aus dem Request gelesen und nicht an save() übergeben wurden. Fix ist implementiert.
- [x] Vor-/Nachname-Fix: User-Settings speichern jetzt auch Vor- und Nachname korrekt (settings.php angepasst).
- NEU: Bug: Firstname wurde trotz Fix nicht gespeichert, weil das Feld im Template durch einen alten Scope-Effekt weiterhin readonly war. Fix: readonly-Status wird jetzt explizit gelöscht, Felder sind wieder editierbar und übertragbar. Debug-Analyse und Lösung dokumentiert.
- NEU: Bug: readonly-Problem für firstname/lastname: readonly-Status wird jetzt explizit gelöscht, Felder sind wieder editierbar und übertragbar. Debug-Analyse und Lösung dokumentiert.
- NEU: Bug: Auth-Layer-Save: In auth.inc.php wurde beim Speichern von User-Settings weiterhin der alte Wert aus $this->giveValue('firstname')/$this->giveValue('lastname') statt der neuen Werte aus $data verwendet. Fix: Jetzt werden die Werte aus $data genommen und korrekt gespeichert. (Root Cause für das Nicht-Speichern trotz POST und Debug-Logs)
- NEU: Bug: Nach erfolgreichem Speichern der User-Settings wurden Änderungen erst nach Reload sichtbar, weil das Auth-Objekt nicht aktualisiert wurde. Fix: Nach save() wird jetzt fetchAdditionalData() aufgerufen, damit Änderungen sofort im UI erscheinen.
- NEU: Es existieren zwei Konfigurations-Templates: `/include/config.inc.php.sample` (vollständig, modern, für Entwickler) und `/install/config.inc.php-dist` (Install-Template, bisher veraltet und unvollständig, mit Platzhaltern). Die Install-Template wurde jetzt mit allen modernen Features (Debug, Registration, Security-Defaults) aus der Sample-Datei synchronisiert und Syntax-Probleme wurden behoben. Die Templates sind jetzt konsistent und PHP-lint geprüft.
- NEU: Mobile Touch-Fix: Der User-Avatar-Dropdown (Logout-Link) ist jetzt auf Touch-Geräten (Mobile) per Touch-Click-Toggle erreichbar, sodass der Logout-Link auch auf mobilen Geräten zuverlässig funktioniert. JS/CSS und Template sind angepasst und getestet.
- [x] Mobile/Touch Dropdown-Fix für User-Avatar (Option A: Touch-Click Toggle)
- NEU: Bugfix: Gerade erstellte/laufende Efforts (begin == end) konnten wegen invertierter Logik in effort.inc.php::stop() nicht gestoppt oder gelöscht werden. Die Logik ist jetzt klar und korrekt: Nur laufende Efforts (begin == end, nicht in der Zukunft) können gestoppt/gelöscht werden. Fix implementiert und getestet.
- NEU: Workaround: Wenn ein Aufwand sofort gestoppt wird (Dauer < 1 Minute), wird der Startzeitpunkt automatisch um 1 Minute zurückgesetzt und die Dauer auf 1 Minute gesetzt. Dadurch erscheinen auch diese Aufwände korrekt als gestoppt und nicht mehr als offen. (UX/Data-Fix, effort.inc.php)
- NEU: Gruppennamen-Validierung: Die Auto-Group-Creation bei Registrierung verwendet jetzt immer den eindeutigen Gruppennamen `username_personal` (Option 3), um Namenskonflikte zu vermeiden.
- NEU: Bugfix: In efforts.php wird jetzt vor dem Aufruf von $effort->stop() geprüft, ob ein gültiges Effort-Objekt existiert (Fehlerseite bei fehlendem eid), um Fatal Errors zu verhindern.
- NEU: Bugfix: Auch beim manuellen Anlegen von Usern durch Admins wird jetzt die Auto-Group-Creation mit eindeutigem Gruppennamen (`username_personal`) und korrektem id-Feld durchgeführt, sodass keine PHP-Warning mehr auftritt und die Gruppe zuverlässig erstellt wird.
- NEU: Bug: Beim manuellen Anlegen eines Users durch Admins wird der neue User nicht automatisch der neuen persönlichen Gruppe zugeordnet. Analyse und Fix erforderlich (User muss nach Group-Creation der Gruppe hinzugefügt werden).
- NEU: UX-Bug: Beim Anlegen eines neuen Users werden Vor- und Nachname-Felder mit den Daten des aktuellen Benutzers vorausgefüllt; sie sollten leer sein. Zusätzlich sollte beim Eintippen des Usernamens dieser als Name vorgeschlagen werden (Auto-Vorschlag via JS).
- [x] Bugfix: Nach User-Anlage (Admin) neuen User automatisch persönlicher Gruppe zuordnen
- [x] UX-Fix: User-Formular bei Neuanlage – Vor-/Nachname leer, Username als Name vorschlagen
- NEU: Neue Anforderung: Im Template empty/left.ihtml soll das Logo immer angezeigt werden, aber die Navigation ("Navigation"-Text) und der GitHub-Link nur für eingeloggte User. Die letzte Änderung hat zu viel versteckt, jetzt ist die Logik selektiv umgesetzt.
- [x] Navigation und GitHub-Link in empty/left.ihtml werden jetzt selektiv für eingeloggte User angezeigt, das Logo bleibt immer sichtbar
- [x] HTML-Tabellenstruktur in empty/left.ihtml korrigiert (keine kaputten <tr>-Tags mehr, alles valide)

## Task List
- [x] Auto-Group-Creation für Registrierung (register.php): Nach Registrierung Gruppe anlegen und Nutzer zuordnen
- [x] Logo im Dark Mode: Hellen, dünnen Border um das Logo ergänzen
- [x] Gruppennamen bei Registrierung eindeutig mit _personal suffix
- [x] Null-Check für Effort-Stop-Funktion (Fehlerseite bei fehlendem eid)
- [x] Globale Passwort-Validierungsfunktionen (JS+PHP) implementieren und in password_reset.php + settings.php integrieren
- [x] Rate-Limiting für Passwort-Reset-E-Mails (1 Minute Cooldown) implementieren
- [x] note.ihtml-Template mit moderner App-Optik, Padding und Rück-Link ergänzen
- [x] Passwort-Validierung und Rate-Limiting in settings.php integrieren
- [x] Template-Kaskade für no_login Scripts (password_reset.php) absichern (Checks für $_PJ_auth, $_PJ_session_timeout in allen shared-Templates)
- [x] Rate-Limiting-Logik im Password-Reset prüft jetzt nur noch auf aktive Tokens, nicht mehr auf reset_expires/24h – verhindert False Positives beim Reset.
- [x] Session-basiertes Rate-Limiting (10 Sekunden Cooldown) für Passwort-Reset implementiert (funktioniert jetzt wie erwartet)
- [x] Session-basiertes Rate-Limiting und Template/Session-Probleme sind behoben

## Current Goal
- NEU: User-Request: Username-in-Klammern-Logik (wie in report/form.ihtml) wurde im Kundenformular (Bearbeiter-Selectbox) umgesetzt.
- NEU: Systematische Prüfung und Vereinheitlichung aller weiteren User-Selectboxen in den Templates auf diese Logik ist als nächster Schritt geplant.
- [x] Passwortlängen-/Stärkenprüfung bei User-Anlage/Edit als Admin ergänzen
- [x] Username-in-Klammern-Logik für Bearbeiter-Selectbox im Kundenformular ergänzt
- [ ] Alle weiteren User-Selectboxen systematisch auf Username-in-Klammern-Logik prüfen und ggf. anpassen
- NEU: User-Request: In der Benutzer-Auswahl von report/index.php soll der Username in Klammern angezeigt werden, wenn er vom Namen abweicht.
- NEU: User-Request: Beim Anlegen eines neuen Users soll das Namens-Autofill-JS nur bei onblur() (nicht bei jedem Keystroke) auslösen, wenn das Namensfeld leer ist.
- NEU: User-Request: Beim Anlegen/Editieren eines Users als Admin fehlt die Passwortlängen-/Stärkenprüfung noch (analog zu password_reset.php).

## Task List
- [x] Username in report/index.php User-Auswahl anzeigen, wenn abweichend
- [x] JS: Namensvorschlag bei User-Anlage nur bei onblur() und leerem Namensfeld
- [x] Passwortlängen-/Stärkenprüfung bei User-Anlage/Edit als Admin ergänzen
- [ ] Username-Anzeige in allen relevanten User-Selectboxen vereinheitlichen

## Current Goal
Nächsten User-Request abwarten
- [ ] Username-Anzeige in allen relevanten User-Selectboxen vereinheitlichen