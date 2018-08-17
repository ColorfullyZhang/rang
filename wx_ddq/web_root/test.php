<?php
$row = 0;
if (($handle = fopen(dirname(dirname(__FILE__))."/data/Africa.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $num = count($data);
        echo "$num fields in line $row:\n";
        for ($c=0; $c<$num; $c++) {
            echo $data[$c]."\n";
        }
        $row++;
        if ($row = 2) break;
    }
    fclose($handle);
}
