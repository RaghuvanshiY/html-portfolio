<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['pre_system'][] = array(
    'class'    => 'Check_installer',
    'function' => 'check_for_installer',
    'filename' => 'check_installer.php',
    'filepath' => 'hooks');

$hook['post_controller_constructor'][] = array(
    'class'    => 'Timezone',
    'function' => 'Timezonefunction',
    'filename' => 'timezone.php',
    'filepath' => 'hooks');
    
$hook['post_controller_constructor'][] = array(
    'class'    => 'BaseUrl',
    'function' => 'MyBaseUrl',
    'filename' => 'baseUrl.php',
    'filepath' => 'hooks');

$hook['post_controller_constructor'][] = array(  
    'class'    => 'MyOtherClass',
    'function' => 'MyOtherfunction',
    'filename' => 'Myotherclass.php',
    'filepath' => 'hooks');

$hook['post_controller_constructor'][] = array(
    'class'    => 'LanguageLoader',
    'function' => 'initialize',
    'filename' => 'LanguageLoader.php',
    'filepath' => 'hooks');


    

