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
require dirname(dirname(dirname(__DIR__))) . '/inc/php/session.php';
$projectName = $_GET['project'];

function L($str) {return str_replace('_',' ',$str);}

//error_reporting(0);

// define some paths
$rel = '../../../';
$css = $rel.'../vendor/cmskit/jquery-ui/themes/'.end($_SESSION[$projectName]['config']['theme']).'/';
$js  = $rel.'../vendor/cmskit/jquery-ui/';

// collect Thumbnails for the Style-Switcher
$styles = glob($rel.'../vendor/cmskit/jquery-ui/themes/*', GLOB_ONLYDIR);
$style_html = '<h3>'.L('change_theme').'</h3>';
foreach($styles as $style)
{
	if(file_exists($style.'/preview.png'))
	{
		$n = basename($style);
		$style_html .= '<img onclick="setTheme(\''.$n.'\')" title="'.$n.'" src="'.$rel.'../vendor/cmskit/jquery-ui/themes/'.$n.'/preview.png" /> ';
	}
}


$header_html = '<span>'.L('last_Login').': '.strftime(L('%d._%m._%Y,_%H:%M:%S'), $_SESSION[$projectName]['special']['user']['lastlogin']).'</span> / 
<span>'.L('logged_in_since').': <span id="loggedSince"></span></span>
';
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
	label a {
		text-decoration: none;
		color: #222;
		cursor: default;
	}
	label a span {
		display: none;
	}
	.field {
		clear:both;
	}
	#deleteButton {
		display: none;
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

<script type="text/javascript" src="<?php echo $js;?>jquery.min.js"></script>
<script type="text/javascript" src="<?php echo $js;?>jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $js;?>plugins/jquery.plugin_password_strength.js"></script>
<script type="text/javascript" src="../../../inc/js/gpw.js"></script>


</head>
<body>
<div>
<?php echo $header_html?>	
</div>
<form id="frm" onsubmit="return false"></form>
<!--
<div>
	<a href="#">A<sup>-</sup></a>
	<a href="#">A</a>
	<a href="#">A<sup>+</sup></a>
</div>
-->
<div>
<?php echo $style_html?>
</div>
<script type="text/javascript">
	
// this is called from the save-button
function saveContent (id)
{
	
	if (!encoded)
	{
		encode();
		return;
	}
	
	$.get('ajax_create_session.php?project=<?php echo $projectName;?>', function(){
		$.post(
			'<?php echo $rel;?>crud.php?action=saveContent&actTemplate=default&project=<?php echo $projectName;?>&object=_user&actTemplate=default&objectId='+id,
			$('#frm').serialize(), 
			function (data)
			{
				if (isNaN(data))// error message
				{
					parent.message(data);
				}
				else 
				{
					parent.message(parent.langLabels.saved);
					
				}
			}
		);
	});
};

var oldPasswordHash = '';
var encoded = true;
function encode()
{
	if(encoded) return;
	var v = $('#input_password').val();
	
	// if the password-sting is empty or too short refill with old password-hash and exit
	if(v.length < 5)
	{
		$('#input_password').val(oldPasswordHash);
		encoded = true;
		
		// tell the user that the password was not accepted
		if(v.length > 0) alert('<?php echo L('Password_is_not_accepted_because_it_is_too_short')?>');
		
		return;
	}
	
	// get the password-hash
	$.post('ajax_encode_pw.php?project=<?php echo $projectName;?>&x='+Math.random(),
	{
		pass: v
	},
	function (data)
	{
		$('#input_password').val(data);
		encoded = true;
		$('#saveButton').show();
	})
};

function setTheme(t)
{
	$.get('ajax_set_theme.php',
	{
		project: '<?php echo $projectName;?>',
		theme: t
	},
	function (data)
	{
		alert(data);
		top.location.reload();
	})
}

function count(sb)
{
	var sec = <?php echo (time()-$_SESSION[$projectName]['loginTime']);?>+sb, m = parseInt(sec/60);
		sec = sec%60,
		hr=parseInt(m/60),
		m = m % 60;
	
	// faster bypassing jQuery
	document.getElementById('loggedSince').innerHTML = (('0'+hr).substr(-2)+':'+('0'+m).substr(-2)+':'+('0'+sec).substr(-2));
	sb++;
	window.setTimeout(count, 1000, sb);
}

$(document).ready(function()
{
	var pw_opts = {
		'minLength' : 5,
		'texts' : {
			1 : '<?php echo L('Password_too_weak')?>',
			2 : '<?php echo L('Password_weak')?>',
			3 : '<?php echo L('Password_ok')?>',
			4 : '<?php echo L('Password_strong')?>',
			5 : '<?php echo L('Password_very_strong')?>'
		}
	};
	
	$.get('ajax_create_session.php?project=<?php echo $projectName;?>', function()
	{
		$.get('<?php echo $rel;?>crud.php', 
		{
			action: 'getContent', 
			project: '<?php echo $projectName;?>', 
			object: '_user',
			relObject: 0,
			objectId: '<?php echo $_SESSION[$projectName]['special']['user']['id'];?>',
			actTemplate: 'default'
		}, 
		function(data)
		{
			$('#frm').html(data);
			$('#frm .input').each(function()
			{
				var e = $(this),
					d = e.data();
				if(d.option)
				{
					$.getScript('<?php echo $rel;?>wizards/'+d.wizard+'/include.php', function() {e[d.wizard]()});
				}
			});//each end
			
			$('#input_password')
			.attr('type','password') // change the type of the password-field
			.password_strength(pw_opts) // activate a password-strength-meter for this field
			.on('focus', function() // activate an onfocus-handler to make the field empty (while saving the old password)
			{
				$('#saveButton').hide();
				oldPasswordHash = $(this).val();
				encoded = false;
				$(this).val('');
			})
			.on('blur', function() // activate an onblur-handler to activate the automatic encoding (hashing)
			{
				encode()
			})
			//.after($(' <button onclick="encode()">encode</button>')); // additionally create an encode-button
		});
	});
	
	count(0);
});

// dummy jQuery function because some objects are triggered from the loaded html
$.ctrl = function(){} 

</script>
</body>
</html>
