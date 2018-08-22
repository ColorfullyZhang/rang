<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Weixin extends CI_Controller {
    private $appID = 'wxfc4ad89f36beb189';
    private $appSecret = '8de181c7b52dccd13087adb97b9620f0';
    private $encodingAESKey = 'saFkURTUS05TFJti201a3L5HI3c897jm4qXNhYD9i3W';
    private $token = 'ddqddz';
    
    public function __construct() {
        parent::__construct();

        if ($this->input->get('signature') !== NULL) {
            $this->checkSignature();
        }
    }

    private function checkSignature() {
        $arr = array($this->token,
                     $this->input->get('timestamp'),
                     $this->input->get('nonce'));
        sort($arr, SORT_STRING);
        if (sha1(implode($arr)) == $this->input->get('signature')) {
            log_message('info', 'Check signature successful');
            echo $this->input->get('echostr');
        } else {
            log_message('info', 'Check signature failed.');
        }
        exit;
    }

    public function index() {
        echo $this->input->get('echostr');
    }
}
