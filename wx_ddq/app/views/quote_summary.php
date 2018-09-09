<?php
$dest = '';
foreach ($items as $item) {
    if ($dest <> $item['dest']) {
        $dest = $item['dest'];
        echo $dest.EOL;
    }
    $echo = array(
        $item['shipOwner'],
        sprintf('%d', $item['ctn20g']+$item['ebs20g']+$item['amsens']).'/'.
        sprintf('%d', $item['ctn40g']+$item['ebs40g']+$item['amsens']).'/'.
        sprintf('%d', $item['ctn40h']+$item['ebs40h']+$item['amsens']),
        str_replace('周', '', $item['weekday']),
        str_replace(['港', '山', '一', '二', '三', '四', '五'], '', $item['loadPort']),
        $item['transitPort'] == '直达' ? '直' : '转',
        $item['duration'],
        date('md', strtotime($item['startDate'])),
        date('md', strtotime($item['endDate']))
    );
    echo implode('|', $echo).EOL;
}