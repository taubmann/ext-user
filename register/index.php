<?php

require dirname( __DIR__ ) . '/inc/header.php';

// check/load register-Configuration
if(!file_exists($projectPath . '/extensions/default/config/'.$configName.'_register.php')) exit(L('configuration_is_missing'));
require $projectPath . '/extensions/default/config/'.$configName.'_register.php';
if(!$conf = json_decode($config, true)) exit(L('configuration_is_corrupt'));

// load general Data-Model
require $projectPath . '/objects/__model.php';

$fieldcount = 0;
$field_html = '';
// JS-Error-Messages
$jsMsgs = array('generate_one','somme_errors_left','a_confirmation_mail_is_sent_to_your_address','you_can_close_this_window_now');

// generate Input-Elements
function inp ($k, $v, $col)
{
	global $lang, $fieldcount;
	
	if ($k == 'captcha')
	{
		$col['type'] = 'CAPTCHA';
		
	}
	
	$label = isset($col['lang'][$lang]['label']) ? $col['lang'][$lang]['label'] : ucfirst($k);
	$sh = '
			<li'.
			((isset($v['test'])) ? ' data-test="'.$v['test'].'"' : '').
			((isset($v['unique'])) ? ' data-unique="1"' : '').
			'>
				<label '.(isset($col['lang'][$lang]['tooltip']) ? 'title="'.strip_tags($col['lang'][$lang]['tooltip']).'"' : '').'>'.
				$label.
				'</label>
				';
	
	$placeholder = (isset($col['lang'][$lang]['placeholder']) ? ' placeholder="'.$col['lang'][$lang]['placeholder'].'"' : '');
	switch($col['type'])
	{
		case 'BOOL':
			$val = '0';
			$chcked = '';
			if(isset($col['default']) && $col['default']==1) {
				$val = '1';
				$chcked = ' checked="checked"';
			}
			$s = $sh.'<input id="'.$k.'" type="hidden" value="'.$val.'" /><input'.$chcked.' type="checkbox" onchange="change(this,\''.$k.'\')" /> ';
			// to show additional Text (e.g. Descriptions), use the Placeholder in Modeling [...]
			if (isset($col['lang'][$lang]['placeholder'])) $s .= $col['lang'][$lang]['placeholder'];
			$fieldcount++;
		break;
		
		case 'HIDDENTEXT':
			$s = '<li><input class="inp" id="'.$k.'" type="hidden" value="'.$col['default'].'" />';
		break;
		
		case 'TEXT':
			$s = $sh.'<textarea class="inp"'.$placeholder.' id="'.$k.'"></textarea>';
			$fieldcount++;
		break;
		
		case 'DATE':
			$s = '<input class="date inp" id="'.$k.'" type="text" /><span></span>';
			$fieldcount++;
		break;
		
		case 'CAPTCHA':
			$s = '<input placeholder="'.L('enter_the_result').'" type="text" class="inp" id="captcha" autocomplete="off" />
				<img id="captcha_img" title="'.L('click_to_refresh_Image').'" style="height:26px;cursor:pointer;vertical-align:bottom" src="../../../inc/php/captcha_math.php?r='.time().'" />';
				$fieldcount++;
		break;
		
		default:
			
			// select-dropdown detected
			if( isset($col['add']['wizard']) && $col['add']['wizard']=='select')
			{
				//prepare options
				$ol = explode('|', $col['add']['option']);
				
				$s = $sh.'<input id="'.$k.'" type="hidden" />';
				$s .= '<select class="selvarchar"><option value="">'.$label.'</option>';
				
				foreach($ol as $o)
				{
					$lbl = $o; 
					$t = '';
					// extract Label if any [...]
					if(preg_match( '/\[(.*?)\]/', $o, $match)===1) {
						$lbl = $match[1];
						$o = str_replace($match[0],'',$o);
					}
					// extract Title if any (...)
					if(preg_match( '/\(([^)]+)\)/', $o, $match)===1) {
						$t = $match[1];
						$o = str_replace($match[0],'',$o);
					}
					$s .= '<option title="'.$t.'" value="'.$o.'">'.$lbl.'</option>';
				}
				$s .= '</select>';
			}
			else
			{
				$s = $sh.'<input class="inp"'.$placeholder.' id="'.$k.'" type="text" autocomplete="off" />';
			}
			$fieldcount++;
		break;
	}
	$s .= '
				<span class="error"></span>
			</li>
			';
	return $s;
}


foreach ($conf['fields'] as $k => $v)
{
	if ($conf['fields'][$k]['show'])
	{
		$field_html .= inp($k, $v, (isset($objects['_user']['col'][$k])?$objects['_user']['col'][$k]:array()));
	}
}
$x=L('enter_the_letters');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Register yourself</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="inc/styles.css" />
	<style>
	</style>
</head>
<body>
<noscript><h1><?php echo L('Javascript_must_be_activated')?>!</h1></noscript>
<div id="form">
	<h3><?php echo L('register_yourself') . ' (' . $fieldcount . ' ' . L('Steps') . ')'; ?></h3>
	<form method="post" >
		<ul>
			
			<?php
			// form-html goes here
			echo $field_html;
			?>

			<li data-test="none" class="submit">
				<input type="submit" value="<?php echo L('Register')?>" id="submit" />
				<span id="submit_error" class="error"></span>
			</li>
		</ul>
	</form>
</div>

<script>
	var projectName = '<?php echo $projectName;?>';
	var configName = '<?php echo $configName;?>';
	var lang = '<?php echo $lang;?>';
	var inputTest = {};
	var jsMsgs = {};
	
<?php
// print JS-Regex-Checks
foreach($conf['tests'] as $k => $v)
{
	echo 'inputTest["'.$k.'"] = [ '.str_replace('\\\\', '\\', base64_decode($v['regex'])).' , "'.L($v['error']).'"];
';
}
echo "\n\t";
// print JS-Error-Messages
foreach($jsMsgs as $v)
{
	echo 'jsMsgs["'.$v.'"] = "'.L($v).'";';
}

?>

</script>
<script src="../../../../vendor/cmskit/jquery-ui/jquery.min.js"></script>
<script src="../../../inc/js/gpw.js"></script>
<script src="inc/functions.js"></script>

</body>
</html>
