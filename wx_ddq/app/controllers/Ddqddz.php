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

            $this->weixin->message->setResponseMsgType(WeixinMessage::MSGTYPE_TEXT);
            $queryType = $this->weixin->getQueryType($this->weixin->message->getFromUserName());
            $content   = $this->weixin->message->getContent(); 
            $this->load->library('exceldata');
            if (! $this->exceldata->canUse()) {
                $this->weixin->sendResponse('查询功能当前不可用');
                return;
            }
            $response  = '';
            switch ($queryType) {
            case Exceldata::QUERY_LANDMARK:
                $response = $this->exceldata->Landmark($content);
                break;
            case Exceldata::QUERY_CUSTOMER:
                $response = $this->exceldata->Customer($content);
                break;
            case Exceldata::QUERY_CONTACT:
                $response = $this->exceldata->Contact($content);
                break;
            case Exceldata::QUERY_STAFF:
                $response = $this->exceldata->Staff($content);
                break;
            case Exceldata::QUERY_QUOTATION:
                // parse $content
                //$response = $this->exceldata->Quote($content);
                $response = 'Not implemented';
                break;
            default:
                $response = '请先选择查询类型';
                break;
            }

            $this->weixin->sendResponse($response);
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
            $this->weixin->message->loadMessage(AAA::$xml11);
            if ($this->weixin->message->getMsgType() == WeixinMessage::MSGTYPE_EVENT &&
                $this->weixin->message->getEvent() == WeixinMessage::EVENT_SUBSCRIBE) {
                $this->weixin->sendResponse($this->weixin->message->getFromUserName().', Thank you for following......');
            } else {
                $this->weixin->sendResponse('Not subscribe message');
            }
            break;
        case '12':
            $this->weixin->message->loadMessage(AAA::$xml12);
            if ($this->weixin->message->getMsgType() == WeixinMessage::MSGTYPE_EVENT &&
                $this->weixin->message->getEvent() == WeixinMessage::EVENT_UNSUBSCRIBE) {
                log_message('info', "User: {$this->weixin->message->getFromUserName()} unsubscribed");
                $this->weixin->sendResponse();
            }
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
            $this->weixin->message->loadMessage(AAA::$xml16);
            if ($this->weixin->message->getMsgType() == WeixinMessage::MSGTYPE_EVENT &&
                $this->weixin->message->getEvent() == WeixinMessage::EVENT_CLICK) {
                $this->load->library('weixin/weixinMenu');
                switch ($this->weixin->message->getEventKey()) {
                case WeixinMenu::MENU_LANDMARK: 
                    $this->weixin->saveQueryType($this->weixin->message->getFromUserName(), WeixinMenu::MENU_LANDMARK);
                    $this->weixin->sendResponse('请回复地标...');
                    break;
                case WeixinMenu::MENU_QUOTATION:
                    $this->weixin->saveQueryType($this->weixin->message->getFromUserName(), WeixinMenu::MENU_QUOTATION);
                    $this->weixin->sendResponse('请回复目的港、船东和箱型...');
                    break;
                case WeixinMenu::MENU_CUSTOMER:
                    $this->weixin->saveQueryType($this->weixin->message->getFromUserName(), WeixinMenu::MENU_CUSTOMER);
                    $this->weixin->sendResponse('请回复公司抬头...');
                    break;
                case WeixinMenu::MENU_CONTACT:
                    $this->weixin->saveQueryType($this->weixin->message->getFromUserName(), WeixinMenu::MENU_CONTACT);
                    $this->weixin->sendResponse('请回复客户姓名...');
                    break;
                case WeixinMenu::MENU_STAFF:
                    $this->weixin->saveQueryType($this->weixin->message->getFromUserName(), WeixinMenu::MENU_STAFF);
                    $this->weixin->sendResponse('请回复同事姓名...');
                    break;
                default:
                    $this->weixin->sendResponse('该功能已停用');
                    break;
                }
            }
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
