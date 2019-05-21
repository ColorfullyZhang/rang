<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exceldata {

    private $excel;
    private $status;

    public function __construct() {
        $file = DATAPATH.'raw/yjc.xlsm';
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
    public function canUse() {
        return $this->status;
    }
    
    public function search($content = NULL) {
        if (is_null($content)) {
            log_message('info', '>>> '.__METHOD__."() logs: Invalid landmark: {$content}");
            return '查询出错';
        }

        $this->excel->setActiveSheetIndexByName('数据');
        $activeSheet = $this->excel->getActiveSheet();
        $data = [];
        $projects = [];
        foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if (in_array($content, [mb_substr($activeSheet->getCell('E'.$r)->getValue(), -1),
                                    substr($activeSheet->getCell('F'.$r)->getValue(), -2),
                                    substr($activeSheet->getCell('G'.$r)->getValue(), -2)])) {
                $projects[] = str_replace(' ', '', $activeSheet->getCell('A'.$r)->getValue()).
                    date('ymd', PHPExcel_Shared_Date::ExcelToPHP($activeSheet->getCell('B'.$r)->getValue()));
            }
        }
        $projects = array_unique($projects);
        if (count($projects) > 0) foreach ($activeSheet->getRowIterator() as $row) {
            if (($r = $row->getRowIndex()) == 1) continue;
            if (in_array(str_replace(' ', '', $activeSheet->getCell('A'.$r)->getValue()).
                    date('ymd', PHPExcel_Shared_Date::ExcelToPHP($activeSheet->getCell('B'.$r)->getValue())), $projects)) {
                $key = str_replace(' ', '', $activeSheet->getCell('A'.$r)->getValue()).
                    date('ymd', PHPExcel_Shared_Date::ExcelToPHP($activeSheet->getCell('B'.$r)->getValue()));
                if (!array_key_exists($key, $data))
                    $data[$key] = array(
                        'project'   => $activeSheet->getCell('A'.$r)->getValue(),
                        'etd'       => date('md', PHPExcel_Shared_Date::ExcelToPHP($activeSheet->getCell('B'.$r)->getValue())),
                        'vessel_en' => $activeSheet->getCell('C'.$r)->getValue(),
                        'voyage'    => $activeSheet->getCell('D'.$r)->getValue(),
                        'vessel_cn' => $activeSheet->getCell('E'.$r)->getValue(),
                        'ctns'      => [],
                    );
                if ($activeSheet->getCell('G'.$r)->getValue() != "")
                    $data[$key]['ctns'][$activeSheet->getCell('G'.$r)->getValue()][] = $activeSheet->getCell('F'.$r)->getValue();
            }
        }

        if (count($data) == 0) {
           return '没找到';
        }

        return $data;
        
        //$CI =& get_instance();
        //return $CI->parser->parse('view', $data, TRUE);
    }
}