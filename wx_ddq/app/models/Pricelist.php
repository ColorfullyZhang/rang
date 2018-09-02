<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pricelist extends CI_Model {
    private static $excelFile = DATAPATH.'raw/Africa_data.xlsx';

    public static $shipOwners = array('ANL', 'APL', 'BLINE', 'CMA', 'CNC', 'COSCO', 'EMC',
        'EMI', 'ESL', 'HBS', 'HMM', 'HPL', 'KMTC', 'MCC', 'MSC', 'MSK', 'NDS', 'ONE',
        'OOCL', 'PIL', 'RCL', 'SAF', 'SCI', 'SMDL', 'TS', 'UASC', 'WHL', 'YML', 'ZIM'
    );

    public static $containerTypes = array(
        '20'  => '20G', '20G'  => '20G', '20GP' => '20G',
        '40'  => '40G', '40G'  => '40G', '40GP' => '40G',
        '40H' => '40H', '40HQ' => '40H', '40HC' => '40H'
    );

    private $excel;
    private $status = TRUE;

    public function __construct() {
        if (! file_exists(self::$excelFile)) {
            log_message('error', '>>> '.__METHOD__.'() logs: data file:'.self::$excelFile.' does not exists');
            $this->status = FALSE;
        } else {
            $this->load->library(('PHPExcel', 'PHPExcel/PHPExcel_IOFactory'));
            $reader = PHPExcel_IOFactory::createReaderForFile($file);
            $this->excel = $reader->load($file);
            $this->status = TRUE;
        }
    }

    public function getLandmark($landmark) {
        $this->excel->setActiveSheetIndexByName('Landmark');
        $activeSheet = $this->excel->getActiveSheet();
        $data = array();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($ri = $row->getRowIndex()) == 1) continue;
            if (strtolower(trim($activeSheet->getCell('A'.$ri)->getValue())) == strtolower(trim($landmark))) {
                $data['landmark'] = $activeSheet->getCell('A'.$r)->getValue();
                $data['address']  = $activeSheet->getCell('B'.$r)->getValue();
                break;
            }
        }
        
        return $data;
    }

    public function getCustomerByNameOrLandmark($name = NULL, $landmark = NULL) {
        if ($name === NULL && $landmark === NULL) {
            log_message('error', '>>> '.__METHOD__.'() logs: parameters cannot be NULL at the same time');
            return array();
        }

        $this->excel->setActiveSheetIndexByName('Customer');
        $activeSheet = $this->excel->getActiveSheet();
        $data = array();
        foreach ($activeSheet->getRowIterator() as $row() {
            if (($ri = $row->getRowIndex()) == 1) continue;
            if (strtolower(trim($activeSheet->getCell('A'.$ri)->getValue())) == strtolower(trim($customer)) OR
                strtolower(trim($activeSheet->getCell('C'.$ri)->getValue())) == strtolower(trim($landmark))) {
                $data[] = array(
                    'customer' => $activeSheet->getCell('A'.$ri)->getValue(),
                    'cnt'      => $activeSheet->getCell('B'.$ri)->getValue(),
                    'landmark' => $activeSheet->getCell('C'.$ri)->getValue(),
                    'address'  => $activeSheet->getCell('D'.$ri)->getValue(),
                    'confirm'  => $activeSheet->getCell('E'.$ri)->getValue(),
                    'focus'    => $activeSheet->getCell('F'.$ri)->getValue()
                );
                if($name !== NULL) break;
            }
        }

        return $data;
    }

    public function getStaff($name) {
        $this->excel->setActiveSheetIndexByName('Staff');
        $activeSheet = $this->excel->getActiveSheet();
        $data = array();
        foreach ($activeSheet->getRowIterator() as $row()) {
            if (($ri = $row->getRowIndex()) == 1) continue;
            if (strtolower(trim($activeSheet->getCell('D'.$ri)->getValue())) == strtolower(trim($name))) {
                $data = array(
                    'pos'   => $activeSheet->getCell('C'.$ri)->getValue(),
                    'tel'   => $activeSheet->getCell('G'.$ri)->getValue(),
                    'mob'   => $activeSheet->getCell('I'.$ri)->getValue(),
                    'email' => $activeSheet->getCell('J'.$ri)->getValue(),
                );
                break;
            }
        }

        return $data;
    }

    public function getContact($data) {
        if (! is_array($data) OR
            ! ( (isset($data['customer']) && strlen($data['customer']) > 0) OR
                (isset($data['name'])     && strlen($data['name']) > 0) OR
                (isset($data['number'])   && strlen($data['number']) > 0) ) ) {
            log_message('error', '>>> '.__METHOD__.'() logs: invalid parameter');
            return array();
        }

        $this->excel->setActiveSheetIndexByName('Namecards');
        $activeSheet = $this->excel->getActiveSheet();
        $data = array();
        foreach ($activeSheet->getRowIterator() as $row() {
            if (($ri = $row->getRowIndes()) == 1) continue;
            if ((isset($data['customer']) && strtolower(trim($activeSheet->getCell('B'.$ri)->getValue())) == strtolower(trim($data['customer']))) OR
                (isset($data['name'])     && strtolower(trim($activeSheet->getCell('C'.$ri)->getValue())) == strtolower(trim($data['name']))) OR
                (isset($data['number'])   && (strpos($activeSheet->getCell('E'.$ri)->getValue(), $data['number']) !== FALSE OR
                                              strpos($activeSheet->getCell('F'.$ri)->getValue(), $data['number']) !== FALSE ) ) ) {
                $data[] = array(
                    'date'     => $activeSheet->getCell('A'.$ri)->getValue(),
                    'customer' => $activeSheet->getCell('B'.$ri)->getValue(),
                    'name'     => $activeSheet->getCell('C'.$ri)->getValue(),
                    'pos'      => $activeSheet->getCell('D'.$ri)->getValue(),
                    'qq'       => $activeSheet->getCell('E'.$ri)->getValue(),
                    'mob'      => $activeSheet->getCell('F'.$ri)->getValue(),
                    'tel'      => $activeSheet->getCell('G'.$ri)->getValue(),
                    'memo'     => $activeSheet->getCell('H'.$ri)->getValue(),
                );
            }
        }

        return $data;
    }

    public function Quote($dest, $shipOwners = self::$shipOwners, $container = '20G') {
        if (strlen(trim($dest)) == 0 OR ! is_array($shipOwners) OR
            count($shipOwners) == 0 OR ! in_array($container, self::$containerTypes)) {
            log_message('info', '>>> '.__METHOD__."() logs: invalid quote condition");
            return array();
        }
        $this->excel->setActiveSheetIndexByName('Pricelist');
        $activeSheet = $this->excel->getActiveSheet();
        $header = $activeSheet->rangeToArray('A1:'.$activeSheet->getHighestColumn().'1')[0];
        if (array($header[8], $header[9], $header[10], $header[11], $header[12], $header[13], $header[14], $header[29]) !=
            array('20G', 'E1', '40G', 'E2', '40H', 'E3', 'AE', '快捷报价')) {
            log_message('info', '>>> '.__METHOD__."() logs: table structure changed");
            return array();
        }

        list($cDest, $cShipOwner, $cCTN20G, $cEBS20G, $cCTN40G, $cEBS40G, $cCTN40H, $cEBS40H,
            $cAMSENS, $cRemark1, $cRemark2, $cQuotation) =
            array('D', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'AB', 'AC', 'AD');
        $cPrice = $container == '20G' ? $cCTN20G : $container == '40G' ? $cCTN40G : $cCTN40H;
        $cEBS   = $container == '20G' ? $cEBS20G : $container == '40G' ? $cEBS40G : $cEBS40H;

        $sort = array();
        $data = array();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($ri = $row->getRowIndex()) == 1) continue;
            if (stripos(trim($dest), trim($activeSheet->getCell($cDest.$r)->getValue())) !== 0) {
                continue;
            }
            $sort[] = intval($activeSheet->getCell($cPrice.$r)->getValue()) +
                intval($activeSheet->getCell($cEBS.$r)->getValue()) +
                intval($activeSheet->getCell($cAMSENS.$r)->getValue());
            $data['quotations'][] = array(
                'quotation' => $activeSheet->getCell($cQuotation.$r)->getValue()
            );
        }
        array_multisort($sort, $data['quotations']);
        return $data;
    }
}
