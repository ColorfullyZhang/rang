<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['upload_menu']          = 'yjc/uploadmenu';
$route['get_access_token']     = 'yjc/getaccesstoken';
$route['ifuaegdhdmjezszoposv'] = 'yjc/index_weixin';
$route['(:num)']               = 'yjc/index_cli/$1';

$route['default_controller']   = 'welcome';
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;