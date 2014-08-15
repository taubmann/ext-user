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

require '../inc/header.php';
error_reporting(0);
$projectPath = realpath($backend . '/../projects/' . $projectName);
require($projectPath . '/objects/__model.php');


if(!file_exists($projectPath . '/extensions/default/config/'.$configName.'_settings.php')) exit(L('configuration_is_missing'));
require $projectPath . '/extensions/default/config/'.$configName.'_settings.php';
if(!$conf = json_decode($config, true)) exit(L('configuration_is_corrupt'));
if (!isset($conf['objects']['user']['name'])) exit(L('user_object_is_not_defined'));

$PASS = $conf['objects']['user']['fields']['pass'];
$NAME = $conf['objects']['user']['fields']['name'];
$MAIL = $conf['objects']['user']['fields']['mail'];

require($projectPath . '/objects/class.'.$conf['objects']['user']['name'].'.php');


// User-Object
$n = $projectName . '\\' . $conf['objects']['user']['name'];
$userObject = new $n();

// test if username is unique
if(isset($_GET['new_username']))
{
	if (count($userObject->GetList(
									array(
										array($NAME,'=',$_GET['new_username']), 
										array('id', '!=', $_SESSION[$projectName]['special']['user']['id'])
									)
								  )
			) > 0)
	{
		exit('username already exists');
	}
	else
	{
		exit('');
	}
}

// setting new Theme via AJAX
if(isset($_GET['theme']))
{
	$_SESSION[$projectName]['config']['theme'] = array($_GET['theme']);
	exit();
}

$conf = array('fields' => array());



// relative js/css-paths
$rel = '../../../';
$css = $rel.'../vendor/cmskit/lib-jquery-ui/themes/'.end($_SESSION[$projectName]['config']['theme']).'/';
$js  = $rel.'inc/js/';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<style>
	body {
		font-family: sans-serif;
		font-size: .8em;
		background: #eee;
		color: #222;
	}
	
	#wrapper {
		width: 550px;
		margin: 20px auto;
	}
	
	label {
		display:block;float:left;padding-top:10px;width:200px;
	}
	
	input[type=text], input[type=password], textarea {
		width: 300px;
	}
	.warning,.success,.error{font-weight:bold}
	.warning{color:orange}
	.success{color:green}
	.error{color:red}
	
	.password_strength   {padding: 0 5px; display: inline-block;}
	.password_strength_1 {background-color: #fcb6b1;}
	.password_strength_2 {background-color: #fccab1;}
	.password_strength_3 {background-color: #fcfbb1;}
	.password_strength_4 {background-color: #dafcb1;}
	.password_strength_5 {background-color: #bcfcb1;}
	
</style>

<link rel="stylesheet" id="baseTheme" type="text/css" href="<?php echo $css?>jquery-ui.css" />
<link rel="stylesheet" id="myTheme" type="text/css" href="<?php echo $css?>style.css" />

<script type="text/javascript" src="<?php echo $js;?>jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $js;?>jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $js;?>jquery.ui.selectmenu.js"></script>
<script type="text/javascript" src="<?php echo $js;?>jquery.plugin_password_strength.js"></script>
<script type="text/javascript" src="<?php echo $js;?>gpw.js"></script>
<script type="text/javascript">

var projectName = '<?php echo $projectName;?>';
$(document).ready(function()
{
	$('button').each(function() { 
		$(this).button( {icons:{ primary: 'ui-icon-'+$(this).attr('rel')}}); 
	});
	//$('select').selectmenu({style:'dropdown'});
});
</script>
</head>
<body>
	
<div id="wrapper">

<?php

// translate Object-Labels
function Lb($name)
{
	global $objects, $lang;
	//@$newname = $objects['_user']['col'][$name]['lang'][$lang]['label'];
	return (
	isset($objects['_user']['col'][$name]['lang'][$lang]['label']) 
	? trim(preg_replace(array('/\[(.*?)\]/','/\s*\([^)]*\)/'), '', $objects['_user']['col'][$name]['lang'][$lang]['label'])) 
	: str_replace('_',' ',$name)
	);
}

$user = $userObject->GetList( array(
										array('id','=', $_SESSION[$projectName]['special']['user']['id'] ),
										array('active','=','1')
									),
									false,
									1
							);


if (isset($user[0]->id))
{
	
	echo '<span>'.L('last_Login').': '.strftime(L('%d._%m._%Y,_%H:%M:%S'), $_SESSION[$projectName]['special']['user']['lastlogin']).'</span> / 
	<span>'.L('logged_in_since').': <span id="loggedSince"></span></span>
	';
	

	if($_POST)
	{
		$canSave = true;
		//
		$n = $projectName . '\\_user';
		$userObject2 = new $n();
		if (count($userObject2->GetList(array(array('username','=',$_POST['username']), array('id', '!=', $_SESSION[$projectName]['special']['user']['id'])))) > 0)
		{
			echo '<p class="error">'.L('username_occupied').'!</p>';
			$canSave = false;
		}
		
		// fields to save 
		
		$autosave = array_merge(array('language','username'), $conf['fields']);
		
		foreach ($autosave as $n)
		{
			$user[0]->$n = $_POST[$n];
		}
		
		//set Language again (probably changed by User)
		$_SESSION[$projectName]['lang'] = $lang = substr($_POST['language'],0,2);
		include_once('../../inc/locale/'.$lang.'.php');
		
		// change Password
		if (!empty($_POST['newPass1']))
		{
			if (strlen($_POST['newPass1']) < 5)
			{
				unset($_POST['newPass1']);// destroy the new password (??)
				echo '<p class="error">'.L('new_Password_is_too_weak').'!</p>';
				$canSave = false;
			}
			
			$ua = explode(':', $user[0]->password);
			if ( $_POST['newPass1'] === $_POST['newPass2'])
			{
				// set new password
				$user[0]->password = crpt($_POST['newPass1'], $_POST['salt']);
				
				//exit($user[0]->password);
				
				echo '<p class="success">'.L('new_Password_saved').'!</p>';
				
				if (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $_POST['newPass1']))
				{
					echo '<p class="warning">'.L('new_Password_saved_but_weak').'!</p>';
				}
			}
			else
			{
				echo '<p class="error">'.L('Password_incorrect').'!</p>';
				$canSave = false;
			}
		}
		
		if ($canSave)
		{
			if ($user[0]->Save())
			{
				echo '<p class="success">'.L('Settings_saved').'!</p>';
			}
			else
			{
				echo '<p class="error">'.L('Error').'!</p>';
			}
		}
		else
		{
			echo '<p class="error">'.L('Settings_not_saved').'!</p>';
		}
	}// if($_POST) END

	$html  = '<form id="frm" method="post" action="?nobackend='.$_GET['nobackend'].'&project='.$projectName.'&uid='. $_GET['uid'] . '">';
	
	/*
	// todo: options should not be represented as string in model
	$html .= '<p><label>'.Lb('language').'</label><select name="language">';
	
	foreach($objects['_user']['col']['language']['add'] as $k => $lbl)
	{
		$html .= '<option value="'.$k.'"'. (($k == $lang) ? ' selected="selected"' : '') .'>'. $lbl.'</option>';
	}
	
	$html .= '</select>';*/
	
	$html .= '<p><label>'.Lb('userName').'</label><input id="username" name="username" type="text" value="'.$user[0]->username.'" /><span id="username_err" class="error"></span></p>';
	
	$html .= '<input type="hidden" name="salt" id="newsalt" />';
	$html .= '<p><label>'.L('new_password').'</label><input name="newPass1" id="password" type="password" /></p>';
	$html .= '<p><label>'.L('repeat_password').'</label><input name="newPass2" id="password2" type="password" /></p>';
	$html .= '';
	$html .= '';
	
	foreach($conf['fields'] as $field)
	{
		$html .= '<p><label>'.Lb($field).'</label>';
		switch($objects['_user']['col'][$field]['type'])
		{
			case 'BOOL':
				$html .= '<input type="checkbox" '.($user[0]->$field==1?'checked="checked"':'').' /><input type="hidden" name="'.$field.'" value="'.intval($user[0]->$field).'" />';
			break;
			case 'TEXT':
			case 'WIZARDTEXT':
				$html .= '<textarea rows="6" name="'.$field.'">'.htmlspecialchars($user[0]->$field).'</textarea>';
			break;
			default:
				$html .= '<input  name="'.$field.'" type="text" value="'.htmlspecialchars($user[0]->$field).'" />';
			break;
		}
		
		$html .= '</p>';
	}
	$html .= '<p><label></label><button type="submit" rel="disk" value="">'.L('save').'</button></p>';
	$html .= '</form>';
	echo $html;
?>

	<script>

	function prevImg(name)
	{
		var img = document.getElementById('prev');
		if(name.length>0)
		{
			img.src = '<?php echo $rel?>../vendor/cmskit/lib-jquery-ui/themes/'+name+'/preview.png';
			img.setAttribute( 'onclick', 'setTheme(\''+name+'\');' );
		}else{
			img.src = 'pixel.gif';
		}
	}
	
	function setTheme (name)
	{
		//set Theme via AJAX and reload the whole thing
		$.get('index.php?project=<?php echo $projectName?>&theme='+name , function() {
			top.location.reload();
		});
	}

	function setFontSize(no, newFontSize)
	{
		if(!newFontSize)
		{
			var currentFontSize = parent.$('html').css('font-size');
			var currentFontSizeNum = parseFloat(currentFontSize, 10);
			var newFontSize = currentFontSizeNum * no;
		}
		parent.$('html').css('font-size', newFontSize);
		parent.store['fnts'] = newFontSize;
		top.window.name = parent.JSON.stringify(parent.store);
	}

	function collectSettings(el)
	{
		document.getElementById('e_settings').value = parent.JSON.stringify(parent.store);
		el.innerHTML = "<strong><?php echo L('Settings_collected');?></strong>";
		el.setAttribute('style','padding:10px;background:#cfc;color:#333;');
	}



	function count(sb)
	{
		var sec = <?php echo (time()-$_SESSION[$projectName]['loginTime']);?>+sb, m = parseInt(sec/60);
		sec = sec%60, hr=parseInt(m/60), m = m%60;
		document.getElementById('loggedSince').innerHTML = (('0'+hr).substr(-2)+':'+('0'+m).substr(-2)+':'+('0'+sec).substr(-2));
		sb++;
		window.setTimeout(count, 1000, sb);
	}

	
	$(function()
	{
		$('form').attr('autocomplete', 'off');
		
		$('form input[type=checkbox]').change(function()
		{
			$(this).next().val(($(this).attr('checked')=='checked')?1:0);
		});
		
		
		//check if username exists
		$('#username').keyup(function()
		{
			$.get('index.php',
			{
				projectName: projectName,
				new_username: $(this).val()
			},
			function(data)
			{
				$('#username_err').text(data);
			});
		});
		
		var opts = {
			'minLength' : 5,
			'texts' : {
				1 : '<?php echo L('Password_too_weak')?>',
				2 : '<?php echo L('Password_weak')?>',
				3 : '<?php echo L('Password_ok')?>',
				4 : '<?php echo L('Password_strong')?>',
				5 : '<?php echo L('Password_very_strong')?>'
			}
		};
		$('#password').password_strength(opts);
		
		$('#password2').keyup(function()
		{
			if($(this).val() != $('#password').val())
			{
				$(this).css('color','red');
			}
			else
			{
				$(this).css('color','#000');
			}
		});
		
		//init when window is opened
		count(0);
		
		// generate a new random Salt
		$('#newsalt').val(GPW.complex(12));
	});
	</script>

	<?php
if(!isset($_GET['nobackend']))
{
	$styles = glob($rel.'inc/css/*', GLOB_ONLYDIR);

	echo '<p style="margin-top:80px;width:600px"><span style="float:right">';

	echo '
	<img id="prev" src="pixel.gif" style="float:right;cursor:pointer;width:90px" title="'.L('click_to_change_style').'" />
	<select class="ui-button ui-widget ui-state-default ui-corner-all" onchange="prevImg(this.value)"><option value="">'.L('change_style').'</option>';

	foreach($styles as $style)
	{
		if(file_exists($style.'/preview.png'))
		{
			$name = basename($style);
			echo '<option value="'.$name.'">'.$name.'</option>';
		}
	}
	echo '</select></span>';

	echo '
	<button type="button" rel="plus" title="'.L('increase_Fontsize').'" onclick="setFontSize(1.2)">A</button> 
	<button type="button" rel="cancel" title="'.L('reset_Fontsize').'"  onclick="setFontSize(1, parent.originalFontSize)">A</button>
	<button type="button" rel="minus" title="'.L('decrease_Fontsize').'" onclick="setFontSize(0.8)">A</button>

	';

	echo '</p>';
	//print_r($styles);
	
}//nobackend end

}//if user[0]->id
else
{
	echo '<h2 class="warning">'.L('user_unknown').'</h2>';
}
?>

</div>
</body>
</html>
