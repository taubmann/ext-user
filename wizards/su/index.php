<?php
/********************************************************************************
*  Copyright notice
*
*  (c) 2013 Christoph Taubmann (info@cms-kit.com)
*  All rights reserved
*
*  This script is part of cms-kit Framework. 
*  This is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License Version 3 as published by
*  the Free Software Foundation, or (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/licenses/gpl.html
*  A copy is found in the textfile GPL.txt and important notices to other licenses
*  can be found found in LICENSES.txt distributed with these scripts.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*********************************************************************************/
require dirname(dirname(dirname(dirname(__DIR__)))) . '/inc/php/session.php';
$backend = '../../../../';

// index.php?project=test&object=_user&objectId=2
$projectName = $_GET['project'];

if(!$_SESSION[$projectName]['root']) exit('no root');

$projectPath = $backend.'../projects/'.$projectName;
if(!file_exists($projectPath)) exit('path not ok');

// load Classes + Model
require($projectPath . '/objects/__database.php');
require($projectPath . '/objects/class._user.php');
require($projectPath . '/objects/class._profile.php');
require($projectPath . '/objects/__model.php');

// check if valid User exists
$u = $projectName . '\\_user';
$user = new $u();

$user->Get($_GET['objectId']);

$_SESSION[$projectName]['special']['user'] = array('id'=>$user->id, 'prename'=>$user->prename, 'lastname'=>$user->lastname, 'lastlogin'=>$user->lastlogin, 'logintime'=>time());

$profiles = $user->Get_profileList(array(array('active','=','1')));

require '../login/process.php';

unset($_SESSION[$projectName]['root']);

$arr = processUserModel($profiles, $objects);
$_SESSION[$projectName]['objects'] = $arr[0];
$_SESSION[$projectName]['special']['user']['profileids'] = $arr[1];

$html = '<h2>switched User to: '.$user->prename.' '.$user->lastname.'</h2>
<a target="_top" href="'.$backend.'backend.php?project='.$projectName.'">now reload Backend ...</a>';
?>
<html>
<head>
<style>
body{font: .8em "Trebuchet MS", sans-serif;}
</style>

</head>
<body>
<?php
	echo $html;
?>
</body>
</html>
