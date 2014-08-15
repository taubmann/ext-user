<?php
require dirname(dirname(dirname(__DIR__))) . '/inc/php/session.php';
if(isset($_GET['theme']))
{
    $_SESSION[$_GET['project']]['settings']['interface']['theme'] = array($_GET['theme']);
	echo 'theme changed';
}
?>
