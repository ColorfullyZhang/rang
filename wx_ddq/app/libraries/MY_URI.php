<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_URI extends CI_URI {

    public function filter_uri(&$str) {
		if ( ! empty($str) && ! empty($this->_permitted_uri_chars) && ! preg_match('/^['.$this->_permitted_uri_chars.']+$/i'.(UTF8_ENABLED ? 'u' : ''), urlencode($str)))
		{
			show_error('The URI you submitted has disallowed characters.(MY_URI->filter_uri)', 400);
		}
    }
}
