<?php
require dirname( __DIR__ ) . '/inc/header.php';

// check/load register-Configuration
if(!file_exists($projectPath . '/extensions/default/config/'.$configName.'_register.php')) exit(L('configuration_is_missing'));
require $projectPath . '/extensions/default/config/'.$configName.'_register.php';
if(!$conf = json_decode($config, true)) exit(L('configuration_is_corrupt'));
if (!isset($conf['objects']['user']['name'])) exit(L('user_object_is_not_defined'));


if (isset($conf['fields']['captcha']) && $_POST['captcha'] != $_SESSION['captcha_answer'])
{
	exit(L('wrong_captcha'));
}

// the required column-names
$PASS = $conf['objects']['user']['fields']['pass'];
$NAME = $conf['objects']['user']['fields']['name'];
$MAIL = $conf['objects']['user']['fields']['mail'];

require $projectPath . '/objects/class.' . $conf['objects']['user']['name'] . '.php';

/////////////////////////////// load  external Authentication-Provider if available ///////////////////////////////////
if (!empty($_POST['provider']) && file_exists($projectPath . '/extensions/default/user/provider/'.$_POST['provider'].'.php'))
{
	require $projectPath . '/extensions/cms/user/provider/' . $_POST['provider'] . '.php';
	exit();
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




// "normal" Registration-Process

// test if E-Mail seems to be valid
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) exit(L('email_is_not_valid'));

// if the user enters a false unlock-code...
// test if correct unlock if mustunlock is activated (BUT fake a ok-Message)
// if ($conf['mustunlock'] || !isset($conf['unlocks'][$_POST['unlock']])) exit('oki');

$n = $projectName . '\\' . $conf['objects']['user']['name'];
$obj = new $n();


// exit if user exists
$list = $obj->GetList(array(array($NAME, '=', $_POST[$NAME])), array(), 1);
if (isset($list[0]))
{
	exit(L('username_exists'));
}

function getVariable ($arr)
{
	global $$arr[0], $fv;
	$fv['default'] = $$arr[0];
}

function encryptPassword ($arr)
{
	global $hash, $PASS;
	// generate Salt
	$ch = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
	$s = '';
	for($i=0; $i < 12; $i++) $s .= $ch[mt_rand(0, strlen($ch)-1)];
	$_POST[$PASS] = crpt($_POST[$PASS], $s);
}


foreach ($conf['fields'] as $fk => $fv)
{
	
	if (isset($fv['prepare']['function']) && function_exists($fv['prepare']['function']))
	{
		call_user_func_array($fv['prepare']['function'], $fv['prepare']['params']);
	}
	
	if ($fv['show'] && !empty($_POST[$fk]))
	{
		$obj->{$fk} = $_POST[$fk];
	}
	else
	{
		if(isset($fv['default'])) $obj->{$fk} = $fv['default'];
	}
}

// if the entry is successfully saved send a confirmation-email
if ($obj->Save())
{
	$subject = L('confirm_registration');
	$confirm_url = 	createURL('confirm.php?c=') 
					. trim( 
						base64_encode(
							$_POST['project'] 
							. ':' 
							. $_POST[$NAME] 
							. ':' 
							. $_POST[$PASS]
						)
					, '=');
	
	$text = L('please_confirm_your_registration_by_following_the_link').': <br /><a href="'.$confirm_url.'">'.$confirm_url.'</a>';

	$fromMail = $conf['mail']['from_mail'];
	$fromName = L('Register_Robot');

	// if credentials for php-mailer are set we prefer this!
	if (isset($conf['mail']['smtp_host']))
	{
		require '../inc/PHPMailer/class.phpmailer.php';
		$mail = new PHPMailer;
		$mail->IsSMTP(); // Set mailer to use SMTP
		$mail->Host = $conf['mail']['smtp_host'];  			// Specify main and backup server
		$mail->SMTPAuth = $conf['mail']['smtp_auth']; 		// Enable SMTP authentication
		$mail->SMTPSecure = $conf['mail']['smtp_secure'];  	// Enable encryption, 'tls' or 'ssl' accepted
		
		$mail->Username = $conf['mail']['smtp_username']; 	// SMTP username
		$mail->Password = $conf['mail']['smtp_password'];  	// SMTP password
		
		$mail->From = $fromMail;
		$mail->FromName = $fromName;
		
		$mail->AddAddress($obj->{$MAIL}, 'new user');  // Add a recipient
		$mail->Subject = $subject;
		$mail->Body = $text;
		$mail->AltBody = strip_tags($text);
		
		$mail->IsHTML(true); // Set email format to HTML
		
		if (!$mail->Send())
		{
			exit( L('error_sending_mail') . ' ' . $mail->ErrorInfo . ' ' . L('please_contact_the_site_administrator') );
		}
	}
	else // send via mail() Function
	{
		$header  = 'MIME-Version: 1.0' . "\r\n";
		$header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$header .= 'From: '.$fromName.' <'.$fromMail.'>' . "\r\n";
		$header .= 'Reply-To: noreply@example.com' . "\r\n";// fake-reply-mail (change for your needs)
		
		if (!mail($obj->{$MAIL}, $subject, $text, $header))
		{
			exit(L('error_sending_mail').' '.L('please_contact_the_site_administrator'));
		}
		
	}
	
	echo 'ok';// this is evaluated by javascript!!
}
else
{
	echo L('sorry_your_account_could_not_be_saved').' '.L('please_contact_the_site_administrator');
}

?>
