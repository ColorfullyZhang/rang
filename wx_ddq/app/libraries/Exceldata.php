<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exceldata {
    public static $shipOwners = array('ANL', 'APL', 'BLINE', 'CMA', 'CNC', 'COSCO', 'EMC',
        'EMI', 'ESL', 'HBS', 'HMM', 'HPL', 'KMTC', 'MCC', 'MSC', 'MSK', 'NDS', 'ONE',
        'OOCL', 'PIL', 'RCL', 'SAF', 'SCI', 'SMDL', 'TS', 'UASC', 'WHL', 'YML', 'ZIM'
    );
    public static $containerTypes = array(
        '20' => '20G', '20G' => '20G', '20GP' => '20G',
        '40' => '40G', '40G' => '40G', '40GP' => '40G',
        '40H' => '40H', '40HQ' => '40H', '40HC' => '40H'
    );

    private $excel;
    private $status;

    public function __construct() {
        $file = DATAPATH.'raw/Africa_data.xlsx';
        if (! file_exists($file)) {
            log_message('info', '>>> '.__METHOD__.'() logs: Data file "'.$file.'" does not exist');
            $this->status = FALSE;
        } else {
            $CI =& get_instance();
            $CI->load->library('PHPExcel');
            $CI->load->library('PHPExcel/PHPExcel_IOFactory');
            $reader = PHPExcel_IOFactory::createReaderForFile($file);
            $this->excel = $reader->load($file);
            $this->status = TRUE;
        }
    }

    public function parseQueryString($content) {
        $exploded = explode(' ', strtoupper(preg_replace('/\s+/', ' ', trim($content))));
        $data = array();
        for ($i = 0; $i < count($exploded); $i++) {
            if (in_array($exploded[$i], self::$shipOwners)) {
                $data['shipOwner'][] = $exploded[$i];
            } else if (array_key_exists($exploded[$i], self::$containerTypes)) {
                $data['container'] = self::$containerTypes[$exploded[$i]];
            } else {
                break;
            }
            $exploded[$i] = '';
        }
        for ($j = count($exploded) -1 ; $j > $i; $j--) {
            if (in_array($exploded[$j], self::$shipOwners)) {
                $data['shipOwner'][] = $exploded[$j];
            } else if (array_key_exists($exploded[$j], self::$containerTypes)) {
                $data['container'] = self::$containerTypes[$exploded[$j]];
            } else {
                break;
            }
            $exploded[$j] = '';
        }
        if (array_key_exists('shipOwner', $data)) {
            $data['shipOwner'] = array_unique($data['shipOwner']);
        }
        $data['dest'] = trim(implode(' ', $exploded));
        if (strlen($data['dest']) == 0 ) {
            unset($data['dest']);
        }
        return $data;
    }

    public function canUse() {
        return $this->status;
    }

    public function landmark($landmark = NULL) {
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
           return '没有查到该地标：'.$landmark; 
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
        return $data;
        //return $this->CI->parser->parse('landmark', $data, TRUE);
    }

    public function customer($customer = NULL) {
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
           return '没有查到该抬头：'.$customer; 
        }
        return $data;
        //return $this->CI->parser->parse('customer', $data, TRUE);
    }

    public function quote($condition = array()) {
        if (! is_array($condition)) {
            log_message('info', '>>> '.__METHOD__."() logs: invalid parameter");
            return '查询暂不可用';
        }
        $dest      = array_key_exists('dest', $condition) ? $condition['dest'] : NULL;
        $shipOwner = array_key_exists('shipOwner', $condition) ? $condition['shipOwner'] : array();
        $container = array_key_exists('container', $condition) ? $condition['container'] : NULL;
        if (is_null($dest) OR $dest == '' OR ! is_array($shipOwner) OR
            count($shipOwner) == 0 OR ! in_array($container, ['20G', '40G', '40H'])) {
            log_message('info', '>>> '.__METHOD__."() logs: invalid quote condition");
            return '查询暂不可用';
        }
        $dest = trim($dest);
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
        $cPrice = $container == '20G' ? $cCTN20G : $container == '40G' ? $cCTN40G : $cCTN40H;
        $cEBS = $container == '20G' ? $cEBS20G : $container == '40G' ? $cEBS40G : $cEBS40H;
        
        $sort = array();
        $data = array();
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if (stripos($dest, trim($activeSheet->getCell($cDest.$r)->getValue())) !== 0) {
                continue;
            }
            $sort[] = intval($activeSheet->getCell($cPrice.$r)->getValue()) +
                intval($activeSheet->getCell($cEBS.$r)->getValue()) +
                intval($activeSheet->getCell($cAMSENS.$r)->getValue());
            $data['quotations'][] = array(
                'quotation' => $activeSheet->getCell($cQuotation.$r)->getValue()
            );
        }
        if (count($data) == 0) {
            return '没有查到价格信息';
        }
        array_multisort($sort, $data['quotations']);
        return $data;
        //return $this->CI->parser->parse('quote', $data, TRUE);
    }

    public function staff($staff = NULL) {
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
            return '没有查到该同事：'.$staff;
        }
        return $data;
        //return $this->CI->parser->parse('staff', $data, TRUE);
    }

    public function contact($contact = NULL) {
        if (is_null($contact)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid Contact: {$contact}");
            return '查询出错';
        }

        $this->excel->setActiveSheetIndexByName('Namecards');
        $activeSheet = $this->excel->getActiveSheet();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('C'.$r)->getValue() == $contact) {
                $data = array(
                    'customer' => $activeSheet->getCell('B'.$r)->getValue(),
                    'contact'  => $contact,
                    'pos'      => $activeSheet->getCell('D'.$r)->getValue(),
                    'mob'      => $activeSheet->getCell('F'.$r)->getValue(),
                    'tel'      => $activeSheet->getCell('G'.$r)->getValue(),
                    'note'     => $activeSheet->getCell('H'.$r)->getValue()
                );
            }
        }
        if (! isset($data['contact'])) {
            return '没有查到该客户：'.$contact;
        }
        return $data;
        //return $this->CI->parser->parse('staff', $data, TRUE);
    }
}
