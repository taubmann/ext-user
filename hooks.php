<?php

/**
* Login-Hook for User-Management
* simply load some additional Code
*/
function loginUserCheck ($mysession)
{
	global $log, $projectName, $projectPath, $objects, $post;
	
	// super-root does not need this check
	if(isset($mysession['root'])) return $mysession;
	
	if($log === true) return;
	include('extensions/user/login/check.php');
	return $mysession;
	
}
// add the Function to the Functions processed on Login
$loginHooks[] = 'loginUserCheck';


/**
* Hook for basic Access Control Management (acm)
* 
* control "CUDAS"-Actions on Object or Entries
* [c] => create [u] => update [d] => delete [a] => associate [s] => sort
* if not available in /hooks/hooks.php copy it to this file
*/

function acm()
{
	global $projectName, $c, $objects, $objectName, $action;
	
	if(isset($_SESSION[$projectName]['root'])) return;
	
	$na = 'action is not allowed';// translate this
	if(!$acl = $objects[$objectName]['acl']) return;// check for access-rights

	// hide Buttons ///////////////////////////////////////
	if($acl['c']<1){ $c->disallow['newbutton']=1; }
	if($acl['u']<1){ $c->disallow['savebutton']=1; }
	if($acl['d']<1){ $c->disallow['deletebutton']=1; }
	if($acl['a']<1){ $c->disallow['referenceselect']=1; }
	if($acl['s']<1 && $objects[$objectName]['ttype']){ $c->disallow['sortbutton']=1; }
	
	// abort if detected an "illegal" Action //////////////
	if($acl['c']<1 && $action=='createContent'){ exit($na); }
	if($acl['u']<1 && $action=='saveContent'){ exit($na); }
	if($acl['d']<1 && $action=='deleteContent'){ exit($na); }
	if($acl['a']<1 && $action=='saveReferences'){ exit($na); }
}

/**
* Function "Content-Copy"
* Allows easy Setup of Workspaces
* For Workspaces with User-restriction just limit access to the Fields {$if} and {$to}
* 
* How does it work?
* 
* $params is an array(if, from, to)
* If the Field {$params[0]} is checked (==1), copy the Content from {$params[1]} to {$params[2]} and reset {$params[0]}
* 
* Field {$params[0]} should be Boolean (Checkbox) ! 
* Fields {$params[1]} and {$params[2]} should have the same Type !
* 
* Example
* PRE:ccopy:go_online,workspace,weboutput
* 
*/
function ccopy($params)
{
	
	if ($_GET['action'] === 'saveContent' && count($params)===3)
	{
		if ($_POST[$params[0]] == 1)
		{
			$_POST[$params[2]] = $_POST[$params[1]];
			$_POST[$params[0]] = 0;
		}
	}
}


/**
* Function "acmByReference"
* Get all IDs connected to the User OR one of it's Profiles
* This can be used to easily create Access-Restrictions based on Groups or Users.
* 
* PRE:acmByRef
* 
*/

function acmByRef()
{
	global 	$objectName, $objectId,
			$referenceName, $referenceId,
			$projectName, $projectPath, $action, $c, $objects, $output, $TMP, $_DB, $_CONF;
	
	// global-access/no filtering for Root-Users
	if (!empty($_SESSION[$projectName]['root'])) return;
	
	// define on what Object we are working on
	$on = $objectName;
	$oid = $objectId;
	
	if (in_array($action, array('getReferences','getConnectedReferences','')))
	{
		$on = $TMP;
		$oid = $referenceId;
	}
	
	$oDB = intval($objects[$on]['db']);
	$objectHooks = $objects[$on]['hooks'];
	$ids = array(0); // id-string-array
	$iq = array();  // insert-query-array
	$outputId = intval($output); // if a creation-Process is returning a new ID it will be safely casted as a positive Integer, otherwise it is 0
	
	// test against _user
	$usermap = array($on, '_user');
	natcasesort($usermap);
	$usermap = implode('', $usermap) . 'map';
	
	if (file_exists($projectPath.'/objects/class.'.$usermap.'.php'))
	{
		$uid = intval($_SESSION[$projectName]['special']['user']['id']);
		$q1 = 'SELECT GROUP_CONCAT(`'.$on.'id`) AS i FROM `'.$usermap.'` WHERE `_userid` = '.$uid;
		try{ $ids[] = $_DB::instance($oDB)->query($q1)->fetch()->i; }catch(Exception $e){ }
		
		// insert-statement to associate a new Entry to the User
		$iq[] = 'INSERT INTO `'.$usermap.'` (`_userid`, `'.$on.'id`, `_usersort`, `'.$on.'sort`) VALUES ('.$uid.', '.$outputId.', 0, 0)';
	}
	
	// test against _profile
	$profilemap = array($on, '_profile');
	natcasesort($profilemap);
	$profilemap = implode('', $profilemap) . 'map';
	
	if (file_exists($projectPath.'/objects/class.'.$profilemap.'.php'))
	{
		$pids = array_filter(array_keys($_SESSION[$projectName]['special']['user']['profiles']), 'is_numeric');
		$q1 = 'SELECT GROUP_CONCAT(`'.$on.'id`) AS i FROM `'.$profilemap.'` WHERE `_profileid` IN (' . implode(',', $pids) . ')';
		try{ $ids[] = $_DB::instance($oDB)->query($q1)->fetch()->i; }catch(Exception $e){ }
		
		// insert-statements to associate a new Entry to all Profiles, the User belongs to
		if ($outputId != 0)
		{
			foreach ($pids as $pid)
			{
				$iq[] = 'INSERT INTO `'.$profilemap.'` (`_profileid`, `'.$on.'id`, `_profilesort`, `'.$on.'sort`) VALUES ('.$pid.', '.$outputId.', 0, 0)';
			}
		}
	}
	
	
	$idss = trim(implode(',', $ids), ',');
	
	switch ($action)
	{
		case 'getList':
		case 'getTreeList':
			if (strlen($idss)>1)
			{
				$c->getAssocListFilter[] = array('a.id IN ('.$idss.')');
				$c->getListFilter[] = array('`id` IN ('.$idss.')');
			}
		break;
		
		case 'getReferences':
		case 'getConnectedReferences':
			if (strlen($idss)>1)
			{
				$c->disableConnectingFor = explode(',',implode(',', $ids));
				$c->getListFilter[] = array('`id` IN ('.$idss.')');
			}
		break;
		
		case 'saveContent':
		
		case 'getContent':
			// if it is a existing Entry
			if ($oid != 0)
			{
				$is = ' ,'.implode(',', $ids).',';// prepare the search-string
				if (strpos($is, ','.$oid.',') === false)
				{
					exit('<h1>'.L('you_have_no_access_to_this_Entry').'</h1>');
				}
			}
			// if we have a "blindcopy" to create a new Entry
			else
			{
				// PRE-State: dynamically add itself as a post-processing-hook
				if ($output === '')
				{
					if (!isset($objectHooks['PST'])) $objectHooks['PST'] = array();
					$objectHooks['PST'][] = array('acmByRef');
				}
				// PST-State: we run all collected Queries
				if ($outputId != 0)
				{
					foreach ($iq as $i)
					{
						try{ $_DB::instance($oDB)->query($i); }catch(Exception $e){ }
					}
				}
			}
		break;
	}
	
	
}//acmByRef END

/**
* Function "filterByOwnership"
* Saves/Checks the Profile-IDs or the User-ID, of the Creator to/from a hidden Field.
* This can be used to easily create Access-Restrictions based on Groups or Users.
* 
* PRE:filterByOwnership:hidden_field[:only_private]
* 
*/
function filterByOwnership ($params)
{
	
	if ( in_array($_GET['action'], array('saveContent','getList','getContent')) )
	{
		global $c, $projectName, $objects, $objectName, $objectId;
		
		// global-access/no filtering for root users
		if (isset($_SESSION[$projectName]['root'])) return;
		
		// get User- OR Profile-IDs
		$id_str =	(
						isset($params[1])
						? ','.$_SESSION[$projectName]['special']['user']['id'].',' 
						: ','.implode(',', array_keys($_SESSION[$projectName]['special']['user']['profiles'])).','
					);
		
		
		
		switch ($_GET['action'])
		{
			case 'saveContent':
				if ($objectId == 0)
				{
					$_POST[$params[0]] = $id_str;
				}
			break;
			case 'getList':
				// create the filter-bubble ;-)
				if ($params[1])
				{
					$filter = array($params[0], 'LIKE', '%,'.$_SESSION[$projectName]['special']['user']['id'].',%');
				}
				else
				{
					$filter = array();
					foreach ($_SESSION[$projectName]['special']['user']['profiles'] as $pid => $pname)
					{
						$filter[] = array($params[0], 'LIKE', '%,'.$pid.',%');
					}
				}
				// apply filters to filter-array
				$c->getListFilter = array_merge($c->getListFilter, $filter);
				
			break;
			case 'getContent':
				if ($objectId !== 0)
				{
					$n = $projectName.'\\'.$objectName;
					$obj = new $n();
					$item = $obj->Get($objectId);
					if (strpos($item->$params[0], $id_str) === false) exit('you have no access to this Entry');
				}
			break;
		}
	}
}


/**
 * Extension: "user"
 * 
 * this Function allows you to build dynamic Access-Restrictions based on Profile-ID(s) (eg. Groups) or User-ID
 * 
 * "dynamic" means, that accessibility of the Element is based on Authorship (eg. who has created the Entry)
 * 
 * This is useful in Situations where several Groups should work on their Contents or for some "private" User-Areas
 * 
 * How does it work?
 * If one (the User) creates a new Entry some extra-informations are written to a hidden Field.
 * Entries are only listed if 
 * 
 * In your Object you need an extra Filter-Field to store the User-Informations (Type: Excluded-Integer OR Excluded-Varchar)
 * 
 * Example-Call
 * PRE:dynAcm:filtermode,filterfield
 * 
 * you can choose between 3 Filter-Modes to give Access to:
 * 
 * 1. all Users whitch are also connected to one or more Profiles of the Creator
 * 2. all Users whitch are also connected to the first Profile of the Creator (sort Profiles accordingly)
 * 3. only the Creator (Filter by User-ID)
 * 
 * feel free to adapt the Filter-Rules to your needs :-)
 * 
 * 
 
function dynAcm ($params)
{
	global $c;
	
	// Root has access to everything!
	if($_SESSION[$_GET['project']]['root']) return;
	
	$act = $_GET['action'];
	$u = $_SESSION[$_GET['project']]['special']['user'];
	$p = $u['profileids'];
	echo $params[0];
	
	// save User/Profile-Infos to 
	if ($act == 'createContent')
	{
		switch ($params[0])
		{
			// 1. use all Profile-IDs as searchable String
			case '1': $c->$params[1] = ','.implode(',', $p).','; break;
			// 2. use the first Profile-ID
			case '2': $c->$params[1] = $p[0]; break;
			// 3. use the User-ID
			case '3': $c->$params[1] = $u['id']; break;
		}
	}
	
	// List-Filter 
	if ($act == 'getList')
	{
		$filter = false;
		switch ($params[0])
		{
			// 1. search for Profile-ID (as a OR-concatenation)
			case '1': $filter = array(array()); foreach($p as $i){ $filter[0][] = array($params[1], 'LIKE', ','.$i.','); } break;
			// 2. search for a Profile-ID
			case '2': $filter = array($params[1], '=', $p[0]); break;
			// 3. search for a User-ID
			case '3': $filter = array($params[1], '=', $u['id']); break;
		}
		
		if ($filter)
		{
			$c->getListFilter[] = $filter;
			//print_r($filter);
		}
	}
	
}*/



/* Extension: "user"
 * 
 * 
 * Example-Call
 * PRE:dynReferenceAcm:filtermode,filterfield,referenceobjectname
 * 
 * */
function dynReferenceAcm ()
{
	global $c, $referenceName;
	// Root has access to everything!
	if($_SESSION[$_GET['project']]['root']) return;
	$act = $_GET['action'];
	$u = $_SESSION[$_GET['project']]['special']['user'];
	$p = $u['profileids'];
	if ($act == 'getReference' && $referenceName==$params[2])
	{
		$filter = false;
		switch ($params[0])
		{
			// 1. search for Profile-ID (as a OR-concatenation)
			case '1': $filter = array(array()); foreach($p as $i){ $filter[0][] = array($params[1], 'LIKE', ','.$i.','); } break;
			// 2. search for a Profile-ID
			case '2': $filter = array($params[1], '=', $p[0]); break;
			// 3. search for a User-ID
			case '3': $filter = array($params[1], '=', $u['id']); break;
		}
		
		if ($filter)
		{
			$c->getAssocListFilter[] = $filter;
			//print_r($filter);
		}
	}
}

?>
