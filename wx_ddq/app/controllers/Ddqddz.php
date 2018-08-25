<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include DATAPATH.'raw/message.php';

class Ddqddz extends CI_Controller {
    public function __construct() {
        parent::__construct();

        $this->load->library('weixin');
    }

    public function index() {
        if ($this->weixin->checkSignature()) {
            return;
        }
    }

    public function test($index = '01') {
        if ($this->weixin->checkSignature()) {
            return;
        }

        switch($index) {
        case '01':
            $this->weixin->message->loadMessage(AAA::$xml01);
            log_message('info', 'Content: '.$this->weixin->message->getContent());
            log_message('info', 'MsgId: '.$this->weixin->message->getMsgId());
            $this->weixin->sendResponse('Contratulations!');
            break;
        case '02':
            $this->weixin->message->loadMessage(AAA::$xml02);
            log_message('info', 'PicUrl: '.$this->weixin->message->getPicUrl());
            log_message('info', 'MediaId: '.$this->weixin->message->getMediaId());
            log_message('info', 'MsgId: '.$this->weixin->message->getMsgId());
            $this->weixin->sendResponse('Image Received');
            break;
        case '03':
            $msg->loadMessage(AAA::$xml03);
            log_message('info', 'Format: '.$msg->getFormat());
            log_message('info', 'MediaId: '.$msg->getMediaId());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            $msg->setResponseMessage(array('content' => 'Voice Received'));
            break;
        case '05':
            $msg->loadMessage(AAA::$xml05);
            log_message('info', 'ThumbMediaId: '.$msg->getThumbMediaId());
            log_message('info', 'MediaId: '.$msg->getMediaId());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            $msg->setResponseMessage(array('content' => 'Video Received'));
            break;
        case '06':
            $msg->loadMessage(AAA::$xml06);
            log_message('info', 'ThumbMediaId: '.$msg->getThumbMediaId());
            log_message('info', 'MediaId: '.$msg->getMediaId());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            $msg->setResponseMessage(array('content' => 'Shortvideo Received'));
            break;
        case '07':
            $msg->loadMessage(AAA::$xml07);
            log_message('info', 'Location_X: '.$msg->getLocationX());
            log_message('info', 'Location_Y: '.$msg->getLocationY());
            log_message('info', 'Scale: '.$msg->getScale());
            log_message('info', 'Label: '.$msg->getLabel());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            $msg->setResponseMessage(array('content' => 'Location Received'));
            break;
        case '08':
            $msg->loadMessage(AAA::$xml08);
            log_message('info', 'Title: '.$msg->getTitle());
            log_message('info', 'Description: '.$msg->getDescription());
            log_message('info', 'Url: '.$msg->getUrl());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            $msg->setResponseMessage(array('content' => 'Link Received'));
            break;
        case '11':
            $msg->loadMessage(AAA::$xml11);
            break;
        case '12':
            $msg->loadMessage(AAA::$xml12);
            log_message('info', 'Unsubscribed userName: '.$msg->getFromUserName());
            $msg->setResponseMessage();
            break;
        case '13':
            $msg->loadMessage(AAA::$xml13);
            $msg->setResponseMessage();
            break;
        case '14':
            $msg->loadMessage(AAA::$xml14);
            $msg->setResponseMessage();
            break;
        case '15':
            $msg->loadMessage(AAA::$xml15);
            $msg->setResponseMessage();
            break;
        case '16':
            $msg->loadMessage(AAA::$xml16);
            $msg->setResponseMessage(array('content' => $msg->getEventKey()));
            break;
        case '17':
            $msg->loadMessage(AAA::$xml17);
            $msg->setResponseMessage(array('content' => $msg->getEventKey()));
            break;
        default:
            echo "Unsupported xml index: {$index}";
            return;
            break;
        }
    }
}