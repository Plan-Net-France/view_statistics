# View-Statistics Erweiterung konfigurieren

## Allgemeines Konfiguration

Im Erweiterungsmanager können in den Erweiterungseinstellungen die folgenden globalen Einstellungen vorgenommen werden:

*   **Who should be tracked?**
    Mit dieser Einstellung können Sie das Tracking-Verhalten festlegen. Mögliche Optionen sind:
    *   **nonLoggedInOnly**
        Hier werden ausschließlich Seitenaufrufe von nicht eingeloggten Benutzern getrackt.
    *   **loggedInOnly**
        Hier werden ausschließlich Seitenaufrufe von eingeloggten Benutzern getrackt.
    *   **all**
        Hier werden alle Seitenaufrufe getrackt, egal on eingeloggter oder nicht eingeloggter Benutzer.
*   **Track frontend user ID?**
    Wenn diese Option aktiviert ist, wird in jedem Tracking-Datensatz gespeichert, welcher eingeloggter Frontend-Benutzer
    diesen ausgelöst hat. Zusätzlich wird auch die Dauer gespeichert, die der Frontend-Benutzer bereits eingeloggt ist.
*   **Track IP address?**
    Wenn diese Option aktiviert ist, wird in jedem Tracking-Datensatz die IP der Anfrage eingesetzt.
*   **Track user agent?**
    Wenn diese Option aktiviert ist, wird in jedem Tracking-Datensatz der User Agent (z. B. Browser) gespeichert.
*   **Track login duration?**
    Wenn diese Option aktiviert ist, wird in jedem Tracking-Datensatz gespeichert, wie lange der Frontend-Benutzer
    bereits eingeloggt ist.
