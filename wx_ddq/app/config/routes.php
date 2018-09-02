<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['upload_menu']          = 'ddqddz/uploadmenu';
$route['get_access_token']     = 'ddqddz/getaccesstoken';
$route['ifuaegdhdmjezszoposv'] = 'ddqddz/index_weixin';
$route['(:num)']               = 'ddqddz/index_cli/$1';

$route['default_controller']   = 'welcome';
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;