<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include DATAPATH.'message.php';

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
        $msg = new WeixinMessage(FALSE);
        $msg->loadMessage(AAA::$xml16);
        $msg->setResponseMsgType(WeixinMessage::MSGTYPE_TEXT);
        $msg->setResponseMessage(array('content' => 'Contratulations!'));
        $msg->sendResponse();
    }
}

class WeixinMessage {
    const MSGTYPE_EVENT      = 'event';
    const MSGTYPE_IMAGE      = 'image';
    const MSGTYPE_LINK       = 'link';
    const MSGTYPE_LOCATION   = 'location';
    const MSGTYPE_MUSIC      = 'music';
    const MSGTYPE_NEWS       = 'news';
    const MSGTYPE_SHORTVIDEO = 'shortvideo';
    const MSGTYPE_TEXT       = 'text';
    const MSGTYPE_VIDEO      = 'video';
    const MSGTYPE_VOICE      = 'voice';

    const EVENT_CLICK       = 'click';
    const EVENT_LOCATION    = 'location';
    const EVENT_SCAN        = 'scan';
    const EVENT_SUBSCRIBE   = 'subscribe';
    const EVENT_UNSUBSCRIBE = 'unsubscribe';
    const EVENT_VIEW        = 'view';

    private $toUserName;
    private $fromUserName;
    private $createTime;
    private $msgType;
    private $message         = array();
    private $responseMessage = array();

    //目前只支持文字回复
    private static $responseMsgTypes = array(
      /*self::MSGTYPE_IMAGE,
        self::MSGTYPE_MUSIC,
        self::MSGTYPE_NEWS,
        self::MSGTYPE_VIDEO,
        self::MSGTYPE_VOICE,*/
        self::MSGTYPE_TEXT
    );
    private $responseMsgType = self::MSGTYPE_TEXT;

    public function __construct($loadMessage = TRUE) {
        if ($loadMessage) {
            $this->loadMessage();
        }
    }
   
    public function loadMessage($xml = NULL) {
        libxml_disable_entity_loader(TRUE);
        if (is_null($xml)) {
            $xml = file_get_contents('php://input');
        }

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $this->toUserName   = $dom->getElementsByTagName('ToUserName')->item(0)->nodeValue;
        $this->fromUserName = $dom->getElementsByTagName('FromUserName')->item(0)->nodeValue;
        $this->createTime   = $dom->getElementsByTagName('CreateTime')->item(0)->nodeValue;
        $this->msgType      = $dom->getElementsByTagName('MsgType')->item(0)->nodeValue;

        switch ($this->msgType) {
        case self::MSGTYPE_TEXT:
            log_message('info', 'Message type: '.self::MSGTYPE_TEXT.' received');
            $this->message = array(
                'content' => $dom->getElementsByTagName('Content')->item(0)->nodeValue,
                'msgId'   => $dom->getElementsByTagName('MsgId')->item(0)->nodeValue
            );
            break;
        case self::MSGTYPE_EVENT:
            log_message('info', 'Message type: '.self::MSGTYPE_EVENT.' received');
            $this->message['event'] = $dom->getElementsByTagName('Event')->item(0)->nodeValue;
            switch ($this->message['event']) {
            case self::EVENT_SUBSCRIBE:
                log_message('info', 'Currently unsupported Event: '.$this->message['event'].' received');
                break;
            case self::EVENT_CLICK:
                $this->message['eventKey'] = $dom->getElementsByTagName('EventKey')->item(0)->nodeValue;
                log_message('info', 'Currently unsupported Event: '.$this->message['event'].' received');
                break;
            default:
                log_message('info', 'Currently unsupported Event: '.$this->message['event'].' received');
            }
            break;
        default:
            log_message('info', 'Currently unsupported message type: '.$this->msgType.' received');
        }

        return $this;
    }

    public function setResponseMsgType($msgType = self::MSGTYPE_TEXT) {
        if (! in_array($msgType, self::$responseMsgTypes)) return FALSE;

        $this->responseMsgType = $msgType;
        return TRUE;
    }

    public function setResponseMessage($message = array()) {
        switch ($this->responseMsgType) {
        case self::MSGTYPE_TEXT:
            if (is_array($message) && isset($message['content'])) {
                $this->responseMessage = $message;
                return TRUE;
            } else {
                log_message('error', 'Data in $message does not match responseMsgType!');
                return FALSE;
            }
        case self::MSGTYPE_IMAGE:
        case self::MSGTYPE_VOICE:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
            return FALSE;
        }
    }
    
    public function sendResponse($message = array()) {
        if (! is_array($message)) {
            log_message('error', 'Invalid response Message!');
            echo 'success';
            return;
        }
        switch ($this->msgType) {
        case self::MSGTYPE_TEXT:
            break;
        case self::MSGTYPE_EVENT:
        default:
            echo 'success';
            return;
        }
        
        $dom = new DOMDocument();
        $e   = $dom->createElement('xml');
        $e->appendChild($dom->createElement('ToUserName'))
          ->appendChild($dom->createCDATASection($this->fromUserName));
        $e->appendChild($dom->createElement('FromUserName'))
          ->appendChild($dom->createCDATASection($this->toUserName));
        $e->appendChild($dom->createElement('CreateTime'))
          ->appendChild($dom->createTextNode($this->createTime));
        
        switch ($this->responseMsgType) {
        case self::MSGTYPE_TEXT:
            $e->appendChild($dom->createElement('MsgType'))
              ->appendChild($dom->createCDATASection(self::MSGTYPE_TEXT));
            $e->appendChild($dom->createElement('Content'))
              ->appendChild($dom->createCDATASection($this->responseMessage['content']));
            break;
        case self::MSGTYPE_IMAGE:
        case self::MSGTYPE_VOICE:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
            echo 'success';
            return;
        }
        $dom->appendChild($e);
        echo $dom->saveXML();
        return;
    }
   
    public function getMsgType() {
        return $this->msgType;
    }
   
    public function getToUserName() {
        return $this->toUserName;
    }
   
    public function getFromUserName() {
        return $this->fromUserName;
    }
       
    public function getCreateTime() {
        return $this->createTime;
    }
    
    public function getTEXTContent() {
        if ($this->getMsgType() == self::MSGTYPE_TEXT) {
            return $this->message['content'];
        } else {
            log_message('info', 'Cannot call WeixinMessage::getTEXTContent() without TEXT message!');
            return FALSE;
        }
    }
}
