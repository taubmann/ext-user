# User-Settings for cms-kit Backend

## Description

to extend this Functionality for a Project you have to:

* copy the File "user_settings.php" into "projects/YOUR_PROJECT/extensions/default/configuration/" and configure it to your needs. This Configuration-File contains Configurations for:
  * "mail": Credentials for Mailing (Settings beginning with "smtp_" are optional in case the Confirmation-Mail could not be sent by PHPs standard mail()-Function. See user/inc/PHPMailer/readme.md)
  * "fields": additional Field-Names shown at the Setting-Wizard
