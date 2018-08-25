<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (! defined('WEIXIN_ROOT')) {
    define('WEIXIN_ROOT', dirname(__FILE__).'/');
    require_once(WEIXIN_ROOT.'Weixin/WeixinMessage.php');
}

class Weixin {
    const RETURN_FAILED     = 'ggEwL0OuJOQ7OuBy';

    private $appID          = 'wxfc4ad89f36beb189';
    private $appSecret      = '8de181c7b52dccd13087adb97b9620f0';
    private $encodingAESKey = 'saFkURTUS05TFJti201a3L5HI3c897jm4qXNhYD9i3W';
    private $token          = 'ddqddz';

    protected $CI;
    public    $message;

    public function __construct () {
        $this->CI =& get_instance();
        log_message('info', '>>> Weixin Class Initalized');
    }

    public function checkSignature() {
        if ($this->CI->input->get('signature') === NULL) {
            $this->message = new WeixinMessage(ENVIRONMENT == 'production');
            return FALSE;
        } else {
            $arr = array($this->token,
                         $this->CI->input->get('timestamp'),
                         $this->CI->input->get('nonce'));
            sort($arr, SORT_STRING);
            if (sha1(implode($arr)) == $this->input->get('signature')) {
                $this->sendResponse($this->CI->input->get('echostr'), TRUE);
                log_message('info', '>>> Check signature successfully');
            } else {
                log_message('info', '>>> Check weixin signature failed');
            }
            return TRUE;
        }
    }

    // 如果是微信接入请求，通过验证时发送回执，参数二仅此一处有用。
    public function sendResponse($message = array(), $checkSignature = FALSE) {
        echo $checkSignature ? $message : $this->message->getResponse($message);
    }
}