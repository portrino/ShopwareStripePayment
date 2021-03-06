## 2.2.1

### en

* Fixes a display error in the login when using Shopware 5.3.

### de

* Behebt einen Darstellungsfehler im Login unter Shopware 5.3.


## 2.2.0

### en

* Adds compatiblity with Shopware 5.3

### de

* Fügt die Kompatiblität zu Shopware 5.3 hinzu


## 2.1.4

### en

* Fixes an error that occurred during checkout in Shopware versions < 5.2.0

### de

* Behebt einen Fehler im Checkout unter Shopware Versionen < 5.2.0


## 2.1.3

### en

* Changes the creation of statement descriptors to prevent skipping order numbers
* Fixes an error that occurred when removing a credit card from a customer account

### de

* Passt die Erstellung von Verwendungszwecken an, sodass keine Bestellnummern mehr übersprungen werden
* Behebt einen Fehler beim Entfernen einer Kreditkarte aus dem Kundenkonto


## 2.1.2

### en

* Fixes an error that occurred during the plugin update

### de

* Behebt einen Fehler, der während des Plugin-Updates auftrat


## 2.1.1

### en

* Improves the internal handling of text snippets to make explicit escaping of single quotes in snippets obsolete

### de

* Verbessert die interne Handhabung von Textbausteinen, sodass einfachen Anführungszeichen in Textbausteinen kein Backslash mehr vorangestellt werden muss


## 2.1.0

### en

* Makes the title snippets of the CVC info popup localizable
* Adds respective logos for all payment methods provided by this plugin
* Adds a new config element for showing/hiding the payment provider logos in the payment form

### de

* Überarbeitet das CVC-Popup, sodass der Titel nun ebenfalls als Textbaustein hinterlegt werden kann
* Fügt die Logos aller Zahlungsarten hinzu, die von diesem Plugin bereitgestellt werden
* Fügt der Plugin-Konfiguration ein neues Feld "Logos der Zahlungsarten anzeigen" hinzu, mithilfe dessen die Logos der Zahlungsarten im Bezahlungs-Formular angezeigt oder ausgeblendet werden können


## 2.0.6

### en

* Adds a new config field for specifying a custom statement descriptor that is used e.g. for SOFORT payments
* Improves the internal handling of text snippets to fix some errors causing the credit card payment form to not load

### de

* Fügt der Plugin-Konfiguration ein neues Feld "Verwendungszweck" hinzu, mithilfe dessen der Verwendungszweck von z.B. SOFORT Zahlungen gesetzt werden kann
* Verbessert die interne Handhabung von Textbausteinen um einige Fehler zu beheben, welche dazu führten, dass das Formular für Kreditkartenzahlungen nicht geladen wurde


## 2.0.5

### en

* Fixes an error in the snippets, which could cause an error when loading the credit card payment form

### de

* Behebt einen Fehler in den Textbausteinen, der dazu führen konnte, dass die Form für Kreditkartenzahlungen nicht geladen wurde


## 2.0.4

### en

* Further improves the construction of statement descriptors

### de

* Verbessert die Erzeugung des Verwendungszwecks


## 2.0.3

### en

* Fixes an error in some payment methods that was caused by invalid characters in the statement descriptor

### de

* Behebt einen Fehler in manchen Zahlungsarten, der durch ungültige Zeichen im Verwendungszweck hervorgerufen wurde


## 2.0.2

### en

* Fixes an error that caused more than one order to use the same order number
* Adds aadditional safeguards to prevent duplication of order numbers

### de

* Behebt einen Fehler der dazu führte, dass Bestellnummern teilweise mehrfach verwendet wurden
* Fügt zusätzlich Schutzmaßnahmen ein, die eine Mehrfachverwendung von Bestellnummern verhindern


## 2.0.1

### en

* Fixes an error in the creation of "SOFORT Überweisung" payments

### de

* Behebt einen Fehler beim Erstellen von Zahlungen mittels "SOFORT Überweisung"


## 2.0.0

### en

**Note:** Please refer to the [plugin documentation](https://docs.google.com/document/d/1FfZU0AqEWtiXd7Ito6e7UiLzfpP5F_D8CT9gtogaZlk) before activating any of the new payment methods.

* Adds a new, disabled payment method "Stripe Kreditkarte (mit 3D-Secure)"
* Adds a new, disabled payment method "Stripe SEPA-Lastschrift"
* Adds a new, disabled payment method "Stripe SOFORT Überweisung"
* Adds a new, disabled payment method "Stripe Giropay"
* Adds a new, disabled payment method "Stripe Apple Pay"
* Adds a new, disabled payment method "Stripe Bancontact"
* Adds a new, disabled payment method "Stripe iDEAL"

### de

**Hinweis:** Bitte lesen Sie zunächste die [Plugin-Dokumentation](https://docs.google.com/document/d/1FfZU0AqEWtiXd7Ito6e7UiLzfpP5F_D8CT9gtogaZlk), bevor Sie eine der neuen Zahlungsarten aktivieren.

* Fügt eine neue, deaktivierte Zahlungsart "Stripe Kreditkarte (mit 3D-Secure)" hinzu
* Fügt eine neue, deaktivierte Zahlungsart "Stripe SEPA-Lastschrift" hinzu
* Fügt eine neue, deaktivierte Zahlungsart "Stripe SOFORT Überweisung" hinzu
* Fügt eine neue, deaktivierte Zahlungsart "Stripe Giropay" hinzu
* Fügt eine neue, deaktivierte Zahlungsart "Stripe Apple Pay" hinzu
* Fügt eine neue, deaktivierte Zahlungsart "Stripe Bancontact" hinzu
* Fügt eine neue, deaktivierte Zahlungsart "Stripe iDEAL" hinzu


## 1.1.1

### en

* From now on the theme files of this plugin are included when compiling them using the console command

### de

* Ab sofort werden die Theme-Dateien des Plugins auch beim Kompilieren über die Konsole berücksichtigt


## 1.1.0

### en

* From now on it is possible to configure different stripe accounts for different subshops
* It is now possible to hide the 'save credit card' checkbox
* The cleared date is now set correctly upon checkout
* Error messages shown during the payment process are now available as text snippets
* Improves the layout of the credit card management in the account settings on small displays

### de

* Ab sofort ist es möglich, verschiedene stripe-Accounts für verschiedene Subshops zu konfigurieren
* Es ist nun möglich, die Checkbox zum Speichern von Kreditkarten auszublenden
* Bei Bestellabschluss wird nun das Zahlungsdatum korrekt gesetzt
* Fehlermeldungen beim Bezahlvorgang sind ab sofort als Text-Schnipsel hinterlegt
* Verbessert die Darstellung der Kreditkartenverwaltung in den Benutzereinstellungen auf kleinen Displays


## 1.0.9

### en

* Fixes a UI bug in the credit card management in the account settings

### de

* Behebt eine Darstellungsfehler in der Kreditkartenverwaltung in den Benutzereinstellungen


## 1.0.8

### en

* Improves the compatibility with Shopware 5.2

### de

* Verbessert die Kompatibilität mit Shopware 5.2


## 1.0.7

### en

* Fixes a crash when saving a credit card

### de

* Behebt einen Fehler beim Speichern von Kreditkarten


## 1.0.6

### en

* Improves the PHP 7 compatibility in Shopware 5 (<= 5.0.3)

### de

* Verbessert die PHP 7 Kompatibilität unter Shopware 5 (<= 5.0.3)


## 1.0.5

### en

* You can now localize the "new card" selection text of the payment form

### de

* Der "Neue Karte" Auswahltext der Zahlungsform kann nun lokalisiert werden


## 1.0.4

### en

* Fixes a UI bug when using the *PayPal Plus* plugin

### de

* Behebt einen Darstellungsfehler bei der Verwendung des *PayPal Plus* Plugins


## 1.0.3

### en

* Fixes a broken text snippet, which triggered an error while loading the payment form

### de

* Repariert einen kaputten Text-Schnipsel, der einen Fehler beim Laden der Zahlungsform verursachte


## 1.0.2

### en

* Fixes a bug that caused some Shopware 5 shops to display a warning instead of the order summary after completing the checkout

### de

* Behebt einen Fehler, der in manchen Shopware 5 Installationen dazu führte, dass nach Abschluss der Bestellung statt der Zusammenfassung eine Warnmeldung angezeigt wurde


## 1.0.1

### en

* Fixes a bug in the checkout in case the stripe payment method was disabled
* Improves the PHP 7 compatibility

### de

* Behebt einen Fehler im Bestellabschluss in dem Fall, dass die stripe Zahlungsart deaktiviert war
* Verbessert die PHP 7 Kompatibilität


## 1.0.0

### en

This is the initial release of the official stripe plugin.

### de

Dies ist das erste Release des offiziellen stripe Plugins.
