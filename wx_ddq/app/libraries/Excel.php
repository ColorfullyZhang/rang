<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Excel {
    private $excel;
    
    public function __construct() {
        parent::__construct();

        $file = DATAPATH.'Africa_data.xlsx';
        if (! file_exists($file)) exit('Data file "'.$file.'" does not exist!');
        //$this->output->cache(4320); //cache 3 days
        //$this->output->delete_cache(); //cache 3 days

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
            if ($activeSheet->getCell('A'.$r)->getValue() == $landmark) {
                $data['landmark'] = $activeSheet->getCell('A'.$r)->getValue();
                $data['address'] = $activeSheet->getCell('B'.$r)->getValue();
            }
        }
        if (! isset($data['landmark'])) exit('Nothing Found!');

        $this->excel->setActiveSheetIndexByName('Customer');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('C'.$r)->getValue() == $data['landmark']) {
                $data['companies'][] = array(
                    'name'    => $activeSheet->getCell('A'.$r)->getValue(),
                    'address' => $activeSheet->getCell('D'.$r)->getValue(),
                );
            }
        }
        $this->parser->parse('landmark', $data);
    }

    public function Customer($customer = NULL) {
        if (is_null($customer)) exit('Customer Needed!');

        $this->excel->setActiveSheetIndexByName('Customer');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('A'.$r)->getValue() == $customer) {
                $data['customer'] = $activeSheet->getCell('A'.$r)->getValue();
                $data['landmark'] = $activeSheet->getCell('C'.$r)->getValue();
                $data['address']  = $activeSheet->getCell('D'.$r)->getValue();
                $data['confirm']  = $activeSheet->getCell('E'.$r)->getValue();
                $data['focus']    = $activeSheet->getCell('F'.$r)->getValue();
            }
        }
        if (! isset($data['customer'])) exit('Nothing Found!');
        $this->parser->parse('customer', $data);
    }

    public function Quote($dest = NULL, $shipOwner = NULL, $sortCTN = '20G') {
        if (is_null($dest)) exit('Destination needed!');
        $dest = strtolower(trim($dest));
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
            if (strtolower(trim($activeSheet->getCell($cDest.$r)->getValue())) <> $dest) continue;
            $sort[] = intval($activeSheet->getCell($cPrice.$r)->getValue()) +
                intval($activeSheet->getCell($cEBS.$r)->getValue()) +
                intval($activeSheet->getCell($cAMSENS.$r)->getValue());
            $data['quotations'][] = array(
                'quotation' => $activeSheet->getCell($cQuotation.$r)->getValue()
            );
        }
        array_multisort($sort, $data['quotations']);
        $this->parser->parse('quote', $data);
    }

    public function Staff($staff = NULL) {
        if (is_null($staff)) exit('Staff Needed!');

        $this->excel->setActiveSheetIndexByName('Staff');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('D'.$r)->getValue() == $staff) {
                $data = array(
                    'pos'   => $activeSheet->getCell('C'.$r)->getValue(),
                    'name'  => $activeSheet->getCell('D'.$r)->getValue(),
                    'tel'   => $activeSheet->getCell('G'.$r)->getValue(),
                    'mob'   => $activeSheet->getCell('I'.$r)->getValue(),
                    'email' => $activeSheet->getCell('J'.$r)->getValue()
                );
            }
        }
        if (! isset($data['name'])) exit('Nothing Found!');
        $this->parser->parse('staff', $data);
    }

    public function Contact($contact = NULL) {
    }
}
