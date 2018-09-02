<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class WeixinMessage {
    const MSGTYPE_RAW_TEXT   = 'raw_text';
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

    private static $msgTypes = array(
        self::MSGTYPE_EVENT,
        self::MSGTYPE_IMAGE,
        self::MSGTYPE_LINK,
        self::MSGTYPE_LOCATION,
        self::MSGTYPE_MUSIC,
        self::MSGTYPE_NEWS,
        self::MSGTYPE_SHORTVIDEO,
        self::MSGTYPE_TEXT,
        self::MSGTYPE_VIDEO,
        self::MSGTYPE_VOICE
    );

    const EVENT_CLICK       = 'click';
    const EVENT_LOCATION    = 'location';
    const EVENT_SCAN        = 'scan';
    const EVENT_SUBSCRIBE   = 'subscribe';
    const EVENT_UNSUBSCRIBE = 'unsubscribe';
    const EVENT_VIEW        = 'view';
    
    private static $events = array(
        self::EVENT_CLICK,
        self::EVENT_LOCATION,
        self::EVENT_SCAN,
        self::EVENT_SUBSCRIBE,
        self::EVENT_UNSUBSCRIBE,
        self::EVENT_VIEW
    );

    private $toUserName;
    private $fromUserName;
    private $createTime;
    private $msgType         = NULL;
    private $message         = array();

    private static $responseMsgTypes = array(
        self::MSGTYPE_RAW_TEXT,
        self::MSGTYPE_IMAGE,
        self::MSGTYPE_MUSIC,
        self::MSGTYPE_NEWS,
        self::MSGTYPE_TEXT,
        self::MSGTYPE_VIDEO,
        self::MSGTYPE_VOICE
    );
    private $responseMsgType = self::MSGTYPE_TEXT;
    const RAW_TEXT_SUCCESS   = 'success';
    private $responseMessage = array();

    public function __construct() {
    }

    private function domElementValue(&$dom, $key) {
        return $dom->getElementsByTagName($key)->item(0)->nodeValue;
    }
   
    public function loadMessage($xml = NULL) {
        if ($xml !== NULL && ENVIRONMENT == 'production') {
            log_message('error', '>>> '.__METHOD__.'() logs: $xml cannot be set manually in production');
            $this->responseSuccess(TRUE);
        }
        libxml_disable_entity_loader(TRUE);
        if (is_null($xml)) {
            $CI =& get_instance();
            $xml = $CI->input->raw_input_stream;
        }

        if (strlen($xml) == 0) {
            log_message('error', '>>> '.__METHOD__.'() logs: bad request without xml');
            show_404();
        }

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $this->toUserName   = $this->domElementValue($dom, 'ToUserName');
        $this->fromUserName = $this->domElementValue($dom, 'FromUserName');
        $this->createTime   = $this->domElementValue($dom, 'CreateTime');
        $this->setMsgType($this->domElementValue($dom, 'MsgType'));
        log_message('info', '>>> '.__METHOD__.'() logs: message type: '.$this->msgType.' received');

        switch ($this->msgType) {
        case self::MSGTYPE_TEXT:
            $this->message = array(
                'content' => $this->domElementValue($dom, 'Content'),
                'msgId'   => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_IMAGE:
            $this->message = array(
                'picUrl'  => $this->domElementValue($dom, 'PicUrl'),
                'mediaId' => $this->domElementValue($dom, 'MediaId'),
                'msgId'   => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_VOICE:
            $this->message = array(
                'format'  => $this->domElementValue($dom, 'Format'),
                'mediaId' => $this->domElementValue($dom, 'MediaId'),
                'msgId'   => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_VIDEO:
            $this->message = array(
                'thumbMediaId'  => $this->domElementValue($dom, 'ThumbMediaId'),
                'mediaId'       => $this->domElementValue($dom, 'MediaId'),
                'msgId'         => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_SHORTVIDEO:
            $this->message = array(
                'thumbMediaId'  => $this->domElementValue($dom, 'ThumbMediaId'),
                'mediaId'       => $this->domElementValue($dom, 'MediaId'),
                'msgId'         => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_LOCATION:
            $this->message = array(
                'locationX' => $this->domElementValue($dom, 'Location_X'),
                'locationY' => $this->domElementValue($dom, 'Location_Y'),
                'scale'     => $this->domElementValue($dom, 'Scale'),
                'label'     => $this->domElementValue($dom, 'Label'),
                'msgId'     => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_LINK:
            $this->message = array(
                'title'       => $this->domElementValue($dom, 'Title'),
                'description' => $this->domElementValue($dom, 'Description'),
                'url'         => $this->domElementValue($dom, 'Url'),
                'msgId'       => $this->domElementValue($dom, 'MsgId')
            );
            break;
        case self::MSGTYPE_EVENT:
            $this->setEvent(strtolower(trim($this->domElementValue($dom, 'Event'))));
            log_message('info', '>>> '.__METHOD__.'() logs: Event: '.$this->getEvent());
            switch ($this->getEvent()) {
            case self::EVENT_SUBSCRIBE:
                break;
            case self::EVENT_LOCATION:
                log_message('info', '>>> '.__METHOD__.'() logs: not implemented');
                break;
            case self::EVENT_CLICK:
                $this->message['eventKey'] = $this->domElementValue($dom, 'EventKey');
                log_message('info', '>>> '.__METHOD__.'() logs: EventKey: '.$this->message['eventKey']);
                break;
            case self::EVENT_VIEW:
                $this->message['eventKey'] = $this->domElementValue($dom, 'EventKey');
                break;
            case self::EVENT_UNSUBSCRIBE:
                break;
            default:
                log_message('error', '>>> '.__METHOD__.'() logs: not implemented event or unknown event or event not set yet');
            }
            break;
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
            break;
        default:
            log_message('info', '>>> '.__METHOD__."() logs: unknown MsgType, message not loaded");
        }
    }

    private function setMsgType($msgType) {
        if (! in_array($msgType, self::$msgTypes)) {
            log_message('error', '>>> '.__METHOD__.'() logs: invalid MsgType:'.$msgType);
            $this->msgType = NULL;
        }
        $this->msgType = $msgType;
        return $this;
    }
    
    private function setEvent($event) {
        if ($this->getMsgType() != self::MSGTYPE_EVENT) {
            log_message('error', '>>> '.__METHOD__.'() logs: function available only if MsgType is: event');
            return;
        }
        if (! in_array($event, self::$events)) {
            log_message('error', '>>> '.__METHOD__.'() logs: invalid event:'.$event);
            $this->message['event'] = NULL;
        }
        $this->message['event'] = $event;
        return $this;
    }

    public function setResponseMsgType($msgType) {
        if (! in_array($msgType, self::$responseMsgTypes)) {
            log_message('error', '>>> '.__METHOD__.'() logs: invalid responseMsgType:'.$msgType);
            $this->responseMsgType = NULL;
        }
        $this->responseMsgType = $msgType;
        return $this;
    }

    public function setResponseMessage($message) {
        if (is_string($message)) {
            $message = array(
                'content' => $message
            );
        }

        switch ($this->responseMsgType) {
        case self::MSGTYPE_RAW_TEXT:
        case self::MSGTYPE_TEXT:
            if (isset($message['content'])) {
                $this->responseMessage = $message;
            }
            break;
        case self::MSGTYPE_IMAGE:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_VOICE:
        default:
            log_message('error', '>>> '.__METHOD__.'() logs: not implemented responseMsgType:'.$this->responseMsgType);
        }
        return $this;
    }

    public function sendResponse($message = NULL) {
        if ($message !== NULL) {
            $this->setResponseMessage($message);
        }
        // some received MsgType must be responsed like this...
        switch ($this->getMsgType()) {
        case self::MSGTYPE_TEXT:
            break;
        case self::MSGTYPE_EVENT:
            switch ($this->getEvent()) {
            case self::EVENT_CLICK:
            case self::EVENT_SUBSCRIBE:
                break 2;
            case self::EVENT_UNSUBSCRIBE:
                echo self::RAW_TEXT_SUCCESS;
                log_message('info', '>>> '.__METHOD__.'() logs: \'success\' responsed');
                break;
            case self::EVENT_LOCATION:
            case self::EVENT_SCAN:
            case self::EVENT_VIEW:
            default:
                echo self::RAW_TEXT_SUCCESS;
                log_message('info', '>>> '.__METHOD__.'() logs: currently response \'success\'');
                break;
            }
            return;
        case self::MSGTYPE_IMAGE:
        case self::MSGTYPE_LINK:
        case self::MSGTYPE_LOCATION:
        case self::MSGTYPE_MUSIC:
        case self::MSGTYPE_NEWS:
        case self::MSGTYPE_SHORTVIDEO:
        case self::MSGTYPE_VIDEO:
        case self::MSGTYPE_VOICE:
            echo self::RAW_TEXT_SUCCESS;
            log_message('info', '>>> '.__METHOD__.'() logs: currently response \'success\'');
            return;
        }

        if ($this->responseMsgType == self::MSGTYPE_RAW_TEXT) {
            if (!isset($this->responseMessage['content'])) {
                log_message('error', '>>> '.__METHOD__.'() logs: response message content not set, success responsed');
                $this->responseSuccess(TRUE);
            }
            log_message('info', '>>> '.__METHOD__.'() logs: response raw text:'.$this->responseMessage['content']);
            echo $this->responseMessage['content'];
            return;
        }
        
        $dom = new DOMDocument();
        $e   = $dom->createElement('xml');
        $e->appendChild($dom->createElement('ToUserName'))->appendChild($dom->createCDATASection($this->fromUserName));
        $e->appendChild($dom->createElement('FromUserName'))->appendChild($dom->createCDATASection($this->toUserName));
        $e->appendChild($dom->createElement('CreateTime'))->appendChild($dom->createTextNode($this->createTime));
        
        switch ($this->responseMsgType) {
        case self::MSGTYPE_TEXT:
            if (! isset($this->responseMessage['content'])) {
                log_message('error', '>>> '.__METHOD__.'() logs: response message content not set, success responsed');
                $this->responseSuccess(TRUE);
            }
            $e->appendChild($dom->createElement('MsgType'))->appendChild($dom->createCDATASection($this->responseMsgType));
            $e->appendChild($dom->createElement('Content'))->appendChild($dom->createCDATASection($this->responseMessage['content']));
            break;
        default:
            log_message('info', '>>> '.__METHOD__.'() logs: responseMsgType: '.$this->responseMsgType.' is not supported at the moment');
            return $this->responseSuccess();
            break;
        }
        $dom->appendChild($e);
        log_message('info', '>>> '.__METHOD__.'() logs: '.$this->responseMsgType.' message responsed');
        echo $dom->saveXML();
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
            log_message('info', '>>> '.__METHOD__.'() logs: method available only if MsgType is: event');
            return;
        }
        return isset($this->message['event']) ? $this->message['event'] : NULL;
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

    public function responseSuccess($exit = FALSE) {
        $this->setResponseMsgType(self::MSGTYPE_RAW_TEXT)->sendResponse(self::RAW_TEXT_SUCCESS);
        $exit && exit;
    }
}
