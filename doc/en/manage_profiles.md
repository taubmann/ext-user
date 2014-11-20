**crappy translation!!**
Profile Management
Rights-management of areas

Here can be (cms-kit typically in JSON format) specifying all areas that should be available to the / the userin. It can determine which input fields are visible and which actions are allowed in the area in addition. For each area to be released a JSON object with the (original) name field is applied. The privilege system provides several stages of expansion for fine granulation of rights.

    easy release: to be released with all the fields and rights, an area sufficient to enter a 1 after the field name (in the example of the "book").
    Sharing of fields: should not all be the profile fields available to adjust with JSON array "show" determine. Here all (original) field names are stored, to be displayed (in the example of the "author").
    Sharing Actions: what actions are possible in the area can be determined via the optional JSON object "action". There is a difference between actions that are either prohibited (= 0) or enables (= 1) are (in the example of the "publisher").
        c (reate): a new entry must be created?
        u (pdate): can an entry be updated?
        d (elete): an entry may be deleted completely (also deletes all links)?
        a (ssociate): may the links of the entry to be edited?
        s (place): in hierarchies, where the order in the database is stored: the entry must be sorted? 

Note: For the control of actions must be in the range of Hook "acm" enabled!

 PRE:acm 

Example Code

 { "buch": 1, "autorin": { "show": [ "vorname", "nachname" ] }, "verlag": { "action": { "a": 1, "s": 1 } }, "grossist": { "show": [ "name", "tel" ], "action": { "c": 0, "u": 0, "d": 0, "a": 1, "s": 1 } } } 

Rights management files

Here you can create file shares.

https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options

Example Code

 { "fileaccess": [ { "driver": "LocalFileSystem", "path": "files/", "accessControl": "access", "alias": "common Folder" }, { "driver": "LocalFileSystem", "path": "user/##ID##/", "accessControl": "access", "alias": "private Folder" } ] } 
