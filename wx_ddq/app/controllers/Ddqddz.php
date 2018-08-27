<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include DATAPATH.'raw/message.php';

class Ddqddz extends CI_Controller {
    private static $queryLog = DATAPATH.'runtime/querylog.txt';

    public function __construct() {
        parent::__construct();

        $this->load->library('weixin');
    }

    public function index($index) {
        if ($this->weixin->checkSignature()) {
            return;
        }
        
        $xml = 'xml'.$index;
        $this->weixin->message->loadMessage(AAA::$$xml);
        switch ($this->weixin->message->getMsgType()) {
        case WeixinMessage::MSGTYPE_EVENT:
            switch ($this->weixin->message->getEvent()) {
                case WeixinMessage::EVENT_CLICK:
                    $this->load->library('exceldata');
                    switch ($this->weixin->message->getEventKey()) {
                    case Exceldata::QUERY_LANDMARK: 
                        $this->saveQueryType($this->weixin->message->getFromUserName(),
                                             array('queryType' => Exceldata::QUERY_LANDMARK));
                        $this->weixin->sendResponse('请回复地标...');
                        break;
                    case Exceldata::QUERY_CUSTOMER:
                        $this->saveQueryType($this->weixin->message->getFromUserName(),
                                             array('queryType' => Exceldata::QUERY_CUSTOMER));
                        $this->weixin->sendResponse('请回复目的港、船东和箱型...');
                        break;
                    case Exceldata::QUERY_CONTACT:
                        $this->saveQueryType($this->weixin->message->getFromUserName(),
                                             array('queryType' => Exceldata::QUERY_CONTACT));
                        $this->weixin->sendResponse('请回复公司抬头...');
                        break;
                    case Exceldata::QUERY_STAFF:
                        $this->saveQueryType($this->weixin->message->getFromUserName(),
                                             array('queryType' => Exceldata::QUERY_STAFF));
                        $this->weixin->sendResponse('请回复客户姓名...');
                        break;
                    case Exceldata::QUERY_QUOTATION:
                        /*
                            * $queryLogData[$userName] = array(
                            *     'time'      => 'xxxx-xx-xx xx:xx:xx',
                            *     'queryType' => 'landmark',
                            *     'dest'      => 'dar es salaam',
                            *     'shipOwner' => array('msk', 'saf'),
                            *     'container' => array('20g', '40g')
                            * );
                            */
                        $this->saveQueryType($this->weixin->message->getFromUserName(),
                                             array('queryType' => Exceldata::QUERY_QUOTATION));
                        $this->weixin->sendResponse('请回复同事姓名...');
                        break;
                    default:
                        $this->weixin->sendResponse('该功能已停用');
                        break;
                    }
                    break;
                case WeixinMessage::EVENT_LOCATION:
                    break;
                case WeixinMessage::EVENT_SCAN:
                    break;
                case WeixinMessage::EVENT_SUBSCRIBE:
                    break;
                case WeixinMessage::EVENT_UNSUBSCRIBE:
                    break;
                case WeixinMessage::EVENT_VIEW:
                    break;
                default:
                    break;
            }
            break;
        case WeixinMessage::MSGTYPE_IMAGE:
            break;
        case WeixinMessage::MSGTYPE_LINK:
            break;
        case WeixinMessage::MSGTYPE_LOCATION:
            break;
        case WeixinMessage::MSGTYPE_MUSIC:
            break;
        case WeixinMessage::MSGTYPE_NEWS:
            break;
        case WeixinMessage::MSGTYPE_SHORTVIDEOS:
            break;
        case WeixinMessage::MSGTYPE_TEXT:
            break;
        case WeixinMessage::MSGTYPE_VIDEO:
            break;
        case WeixinMessage::MSGTYPE_VOICE:
            break;
        default:
            break;
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
            $queryType = $this->getQueryType($this->weixin->message->getFromUserName());
            $content   = $this->weixin->message->getContent(); 
            $this->load->library('exceldata');
            if (! $this->exceldata->canUse()) {
                $this->weixin->sendResponse('查询功能暂不可用');
                return;
            }
            $response  = '';
            switch ($queryType) {
            case Exceldata::QUERY_LANDMARK:
                $data = $this->exceldata->landmark($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('landmark', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:landmark()');
                    $response = '查询功能暂不可用';
                }
                break;
            case Exceldata::QUERY_CUSTOMER:
                $data = $this->exceldata->customer($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('customer', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:customer()');
                    $response = '查询功能暂不可用';
                }
                break;
            case Exceldata::QUERY_CONTACT:
                $data = $this->exceldata->contact($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('contact', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:contact()');
                    $response = '查询功能暂不可用';
                }
                break;
            case Exceldata::QUERY_STAFF:
                $data = $this->exceldata->staff($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('staff', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:staff()');
                    $response = '查询功能暂不可用';
                }
                break;
            case Exceldata::QUERY_QUOTATION:
                // parse query $content
                //$response = $this->exceldata->Quote($content);
                if (gettype($data) == 'string') {
                    $response = $data;
                } else if (gettype($data) == 'array') {
                    $response = $this->parser->parse('quote', $data, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:quote()');
                    $response = '查询功能暂不可用';
                }
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
                $this->weixin->sendResponse($this->weixin->message->getFromUserName().', Thank you for following');
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
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => WeixinMenu::MENU_LANDMARK));
                    $this->weixin->sendResponse('请回复地标...');
                    break;
                case WeixinMenu::MENU_QUOTATION:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => WeixinMenu::MENU_QUOTATION));
                    $this->weixin->sendResponse('请回复目的港、船东和箱型...');
                    break;
                case WeixinMenu::MENU_CUSTOMER:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => WeixinMenu::MENU_CUSTOMER));
                    $this->weixin->sendResponse('请回复公司抬头...');
                    break;
                case WeixinMenu::MENU_CONTACT:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => WeixinMenu::MENU_CONTACT));
                    $this->weixin->sendResponse('请回复客户姓名...');
                    break;
                case WeixinMenu::MENU_STAFF:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => WeixinMenu::MENU_STAFF));
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

    
    private function saveQueryType($userName, $data) {
        $this->load->library('exceldata');
        if (! in_array($data['queryType'], Exceldata::$queryTypes)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid query type: {$data['queryType']}");
            return FALSE;
        }
        $this->load->helper('file');
        if (! file_exists(self::$queryLog) ) {
            touch(self::$queryLog);
        }
        if (($queryLogData = json_decode(file_get_contents(self::$queryLog), TRUE)) === NULL) {
            $queryLogData = array();
        }
        $logData = $queryLogData[$userName];
        unset($queryLogData[$userName]);
        $queryLogData[$userName] = array(
            'time'      => date('Y-m-d H:i:s'),
            'queryType' => $data['queryType'],
        );
        if ($data['queryType'] = Exceldata::QUERY_LANDMARK) {
            $queryLogData[$userName] = array(
                'dest'      => isset($data['dest']) ? $data['dest'] : NULL,
                'shipOwner' => isset($data['shipOwner']) ? $data['shipOwner'] : NULL,
                'container' => isset($data['container']) ? $data['container'] : NULL
            );
        }
        write_file(self::$queryLog, json_encode($queryLogData));
        return TRUE;
    }

    private function getQueryType($userName) {
        $this->load->helper('file');
        if (! file_exists(self::$queryLog) ) {
            touch(self::$queryLog);
        }
        if (($queryLogData = json_decode(file_get_contents(self::$queryLog), TRUE)) === NULL) {
            $queryLogData = array();
        }
        return array_key_exists($userName, $queryLogData) ? $queryLogData[$userName] : NULL;
    }
}