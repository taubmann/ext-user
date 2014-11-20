# User-Registration for cms-kit Backend

**Notice: you can delete this Folder if you don't need a User-Registration-System in one of your Projects**

## Description



## Configuration

to activate this Functionality for a Project you have to:

* copy the File "user_register.php" into "projects/YOUR_PROJECT/extensions/default/configuration/" and configure it to your needs. This Configuration-File contains Configurations for:
  * "mail": Credentials for Mailing (Settings beginning with "smtp_" are optional in case the Confirmation-Mail could not be sent by PHPs standard mail()-Function. See user/inc/PHPMailer/readme.md)
  * "tests": some base64-encoded Regex-Tests for the Input ( convert Regexes: <http://www.motobit.com/util/base64-decoder-encoder.asp> )
  * "fields": Field-Names shown at the Registration-Process (could be made mandatory via tests)
* add following Field(s) to "_user"
  * confirmed (BOOL) + email (VARCHAR) **AND/OR**
  * provider (SELVARCHAR)


### direct unlock

field unlock

settings unlocks (array)


### using external Authentication-Providers (not available atm)

If you want to authenticate your Users against external Providers like Facebook, Google, some LDAP-Repositories etc. you have to add the Field "provider" to _user. You can register your Provider-Names in the addition of the SELVARCHAR like so

	:Internal
	facebook:Facebook account
	google:Google-Account
	...

then you have to place a Authentication-File every Provider in the List above like this:

	projects/YOUR_PROJECT/extensions/default/user/provider/facebook.php
	projects/YOUR_PROJECT/extensions/default/user/provider/google.php

If someone choose an external Provider the normal Registration-Process (in user/register/save.php) will be skipped in favour of the Process defined in this PHP.

If you need a generic Authentication-Framework have a look at: <http://opauth.org>
