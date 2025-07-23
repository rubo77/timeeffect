# TimeEffect Online Deployment Guide

## 🚀 Deployment auf Online-Server (PHP 8.4)

### Schritt 1: Repository aktualisieren
```bash
# Auf dem Online-Server
cd /path/to/your/timeeffect
git pull origin master
```

### Schritt 2: Composer Dependencies installieren
```bash
# Composer installieren falls nicht vorhanden
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Dependencies installieren
composer install --no-dev --optimize-autoloader
```

### Schritt 3: Environment konfigurieren

#### Option A: Aus bestehender Konfiguration generieren (EMPFOHLEN)
```bash
# .env aus bestehender config.inc.php generieren
php dev/generate_env_from_config.php

# Generierte .env prüfen und anpassen
nano .env
```

#### Option B: Manuell erstellen
```bash
# .env Datei erstellen
cp .env.example .env

# .env bearbeiten mit korrekten Datenbankdaten:
nano .env
```

Beispiel `.env` Konfiguration:
```env
# Database Configuration
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
DB_PREFIX=te_

# Application Settings
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

### Schritt 4: PHP-Konfiguration prüfen
```bash
# PHP Version prüfen (sollte 8.4+ sein)
php -v

# Benötigte Extensions prüfen
php -m | grep -E "(mysqli|pdo|json|mbstring)"

# PHP short_open_tag aktivieren (falls nötig)
# In php.ini: short_open_tag = On
```

### Schritt 5: Berechtigungen setzen
```bash
# Schreibrechte für Install-Verzeichnis
mkdir -p install/include
chmod 755 install/include
cp install/config.inc.php-dist install/include/config.inc.php
chmod 666 install/include/config.inc.php

# Log-Verzeichnis erstellen
mkdir -p logs
chmod 755 logs
chown www-data:www-data logs  # oder entsprechender Web-Server-User
```

### Schritt 6: Bootstrap in bestehende Dateien einbinden
Füge am Anfang der Haupt-PHP-Dateien hinzu:
```php
<?php
// Am Anfang von index.php, admin.php, etc.
require_once __DIR__ . '/bootstrap.php';
```

### Schritt 7: Installation durchführen
1. Besuche: `https://your-domain.com/install/`
2. Folge dem Installationsassistenten
3. Die Datenbankverbindung sollte automatisch funktionieren

## 🔧 Troubleshooting

### Problem: "mysql_* function not found"
**Lösung**: Alle mysql_* Funktionen wurden zu mysqli_* migriert. Der Fix ist bereits im Repository.

### Problem: "short_open_tag" Fehler
**Lösung**: 
```bash
# In php.ini aktivieren:
short_open_tag = On

# Apache/Nginx neu starten
systemctl restart apache2  # oder nginx
```

### Problem: Composer nicht gefunden
**Lösung**:
```bash
# Composer global installieren
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### Problem: Datenbankverbindung fehlschlägt
**Lösung**:
1. `.env` Datei prüfen
2. Datenbankbenutzer und -rechte prüfen
3. MySQL-Service läuft: `systemctl status mysql`

## 📋 Checkliste für Deployment

- [ ] Git Repository aktualisiert (`git pull`)
- [ ] Composer Dependencies installiert
- [ ] `.env` Datei konfiguriert
- [ ] PHP 8.4+ läuft mit mysqli Extension
- [ ] `short_open_tag = On` in php.ini
- [ ] Berechtigungen für `install/include/` gesetzt
- [ ] Bootstrap in Haupt-Dateien eingebunden
- [ ] Installation über Web-Interface durchgeführt
- [ ] Funktionalität getestet

## 🎯 Nach dem Deployment

1. **Sicherheit**: Lösche oder schütze das `/install/` Verzeichnis
2. **Performance**: Aktiviere OPcache in php.ini
3. **Monitoring**: Prüfe Logs in `/logs/` Verzeichnis
4. **Backup**: Erstelle regelmäßige Datenbank-Backups

## 🆘 Support

Bei Problemen:
1. Prüfe PHP Error Logs
2. Prüfe `/logs/app.log` (falls vorhanden)
3. Teste Datenbankverbindung separat
4. Prüfe Apache/Nginx Error Logs
