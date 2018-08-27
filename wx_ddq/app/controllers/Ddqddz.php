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
                    $this->weixin->responseSuccess();
                    $response = '菜单已更新';
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

            $this->weixin->message->setResponseMsgType(WeixinMessage::MSGTYPE_TEXT);
            /**
            * $queryLogData = array(
            *     'time'      => 'xxxx-xx-xx xx:xx:xx',
            *     'queryType' => 'landmark',
            *     'dest'      => 'dar es salaam',
            *     'shipOwner' => array('msk', 'saf'),
            *     'container' => array('20g', '40g')
            * );
            */
            $queryLogData = $this->getQueryType($this->weixin->message->getFromUserName());
            if (is_null($queryLogData)) {
                $response = '请先选择查询类型';
                break;
            }
            $queryType = $queryLogData['queryType'];
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
                // parse $content
                // $response = $this->exceldata->Quote($content);
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
        unset($queryLogData[$userName]);
        $queryLogData[$userName] = array(
            'time'      => date('Y-m-d H:i:s'),
            'queryType' => $data['queryType'],
        );
        // if ($data['queryType'] = Exceldata::QUERY_LANDMARK) {
        //     $queryLogData[$userName] = array(
        //         'dest'      => isset($data['dest']) ? $data['dest'] : NULL,
        //         'shipOwner' => isset($data['shipOwner']) ? $data['shipOwner'] : NULL,
        //         'container' => isset($data['container']) ? $data['container'] : NULL
        //     );
        // }
        write_file(self::$queryLog, json_encode($queryLogData));
        return TRUE;
    }
    
    private function saveQuoteCondition($userName, $data) {
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
        if (! array_key_exists($userName, $queryLogData) OR $queryLogData['queryType'] != Exceldata::QUERY_QUOTATION) {
            return FALSE;
        }
        $userData = $queryLogData[$username];
        unset($queryLogData[$userName]);
        
        switch (TRUE) {
        case array_key_exists('dest', $data):
            $userData['dest'] = $data['dest'];
            break;
        case array_key_exists('shipOwner', $data):
            $userData['shipOwner'] = $data['shipOwner'];
            break;
        case array_key_exists('container', $data):
            $userData['container'] = $data['container'];
            break;
        }
        $userData['time'] = date('Y-m-d H:i:s');
        $queryLogData[$userName] = $userData;
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
