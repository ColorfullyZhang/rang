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

        $response = '';
        switch ($this->weixin->message->getMsgType()) {
        case WeixinMessage::MSGTYPE_EVENT:
            switch ($this->weixin->message->getEvent()) {
            case WeixinMessage::EVENT_CLICK:
                $this->load->library('exceldata');
                switch ($this->weixin->message->getEventKey()) {
                case Exceldata::QUERY_LANDMARK:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => Exceldata::QUERY_LANDMARK));
                    $response = '请回复地标...';
                    break;
                case Exceldata::QUERY_CUSTOMER:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => Exceldata::QUERY_CUSTOMER));
                    $response = '请回复公司抬头...';
                    break;
                case Exceldata::QUERY_CONTACT:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => Exceldata::QUERY_CONTACT));
                    $response = '请回复客户姓名...';
                    break;
                case Exceldata::QUERY_STAFF:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => Exceldata::QUERY_STAFF));
                    $response = '请回复同事姓名...';
                    break;
                case Exceldata::QUERY_QUOTATION:
                    $this->saveQueryType($this->weixin->message->getFromUserName(),
                                         array('queryType' => Exceldata::QUERY_QUOTATION));
                    $response = '请回复目的港、船东和箱型...';
                    break;
                default:
                    log_message('info', '>>> '.__METHOD__.'() logs: Unsupport EventKey: '.$this->weixin->message->getEventKey().' received');
                    $this->weixin->responseSuccess(TRUE);
                    break;
                }
                break;
            case WeixinMessage::EVENT_LOCATION:
                log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_EVENT.':'.WeixinMessage::EVENT_LOCATION.' received');
                $this->weixin->responseSuccess(TRUE);
                break;
            case WeixinMessage::EVENT_SCAN:
                log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_EVENT.':'.WeixinMessage::EVENT_SCAN.' received');
                $this->weixin->responseSuccess(TRUE);
                break;
            case WeixinMessage::EVENT_SUBSCRIBE:
                $response = 'Welcome to ddqddz';
                break;
            case WeixinMessage::EVENT_UNSUBSCRIBE:
                $this->weixin->responseSuccess(TRUE);
                break;
            case WeixinMessage::EVENT_VIEW:
                log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_EVENT.':'.WeixinMessage::EVENT_VIEW.' received');
                $this->weixin->responseSuccess(TRUE);
                break;
            default:
                log_message('info', '>>> '.__METHOD__.'() logs: unknown event:'.$this->weixin->message->getEvent().' received');
                $this->weixin->responseSuccess(TRUE);
                break;
            }
            break;
        case WeixinMessage::MSGTYPE_IMAGE:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_IMAGE.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_LINK:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_LINK.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_LOCATION:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_LOCATION.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_MUSIC:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_MUSIC.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_NEWS:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_NEWS.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_SHORTVIDEO:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_SHORTVIDEO.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_TEXT:
            log_message('info', 'Content: '.$this->weixin->message->getContent());
            log_message('info', 'MsgId: '.$this->weixin->message->getMsgId());

            $userData = $this->getQueryType($this->weixin->message->getFromUserName());
            if (is_null($userData)) {
                $response = '请先选择查询类型';
                break;
            }
            $queryType = $userData['queryType'];
            $content   = $this->weixin->message->getContent();
            $this->load->library('exceldata');
            if (! $this->exceldata->canUse()) {
                $response = '查询功能暂不可用';
                break;
            }

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
                $condition = $this->exceldata->parseQueryString($content);
                if (count($condition) > 0) {
                    if (array_key_exists('dest', $condition)) {
                        $userData['dest'] = $condition['dest'];
                        $userData['shipOwner'] = NULL;
                        $userData['container'] = NULL;
                    }
                    if (array_key_exists('shipOwner', $condition)) {
                        $userData['shipOwner'] = $condition['shipOwner'];
                    }
                    if (array_key_exists('container', $condition)) {
                        $userData['container'] = $condition['container'];
                    }
                    $this->saveQueryType($this->weixin->message->getFromUserName(), $userData);
                }

                unset($condition['time']);
                unset($condition['queryType']);
                $result = $this->exceldata->Quote($condition);

                if (gettype($result) == 'string') {
                    $response = $result;
                } else if (gettype($result) == 'array') {
                    $response = $this->parser->parse('quote', $result, TRUE);
                } else {
                    log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:quote()');
                    $response = '查询功能暂不可用';
                }
                break;
            default:
                $response = '未知查询类型';
                break;
            }
            break;
        case WeixinMessage::MSGTYPE_VIDEO:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_VIDEO.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        case WeixinMessage::MSGTYPE_VOICE:
            log_message('info', '>>> '.__METHOD__.'() logs: '.WeixinMessage::MSGTYPE_VOICE.' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        default:
            log_message('info', '>>> '.__METHOD__.'() logs: unknown MsgType:'.$this->weixin->message->getMsgType().' received');
            $this->weixin->responseSuccess(TRUE);
            break;
        }

        $this->weixin->sendResponse($response);
    }

    private function saveQueryType($userName, $data) {
        $this->load->library('exceldata');
        if (! array_key_exists('queryType', $data)) {
            log_message('info', '>>> '.__METHOD__.'() logs: queryType required');
            return FALSE;
        }
        if (! in_array($data['queryType'], Exceldata::$queryTypes)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Invalid queryType:'.$data['queryType']);
            return FALSE;
        }
        $this->load->helper('file');
        if (! file_exists(self::$queryLog) ) {
            touch(self::$queryLog);
        }
        if (($queryLogData = json_decode(file_get_contents(self::$queryLog), TRUE)) === NULL) {
            $queryLogData = array();
        }
        unset($queryLogData[$userName]);
        $data = array_merge(array('time' => date('Y-m-d H:i:s')), $data);
        $queryLogData[$userName] = $data;
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
