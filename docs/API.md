# API Dokumentation

Dieses Projekt bietet eine REST-ähnliche API unter `/api/v1/` an.

## Authentifizierung (Token-Only)

Zur Authentifizierung **muss** ein API-Token im HTTP-Header mitgeschickt werden. Browser-Sitzungen (Cookies) werden ignoriert.

**Header:** `X-API-Token: [Ihr-Token]`

Dieses kann auf der Profilseite erstellt werden!

---

## Endpunkte & Berechtigungen

### 1. Termine (`appointments.php`)
Verwaltet Kalendereinträge und Wiederholungen.

| Methode | Aktion | Zugriff |
| :--- | :--- | :--- |
| **GET** | Alle Termine abrufen | Alle (Filtert automatisch nach zugewiesenen Bereichen) |
| **GET** | Einzelnen Termin (`?id=X`) | Alle (Prüft Bereichszugriff) |
| **POST** | Termin erstellen | Admin, Lehrer |
| **PUT** | Termin ändern | Admin, Ersteller |
| **DELETE** | Termin löschen | Admin, Ersteller |

*   **Hardening:** 
    *   `recurrenceType` ist auf `none`, `weekly`, `monthly` limitiert.
    *   `recurrenceInterval` ist auf Werte zwischen `1` und `24` begrenzt.
    *   `title` und `description` werden gegen XSS gereinigt.

### 2. Berufsbereiche (`jobs.php`)
Verwaltet die Kategorien/Bereiche.

| Methode | Aktion | Zugriff                                      |
| :--- | :--- |:---------------------------------------------|
| **GET** | Alle Bereiche abrufen | Admin                                        |
| **GET** | Einzelnen Bereich (`?id=X`) | (Admin)Alle (Normaler Usersofern zugewiesen) |
| **POST** | Bereich erstellen | Admin                                        |
| **PUT** | Bereich umbenennen | Admin                                        |
| **DELETE** | Bereich löschen | Admin                                        |

*   **Hardening:** 
    *   Namen werden gegen XSS gereinigt.
    *   Strikte Prüfung auf numerische IDs.

### 3. Benutzer (`users.php`)
Verwaltet Benutzerkonten und Rollen.

| Methode | Aktion | Zugriff |
| :--- | :--- | :--- |
| **GET** | Benutzerliste abrufen | Admin |
| **POST** | Benutzer anlegen | Admin |
| **PUT** | Profildaten ändern | Admin, Eigenes Konto |
| **DELETE** | Benutzer löschen | Admin |

*   **Hardening:** 
    *   Rollen (`role`) sind auf `Ausbilder`, `Lehrer`, `Admin` whitelisted.
    *   Metadaten werden gegen XSS gereinigt.
    *   E-Mails werden serverseitig validiert.

---

## Sicherheitsmaßnahmen (Hardening)

1.  **XSS-Schutz**: Alle eingehenden Strings werden über den `HtmlSanitizer` gereinigt. Ein Speichern von bösartigem HTML oder JavaScript ist nicht möglich.
2.  **Input Validation**: 
    *   IDs werden strikt als positive Ganzzahlen validiert.
    *   Enums (wie Rollen oder Wiederholungstypen) werden gegen eine Whitelist geprüft. Unbekannte Werte werden abgelehnt oder auf Defaults gesetzt.
3.  **Logische Zugriffskontrolle**: Selbst mit einem gültigen Token prüft das System bei jedem Schreibzugriff, ob der Benutzer der **Eigentümer** der Ressource ist oder **Admin-Rechte** besitzt.
4.  **Status-Check**: Deaktivierte oder gesperrte Benutzerkonten verlieren sofort jeglichen API-Zugriff, auch wenn der Token technisch korrekt ist.

---

## Aktuelle Einschränkungen (Was fehlt?)

*   **Pagination**: Listen-Abrufen geben immer alle Datensätze zurück. Dies ist nicht gut für die Performence bspw, bei den Terminen.
*   **Forum**: Das Forum hat aktuell keine API-Schnittstelle
*   **Detaillierte Fehlermeldungen**: Die API liefert zwar HTTP-Statuscodes (400, 401, 403, 404, 500), aber die Beschreibungen sind teilweise sehr generisch.
*   **Rate Limiting**: Es gibt aktuell keine Begrenzung für die Anzahl der Anfragen pro Minute. Dies ist aber nicht essentiel, da so ein fall nicht im Scope des BS-Projektes eintreten wird.
