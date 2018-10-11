<?php
foreach ($items as $item) {
    if (!in_array('ALLSHP', $shipOwner) && !in_array($item['shipOwner'], $shipOwner)) continue;
    echo $item['dest'].' '.$item['shipOwner'].' USD';
    switch ($ctnType) {
        case '20G':
            echo sprintf('%d', $item['ctn20g']).'/20GP';
            echo $item['ebs20g'] == 0 ? ' (含EBS)' : sprintf(' + EBS%d', $item['ebs20g']);
            break;
        case '40G':
            echo sprintf('%d', $item['ctn40g']).'/40GP';
            echo $item['ebs40g'] == 0 ? ' (含EBS)' : sprintf(' + EBS%d', $item['ebs40g']);
            break;
        case '40H':
            echo sprintf('%d', $item['ctn40h']).'/40HQ';
            echo $item['ebs40h'] == 0 ? ' (含EBS)' : sprintf(' + EBS%d', $item['ebs40h']);
            break;
        case 'ALLCTN':
            echo sprintf('%d/%d', $item['ctn20g'], $item['ctn40g']);
            echo $item['ctn40g'] == $item['ctn40h'] ? '' : sprintf('/%d', $item['ctn40h']);
            echo $item['ebs20g'] == 0 ? ' (含EBS)' : sprintf(' + EBS%d/%d', $item['ebs20g'], $item['ebs40h']);
            break;
    }
    $search  = array('东', '南', '西', '北');
    $replace = array('', 'AMS', '', 'ENS');
    echo $item['amsens'] == 0 ?
         '' : ' + '.str_replace($search, $replace, $item['region']).$item['amsens'].'/BILL';
    echo '，';
    echo $item['weekday'].$item['loadPort'].$item['transitPort'];
    echo mb_substr($item['transitPort'], -2) == '直达' ? '' : '转';
    echo $item['duration'].'天，价格';
    if (strtotime($item['startDate']) > strtotime('+4 day')) {
        echo '从'.date('n月j日', strtotime($item['startDate']));
    }
    echo '到'.date('n月j日', strtotime($item['endDate']));
    if (strtotime($item['endDate']) < strtotime('+4 day')) {
        echo '，已过期仅供参考';
    }
    echo '。'.PHP_EOL;
}