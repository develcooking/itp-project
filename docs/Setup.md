Server setup:

Server Base: Ubuntu 24.04.3 LTS

Diese Pakete haben wir installiert
`apt install curl wget git php libapache2-mod-php apache2 sudo neovim mariadb-server php-mysql composer`

Wir haben gemeinsam git auf jeden Computer eingerichtet. Dazu haben wir einen SSH-Key in Github hinterlegt und anschließend das Repository auf jeden PC synchronisiert.

Anschließend haben wir das git repo auch auf den server unter
`/var/www/` eingebunden

Anschließend haben wir in `/etc/apache2/sites-available/000-default.conf` editiert und den directory name von html zu it-project geändert