<?php
// general Header for User-Wizards

error_reporting(0);
foreach($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W/', '', $v); }

$projectName = $_GET['project'];
$objectName = $_GET['object'];


$lang = $_SESSION[$projectName]['lang'];

$backend = realpath(__DIR__ . '/../../../');
require($backend . '/inc/php/functions.php');

@include (__DIR__ . '/locale/'.$lang.'.php');
