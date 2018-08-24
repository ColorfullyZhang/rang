<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include DATAPATH.'raw/message.php';

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
            log_message('info', '>>> '.__METHOD__.'() logs: Check signature successful');
            echo $this->input->get('echostr');
        } else {
            log_message('info', '>>> '.__METHOD__.'() logs: Check signature failed.');
        }
        exit;
    }

    public function index($index = '01') {
        $msg = new WeixinMessage(FALSE);
        switch($index) {
        case '01':
            $msg->loadMessage(AAA::$xml01);
            $msg->setResponseMessage(array('content' => 'Contratulations!'));
            log_message('info', 'Content: '.$msg->getContent());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            break;
        case '02':
            $msg->loadMessage(AAA::$xml02);
            $msg->setResponseMessage(array('content' => 'Image Received'));
            log_message('info', 'PicUrl: '.$msg->getPicUrl());
            log_message('info', 'MediaId: '.$msg->getMediaId());
            log_message('info', 'MsgId: '.$msg->getMsgId());
            break;
        case '11':
            $msg->loadMessage(AAA::$xml11);
            break;
        case '16':
            $msg->loadMessage(AAA::$xml16);
            $msg->setResponseMessage(array('content' => $msg->getEventKey()));
            break;
        default:
            echo "Unsupported xml index: {$index}";
            return;
            break;
        }
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
    private $msgType         = NULL;
    private $message         = array();
    private $responseMessage = array();

    private static $responseMsgTypes = array(
        self::MSGTYPE_IMAGE,
        self::MSGTYPE_MUSIC,
        self::MSGTYPE_NEWS,
        self::MSGTYPE_VIDEO,
        self::MSGTYPE_VOICE,
        self::MSGTYPE_TEXT
    );
    private $responseMsgType = self::MSGTYPE_TEXT;

    public function __construct($loadMessage = TRUE) {
        if ($loadMessage) {
            $this->loadMessage();
        }
    }

    private function domValue(&$dom, $key) {
        return $dom->getElementsByTagName($key)->item(0)->nodeValue;
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
        $msgType            = $dom->getElementsByTagName('MsgType')->item(0)->nodeValue;

        switch ($msgType) {
        case self::MSGTYPE_TEXT:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_TEXT.' received');
            $this->message = array(
                'content' => $dom->getElementsByTagName('Content')->item(0)->nodeValue,
                'msgId'   => $dom->getElementsByTagName('MsgId')->item(0)->nodeValue
            );
            break;
        case self::MSGTYPE_IMAGE:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_IMAGE.' received');
            $this->message = array(
                'picUrl'  => $this->domValue($dom, 'PicUrl'),
                'mediaId' => $this->domValue($dom, 'MediaId'),
                'msgId'   => $this->domValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_EVENT:
            $this->message['event'] = $dom->getElementsByTagName('Event')->item(0)->nodeValue;
            switch ($this->message['event']) {
            case self::EVENT_SUBSCRIBE:
                log_message('info', '>>> '.__METHOD__."() logs: Event: {$this->message['event']} received");
                break;
            case self::EVENT_CLICK:
                $this->message['eventKey'] = $dom->getElementsByTagName('EventKey')->item(0)->nodeValue;
                log_message('info', '>>> '.__METHOD__."() logs: Event: {$this->message['event']} received");
                break;
            default:
                log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported Event: {$this->message['event']} received");
            }
            break;
        case self::MSGTYPE_LINK:
        case self::MSGTYPE_LOCATION:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
        case self::MSGTYPE_SHORTVIDEO:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_VOICE:
            log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported message type: {$msgType} received");
            break;
        default:
            $msgType = NULL;
            log_message('info', '>>> '.__METHOD__."() logs: Invalid message type: {$msgType} received");
        }
        $this->msgType = $msgType;

        return $this;
    }

    public function setResponseMsgType($msgType = self::MSGTYPE_TEXT) {
        if (in_array($msgType, self::$responseMsgTypes)) {
            $this->responseMsgType = $msgType;
            return TRUE;
        } else {
            log_message('error', '>>> '.__METHOD__."() logs: Invalid responseMsgType: {$msgType}!");
            return FALSE;
        }
    }

    public function setResponseMessage($message = array()) {
        if (! is_array($message)) {
            log_message('error', '>>> '.__METHOD__.'() logs: Invalid parameter type: '.gettype($message).' received!');
            return FALSE;
        }
        if (isset($message['msgType'])) {
            if (! $this->setResponseMsgType($message['msgType'])) {
                return FALSE;
            }
            unset($message['msgType']);
        }
        switch ($this->responseMsgType) {
        case self::MSGTYPE_TEXT:
            if (isset($message['content'])) {
                $this->responseMessage = $message;
            }
            break;
        case self::MSGTYPE_IMAGE:
            break;
        case self::MSGTYPE_VOICE:
            break;
        case self::MSGTYPE_VIDEO:
            break;
        case self::MSGTYPE_MUSIC:
            break;
        case self::MSGTYPE_NEWS:
            break;
        }
        return TRUE;
    }
    
    public function sendResponse($message = array()) {
        if (! $this->setResponseMessage($message)) {
            echo 'success';
            return;
        }

        // 收到哪些信息现在还不能回复，支持一个可删除一个
        switch ($this->msgType) {
        case self::MSGTYPE_EVENT:
            switch ($this->message['event']) {
            case self::EVENT_LOCATION:
            case self::EVENT_SCAN:
            case self::EVENT_UNSUBSCRIBE:
            case self::EVENT_VIEW:
                log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported Event: {$this->message['event']}");
                echo 'success';
                return;
            }
            break;
        case self::MSGTYPE_LINK:
        case self::MSGTYPE_LOCATION:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
        case self::MSGTYPE_SHORTVIDEO:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_VOICE:
            log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported message type: {$this->msgType}");
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
            if ($this->msgType == self::MSGTYPE_EVENT && $this->message['event'] == self::EVENT_SUBSCRIBE) {
                $this->setResponseMessage(array('content' => 'Thank you for your follow!'));
            } else if (! isset($this->responseMessage['content'])) {
                log_message('info', '>>> '.__METHOD__.'() logs: "content" must be set before response text message');
                echo 'success';
                return;
            }
            $e->appendChild($dom->createElement('MsgType'))
              ->appendChild($dom->createCDATASection($this->responseMsgType));
            $e->appendChild($dom->createElement('Content'))
              ->appendChild($dom->createCDATASection($this->responseMessage['content']));
            break;
        default:
            log_message('info', '>>> '.__METHOD__.'() logs: responseMsgType: '.$this->responseMsgType.' is not supported at the moment');
            echo 'success';
            return;
        }
        $dom->appendChild($e);
        echo $dom->saveXML();
        log_message('info', '>>> '.__METHOD__.'() logs: '.$this->responseMsgType.' message responsed');
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
    
    public function getContent() {
        if ($this->getMsgType() != self::MSGTYPE_TEXT) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is '.$this->getMsgType());
            return FALSE;
        }
        return $this->message['content'];
    }

    public function getEvent() {
        if ($this->getMsgType() != self::MSGTYPE_EVENT) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is not '.self::MSGTYPE_EVENT);
            return FALSE;
        }
        return $this->message['event'];
    }

    public function getEventKey() {
        $validEvents = array(self::EVENT_SUBSCRIBE, self::EVENT_SCAN, self::EVENT_CLICK, self::EVENT_VIEW);
        if (! in_array($this->getEvent(), $validEvents)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is '.$this->getMsgType());
            return FALSE;
        }
        return $this->message['eventKey'];
    }

    public function getMsgId() {
        $validMsgTypes= array( self::MSGTYPE_TEXT, self::MSGTYPE_IMAGE, self::MSGTYPE_VOICE,
            self::MSGTYPE_VIDEO, self::MSGTYPE_SHORTVIDEO, self::MSGTYPE_LOCATION, self::MSGTYPE_LINK);
        if (! in_array($this->getMsgType(), $validMsgTypes)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is '.$this->getMsgType());
            return FALSE;
        }
        return $this->message['msgId'];
    }

    public function getPicUrl() {
        if ($this->getMsgType() != self::MSGTYPE_IMAGE) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_IMAGE);
            return FALSE;
        }
        return $this->message['PicUrl'];
    }

    public function getMediaId() {
        $validMsgTypes= array(self::MSGTYPE_IMAGE, self::MSGTYPE_VOICE,
            self::MSGTYPE_VIDEO, self::MSGTYPE_SHORTVIDEO);
        if (! in_array($this->getMsgType(), $validMsgTypes)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is '.$this->getMsgType());
            return FALSE;
        }
        return $this->message['mediaId'];
    }

    public function getFormat() {
        if ($this->getMsgType() != self::MSGTYPE_VOICE) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_VOICE);
            return FALSE;
        }
        return $this->message['format'];
    }

    public function getRecognition() {
        log_message('error', 'Not implemented method: '.__METHOD__.'()');
        return FALSE;
    }

    public function getThumbMediaId() {
        $validMsgTypes= array(self::MSGTYPE_VIDEO, self::MSGTYPE_SHORTVIDEO);
        if (! in_array($this->getMsgType(), $validMsgTypes)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is '.$this->getMsgType());
            return FALSE;
        }
        return $this->message['thumbMediaId'];
    }

    public function getLocationX() {
        if ($this->getMsgType() != self::MSGTYPE_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LOCATION);
            return FALSE;
        }
        return $this->message['location_x'];
    }

    public function getLocationY() {
        if ($this->getMsgType() != self::MSGTYPE_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LOCATION);
            return FALSE;
        }
        return $this->message['location_y'];
    }

    public function getScale() {
        if ($this->getMsgType() != self::MSGTYPE_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LOCATION);
            return FALSE;
        }
        return $this->message['scale'];
    }

    public function getLabel() {
        if ($this->getMsgType() != self::MSGTYPE_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LOCATION);
            return FALSE;
        }
        return $this->message['label'];
    }

    public function getDescription() {
        if ($this->getMsgType() != self::MSGTYPE_LINK) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LINK);
            return FALSE;
        }
        return $this->message['description'];
    }

    public function getTitle() {
        if ($this->getMsgType() != self::MSGTYPE_LINK) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LINK);
            return FALSE;
        }
        return $this->message['title'];
    }

    public function getUrl() {
        if ($this->getMsgType() != self::MSGTYPE_LINK) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LINK);
            return FALSE;
        }
        return $this->message['url'];
    }

    public function getTicket() {
        $validEvents = array(self::EVENT_SUBSCRIBE, self::EVENT_SCAN);
        if ($this->getEvent() != self:MSGTYPE_EVENT && ! in_array($this->getEvent(), $validEvents)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method cannot be called when message type is '.$this->getMsgType());
            return FALSE;
        }
        return $this->message['thumbMediaId'];
    }

    public function getLatitude() {
        log_message('error', 'Not implemented method: '.__METHOD__.'()');
        return FALSE;
    }

    public function getLongitude() {
        log_message('error', 'Not implemented method: '.__METHOD__.'()');
        return FALSE;
    }
}
