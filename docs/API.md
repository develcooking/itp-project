# API Dokumentation

Dieses Projekt bietet eine REST-ähnliche API unter `/api/v1/` an. Zur Authentifizierung ist ein **API-Token** zwingend erforderlich. Herkömmliche Browser-Sitzungen (Sessions) werden für API-Aufrufe nicht mehr akzeptiert.

## API-Token

Der API-Token ist ein benutzergebundener Sicherheitsschlüssel, der als einziger Authentifizierungsfaktor für die API dient.

### Token erhalten & verwalten
1. Navigieren Sie zu Ihrem **Profil**.
2. Scrollen Sie zum Bereich **API-Zugriff** am Ende der Seite.
3. Klicken Sie auf **API-Token aktivieren**, um einen neuen Token zu generieren.
4. Sie können den Token jederzeit **kopieren**, einen **neuen generieren** (der alte wird ungültig) oder den Zugriff vollständig **widerrufen**.

> **Sicherheitshinweis:** Behandeln Sie Ihren API-Token wie ein Passwort. Geben Sie ihn niemals an Dritte weiter. Falls der Token kompromittiert wurde, generieren Sie umgehend einen neuen.

### Verwendung in Anfragen
Um sich bei der API zu authentifizieren, **muss** der Token im HTTP-Header der Anfrage mitgeschickt werden:

**Header:** `X-API-Token: [Ihr-Token]`

#### Beispiel mit curl:
```bash
curl -X GET "https://ihre-domain.de/api/v1/appointments.php" \
     -H "X-API-Token: 5f3d...a1b2" \
     -H "Content-Type: application/json"
```

## Technische Implementierung

### Authentifizierungs-Ablauf
Die Logik befindet sich zentral in der `api/v1/api_helper.php`. 

1. Bei jeder Anfrage an einen API-Endpunkt wird `tryTokenAuth()` aufgerufen.
2. **Session-Isolation:** Zuerst werden alle vorhandenen Sitzungsdaten (`$_SESSION`) für den aktuellen Request-Kontext gelöscht, um sicherzustellen, dass nur der Token zur Identifizierung verwendet wird. Browser-Cookies werden ignoriert.
3. Die Funktion prüft auf den Header `X-API-Token`.
4. Ist ein gültiger Token vorhanden, wird in der Datenbank nach dem zugehörigen Benutzer gesucht.
5. **Sicherheitsprüfung:** Der Zugriff wird nur gewährt, wenn der Benutzer **aktiviert** und **nicht gesperrt** ist.
6. Bei Erfolg wird die globale Variable `$_SESSION` temporär für die Dauer der Anfrage mit der `userId`, `userName` und `role` des Benutzers gefüllt. Dadurch bleiben alle bestehenden Berechtigungsprüfungen (`checkAdmin()`, `hasAccessToJob()`) funktionsfähig.

### Datenbank
Der Token wird in der Tabelle `Users` in der Spalte `apiToken` gespeichert (Migration `019_add_apiToken_to_Users.sql`).

### Sicherheit
- Die Generierung erfolgt über `bin2hex(random_bytes(32))`, was einen kryptographisch sicheren 64-Zeichen Hex-String erzeugt.
- Token sind eindeutig (`UNIQUE` Constraint in der DB).
- Bei Widerruf oder Neugenerierung wird der alte Wert in der Datenbank sofort überschrieben/gelöscht.
