**crappy translation!!**

User Management
Target

This extension established a modular User-/Rechte-Management in cms-kit backend. With the two areas and user profiles can be accessed areas and content items control.

The cms-kit backend provides specific APIs for this extension.

There are two user types

    root user within the project have full access to all areas and content and project-based on the most Andministrationswerkzeuge. However, you can not create new projects and no global extensions. A root account must be assigned to any profile.
    A regular user will only have the rights which are conferred on them by means of assigned profiles.
        Profiles are collections of permissions
        If a user is assigned to multiple profiles, cumulate the rights under (further rights override lower permissions). 

Installation

    Import hooks
    Import object schema "model.xml" and take in the modeler via the "Setup" 

Facility
Creating a User

    enabled (checked) with the checkbox determines whether the account is active
    root (checked) on the checkbox determines if the account has root privileges.
    User name login name (must be unique!)
    Password encrypted program stored user password. New investing about the password wizard
    Language Interface language in the backend
    Settings, this field stores the settings on logout of the account
    Expire here a time stamp can be set after the account is no longer valid. A value of 0 means a perpetual validity.
    Last Login automatically generated time stamp of the last login 

Create profiles

    enabled (checked) with the checkbox determines whether the profile is active
    Rights in the field are (cms-kit typically in JSON format) stored the rights which the profile. Rights can be on the Rights Manager to manage. 

Workspaces

In combination with the user management can be set up for individual areas release systems or workspaces.

Scenario:

A simple editor should be able to be an article and revise, but the release of the article to be made by a chief editor. Even if the item is onine this can be further revised, the changes are visible but again only with the re-release of the editor.

Implementation:

For a simple workspace (the whole thing can be designed in several stages by the way) we need 2 profiles (eg Editor + Editor in Chief) and in the region itself three fields:

    Work Item Field: The field is accessible both profiles (in the example: "work-field")
    Issue article Field: The field is either completely or only verstekt the editorial accessible (in the example: "output field")
    Enable Checkbox: This field is only accessible to the editorial board (Type: True / False, in the example: "allesok") 

In addition, a hook must now be set up for the area that copies the contents of the working field in the output field when the release occurs (ie, the check mark is set) and then resets the enable box. The Hook "ccopy" (template can be found in the hooks.php file) is called as follows.

 PRE:ccopy:allesok,arbeitsfeld,ausgabefeld 

"Dynamic" rights

Based on the user management can also be "dynamic", implement attached to the respective item and the / The maker of access rights.

Scenarios:

    An area should be accessible to several groups, each group only has access to the entries that were created by one of the group members. Examples are departments that are responsible for different sections of a website or intranet, where intra-group discussions are to be mapped.
    A scope to include only private content. Only the / The producer has access to the entry (root users can see the entries of course). 

Implementation:

For this system we need rights in the region / object, an additional hidden field (type: "Excluded varchar") to save the "group membership" or (on the addition "private") of "ownership".

Then we define a hook that monitors the content creation and display.

 PRE:filterByOwnership,hidden_field[,private] 

Notes

    The filter systems (user / profiles / Hooks) described above can easily be used as a building block for their own regulations. How about, for example, with a hook that restricts access to the regular working hours? (Motto: "On Saturdays, Mami me") :-)
    The areas of user profiles and are associated with the filter-day administration. This can be changed in the Modeler.
    The extension has three separate wizards to support the user management.
        su ("switch user") allows the administration backend with permissions of the respective user profile access (eg to check the permissions set) without the current user password needing to know.
        setpass allows Admins to create / set a new user password.
        settings enables users to edit their personal settings. 
