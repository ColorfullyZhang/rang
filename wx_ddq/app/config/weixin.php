<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['weixin_appID']          = 'wxfc4ad89f36beb189';
$config['weixin_appSecret']      = '3df5deb997a99b594a61f54fc26b464f';
$config['weixin_encodingAESKey'] = '';
$config['weixin_token']          = 'ddqddz';

$config['weixin_tokenFile']      = DATAPATH.'runtime/token.txt';
$config['weixin_queryLog']       = DATAPATH.'runtime/querylog.txt';
$config['weixin_help']           = <<<EOF
回复序号查询
?： 看帮助
1： 查地标
2： 查公司
3： 查客户
4： 查同事
5： 查报价
EOF;
