<?php
// general Header for some User-Wizards
require dirname(dirname(dirname(__DIR__))) . '/inc/php/session.php';

//error_reporting(0);

foreach($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '_', $v); }
//foreach($_POST as $k=>$v){ $_POST[str_replace('amp;','',$k)] = preg_replace('/\W,@ _/', '', $v); }
foreach($_REQUEST as $k=>$v){ $_REQUEST[str_replace('amp;','',$k)] = preg_replace('/\W,@ _/', '', $v); }

// disabling magic quotes at runtime
if (get_magic_quotes_gpc())
{
	function stripslashes_gpc(&$value)
	{
		$value = stripslashes($value);
	}
	array_walk_recursive($_POST, 'stripslashes_gpc');
}


@$projectName = ($_REQUEST['projectName']?:$_REQUEST['project']);
@$objectName = ($_REQUEST['objectName']?:$_REQUEST['object']);

//
@$configName = ($_REQUEST['conf']?:'user');

$backend = realpath( __DIR__ . '/../../../');
$projectPath = realpath( __DIR__ . '/../../../../projects/' . $projectName );

require($backend . '/inc/php/functions.php');

$lang = (	isset($_SESSION[$projectName]['lang']) 
			? $_SESSION[$projectName]['lang']
			: browserLang(glob(__DIR__ . '/locales/*.php'))
		);

@include (__DIR__ . '/locale/'.$lang.'.php');


function showDialog ($header, $message, $error=false, $asPage=false)
{
	$body =   '<div '
			. ($error ? 'class="err"' : '')
			. '>'
			. '<h3>' . L($header) . '</h3>'
			. '<p>'  . L($message) . '</p></div>';
	
	$html =   '<!DOCTYPE html>'
			. '<html><head><meta charset="utf-8"></head>'
			. '<body>'
			. $body
			. '</body></html>';
	
	return ($asPage?$html:$body);
}

// create the absolute url pointing to a script in the same folder with the calling script
function createURL ($script='?')
{
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . preg_replace('#[^/]*$#', '', $_SERVER['REQUEST_URI']) . $script;
}

