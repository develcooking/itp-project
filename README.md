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
Erstelle eine .env Datei mit den folgenden informationen:
```ini
DB_HOST=127.0.0.1
DB_USER=xxx
DB_PASS=xxx
DB_NAME=xxx
```