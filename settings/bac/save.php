<?php
// save user-settings
exit();
$projectName = preg_replace('/\W/', '', $_GET['project']);
if(!$_SESSION[$projectName]['special']['user'] || $_SESSION[$projectName]['special']['user']['id'] != $_POST['id']) exit ('|:-(');
require('../../../../projects/' . $projectName . '/objects/class._user.php');
$n = $projectName . '\\_user';
$obj = new $n();
$user = $obj->Get($_POST['id']);
if($user->id)
{
	if(@$o = json_decode($_POST['json'], true))
	{
		$o['labels'] = $_SESSION[$projectName]['labels'];
		$o['theme'] = $_SESSION[$projectName]['config']['theme'];
		$user->settings = json_encode($o);
		$user->Save();
	}
}
?>
