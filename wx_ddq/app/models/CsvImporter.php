<?php
class CsvImporter {
    private $fp;
    private $parse_header;
    private $header;
    private $delimiter;
    private $length;

    function __construct($file_name, $parse_header=false, $delimiter="\t", $length=8000) {
        $this->fp = fopen($file_name, "r");
        $this->parse_header = $parse_header;
        $this->delimiter = $delimiter;
        $this->length = $length;
        $this->lines = $lines;

        if ($this->parse_header)
            $this->header = fgetcsv($this->fp, $this->length, $this->delimiter);
    }

    function __destruct() {
        if ($this->fp) fclose($this->fp);
    }

    function get($max_lines=0) {
        $data = array();
        if ($max_lines > 0) $line_count = 0;
        else $line_count = -1;

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter)) !== false) {
            if ($this->parse_header) {
                foreach ($this->parse_header) {
                    $row_new[$heading_i] = $row[$i];
                }
                $data[] = $row_new;
            } else {
                $data[] = $row;
            }

            if ($max_lines > 0) $line_count++;
        }
        return $data;
    }
}
