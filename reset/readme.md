# User-Password-Reset for cms-kit Backend

**Notice: you can delete this Folder if you don't want a Password-Reset for Users in one of your Projects**

## Description

This Wizard let a User reset his/her Password by enterig a valid e-mail. A newly generated random Password is sent to this Address.

## Configuration

to activate this Functionality for a Project you have to:

* copy the File "user_remember.php" into "projects/YOUR_PROJECT/extensions/default/configuration/" and configure it to your needs. This Configuration-File contains Configurations for:
  * "mail": Credentials for Mailing (Settings beginning with "smtp_" are optional in case the Confirmation-Mail could not be sent by PHPs standard mail()-Function. See user/inc/PHPMailer/readme.md)

## ToDo

think about some/ implement strategies to protect users against "password-reset-jokes" from other users (secret question etc.)...
