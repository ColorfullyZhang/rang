<?php
foreach ($prjs as $prj) {
    echo $prj['project'] . ' ' . $prj['etd'] . PHP_EOL;
    echo $prj['vessel_en'] . ' ' . $prj['voyage'] . PHP_EOL;
    echo $prj['vessel_cn'] . PHP_EOL;
    foreach ($prj['ctns'] as $ctn => $bls) {
        echo $ctn . PHP_EOL;
        foreach ($bls as $bl)
            echo '  ' . $bl . PHP_EOL;
    }
    echo PHP_EOL;
}