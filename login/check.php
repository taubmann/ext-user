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
/**
 * User-Management
 *
 * Login-Check
 * check if a valid User exists
 */

include_once($projectPath . '/objects/class._user.php');
include_once($projectPath . '/objects/class._profile.php');

// 
$u = $projectName . '\\_user';
$userObject = new $u();

$p = $projectName . '\\_profile';
$profileObject = new $p();


/**
 * Processing of user-rights
 *
 * @param mixed $profiles
 * @param mixed $objects
 */
function processUserModel($profiles, $objects)
{
    global $mysession;
    $accessibleObjects = array();
    $aclObjects = array();
    $profileList = array();
    $directoryList = array();
    $directoryCheckList = array();

    foreach ($profiles as $profile) {

        // fill Profile-Array
        $profileList[$profile->id] = $profile->name;

        // decode Profile-JSON
        if ($acc_json = json_decode($profile->object_access, true)) {

            // loop Objects
            foreach ($acc_json as $k => $v) {
                if (isset($objects[$k])) {

                    if (!isset($accessibleObjects[$k])) {
                        $accessibleObjects[$k] = array();
                    }

                    // define empty Column-Array
                    $col = array();

                    // Object is completely accessible (no ACL)
                    if ($v === 1) {
                        foreach ($objects[$k]['col'] as $ko => $vo) {
                            $col[] = $ko;
                        }
                        $aclObjects[$k] = 1;
                    } else {

                        // collect Fields
                        if (isset($v['show'])) {
                            //exit('hier');
                            foreach ($v['show'] as $vs) {
                                if ($objects[$k]['col'][$vs]) {
                                    $col[] = $vs;
                                }
                            }
                        }

                        // create/fill Action-Control-List
                        if (isset($v['action']) && (!isset($aclObjects[$k]) || $aclObjects[$k] !== 1)) {
                            // set ACL-Array && Hook
                            if (!isset($aclObjects[$k])) {
                                $aclObjects[$k] = array('c' => 0, 'r' => 0, 'u' => 0, 'd' => 0, 'a' => 0, 's' => 0);


                                if (!isset($objects[$k]['hooks'])) {
                                    $objects[$k]['hooks'] = array('PRE' => array());

                                }

                                // register the acm-Hook for this Object
                                $objects[$k]['hooks']['PRE'][] = array('acm');
                            }

                            // increase Action-Permissions
                            foreach ($v['action'] as $ka => $va) {
                                if (isset($aclObjects[$k][$ka]) && $aclObjects[$k][$ka] < $va) {
                                    $aclObjects[$k][$ka] = $va;
                                }
                            }

                            // assign ACL to objects
                            $objects[$k]['acl'] = $aclObjects[$k];
                        }

                    }

                    // merge Fields into the Column-Array
                    $accessibleObjects[$k] = array_unique(array_merge($accessibleObjects[$k], $col));
                }
            }
        }

        // modulate (replace/extend) some Properties in $objects through profiles
        if ($mod_json = json_decode($profile->modulation, true)) {
            $objects = array_replace_recursive($objects, $mod_json);
        }

        // collect File-Access
        if ($file_json = json_decode($profile->file_access, true)) {
            foreach ($file_json['fileaccess'] as $v) {
                if (!in_array($v['path'], $directoryCheckList)) {
                    $directoryList[] = $v;
                }
                $directoryCheckList[] = $v['path'];
            }
        }
    }

    // loop Objects
    foreach ($objects as $on => $oa) {
        // if the object should not be accessible, we delete it
        if (!isset($accessibleObjects[$on])) {
            unset($objects[$on]);
        } // Object is accessible, we have to loop the Fields
        else {
            // if the Field should not be accessible, we delete it (except id)
            foreach ($oa['col'] as $cn => $cv) {
                if ($cn !== 'id' && !in_array($cn, $accessibleObjects[$on])) {
                    unset($objects[$on]['col'][$cn]);
                }
            }

            // if the Relation should not be accessible, we delete it
            if (isset($oa['rel'])) {
                foreach ($oa['rel'] as $rn => $rv) {
                    if (!isset($accessibleObjects[$rn])) {
                        unset($objects[$on]['rel'][$rn]);
                    }
                }
            }
        }
    }

    //print_r($accessibleObjects);exit();

    return array($objects, $profileList, $directoryList);

}// processUserModel END

 ///////////////////////////////////////////////////////////////////////////

// lookup for the User
$user = $userObject->GetList(array( // fcv
        array('username', '=', $_POST['name']), // Username
        array('active', '=', '1'), // User must be activated
        array(
            array('expire', '=', '0'), // Account-Expiration must be set to 0 (==infinitive)
            array('expire', '>', time()) // OR Timestamp must be in the Future
        )
    ),
    array(), // we need no sortby
    1 // limit 1 (we assume there is only one User with this Username)
);

// User exists, go on
if (isset($user[0]->id)) {

    // check the Password ( salt:crypted_hash ) by re-encoding the Hash
    $a = explode(':', $user[0]->password);
    $a = array_shift($a);

    if ($user[0]->password === crpt(substr($_POST['pass'], 0, 200), $a)) {

        $log = true;

        $mysession['special']['user'] = array(
            'prename' => $user[0]->prename,
            'lastname' => $user[0]->lastname,
            'profiles' => array(),
            'id' => $user[0]->id,
            'lastlogin' => $user[0]->lastlogin,
            'logintime' => time(),
            'wizards' => array(
                array(
                    'name' => L('Settings'),
                    'url' => 'extensions/user/settings/?project=' . $projectName
                )
            ),
            'fileaccess' => array(
                array(
                    'driver' => 'LocalFileSystem',
                    'path' => 'files/',
                    'accessControl' => 'access',
                    'alias' => 'Files',
                    'tmbPath' => 'files/.tmb',
                )
            ),
        );
        // overwrite language by user-language
        $mysession['lang'] = $user[0]->language;

        if (!empty($user[0]->settings)) $mysession['settings'] = json_decode($user[0]->settings, true);


        // User is not ROOT => collect access-rules from Profiles
        if ($user[0]->is_root != 1) {
            unset($mysession['root']);

            $profiles = $user[0]->Get_profileList(array(array('active', '=', '1')));

            $arr = processUserModel($profiles, $objects);

            $objects = $arr[0];
            $mysession['special']['user']['profiles'] = $arr[1];
            $mysession['special']['user']['fileaccess'] = $arr[2];
        } else // User is Project-Root
        {
            $mysession['root'] = 1;
            $profiles = $profileObject->GetList(array(array('active', '=', '1')));

            foreach ($profiles as $profile) {
                $mysession['special']['user']['profiles'][$profile->id] = $profile->name;
            }
        }

        // set&save actual Timestamp as "last Login"
        $user[0]->lastlogin = time();
        $user[0]->Save();

    } else {
        // wrong Password! we can do some actions to protect against brute-forge password-guessing
        $log = false;
    }

} else {
    // User does not exist...
    $log = false;
}
