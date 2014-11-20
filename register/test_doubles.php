<?php
require dirname( __DIR__ ) . '/inc/header.php';

// re-define $projectName
$projectName = $_POST['project'];
$projectPath = realpath( __DIR__ . '/../../../../projects/' . $projectName );

// check/load register-Configuration
if (!file_exists($projectPath . '/extensions/default/config/'.$configName.'_register.php')) exit(L('configuration_is_missing'));
require $projectPath . '/extensions/default/config/'.$configName.'_register.php';
if (!$conf = json_decode($config, true)) exit(L('configuration_is_corrupt'));
if (!isset($conf['objects']['user']['name'])) exit(L('database_object_is_not_defined'));

// test for captcha if set (don't use precise comparsion)
if (isset($conf['fields']['captcha']) && $_POST['captcha'] != $_SESSION['captcha_answer'])
{
	exit(L('wrong_captcha'));
}

require $projectPath . '/objects/class.' . $conf['objects']['user']['name'] . '.php';

$n = $projectName . '\\' . $conf['objects']['user']['name'];
$obj = new $n();
$list = $obj->GetList(array(array($_POST['field'], '=', $_POST['val'])), array(), 1);

if (isset($list[0]))
{
	$k = $_POST['field'].'_exists';
	echo isset($LL[$k])?$LL[$k]:str_replace('_',' ',$k);
}
else
{
	echo 'ok';
}
?>
