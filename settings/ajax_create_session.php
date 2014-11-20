<?php
require dirname(dirname(dirname(__DIR__))) . '/inc/php/session.php';
$projectName = $_GET['project'];
$projectPath = dirname(dirname(dirname(dirname(__DIR__)))) . '/projects/' . $projectName;
// load global model and settings-config
require_once($projectPath . '/objects/__model.php');

$userSettingsPath = $projectPath . '/extensions/default/config/user_settings.php';

if(!file_exists($userSettingsPath)) exit('configuration is missing');
require $userSettingsPath;

if(!$conf = json_decode($config, true)) exit('configuration is corrupt');


// push the field-definitions from global objects to temporary array
$tmp = array();
foreach ($conf['fields'] as $field)
{
	if (isset($objects['_user']['col'][$field]))
	{
		$tmp[$field] = $objects['_user']['col'][$field];
	}
}

// create a temporary object-session for crud
$_SESSION['TMP__'.$projectName] = $_SESSION[$projectName];
$_SESSION['TMP__'.$projectName]['objects']['_user'] = array(
	'hooks' => $objects['_user']['hooks'],
	'db' => $objects['_user']['db'],
	'inc' => $objects['_user']['inc'],//??
	'templates' => '',
	'ttype' => 'List',
	'hidettype' => '',
	'config' => array(),
	'rel' => array(),
	'col' => $tmp,
	'acl' => false//(isset($objects['_user']['acl'])?$objects['_user']['acl']:array())
);
?>
 
