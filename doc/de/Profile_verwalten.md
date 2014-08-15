# Profil-Management


## Rechte-Verwaltung der Bereiche

Hier lassen sich (cms-kit-typisch im JSON-Format) sämtliche Bereiche Angeben, die der/dem UserIn zugänglich sein sollen. Dabei lässt sich zusätzlich festlegen, welche Eingabe-Felder zu sehen sind und welche Aktionen in dem Bereich erlaubt sind. Für jede freizugebenden Bereich wird ein JSON-Objekt mit dem (Original-)Bereichsnamen angelegt. Das Rechtesystem bietet **mehrere Ausbaustufen** zur feinen Granulierung der Rechte.

  1. **einfache Freigabe**: soll ein Bereich mit allen Feldern und Rechten frei gegeben werden genügt die Eingabe einer 1 nach dem Bereichsnamen (im Beispiel: der Bereich "buch").
  2. **Freigabe von Feldern**: sollen dem Profil nicht alle Felder verfügbar sein lässt sich das mit dem JSON-Array "show" bestimmen. Hier werden alle (original-)Feld-Namen hinterlegt, die angezeigt werden sollen (im Beispiel: der Bereich "autorin").
  3. **Freigabe von Aktionen**: welche Aktionen in dem Bereich möglich sind lässt sich optional über das JSON-Objekt "action" bestimmen. Hier wird zwischen mehreren Aktionen unterschieden, die entweder verboten (=0) oder erlaubt (=1) sind (im Beispiel: der Bereich "verlag").
      * c(reate): darf ein neuer Eintrag angelegt werden?
      * u(pdate): darf ein Eintrag aktualisiert werden?
      * d(elete): darf ein Eintrag komplett gelöscht werden (löscht auch alle Verknüpfungen)?
      * a(ssociate): dürfen die Verknüpfungen des Eintrags bearbeitet werden?
      * s(ort): bei Hierarchien, bei denen die Sortierung in der Datenbank gespeichert wird: darf der Eintrag sortiert werden?

**Achtung**: Zur Kontrolle von Aktionen muss in dem Bereich der Hook "acm" aktiviert sein!

	PRE:acm

Beispiel-Code

~~~json
{
	"buch": 1,
	"autorin":  {
		"show":  [
			"vorname",
			"nachname"
		]
	},
	"verlag":  {
		"action":  {
			"a": 1,
			"s": 1
		}
	},
	"grossist":  {
		"show":  [
			"name",
			"tel"
		],
		"action":  {
			"c": 0,
			"u": 0,
			"d": 0,
			"a": 1,
			"s": 1
		}
	}
}
~~~

## Rechte-Verwaltung Dateien

Hier lassen sich die Datei-Freigaben anlegen. 

<https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options>

Beispiel-Code
~~~json
{
  "fileaccess": [
    {
      "driver": "LocalFileSystem",
      "path": "files/",
      "accessControl": "access",
      "alias": "common Folder"
    },
    {
      "driver": "LocalFileSystem",
      "path": "user/##ID##/",
      "accessControl": "access",
      "alias": "private Folder"
    }
  ]
}
~~~
