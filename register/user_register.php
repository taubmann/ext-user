<?php
$config = <<<'EOD'
{
	"mail": {
		"from_mail": "noreply@example.com",
		"smtp_host": "smtp.example.com:465",
		"smtp_auth": true,
		"smtp_secure": "ssl",
		"smtp_username": "noreply@example.com",
		"smtp_password": "SUPERSECRET"
	},
	"tests": {
		"email": {
		  "regex": "L14oW1x3LV0rKD86XC5bXHctXSspKilAKCg/Oltcdy1dK1wuKSpcd1tcdy1dezAsNjZ9KVwuKFthLXpdezIsNn0oPzpcLlthLXpdezJ9KT8pJC9p",
		  "error": "email_is_not_valid"
		},
		"password": {
		  "regex": "L14oPz0uKlxcZCkoPz0uKlthLXpBLVpdKS57NSwyMH0kLw==",
		  "error": "password_must_be_min_5_characters_containing_min_one_number"
		},
		"strlen3": {
		  "regex": "L15bQS1aYS16XXszLDMwfSQv",
		  "error": "min_3_characters"
		},
		"strlen_5": {
		  "regex": "L15bQS1aYS16XXs1LDMwfSQv",
		  "error": "min_5_characters"
		},
		"valid": {
		  "regex": "L15bQS1aYS16MC05X0AtXXsxLDMwMH0kLw",
		  "error": "only_valid_characters_allowed"
		}
	  },
	  "fields": {
		"username": {
		  "unique": 1,
		  "test": "strlen3"
		},
		"email": {
		  "unique": 1,
		  "test": "email"
		},
		"password": {
		  "test": "password"
		},
		"prename": {
		  "test": "strlen3"
		},
		"lastname": {
		  "test": "strlen3"
		}
	 },
	"profiles": [
		1,
		2
	]
}
EOD;
;?>
