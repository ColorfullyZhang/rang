<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Yjc extends CI_Controller {
    public function __construct() {
        parent::__construct();
        //$this->load->driver('cache');
    }

    public function getaccesstoken() {
        !is_cli() && exit('forbidden');
        if (($token = $this->cache->apc->get('access_token')) === FALSE) {
            $this->config->load('weixin');
            $url    = 'https://api.weixin.qq.com/cgi-bin/token?';
            $params = http_build_query(array(
                'grant_type' => 'client_credential',
                'appid'      => $this->config->item('weixin_appID'),
                'secret'     => $this->config->item('weixin_appSecret')
            ));
            /**
            $opts   = array(
                'http' => array(
                    'method'  => 'GET',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $params
                )
            );
            $token = file_get_contents($url, FALSE, stream_context_create($opts));
             */
            $token = json_decode(file_get_contents($url.$params), TRUE)['access_token'];
            $this->cache->apc->set('access_token', $token, 5400);
        }
        log_message('info', '>>> '.__METHOD__.'() logs: access_token: '.substr($token, 0, 20).'...');
        return $token;
    }

    public function uploadmenu() {
        !is_cli() && exit('forbidden');
        exit(__METHOD__.'() not implemented');

        $url  = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getaccesstoken();
        $menu = $this->config->item('weixin_menu');
    }

    private function handleRequest() {
        $response = '';
        switch ($this->weixin->message->getMsgType()) {
        case WeixinMessage::MSGTYPE_EVENT:
            switch ($this->weixin->message->getEvent()) {
            case WeixinMessage::EVENT_SUBSCRIBE:
                $response = '欢迎关注！'.PHP_EOL.PHP_EOL.$this->weixin->getHelp();
                break;
            case WeixinMessage::EVENT_UNSUBSCRIBE:
                $this->weixin->responseSuccess(TRUE);
                break;
            case WeixinMessage::EVENT_CLICK:
                $this->weixin->responseSuccess(TRUE);
                break;
                switch ($this->weixin->message->getEventKey()) {
                case self::QUERY_LANDMARK:
                    //$this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_LANDMARK));
                    //$response = '请回复地标...';
                    break;
                case self::QUERY_CUSTOMER:
                    //$this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CUSTOMER));
                    //$response = '请回复公司抬头...';
                    break;
                case self::QUERY_CONTACT:
                    //$this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_CONTACT));
                    //$response = '请回复客户姓名...';
                    break;
                case self::QUERY_STAFF:
                    //$this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_STAFF));
                    //$response = '请回复同事姓名...';
                    break;
                case self::QUERY_QUOTATION:
                    //$this->saveQueryData($this->weixin->message->getFromUserName(), array('queryType' => self::QUERY_QUOTATION));
                    //$response = '请回复目的港、船东和箱型...';
                    break;
                default:
                    log_message('info', '>>> '.__METHOD__.'() logs: Unsupport EventKey: '.$this->weixin->message->getEventKey().' received');
                    $this->weixin->responseSuccess(TRUE);
                    break;
                }
                break;
            default:
                $this->weixin->responseSuccess(TRUE);
                break;
            }
            break;
        case WeixinMessage::MSGTYPE_TEXT:
            log_message('info', 'Content: '.$this->weixin->message->getContent());

            $content   = $this->weixin->message->getContent();
            $this->load->library('exceldata');
            if (! $this->exceldata->canUse()) {
                $response = '查询功能暂不可用';
                break;
            }
            
            $data = $this->exceldata->search($content);
            if (gettype($data) == 'string') {
                $response = $data;
            } else if (gettype($data) == 'array') {
                $response = $this->parser->parse('view', array('prjs' => $data), TRUE);
            } else {
                log_message('info', '>>> '.__METHOD__.'() logs: Invalid data returned from Exceldata:search()');
                $response = '查询功能暂不可用';
            }
            break;
        default:
            $response = $this->weixin->getHelp();
        }
        return $response;
    }

    public function index_weixin() {
        is_cli() && exit('cli forbidden');

        $this->load->library('weixin');
        if ($this->weixin->checkSignature()) {
            return;
        }

        $this->weixin->message->loadMessage();

        $this->weixin->sendResponse($this->handleRequest());
    }

    public function index_cli($index = '01') {
        ! is_cli() && show_404();

        $this->load->library('weixin');
        $xml = 'xml'.$index;
        include DATAPATH.'raw/message_yjc.php';
        $this->load->library('Weixin/WeixinMessage');
        $this->weixin->message->loadMessage(AAA::$$xml);
        $this->weixin->message->setResponseMsgType(WeixinMessage::MSGTYPE_RAW_TEXT);
        $this->weixin->sendResponse($this->handleRequest());
        echo EOL.round(memory_get_usage()/1048576,2).'M';
    }

    public function test() {
        echo 'test OK';
    }
}
