<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Excel extends CI_Controller {
    public function test() {
        $file = dirname(APPPATH)."/data/Africa.xlsm";
        $this->load->library('PHPExcel');
		$this->load->library('PHPExcel/IOFactory');
        $obj = IOFactory::load($file);
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
