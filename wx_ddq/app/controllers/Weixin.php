<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Weixin extends CI_Controller {
    private $appID = 'wxfc4ad89f36beb189';
    private $appSecret = '8de181c7b52dccd13087adb97b9620f0';
    private $encodingAESKey = 'saFkURTUS05TFJti201a3L5HI3c897jm4qXNhYD9i3W';
    
    public function __construct() {
        parent::__construct();
    }

    public function index() {
    }
}
