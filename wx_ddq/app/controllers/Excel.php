<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Excel extends CI_Controller {
    public function index() {
        $file = DATAPATH."Africa_test.xlsm";
        $this->load->library('PHPExcel');
		$this->load->library('PHPExcel/IOFactory');

        $obj = new PHPExcel();
        var_dump($obj->getSecurity());
        return;

        $reader = IOFactory::createReader('Excel2007');
        $obj = $reader->load($file);

        print_r($obj->getActiveSheet()->rangeToArray('A417:AD418'));
        return;
        $cell_collection = $obj->getActiveSheet()->getCellCollection();
        foreach ($cell_collection as $cell) {
            $column = $obj->getActiveSheet()->getCell($cell)->getColumn();
            $row = $obj->getActiveSheet()->getCell($cell)->getRow();
            $data_value = $obj->getActiveSheet()->getCell($cell)->getCalculatedValue();

            if ($row == 3) break;

            if ($row == 1) {
                $header[$data_value] = $column;
            } else {
                $arr_data[$row][$column] = $data_value;
            }
        }

        $data['header'] = $header;
        $data['values'] = $arr_data;
		
		print_r($data);
    }
}
