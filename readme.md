# cms-kit extension Usermanagement

Installation-path: backend/extensions/user

## Description

This extension implements a highly configurable user-/profile-management for cms-kit backend.


additional *Tables/Objects* in your Database: _user, _profile


additional *Wizards/Functions*:

* **settings** Wizard for User-Settings 
* **login** Functions to verify username/password + map profiles
* **register** Frontend User-Registration (needs the Fields "email" (VARCHAR) + "confirmed" (BOOL))
* **reset** Frontend Password-Reset (needs the Field "email" (VARCHAR))
* **profile_manager** Wizard to edit Profiles
* **su** "Switch-User"-Wizard (roots only)
* **set_pass** Wizard to set a new Password in Backend	

additional *Hooks*: 

## Installation

### Manual installation

1. download and extract this Folder (user) into backend/extensions/

### Installation via package management
