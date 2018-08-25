<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
        $this->toUserName   = $this->domValue($dom, 'ToUserName');
        $this->fromUserName = $this->domValue($dom, 'FromUserName');
        $this->createTime   = $this->domValue($dom, 'CreateTime');
        $msgType            = $this->domValue($dom, 'MsgType');

        switch ($msgType) {
        case self::MSGTYPE_TEXT:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_TEXT.' received');
            $this->message = array(
                'content' => $this->domValue($dom, 'Content'),
                'msgId'   => $this->domValue($dom, 'MsgId')
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
        case self::MSGTYPE_VOICE:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_VOICE.' received');
            $this->message = array(
                'format'  => $this->domValue($dom, 'Format'),
                'mediaId' => $this->domValue($dom, 'MediaId'),
                'msgId'   => $this->domValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_VIDEO:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_VIDEO.' received');
            $this->message = array(
                'thumbMediaId'  => $this->domValue($dom, 'ThumbMediaId'),
                'mediaId'       => $this->domValue($dom, 'MediaId'),
                'msgId'         => $this->domValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_SHORTVIDEO:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_SHORTVIDEO.' received');
            $this->message = array(
                'thumbMediaId'  => $this->domValue($dom, 'ThumbMediaId'),
                'mediaId'       => $this->domValue($dom, 'MediaId'),
                'msgId'         => $this->domValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_LOCATION:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_LOCATION.' received');
            $this->message = array(
                'locationX' => $this->domValue($dom, 'Location_X'),
                'locationY' => $this->domValue($dom, 'Location_Y'),
                'scale'     => $this->domValue($dom, 'Scale'),
                'label'     => $this->domValue($dom, 'Label'),
                'msgId'     => $this->domValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_LINK:
            log_message('info', '>>> '.__METHOD__.'() logs: Message type: '.self::MSGTYPE_LINK.' received');
            $this->message = array(
                'title'       => $this->domValue($dom, 'Title'),
                'description' => $this->domValue($dom, 'Description'),
                'url'         => $this->domValue($dom, 'Url'),
                'msgId'       => $this->domValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_EVENT:
            $this->message['event'] = strtolower($this->domValue($dom, 'Event'));
            switch ($this->message['event']) {
            case self::EVENT_SUBSCRIBE:
                log_message('info', '>>> '.__METHOD__."() logs: Event: {$this->message['event']} received");
                break;
            case self::EVENT_CLICK:
                $this->message['eventKey'] = $this->domValue($dom, 'EventKey');
                log_message('info', '>>> '.__METHOD__."() logs: Event: {$this->message['event']} received");
                break;
            case self::EVENT_UNSUBSCRIBE:
                log_message('info', '>>> '.__METHOD__."() logs: Event: {$this->message['event']} received");
                break;
            case self::EVENT_VIEW:
                $this->message['eventKey'] = $this->domValue($dom, 'EventKey');
                log_message('info', '>>> '.__METHOD__."() logs: Event: {$this->message['event']} received");
                break;
            default:
                log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported Event: {$this->message['event']} received");
            }
            break;
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
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
        if (is_string($message)) {
            $message = array(
                'msgType' => self::MSGTYPE_TEXT,
                'content' => $message
            );
        }

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

    // 返回要发给微信服务器的信息
    public function getResponse($message) {
        if ($this->msgType == self::MSGTYPE_EVENT && $this->getEvent() == self::EVENT_UNSUBSCRIBE) {
            log_message('info', '>>> '.__METHOD__.'() logs: "success" responsed');
            $this->responseSuccess();
        }
        if (! $this->setResponseMessage($message)) {
            $this->responseSuccess();
        }

        // 收到哪些信息现在还不能回复，支持一个可删除一个
        switch ($this->msgType) {
        case self::MSGTYPE_EVENT:
            switch ($this->getEvent()) {
            case self::EVENT_LOCATION:
            case self::EVENT_SCAN:
                log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported Event: {$this->message['event']}");
                $this->responseSuccess();
            }
            break;
        case self::MSGTYPE_LINK:
        case self::MSGTYPE_LOCATION:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
        case self::MSGTYPE_SHORTVIDEO:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_VOICE:
        case self::MSGTYPE_IMAGE:
            log_message('info', '>>> '.__METHOD__."() logs: Currently unsupported message type: {$this->msgType}");
            $this->responseSuccess();
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
                $this->responseSuccess();
            }
            $e->appendChild($dom->createElement('MsgType'))
              ->appendChild($dom->createCDATASection($this->responseMsgType));
            $e->appendChild($dom->createElement('Content'))
              ->appendChild($dom->createCDATASection($this->responseMessage['content']));
            break;
        default:
            log_message('info', '>>> '.__METHOD__.'() logs: responseMsgType: '.$this->responseMsgType.' is not supported at the moment');
            $this->responseSuccess();
        }
        $dom->appendChild($e);
        log_message('info', '>>> '.__METHOD__.'() logs: '.$this->responseMsgType.' message responsed');
        return $dom->saveXML();
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
        return $this->message['picUrl'];
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
        return $this->message['locationX'];
    }

    public function getLocationY() {
        if ($this->getMsgType() != self::MSGTYPE_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method can only be called when message type is '.self::MSGTYPE_LOCATION);
            return FALSE;
        }
        return $this->message['locationY'];
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
        if ($this->getMsgType() != self::MSGTYPE_EVENT OR ! in_array($this->getEvent(), $validEvents)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method only available within Event:subscribe and Event:scan');
            return FALSE;
        }
        return $this->message['ticket'];
    }

    public function getLatitude() {
        if ($this->getMsgType() != self::MSGTYPE_LINK OR $this->getEvent() != self::EVENT_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method only available with Event:location');
            return FALSE;
        }
        return $this->message['latitude'];
    }

    public function getLongitude() {
        if ($this->getMsgType() != self::MSGTYPE_LINK OR $this->getEvent() != self::EVENT_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method only available with Event:location');
            return FALSE;
        }
        return $this->message['longitude'];
    }

    public function getPrecision() {
        if ($this->getMsgType() != self::MSGTYPE_LINK OR $this->getEvent() != self::EVENT_LOCATION) {
            log_message('info', '>>> '.__METHOD__.'() logs: Method only available with Event:location');
            return FALSE;
        }
        return $this->message['precision'];
    }
    
    // 当收到还不支持的信息时，只返回接收确认给微信服务器，对用户不予理会
    public function responseSuccess() {
        log_message('info', '>>> "success" sent to Weixin server');
        echo 'success';
        exit;
    }
}