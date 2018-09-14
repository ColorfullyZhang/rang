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

    public function quote($dest) {
        if (! is_string($dest) OR $dest === '') {
            log_message('info', '>>> '.__METHOD__."() logs: invalid parameter");
            return '查询暂不可用';
        }
        
        $this->excel->setActiveSheetIndexByName('Pricelist');
        $activeSheet = $this->excel->getActiveSheet();

        $header = $activeSheet->rangeToArray('A1:'.$activeSheet->getHighestColumn().'1')[0];
        switch (FALSE) {
            case $header[8]  == '20G':
            case $header[9]  == 'E1':
            case $header[10] == '40G':
            case $header[11] == 'E2':
            case $header[12] == '40H':
            case $header[13] == 'E3':
            case $header[14] == 'AE':
                log_message('info', '>>> '.__METHOD__."() logs: table structure changed!");
                return '查询暂不可用';
        }

        list($cRegion, $cDest, $cShipOwner, $cCTN20G, $cEBS20G, $cCTN40G, $cEBS40G, $cCTN40H, $cEBS40H, $cAMSENS,
            $cWeekday, $cLoadPort, $cTransitPort, $cDuration, $cStartDate, $cEndDate, $cMark1, $cMark2) =
            array('C', 'D', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'U', 'V', 'X', 'Y', 'Z', 'AA', 'AB', 'AC');

        $data = array();
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if (stripos(trim($activeSheet->getCell($cDest.$r)->getValue()), $dest) === FALSE)
                continue;

            $data[] = array(
                'region'      => $activeSheet->getCell($cRegion.$r)->getValue(),
                'dest'        => ucwords(strtolower(trim($activeSheet->getCell($cDest.$r)->getValue()))),
                'shipOwner'   => $activeSheet->getCell($cShipOwner.$r)->getValue(),
                'ctn20g'      => intval($activeSheet->getCell($cCTN20G.$r)->getValue()),
                'ebs20g'      => intval($activeSheet->getCell($cEBS20G.$r)->getValue()),
                'ctn40g'      => intval($activeSheet->getCell($cCTN40G.$r)->getValue()),
                'ebs40g'      => intval($activeSheet->getCell($cEBS40G.$r)->getValue()),
                'ctn40h'      => intval($activeSheet->getCell($cCTN40H.$r)->getValue()),
                'ebs40h'      => intval($activeSheet->getCell($cEBS40H.$r)->getValue()),
                'amsens'      => intval($activeSheet->getCell($cAMSENS.$r)->getValue()),
                'weekday'     => trim($activeSheet->getCell($cWeekday.$r)->getValue()),
                'loadPort'    => trim($activeSheet->getCell($cLoadPort.$r)->getValue()),
                'transitPort' => trim($activeSheet->getCell($cTransitPort.$r)->getValue()),
                'duration'    => trim($activeSheet->getCell($cDuration.$r)->getValue()),
                'startDate'   => date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($activeSheet->getCell($cStartDate.$r)->getValue())),
                'endDate'     => date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($activeSheet->getCell($cEndDate.$r)->getValue())),
                'mark1'       => trim($activeSheet->getCell($cMark1.$r)->getValue()),
                'mark2'       => trim($activeSheet->getCell($cMark2.$r)->getValue())
            );
            if (count($data) > 11) return 'too_many_result';
        }
        return $data;
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
            return 'error';
        }

        $this->excel->setActiveSheetIndexByName('Contacts');
        $activeSheet = $this->excel->getActiveSheet();
        if ($activeSheet->getCell('G1')->getValue() != '姓名') {
            return 'Table structure changed';
        }
        
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if ($activeSheet->getCell('G'.$r)->getValue() == $contact) {
                $data = array(
                    'customer' => $activeSheet->getCell('B'.$r)->getValue(),
                    'contact'  => $contact,
                    'namecard' => $activeSheet->getCell('F'.$r)->getValue(),
                    'pos'      => $activeSheet->getCell('H'.$r)->getValue(),
                    'mob'      => $activeSheet->getCell('I'.$r)->getValue(),
                    'tel'      => $activeSheet->getCell('J'.$r)->getValue(),
                    'note'     => $activeSheet->getCell('K'.$r)->getValue()
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
