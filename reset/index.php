<?php
require dirname( __DIR__ ) . '/inc/header.php';

$arr = array();

// we have to re-define $projectName by extracting the Project-Name from the hashed URL _OR_ from a normal Request(POST/GET)
if(isset($_GET['r']))
{
	$arr = explode(':', base64_decode($_GET['r']));
	
	foreach($arr as $k=>$v)
	{
		$arr[$k] = preg_replace('/\W@/', '', $v);
	}
	$projectName = $arr[0];
}
else
{
	$projectName = preg_replace('/\W/', '', $_REQUEST['project']);
}

// check/load reset-Configuration
$projectPath = realpath( __DIR__ . '/../../../../projects/' . $projectName );
if(!file_exists($projectPath . '/extensions/default/config/'.$configName.'_remember.php')) exit(L('configuration_is_missing'));
require $projectPath . '/extensions/default/config/'.$configName.'_remember.php';
if(!$conf = json_decode($config, true)) exit(L('configuration_is_corrupt'));
if (!isset($conf['objects']['user']['name'])) exit(L('user_object_is_not_defined'));

$PASS = $conf['objects']['user']['fields']['pass'];
$NAME = $conf['objects']['user']['fields']['name'];
$MAIL = $conf['objects']['user']['fields']['mail'];
$ACTIVE = $conf['objects']['user']['fields']['active'];


$html = '
<form method="post" action="index.php" id="frm">
	<label>'.L('spam_protection').'</label>
	<input placeholder="'.L('enter_the_result_of').'" type="text" name="captcha" onblur="testCaptcha(this)" />
	<img id="captcha_img" onclick="this.src+=1" title="'.L('click_to_refresh_Image').'" style="height:26px;cursor:pointer;vertical-align:bottom" src="../../../inc/php/captcha_math.php?r=1" />
	<hr />
	<label>'.L('e_mail').'</label>
	<input placeholder="'.L('enter_your_email').'" onkeyup="test(this)" onblur="test(this)" name="mail" type="email" />
	<input type="hidden" name="project" value="'.$projectName.'" />
	<input disabled="disabled" id="sender" type="submit" value="'.L('reset').'" />
</form>';

//
require_once $projectPath . '/objects/class.'.$conf['objects']['user']['name'].'.php';
$n = $projectName . '\\' . $conf['objects']['user']['name'];
$obj = new $n();

if (isset($_POST['captcha']) && isset($_POST['mail']))
{
	
	if (!filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL))	exit(showDialog(L('email_is_not_valid'), '', true, true));
	if ($_POST['captcha'] != $_SESSION['captcha_answer']) 	exit(showDialog(L('false_captcha'), '', true, true));
	
	$list = $obj->GetList(
							array(
									array($ACTIVE,	'=', '1'),// user should be active
									array($MAIL,	'=', $_POST['mail']),// and shoult have of course the right mail
								 ),
							array(),
							1
						  );
	
	if (isset($list[0]->id))
	{
		// if the password-field is empty, create a new, temporary password and save it
		if (empty($list[0]->{$PASS}))
		{
			$list[0]->{$PASS} = substr(md5(mt_rand()),0,12) . ':' . md5(mt_rand());
			$list[0]->Save();
		}
		
		
		$reset_url =	createURL('index.php?r=') 
						. trim( 
							base64_encode(
								$projectName 
								. ':' 
								. $list[0]->{$NAME} 
								. ':' 
								. $list[0]->{$PASS}
							)
						, '=');
		
		$fromMail = $conf['mail']['from_mail'];
		$fromName = L('Password_Reset_Robot');
		$subject = L('password_reset');
		$text = L('reset_your_password_with_this_link').': <br /><a href="'.$reset_url.'">reset</a> <br />or copy this URL in your browser window<br />'.$reset_url.' ';
		$error = false;
		
		
		if (isset($conf['mail']['smtp_host']))
		{
			require '../inc/PHPMailer/class.phpmailer.php';
			$mail = new PHPMailer;
			$mail->IsSMTP(); // Set mailer to use SMTP
			$mail->Host = $conf['mail']['smtp_host'];  // Specify main and backup server
			$mail->SMTPAuth = $conf['mail']['smtp_auth']; // Enable SMTP authentication
			$mail->SMTPSecure = $conf['mail']['smtp_secure'];  // Enable encryption, 'tls' or 'ssl' accepted
			
			$mail->Username = $conf['mail']['smtp_username']; // SMTP username
			$mail->Password = $conf['mail']['smtp_password'];  // SMTP password
			
			$mail->From = $fromMail;
			$mail->FromName = $fromName;
			
			$mail->AddAddress($list[0]->{$MAIL}, 'user');  // Add a recipient
			$mail->Subject = $subject;
			$mail->Body = $text;
			$mail->AltBody = strip_tags($text);
			
			$mail->IsHTML(true); // Set email-format to HTML
			
			if (!$mail->Send())
			{
				$error = L('error_sending_mail') . ': ' . $mail->ErrorInfo;
			}
		}
		else // send via mail() Function
		{
			$header  = 'MIME-Version: 1.0' . "\r\n";
			$header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$header .= 'From: '.$fromName.' <'.$fromMail.'>' . "\r\n";
			$header .= 'Reply-To: noreply@example.com' . "\r\n";// fake-reply-mail (change for your needs)
			
			if (!mail($list[0]->{$MAIL}, $subject, $text, "From: ".$fromName." <".$fromMail.">\r\n"))
			{
				$error = L('error_sending_mail');
			}
		}
		
		
		$html = $error 
				? showDialog($error, '')
				: showDialog('we_sent_an_email_with_a_reset_link_to_your_address', 'go_back_to').'<p><a href="../../../index.php">login</a></p>';
	}
	else
	{
		$html = showDialog('email_does_not_exist_or_account_is_not_active', 'try_again', true);
		
		if (isset($conf['redirect']))
		{
			$html .= 	'<p>
						 <a href="'
						. $conf['redirect']
						. '">'
						. L('go_on')
						. '</a>
						   </p>';
		}
	}
	
}

// reset the Password
if(count($arr) == 4)
{
	$list = $obj->GetList(
							array(
									array($NAME, '=', $arr[1]),
									array($PASS, '=', $arr[2].':'.$arr[3])
								 ),
							array(),
							1
						  );
	
	if (isset($list[0]))
	{	
		// create a random password
		$md5 = md5(rand());
		$pwd = substr($md5, 0, 12);
		//$salt = substr($md5, 13, 12);
		$list[0]->password = crpt($pwd);
		
		
		if ($list[0]->Save())
		{
			$html =  showDialog('password_reset', 'your_new_password_is') . '<p><em>'.$pwd.'</em></p>';
			
			if (isset($conf['redirect']))
			{
				$html .= 	'<p>
							 <a href="'
							. $conf['redirect']
							. '">'
							. L('go_on')
							. '</a>
							   </p>';
			}
		}
		else
		{
			$html = showDialog('confirmation_could_not_be_updated', '', true);
		}
	}
	else
	{
		$html = showDialog('confirmation_user_not_found', '', true);
	}
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Password-Reset</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /> 
	<script src="../../../inc/js/jx_compressed.js"></script>
	<style>
	body
	{
		font: bold .9em sans-serif;
	}
	#wrapper
	{
		width: 470px;
		border: 2px solid #ccc;
		border-radius: 6px;
		padding: 10px;
		margin: 40px auto;
	}
	label {
		display: inline-block;
		width: 150px;
	}
	input {
		background: #fff;
		border: 2px solid #999;
		border-radius: 4px;
		padding: 3px;
	}
	</style>
	<script>
	function test(el)
	{
		var t = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
		if (!t.test(el.value)) { el.style.background='#fcc'; }
		else { el.style.background='#fff'; }
	}
	function testCaptcha(el)
	{
		jx.load('../../../inc/php/captcha_check.php?c='+el.value, function(data){
			if(data!='ok')
			{
				document.getElementById('captcha_img').setAttribute('src', '../../../inc/php/captcha_math.php?r='+Math.random());
				el.style.borderColor = '#c00';
			}
			else
			{
				el.style.borderColor = '#0c0';
				document.getElementById('sender').removeAttribute('disabled');
			}
		});
	}
	</script>
</head>
<body>
	<div id="wrapper">
		<?php echo $html;?>
	</div>
</body>
</html>
