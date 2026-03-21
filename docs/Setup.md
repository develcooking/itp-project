# Server setup:

## Betriebssystem:
Server Base: Ubuntu 24.04.3 LTS

### Pakete und deren Setup

Diese Pakete haben wir installiert
`apt install curl wget git php libapache2-mod-php apache2 sudo neovim mariadb-server php-mysql composer php-xml`

Wir haben gemeinsam git auf jeden Computer eingerichtet. Dazu haben wir einen SSH-Key in Github hinterlegt und anschließend das Repository auf jeden PC synchronisiert.

Anschließend haben wir das git repo auch auf den server unter
`/var/www/` eingebunden

Anschließend haben wir in `/etc/apache2/sites-available/000-default.conf` editiert und den directory name von html zu it-project geändert

### Datenbank Setup
Um die Datenbank aufzusetzen, logge dich als root user in die MariaDB ein. Und erstelle einen User bspw: `user1` eine Datenbank bspw: `Database` und gebe diesen Nutzer alle Rechte auf die Datenbank, die er braucht.

Gehe anschließend die Migrationen in [../database/migrations](../database/migrations) durch um die Datenbank Tabellen zur erstellen

Nun Fehlt nur noch der erste Admin-Account. Gehe nun auf die Webseite wahrscheinlich auf Port 80.
Gehe die Registrierung durch, und wähle Admin als Rolle aus.

Nun gehe wieder auf die CMD von MariaDB und update deinen Admin-Account zu activated = 1

**Wichtig** stelle Sicher, dass du der User mit der userId 1 bist, sonst würdest du einen nicht überprüften Nutzer aktivieren.
```sql
UPDATE Users
SET activated = 1
WHERE userId = 1;
```