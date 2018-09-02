<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Weixin {
    public $message;
    protected $CI;

    public function __construct () {
        log_message('info', '>>> Weixin Class Initalized');

        $this->CI =& get_instance();
        $this->CI->load->library('weixin/WeixinMessage');
        $this->message = new WeixinMessage();
    }

    public function checkSignature() {
        if ($this->CI->input->get('echostr') === NULL) {
            return FALSE;
        } else {
            $this->CI->config->load('weixin');
            $arr = array($this->CI->config->item('weixin_token'),
                         $this->CI->input->get('timestamp'),
                         $this->CI->input->get('nonce'));
            sort($arr, SORT_STRING);
            if (sha1(implode($arr)) == $this->CI->input->get('signature')) {
                $this->message->setResponseMsgType(WeixinMessage::MSGTYPE_RAW_TEXT);
                $this->sendResponse($this->CI->input->get('echostr'));
                log_message('info', '>>> check signature successfully');
            } else {
                log_message('info', '>>> check signature failed');
            }
            return TRUE;
        }
    }

    public function sendResponse($message = NULL) {
        $this->message->sendResponse($message);
    }

    public function responseSuccess($exit = FALSE) {
        $this->message->responseSuccess($exit);
    }

    public function getHelp() {
        $this->CI =& get_instance();
        $this->CI->config->load('weixin');
        return $this->CI->config->item('weixin_help');
    }
}
