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

*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*********************************************************************************/
/**
* generate a new User-Password-Hash based on Input + Salt
* 
*/
require '../../inc/header.php';
if(!$_SESSION[$projectName]['objects'][$objectName]) exit( L('access_not_allowed') );

$hash = '';

if(!empty($_POST['pass']) && !empty($_POST['salt']))
{
	require($backend.'/../projects/'.$projectName.'/objects/class._user.php');
	$hash = crpt( $_POST['pass'], $_POST['salt'] );
}

?>
<!DOCTYPE html>
<html>
<head>
<title>set User-Password</title>
<meta charset="utf-8" />

<!-- use the common JS-Password-Generator (client hopefully has a lot Randomness) -->
<script src="../../../../inc/js/gpw.js"></script>

<style>
body
{
	font-family: "Trebuchet MS", sans-serif;
	font-size: .8em;
	margin: 20px;
}
#new, #given
{
	background: #fff;
	padding: 20px;
}
input
{
	background: #fff;
	border : 1px solid #ccc;
	padding: 5px;
	margin: 5px;
}
input[type=button], input[type=submit]
{
	cursor:pointer;
}
</style>
</head>
<body>
<div id="given"></div>
<hr />
<?php
echo '
<form method="post" action="index.php?project='.$projectName.'&object='.$objectName.'">
	<input type="hidden" name="salt" id="newsalt" />
	<input type="text" name="pass" id="newpw" placeholder="'.L('new_Password').'" />
	<input type="button" onclick="generate()" value="'.L('generate_Password').'" />
	<br />
	<input type="submit" value="'.L('encode_Password').'" />
</form>
<hr />

<div id="new">'.L('new_Hash').': '.$hash.'</div>
<input type="button" onclick="transport()" value="'.L('transfer_Hash').'" />
';
?>
<script>

// shortcut
function $(id)
{
	return document.getElementById(id);
};

// get the old Password from parent-window
$('given').innerHTML = '<?php echo L('current_Hash')?>: ' + parent.$('#'+parent.targetFieldId).val();

function generate()
{
	$('newpw').value = GPW.pronounceable(12);// create pronouncable Password
	$('newsalt').value = GPW.complex(12);// additional create a new unique Salt
};

function transport()
{
	parent.$('#'+parent.targetFieldId).val('<?php echo $hash;?>');
	alert('<?php echo L('Password_transferred_please_save')?>');
};

$('newsalt').value = GPW.complex(12);

</script>
</body>
</html>
