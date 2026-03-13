# ITP-Projekt Zwei Ausländer / Ausbildungsportal

### 1- SSH Verbindung mit GitHub: 
SSH-Key ertellen:
    ssh-keygen -t ed25519 -C "deine_email@example.com"

2- Drücke einfach Enter, um den Standardpfad zu verwenden.    
Öffentlichen Schlüssel anzeigen
    cat ~/.ssh/id_ed25519.pub

3- Kopiere die komplette Ausgabe (beginnt mit ssh-ed25519).
Key bei GitHub hinzufügen
Öffne: https://github.com/settings/keys
Klicke auf New SSH key
Key: den kopierten Schlüssel einfügen
Speichern

### Setup
Lies für initiales Setup: [Setup.md](docs/Setup.md)

Erstelle eine .env Datei mit den folgenden informationen:

```ini
DB_HOST=127.0.0.1
DB_USER=xxx
DB_PASS=xxx
DB_NAME=xxx
APP_ENV=production
```
Wenn APP_ENV nicht `development` ist, werden sql errors nicht angezeigt.


IPv6 : `2001:1640:18e:8000:be24:11ff:fe45:c45f`

# Rolle
Rollen werden als string ziffern gespeichert es gibt drei optionen:
```
0 = Admin
1 = Leher
2 = ausbilder
```