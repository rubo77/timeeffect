# TimeEffect Docker & User Creation Debugging Plan

## Notes
- Initial port 3306 conflict was due to MySQL already running on host; resolved by stopping mysql before docker-compose up.
- The Dockerfile's removal of the Apache /icons/ alias is unrelated to the main app loading.
- Apache and MariaDB containers start correctly; application files are present in /var/www/html in the container.
- No index.php exists; index.html redirects to inventory/customer.php (the real entry point).
- nginx was still running and serving its default page on port 80, causing confusion; script now disables nginx and mysql.
- User creation in user/index.php throws multiple "Undefined variable" PHP warnings (e.g. $telephone, $email, $password, etc.)
- The document root in the container is /var/www/html, which is where the application files are served from.
- The te-docker-restart.sh script now properly stops and disables nginx to prevent port conflicts.
- The PHP warnings are likely due to uninitialized variables in the user/index.php script.
- Undefined variable warnings in user/index.php have been fixed by initializing all relevant variables from $_REQUEST.
- Undefined array key warning in user.inc.php has been fixed by checking for key existence before accessing permission names.
- PHP 8.4 deprecation warnings for substr() in data.inc.php fixed by ensuring null values are not passed to substr().
- Investigating root cause of null access values in data.inc.php (not just handling the symptom).
- Root cause found: Effort and Project classes did not initialize access field with default if missing, causing nulls to override DB default. Fixed by initializing access in Effort::initEffort() and Project::save(). Customer class already had a fallback, though with a slightly different default.
- Previous fixes did not resolve the errors; root cause likely in ACL (Access Control List) logic.
- Diagnostic logging (tag: ACL_DEBUG) added to getUserAccess() in data.inc.php to identify when/why access is null.
- Root cause: Customer, Project, Effort Konstruktor riefen getUserAccess() zu früh auf (ohne ID/access), was zu NULL-ACL führte. Jetzt wird getUserAccess() nur bei gültigen Daten aufgerufen, sonst Default-ACL gesetzt.
- PHPUnit installiert, ACL-Test geschrieben und ausgeführt; Testlauf aktuell mit Fehler wegen fehlender Verzeichnisse/Globals.
- WICHTIG: Keine Fallbacks mehr in ACL/Constructor-Logik! Stattdessen Ursache analysieren, warum getUserAccess() zu früh (ohne ID) aufgerufen wird, und ggf. den Aufruf verschieben. getUserAccess() darf selbst default-ACL liefern, aber keine Konstruktor-Fallbacks! (User-Präferenz)
- Fallbacks aus allen Konstruktoren entfernt. getUserAccess() gibt jetzt direkt das Default-ACL zurück, wenn access NULL ist. Konstruktoren rufen immer getUserAccess() auf.
- Korrigiere PHPUnit-Testlauf (Verzeichnis/Globals) falls nötig
- Wenn access NULL ist, wird jetzt ein fataler Fehler ausgelöst (die() mit Fehlerlog). Das ist ein Programmierfehler, kein legitimer Zustand!
- Nächster Schritt: Ursachenanalyse, warum Objekte ohne access erzeugt werden (statt Symptome abzufangen)
- Root Cause identifiziert: Customer-Objekte werden mit leerem String als ID erzeugt, wenn keine cid im Request vorhanden ist (z.B. in inventory/customer.php oder report/index.php). Das führt zu fatalem Fehler, weil keine Daten geladen werden und access NULL bleibt. Objekt-Erstellung muss auf gültige ID geprüft werden (und ggf. gar nicht erfolgen, wenn keine ID vorliegt).
- Customer-Objekt-Erstellung ohne gültige ID wird jetzt systematisch verhindert (in inventory/customer.php und report/index.php). Fehler tritt nicht mehr auf.
- Fehlerbehebung getestet: FATAL ERROR tritt nicht mehr auf, wenn keine gültige ID für Customer übergeben wird. User ohne Rechte sieht keine Kunden mehr in der Übersicht; ACL greift korrekt.
- Project-Objekt-Erstellung ohne gültige ID wird jetzt ebenfalls systematisch verhindert (in inventory/projects.php). Fehler tritt auch dort nicht mehr auf.
- Es gab einen FATAL ERROR für Effort-Objekte, wenn keine gültige ID übergeben wird (z.B. in inventory/efforts.php). Muss analog abgesichert werden.
- Effort-Objekt-Erstellung ohne gültige ID wird jetzt ebenfalls systematisch verhindert (in inventory/efforts.php). Fehler tritt auch dort nicht mehr auf.
- ACL-Problem: User sehen weiterhin Kunden/Projekte, auf die sie keinen Zugriff haben. CustomerList und ProjectList müssen ACL korrekt filtern (User "ruben" sieht zu viele Kunden/Projekte).
- Effort-Objekt-Erstellung ohne gültige ID wurde erfolgreich abgesichert. Jetzt liegt der Fokus auf der ACL-Filterung in CustomerList und ProjectList.
- Temporäres ACL-Debug-Logging in CustomerList hinzugefügt, um Ursache für zu weite Sichtbarkeit zu analysieren.
- ACL-Debug zeigt: User ruben ist kein Admin, aber sieht alle Kunden. Nächster Schritt: access-Felder der Kunden und generierte Query prüfen, dann ACL-Filter korrigieren.
- ACL-Problem identifiziert: access=rwxr-xr-- gibt Welt-Lesezugriff, ACL-Query gibt alle Kunden zurück, auch wenn User keinen direkten Zugriff hat. Nächster Schritt: PHPUnit-Test, der dieses Verhalten prüft.
- ACL-Test zeigt: Auch Kunden ohne Welt-Lesezugriff werden angezeigt, wenn Gruppen-Lesezugriff besteht und User in der Gruppe ist. Testlogik und Testfälle erfolgreich ausgeführt. Nächster Schritt: ACL-Filter korrigieren, sodass wirklich nur berechtigte Kunden angezeigt werden.
- ACL-Logik ist korrekt: Gruppenmitglieder dürfen Kunden sehen, wenn Gruppen-Lesezugriff gesetzt ist. Test mit Kunde ohne Gruppen-/Welt-Zugriff bestätigt die Filterung funktioniert wie erwartet. Nächster Schritt: Fehleranalyse beim Anlegen eines neuen Kunden als User "ruben" (giveValue() on null in form.ihtml).

## Task List
- [x] Diagnose port 3306 conflict and docker-compose startup
- [x] Confirm Apache and MariaDB containers are healthy and running
- [x] Analyze document root in container and index.html redirect
- [x] Fix te-docker-restart.sh to properly stop/disable nginx and mysql
- [x] Fix undefined variable warnings in user/index.php when creating a user
- [x] Verify user creation works without warnings
- [x] Fix PHP 8.4 substr() deprecation warnings in data.inc.php
- [x] Investigate root cause of null access value in data.inc.php
- [x] Add diagnostic logging for access field in ACL logic
- [x] Investigate ACL logic as root cause of access/null issues
- [x] Analyze logs for ACL_DEBUG entries and identify faulty data flow
- [x] Fix Konstruktoren von Customer, Project, Effort: getUserAccess() nur bei gültigen Daten aufrufen
- [x] Installiere PHPUnit und schreibe ACL-Unit-Test
- [x] Behebe PHPUnit-Testfehler durch fehlende Verzeichnisse/Globals
- [x] Entferne Fallbacks aus ACL/Constructor-Logik und analysiere, warum getUserAccess() ohne ID aufgerufen wird
- [x] Bei access=NULL: fataler Fehler statt Fallback
- [x] Analysiere, warum Objekte ohne access erzeugt werden (Root Cause)
- [x] Verhindere Customer-Objekt-Erstellung ohne gültige ID (z.B. in inventory/customer.php, report/index.php)
- [x] Verhindere Project-Objekt-Erstellung ohne gültige ID (z.B. in inventory/projects.php)
- [x] Teste, ob Fehler (FATAL ERROR: access field is null) nicht mehr auftritt
- [x] Verhindere Effort-Objekt-Erstellung ohne gültige ID (z.B. in inventory/efforts.php)
- [x] Schreibe einen PHPUnit-Test, der prüft, dass User ohne Welt-Lesezugriff keine fremden Kunden sieht
- [x] ACL-Filter und Rechteverhalten mit Testdaten und Debug-Log verifizieren
- [x] Fehleranalyse: giveValue() on null beim Kunden anlegen als User "ruben"

## Current Goal
Weitere Fehlerbeobachtung und Regressionstests