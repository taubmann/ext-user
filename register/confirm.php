<?php
require dirname( __DIR__ ) . '/inc/header.php';

function show($msg)
{
	$body = '<h3>'.$msg[0].'</h3><p>'.$msg[1].'</p>';
	echo str_replace('###CONTENT###', $body, file_get_contents('inc/confirm.html'));
}

if(isset($_GET['c']))
{
	$arr = explode(':', base64_decode($_GET['c']));// project, username, salt, hash
	if(count($arr) === 4)
	{
		// re-define $projectName
		$projectName = $arr[0];
		if(!$projectPath = realpath( __DIR__ . '/../../../../projects/' . $projectName )) exit(show(array(L('wrong_projectname'),'')));
		
		// check/load register-Configuration
		if(!file_exists($projectPath . '/extensions/default/config/'.$configName.'_register.php')) exit(L('configuration_is_missing'));
		require $projectPath . '/extensions/default/config/'.$configName.'_register.php';
		if(!$conf = json_decode($config, true)) exit(L('configuration_is_corrupt'));
		
		if (!isset($conf['objects']['user']['name'])) exit(L('user_object_is_not_defined'));
		if (!isset($conf['objects']['groups']['name'])) exit(L('profile_object_is_not_defined'));
		
		// load classes
		require $projectPath . '/objects/class.' . $conf['objects']['user']['name'] . '.php';
		require $projectPath . '/objects/class.' . $conf['objects']['groups']['name'] . '.php';
		
		// the required column-names
		$PASS = $conf['objects']['user']['fields']['pass'];
		$NAME = $conf['objects']['user']['fields']['name'];
		$ACTIVE = $conf['objects']['user']['fields']['active'];
		$CONFIRMED = $conf['objects']['user']['fields']['confirmed'];
		$UNLOCK = $conf['objects']['user']['fields']['unlock'];
		
		
		$n = $projectName . '\\' . $conf['objects']['user']['name'];
		$u_obj = new $n();
		
		$list = $u_obj->GetList(array(
										array( $NAME, '=', $arr[1] ), 
										array( $PASS, '=', $arr[2].':'.$arr[3] )
									), array(), 1);
		
		if (isset($list[0]))
		{
			
			if ($list[0]->{$ACTIVE} == 1)
			{
				show(array(L('account_already_confirmed'), L('you_can_close_this_window_now')));
				exit();
			}
			
			// auto-unlock and associate to profiles defined in autounlock-Array
			if ( isset($conf['autounlock']) )
			{
				$list[0]->{$ACTIVE} = 1;// user is activated
				$conf['profiles'] = $conf['autounlock'];
			}
			
			// if we have a -valid- unlock-Code, activate the account immediately
			if ( !empty($list[0]->{$UNLOCK}) && isset($conf['unlocks'][$list[0]->{$UNLOCK}]) )
			{
				$list[0]->{$ACTIVE} = 1;// user is activated
				$conf['profiles'] = $conf['unlocks'][$list[0]->{$UNLOCK}];// user is assocciated to the profiles within the unlock-array
				echo 'da';
			}
			
			
			// set the account to "confirmed"
			$list[0]->{$CONFIRMED} = 1;
			
			// add the user to the defined Profile-IDs
			if(isset($conf['profiles']))
			{
				$n = $projectName . '\\' . $conf['objects']['groups']['name'];
				$p_obj = new $n();
				
				foreach($conf['profiles'] as $p_id)
				{
					$p_item = $p_obj->Get($p_id);
					$p_action = 'Add' . $conf['objects']['groups']['name'];
					$list[0]->$p_action($p_item);
				}
			}
			
			if($list[0]->Save())
			{
				$txt = (isset($conf['redirect']) ? '<a href="'.$conf['redirect'].'">'.L('go_on').'</a>' : L('you_can_close_this_window_now'));
				show(array('confirmation_success', $txt));
			}
			else
			{
				show(array(L('sorry_your_account_could_not_be_saved'), L('please_contact_the_site_administrator')));
			}
		}
		else
		{
			show(array(L('confirmation_user_not_found'), ''));
		}
	}
	else
	{
		show(array(L('confirmation_code_incorrect'), ''));
	}
}
else
{
	show(array(L('get_param_missing'), ''));
}

?>
