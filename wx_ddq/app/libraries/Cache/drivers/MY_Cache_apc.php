<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Cache_apc extends CI_Driver {
    public function __construct() {
        if ( ! $this->is_supported()) {
            log_message('error', 'Cache: Failed to initialize APCU; extension not loaded/enabled?');
        }
    }

    public function get($id) {
        $success = FALSE;
        $data = apcu_fetch($id, $success);

        return ($success === TRUE) ? $data : FALSE;
    }

    public function save($id, $data, $ttl = 60, $raw = FALSE) {
        return apcu_store($id, $data, (int) $ttl);
    }

    public function delete($id) {
        return apcu_delete($id);
    }

    public function increment($id, $offset = 1) {
        return apcu_inc($id, $offset);
    }

    public function decrement($id, $offset = 1) {
        return apcu_dec($id, $offset);
    }

    public function clean() {
        return apcu_clear_cache('user');
    }

     public function cache_info($type = NULL) {
         return apcu_cache_info($type);
     }

    public function get_metadata($id) {
        $cache_info = apcu_cache_info('user', FALSE);
        if (empty($cache_info) OR empty($cache_info['cache_list'])) {
            return FALSE;
        }

        foreach ($cache_info['cache_list'] as &$entry) {
            if ($entry['info'] !== $id) {
                continue;
            }

            $success  = FALSE;
            $metadata = array(
                'expire' => ($entry['ttl'] ? $entry['mtime'] + $entry['ttl'] : 0),
                'mtime'  => $entry['ttl'],
                'data'   => apcu_fetch($id, $success)
            );

            return ($success === TRUE) ? $metadata : FALSE;
        }

        return FALSE;
    }

    public function is_supported() {
        return (extension_loaded('apcu') && ini_get('apc.enabled'));
    }
}
