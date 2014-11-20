#User Management

##Ziel
Diese Extension etabliert ein modulares User-/Rechte-Management im cms-kit-Backend.
Mit den beiden Bereichen **User** und **Profile** lässt sich der Zugriff auf Bereiche und Inhaltselemente steuern. 

Das cms-kit-Backend bietet spezielle APIs für diese Extension.

Es gibt zwei User-Typen

* **root-User** haben innerhalb des Projekts vollen Zugriff auf alle Bereiche und Inhalte und Projekt-bezogen auf die meisten Andministrationswerkzeuge. Sie können jedoch keine neuen Projekte und keine globalen Extensions anlegen. Ein Root-Account muss keinem Profil zugeordnet sein.
* **normale User** verfügen nur über die Rechte, die ihnen über zugeordnete Profile verliehen werden.
  * **Profile** sind Sammlungen von Berechtigungen
  * Ist ein User mehreren Profilen zugeordnet, kumulieren die Rechte entsprechend (weiter gehende Rechte überschreiben geringere Berechtigungen).

##Installation

1. Hooks importieren,
2. Objekt-Schema "model.xml" importieren und im Modellierer via "Setup" übernehmen


##Einrichtung

###User anlegen

* **aktiviert** über die Checkbox (Häkchen gesetzt) wird festgelegt, ob der Account aktiv ist
* **root** über die Checkbox (Häkchen gesetzt) wird festgelegt, ob der Account Root-Rechte hat.
* **Username** Login-Name (muss einmalig sein!)
* **Passwort** verschlüsselt abgespeichertes User-Passwort. Neu anlege über den Passwort-Wizard
* **Sprache** Interface-Sprache im Backend
* **Settings** dieses Feld speichert beim Logout die Einstellungen des Accounts
* **Expire** hier kann ein Zeitstempel festgelegt werden, nachdem der Account nicht mehr gültig ist. Der Wert 0 bedeutet eine zeitlich unbeschränkte Gültigkeit.
* **LastLogin** automatisch erzeugter Zeitstempel des letzten Logins


###Profile anlegen


* **aktiviert** über die Checkbox (Häkchen gesetzt) wird festgelegt, ob das Profil aktiv ist
* **Rechte** in dem Feld sind (cms-kit-typisch im JSON-Format) die Rechte hinterlegt, die das Profil hat. Rechte lassen sich über den [Rechtemanager](rightsmanager.txt) verwalten.

## Workspaces

In Kombination mit den User-Management lassen sich für einzelne Bereiche Freigabe-Systeme oder auch Workspaces einrichten.

Szenario:

Ein einfacher Redakteur soll einen Artikel be- und überarbeiten können, die Freigabe des Artikels soll aber von einer Chefredaktion erfolgen. 
Auch wenn der Artikel onine ist kann dieser weiter überarbeitet werden, die Änderungen werden aber wiederum erst mit der erneuten Freigabe durch die Chefredaktion sichtbar.

Umsetzung:

Für einen einfachen Workspace (das Ganze lässt übrigens sich auch mehrstufig gestalten) benötigen wir 2 Profile (z.B. Redaktion + Chefredaktion) und in dem Bereich selbst 3 Felder:

* Arbeits-Artikelfeld: Das Feld ist beiden Profilen zugänglich  (im Beispiel: "arbeitsfeld")
* Ausgabe-Artikelfeld: Das Feld ist entweder komplett verstekt oder nur der Chefredaktion zugänglich  (im Beispiel: "ausgabefeld")
* Freigabe-Checkbox: Das Feld ist *nur der Chefredaktion zugänglich* (Typ: Wahr/Falsch, im Beispiel: "allesok")

Zusätzlich muss nun für den Bereich ein Hook eingerichtet werden, der den Inhalt des Arbeitsfelds in das Ausgabefeld kopiert *wenn* die Freigabe erfolgt (also das Häkchen gesetzt wird) und anschliessend das Freigabefeld zurücksetzt.
Der Hook "ccopy" (Vorlage findet sich in der Datei hooks.php) wird folgendermaßen aufgerufen.

	PRE:ccopy:allesok,arbeitsfeld,ausgabefeld

## "dynamische" Rechte

Basierend auf dem User-Management lassen sich auch "dynamische", an den jeweiligen Eintrag und den/die ErstellerIn gebundene Zugriffsrechte umsetzen.

Szenarien:

* Ein Bereich soll mehreren Gruppen zugänglich sein, wobei jede Gruppe nur auf die Einträge Zugriff hat, die von einem der Gruppenmitglieder erstellt wurden. Beispiele wären Abteilungen, die für unterschiedliche Sektionen einer Webseite zuständig sind oder Intranets, in denen gruppeninterne Diskussionen abgebildet werden sollen.
* Ein Bereich soll nur private Inhalte enthalten. Nur der/die ErstellerIn hat Zugriff auf den Eintrag (Root-User sehen die Einträge natürlich auch). 

Umsetzung:

Für dieses Rechtesystem benötigen wir in dem Bereich/Objekt ein zusätzliches verstecktes Feld (Typ: "Excluded Varchar") zum Speichern der "Gruppenzugehörigkeit" oder (über den Zusatz "private") der "Eigentümerschaft".

Dann definieren wir einen Hook, der die Inhaltserstellung und -anzeige überwacht.

	PRE:filterByOwnership,hidden_field[,private]

## Hinweise

* Die oben beschriebenen Filtersysteme (User/Profile/Hooks) können leicht als Baukasten für eigene Reglements genutzt werden. Wie wäre es z.B. mit einem Hook, der den Zugriff auf die reguläre Arbeitszeit beschränkt? (Motto: "Samstags gehört Mami mir") :-)
* Die Bereiche User und Profile sind dem Filter-Tag **Administration** zugeordnet. Das lässt sich im Modellierer ändern.
* Die Extension verfügt über drei eigene Wizards zur Unterstützung des User-Managements.
  * **su** ("switch user") erlaubt es der Administration das Backend mit den Berechtigungen des jeweiligen Userprofils aufzurufen (z.B. zur Überprüfung der gesetzten Berechtigungen) ohne das aktuelle User-Passwort kennen zu müssen.
  * **setpass** ermöglicht den Admins das Erzeugen/Setzen eines neuen User-Passworts.
  * **settings** ermöglicht den Usern die Bearbeitung ihrer persönlichen Einstellungen.




