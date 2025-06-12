# Pulszähler
Das Modul ermöglicht es, einen Zähler auf einer Variable aufzusetzen, der in einem definierten Zeitraum die Impulse zählt

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [Konfiguration](#6-konfiguration)
7. [Visualisierung](#7-visualisierung)
8. [PHP-Befehlsreferenz](#8-php-befehlsreferenz)


### 1. Funktionsumfang

* Schaltvorgänge einer Variable überwachen und Zählen

### 2. Voraussetzungen

- IP-Symcon ab Version 8.0

### 3. Software-Installation

* Über den Module Store das 'Pulszähler'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen: https://github.com/migodev/PulseCounter

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'Pulszähler'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

### 5. Statusvariablen und Profile

Es werden keine Profile angelegt.
Es werden 4 Statusvariablen angelegt:

Name                  | Typ					| Funktion
--------------------- | ------------------- | -------------------
Ergebnis 			  | Boolean				| Schaltet auf true wenn das Limit im Vorgegeben Zeitraum erreicht wurde
Counter				  | Integer				| Zeigt den Counter-Stand an
Restlaufzeit		  | Integer				| Restlaufzeit in Sekunden für eine Messung
Differenzzeit		  | Integer				| Differenzeit zwischen 2 Auslösungen

### 6. Konfiguration

| Eigenschaft                                           |   Typ   | Standardwert | Funktion                                                  |
|:------------------------------------------------------|:-------:|:-------------|:----------------------------------------------------------|
| Eingangsvariable                                      | integer | 0            | Die TasterVariable auf die reagiert werden soll, z.B. Status AO oder Gedrückt |
| Eingangswert                                      	| integer | 0            | Auf welchen Wert soll reagiert werden, true, false oder beides. Nur wenn dieser Wert durch die Eingangsvariable ausgelöst wird, wird der Counter hochgezählt |
| Auslöser		                                        | integer | 0            | Entscheidung ob nur bei einer Änderung oder jeder Aktualisierung der Eingangsvariable der Counter hochgezählt wird |
| Laufzeit in sek                                       | integer | 0            | Laufzeit in Sekunden in denen der Counter einen Vorgang zählt |
| Limit							                        | integer | 0            | Ist dieser Limit Wert erreicht, wird die Ergebnis Variable auf true geschaltet |
| Restlaufzeit Update Interval                          | integer | 0            | Das UpdateInterval für die Restlaufzeit Variable. |


### 6. Visualisierung

Das Modul bietet keine Funktion in der Visualisierung.

### 7. PHP-Befehlsreferenz

Über die Methode MPC_StopCounterAndReset kann von außerhalb der laufende Timer & Counter zurückgesetzt werden