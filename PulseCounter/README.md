# Pulszähler
Das Modul ermöglicht es, einen Zähler auf einer Variable aufzusetzen, der in einem definierten Zeitraum die Impulse zählt

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [Visualisierung](#6-visualisierung)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)


### 1. Funktionsumfang

* Schaltvorgänge einer Variable überwachen und Zählen

### 2. Voraussetzungen

- IP-Symcon ab Version 7.1

### 3. Software-Installation

* Über den Module Store das 'Pulszähler'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen: https://github.com/migodev/PulseCounter

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'Pulszähler'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

### 5. Statusvariablen und Profile

Es werden keine Profile angelegt.
Es werden 3 Statusvariablen angelegt:

Name                  | Typ
--------------------- | -------------------
Ergebnis 			  | Boolean
Counter				  | Integer
Restlaufzeit		  | String

### 6. Visualisierung

Das Modul bietet keine Funktion in der Visualisierung.

### 7. PHP-Befehlsreferenz

Über die Methode MPC_StopCounterAndReset kann von außerhalb der laufende Timer & Counter zurückgesetzt werden