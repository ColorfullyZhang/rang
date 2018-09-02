<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('parse_quote_string')) {
    function parse_quote_string($str) {
        if (! class_exists('Pricelist')) {
            log_message('error', '>>> '.__METHOD__.'() logs: class Pricelist must be loaded before using this function');
            return array();
        }

        $exploded = explode(' ', strtoupper(preg_replace('/\s+/', ' ', trim($str))));
        $data = array();
        // The last containerType is returned
        for ($i = count($exploded) - 1; $i >= 0; $i++) {
            if (in_array($exploded[$i], Pricelist::$shipOwners)) {
                $data['shipOwners'][] = $exploded[$i];
            } else if (array_key_exists($exploded[$i], Pricelist::$containerTypes)) {
                if (! isset($data['container'])) {
                    $data['container'] = Pricelist::$containerTypes[$exploded[$i]];
                }
            } else {
                break;
            }
            unset($exploded[$i]);
        }
        for ($j = 0; $j < $i - 1; $j++) {
            if (in_array($exploded[$j], Pricelist::$shipOwners)) {
                $data['shipOwners'][] = $exploded[$j];
            } else if (array_key_exists($exploded[$j], Pricelist::$containerTypes)) {
                $container = Pricelist::$containerTypes[$exploded[$j]];
            } else {
                break;
            }
            $exploded[$j] = '';
        }
        if (! isset($data['container']) && isset($container) {
            $data['container'] = $container;
        }
        if (array_key_exists('shipOwners', $data)) {
            $data['shipOwners'] = array_unique($data['shipOwners']);
        }
        $data['dest'] = trim(implode(' ', $exploded));
        if (strlen($data['dest']) == 0 ) {
            unset($data['dest']);
        }
        return $data;
    }
}
