<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Weixin {
    public $message;

    public function __construct () {
        log_message('info', '>>> Weixin Class Initalized');

        $this->message = new WeixinMessage();
    }

    public function checkSignature() {
        $CI =& get_instance();

        if ($CI->input->get('signature') === NULL) {
            return FALSE;
        } else {
            $CI->config->load('weixin');
            $arr = array($CI->config->item('weixin_token'),
                         $CI->input->get('timestamp'),
                         $CI->input->get('nonce'));
            sort($arr, SORT_STRING);
            if (sha1(implode($arr)) == $CI->input->get('signature')) {
                $this->message->setResponseMsgType(WeixinMessage::MSGTYPE_RAW_TEXT);
                $this->sendResponse($CI->input->get('echostr'));
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
        $CI =& get_instance();
        $CI->config->load('weixin');
        return $CI->config->item('weixin_help');
    }
}
