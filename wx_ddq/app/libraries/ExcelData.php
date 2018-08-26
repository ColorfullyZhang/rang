<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exceldata {
    const QUERY_LANDMARK  = 'landmark';
    const QUERY_CUSTOMER  = 'customer';
    const QUERY_CONTACT   = 'contact';
    const QUERY_STAFF     = 'staff';
    const QUERY_QUOTATION = 'quotation';
    public static $queryTypes = array(
        self::QUERY_LANDMARK,
        self::QUERY_CUSTOMER,
        self::QUERY_CONTACT,
        self::QUERY_STAFF,
        self::QUERY_QUOTATION
    );

    private $excel;
    private $status;

    protected $CI;
    
    public function __construct() {
        $file = DATAPATH.'raw/Africa_data.xlsx';
        if (! file_exists($file)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Data file "'.$file.'" does not exist');
            $this->status = FALSE;
        } else {
            $this->CI =& get_instance();

            $this->CI->load->library('PHPExcel');
		    $this->CI->load->library('PHPExcel/PHPExcel_IOFactory');
            $reader = PHPExcel_IOFactory::createReaderForFile($file);
            $this->excel = $reader->load($file);
            $this->status = TRUE;
        }
    }

    public function canUse() {
        return $this->status;
    }

    public function Landmark($landmark = NULL) {
        if (is_null($landmark)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid landmark: {$landmark}");
            return '查询出错';
        }

        $this->excel->setActiveSheetIndexByName('Landmark');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('A'.$r)->getValue() == $landmark) {
                $data['landmark'] = $activeSheet->getCell('A'.$r)->getValue();
                $data['address'] = $activeSheet->getCell('B'.$r)->getValue();
            }
        }
        if (! isset($data['landmark'])) {
           return '什么也没查到'; 
        }

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
        return $this->CI->parser->parse('landmark', $data, TRUE);
    }

    public function Customer($customer = NULL) {
        if (is_null($customer)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid customer: {$customer}");
            return '查询出错';
        }

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
        if (! isset($data['customer'])) {
           return '什么也没查到'; 
        }
        return $this->CI->parser->parse('customer', $data, TRUE);
    }

    public function Quote($dest = NULL, $shipOwner = NULL, $sortCTN = '20G') {
        if (is_null($dest)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid destination: {$dest}");
            return '查询出错';
        }
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
                return 'Table Structure Changed!';
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
        if (count($data) == 0) {
            return 'Nothing Found';
        }
        array_multisort($sort, $data['quotations']);
        return $this->CI->parser->parse('quote', $data, TRUE);
    }

    public function Staff($staff = NULL) {
        if (is_null($staff)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid Staff: {$staff}");
            return '查询出错';
        }

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
        if (! isset($data['name'])) {
            return 'Nothing Found';
        }
        return $this->CI->parser->parse('staff', $data, TRUE);
    }

    public function Contact($contact = NULL) {
        return 'Not implemented';
    }
}
