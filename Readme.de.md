# Statistiken für TYPO3-Frontend-Benutzer anzeigen

Diese Erweiterung fügt Statistikdatensätze in jede Seitenansicht ein. Diese Erweiterung verwendet keine Cookies!

## Features

* Tracking von Seitenaufrufen und Extension-Datensätzen
* Es ist konfigurierbar, welche Aufrufe getrackt werden sollen:
    * alle Seitenaufrufe (angemeldete und nicht angemeldete Besucher)
    * nur angemeldete Besucher
    * nur nicht angemeldete Besucher
* Optional: Tracking der ID des angemeldeten Benutzers
* Optional: Tracking der IP-Adresse
* Optional: Tracking der Anmeldezeiten für angemeldete Frontend-Benutzer
* Optional: Tracking des User Agents (z. B. welcher Browser benutzt wurden)
* Einfache Konfiguration im Extension-Manager und über Typoscript
* Backend-Modul mit:
    * Übersicht über alle Trackings
    * Auflistung nach Seite
    * Auflistung nach Benutzer
    * Auflistung nach Objekten (Downloads, Nachrichten, Shop-Produkte, Portfolios und mehr)
    * CSV-Export der Trackingdaten
    * Benutzereinschränkung: Der Administrator sieht die gesamten Tracking-Daten. Der Editor sieht nur die Daten
      der aktuell ausgewählten Seite.
* Tracking für Seiten und Objekte wie:
    * Anzeigen von Nachrichten (EXT:news)
    * Herunterladen von Dateien (EXT:downloadmanager)
    * Produkte (EXT:shop)
    * Immobilien (EXT:openimmo)
    * Konfigurieren Sie Tracking-Konfigurationen per Typoscript

> ** Achtung: **
>
> Diese Erweiterung trackt nicht, wenn Sie gleichzeitig mit einem Backend-Benutzer angemeldet sind
> und das Frontend mit demselben Domainnamen aufrufen. Verwenden Sie in diesem Fall einen anderen Browser für den
> Aufruf des Frontends, um das Tracking auszulösen! Selbst ein Inkognito-Fenster des gleichen Browsers kann u. U.
> verhindern, dass das Tracking ausgeführt wird.

### Links

*   [TYPO3 View-Statistics Product details][link-typo3-view-statistics-product-details]
*   [TYPO3 View-Statistics Documentation][link-typo3-view-statistics-documentation]

[link-typo3-view-statistics-product-details]: https://www.coding.ms/de/typo3-extensions/typo3-view-statistics "TYPO3 View-Statistics Produkt-Details"
[link-typo3-view-statistics-documentation]: https://www.coding.ms/de/documentation/typo3-view-statistics "TYPO3 View-Statistics Dokumentation"
