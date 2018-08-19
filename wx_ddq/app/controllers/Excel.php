<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Excel extends CI_Controller {
    private $excel;
    
    public function __construct() {
        parent::__construct();

        $file = DATAPATH.'Africa_test.xlsm';

        $this->load->library('PHPExcel');
		$this->load->library('PHPExcel/PHPExcel_IOFactory');
        $reader = PHPExcel_IOFactory::createReaderForFile($file);
        $this->excel = $reader->load($file);
    }

    public function Landmark($landmark = NULL) {
        if (is_null($landmark)) exit('Landmark needed!');

        $this->excel->setActiveSheetIndexByName('Landmark');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('B'.$r)->getValue() == $landmark) {
                $data['landmark'] = $activeSheet->getCell('A'.$r)->getValue();
                $data['address'] = $activeSheet->getCell('C'.$r)->getValue();
            }
        }

        $this->excel->setActiveSheetIndexByName('Customer');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('D'.$r)->getValue() == $data['landmark']) {
                $data['companies'][] = array(
                    'name'    => $activeSheet->getCell('A'.$r)->getValue(),
                    'address' => $activeSheet->getCell('E'.$r)->getValue(),
                );
            }
        }
        if ($data['landmark'] == '') exit('Nothing Found!');
        $this->parser->parse('landmark', $data);
    }

    public function Customer($customer = NULL) {
        if (is_null($customer)) exit('Customer Needed!');

        $this->excel->setActiveSheetIndexByName('Customer');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('B'.$r)->getValue() == $customer) {
                $data['customer'] = $activeSheet->getCell('A'.$r)->getValue();
                $data['address'] = $activeSheet->getCell('E'.$r)->getValue();
                $data['focus'] = $activeSheet->getCell('G'.$r)->getValue();
            }
        }
        if ($data['customer'] == '') exit('Nothing Found!');
        $this->parser->parse('customer', $data);
    }

    public function Quote($dest = NULL, $shipOwner = NULL, $sortCTN = '20G') {
        if (is_null($dest)) exit('Destination needed!');
        $this->excel->setActiveSheetIndexByName('Pricelist');

        $header = $this->excel->getActiveSheet()->rangeToArray(
            'A1:'.$this->excel->getActiveSheet()->getHighestColumn().'1')[0];
        switch (TRUE) {
            case $header[8]  <> '20G':
            case $header[9]  <> 'E1':
            case $header[10] <> '40G':
            case $header[11] <> 'E2':
            case $header[12] <> '40H':
            case $header[13] <> 'E3':
            case $header[14] <> 'AE':
            case $header[29] <> '快捷报价':
                exit('Table Structure Changed!');
        }

        list($cDest, $cShipOwner, $cCTN20G, $cEBS20G, $cCTN40G, $cEBS40G,
            $cCTN40H, $cEBS40H, $cAMSENS, $cRemark1, $cRemark2, $cQuotation) =
            array('D', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'AB', 'AC', 'AD');
        $cPrice = $cCTN20G;
        $cEBS = $cEBS20G;
        
        $sort = array();
        $data = array();
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell($cDest.$r)->getValue() <> 'COTONOU') continue;
            $sort[] = $activeSheet->getCell($cPrice.$r)->getValue() +
                $activeSheet->getCell($cEBS.$r)->getValue() +
                $activeSheet->getCell($cAMSENS.$r)->getValue();
            $data[] = $activeSheet->getCell($cQuotation.$r)->getValue();
        }
        array_multisort($sort, $data);
        print_r($data);
    }
}
