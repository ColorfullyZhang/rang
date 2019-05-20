<?php
class AAA {
//<!-- 文本消息 -->
public static $xml01 = '<xml>
    <ToUserName><![CDATA[toUser_01]]></ToUserName>
    <FromUserName><![CDATA[fromUser_01]]></FromUserName>
    <CreateTime>1348831860</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[65]]></Content>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 点击菜单拉取消息时的事件 -->
//<!-- landmark quotation customer contact staff unknown -->
public static $xml16 = '<xml>
    <ToUserName><![CDATA[toUser_16]]></ToUserName>
    <FromUserName><![CDATA[fromUser_01]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[CLICK]]></Event>
    <EventKey><![CDATA[quotation]]></EventKey>
</xml>';

//<!-- 图片消息 -->
public static $xml02 = '<xml>
    <ToUserName><![CDATA[toUser_02]]></ToUserName>
    <FromUserName><![CDATA[fromUser_02]]></FromUserName>
    <CreateTime>1348831860</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    <PicUrl><![CDATA[this is a url]]></PicUrl>
    <MediaId><![CDATA[media_id]]></MediaId>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 语音消息 -->
public static $xml03 = '<xml>
    <ToUserName><![CDATA[toUser_03]]></ToUserName>
    <FromUserName><![CDATA[fromUser_03]]></FromUserName>
    <CreateTime>1357290913</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    <MediaId><![CDATA[media_id]]></MediaId>
    <Format><![CDATA[Formattttttttttt]]></Format>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 语音消息 开通语音识别后 -->
public static $xml04 = '<xml>
    <ToUserName><![CDATA[toUser_04]]></ToUserName>
    <FromUserName><![CDATA[fromUser_04]]></FromUserName>
    <CreateTime>1357290913</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    <MediaId><![CDATA[media_id]]></MediaId>
    <Format><![CDATA[Format]]></Format>
    <Recognition><![CDATA[腾讯微信团队]]></Recognition>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 视频消息 -->
public static $xml05 = '<xml>
    <ToUserName><![CDATA[toUser_05]]></ToUserName>
    <FromUserName><![CDATA[fromUser_05]]></FromUserName>
    <CreateTime>1357290913</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    <MediaId><![CDATA[media_id]]></MediaId>
    <ThumbMediaId><![CDATA[thumb_media_id]]></ThumbMediaId>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 小视频消息 -->
public static $xml06 = '<xml>
    <ToUserName><![CDATA[toUser_06]]></ToUserName>
    <FromUserName><![CDATA[fromUser_06]]></FromUserName>
    <CreateTime>1357290913</CreateTime>
    <MsgType><![CDATA[shortvideo]]></MsgType>
    <MediaId><![CDATA[media_id]]></MediaId>
    <ThumbMediaId><![CDATA[thumb_media_id]]></ThumbMediaId>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 地理位置消息 -->
public static $xml07 = '<xml>
    <ToUserName><![CDATA[toUser_07]]></ToUserName>
    <FromUserName><![CDATA[fromUser_07]]></FromUserName>
    <CreateTime>1351776360</CreateTime>
    <MsgType><![CDATA[location]]></MsgType>
    <Location_X>23.134521</Location_X>
    <Location_Y>113.358803</Location_Y>
    <Scale>20</Scale>
    <Label><![CDATA[位置信息]]></Label>
    <MsgId>1234567890123456</MsgId>
</xml>';

//<!-- 链接消息    -->
public static $xml08 = '<xml>
    <ToUserName><![CDATA[toUser_08]]></ToUserName>
    <FromUserName><![CDATA[fromUser_08]]></FromUserName>
    <CreateTime>1351776360</CreateTime>
    <MsgType><![CDATA[link]]></MsgType>
    <Title><![CDATA[公众平台官网链接]]></Title>
    <Description><![CDATA[公众平台官网链接]]></Description>
    <Url><![CDATA[url]]></Url>
    <MsgId>1234567890123456</MsgId>
</xml>';



//<!-- 关注事件 -->
public static $xml11 = '<xml>
    <ToUserName><![CDATA[toUser_11]]></ToUserName>
    <FromUserName><![CDATA[FromUser_11]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[subscribe]]></Event>
</xml>';

//<!-- 取消关注事件 -->
public static $xml12 = '<xml>
    <ToUserName><![CDATA[toUser_12]]></ToUserName>
    <FromUserName><![CDATA[FromUser_12]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[unsubscribe]]></Event>
</xml>';

//<!-- 扫描带参数二维码 未关注时，进行关注后的事件 -->
public static $xml13 = '<xml>
    <ToUserName><![CDATA[toUser_13]]></ToUserName>
    <FromUserName><![CDATA[FromUser_13]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[subscribe]]></Event>
    <EventKey><![CDATA[qrscene_123123]]></EventKey>
    <Ticket><![CDATA[TICKET]]></Ticket>
</xml>';

//<!-- 扫描带参数二维码 已关注时的事件 -->
public static $xml14 = '<xml>
    <ToUserName><![CDATA[toUser_14]]></ToUserName>
    <FromUserName><![CDATA[FromUser_14]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[SCAN]]></Event>
    <EventKey><![CDATA[SCENE_VALUE]]></EventKey>
    <Ticket><![CDATA[TICKET]]></Ticket>
</xml>';

//<!-- 上报地理位置事件 -->
public static $xml15 = '<xml>
    <ToUserName><![CDATA[toUser_15]]></ToUserName>
    <FromUserName><![CDATA[fromUser_15]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[LOCATION]]></Event>
    <Latitude>23.137466</Latitude>
    <Longitude>113.352425</Longitude>
    <Precision>119.385040</Precision>
</xml>';

//<!-- 点击菜单跳转链接时的事件 -->
public static $xml17 = '<xml>
    <ToUserName><![CDATA[toUser_17]]></ToUserName>
    <FromUserName><![CDATA[FromUser_17]]></FromUserName>
    <CreateTime>123456789</CreateTime>
    <MsgType><![CDATA[event]]></MsgType>
    <Event><![CDATA[VIEW]]></Event>
    <EventKey><![CDATA[www.qq.com]]></EventKey>
</xml>';





//<!-- 回复文本消息 -->
public static $xml91 = '<xml>
    <ToUserName><![CDATA[toUser_91]]></ToUserName>
    <FromUserName><![CDATA[fromUser_91]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[你好]]></Content>
</xml>';

//<!-- 回复图片消息 -->
public static $xml92 = '<xml>
    <ToUserName><![CDATA[toUser_92]]></ToUserName>
    <FromUserName><![CDATA[fromUser_92]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    <Image>
        <MediaId><![CDATA[media_id]]></MediaId>
    </Image>
</xml>';

//<!-- 回复语音消息 -->
public static $xml93 = '<xml>
    <ToUserName><![CDATA[toUser_93]]></ToUserName>
    <FromUserName><![CDATA[fromUser_93]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    <Voice>
        <MediaId><![CDATA[media_id]]></MediaId>
    </Voice>
</xml>';

//<!-- 回复视频消息 -->
public static $xml94 = '<xml>
    <ToUserName><![CDATA[toUser_94]]></ToUserName>
    <FromUserName><![CDATA[fromUser_94]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    <Video>
        <MediaId><![CDATA[media_id]]></MediaId>
        <Title><![CDATA[title]]></Title>
        <Description><![CDATA[description]]></Description>
    </Video>
</xml>';

//<!-- 回复音乐消息 -->
public static $xml95 = '<xml>
    <ToUserName><![CDATA[toUser_95]]></ToUserName>
    <FromUserName><![CDATA[fromUser_95]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    <Music>
        <Title><![CDATA[TITLE]]></Title>
        <Description><![CDATA[DESCRIPTION]]></Description>
        <MusicUrl><![CDATA[MUSIC_Url]]></MusicUrl>
        <HQMusicUrl><![CDATA[HQ_MUSIC_Url]]></HQMusicUrl>
        <ThumbMediaId><![CDATA[media_id]]></ThumbMediaId>
    </Music>
</xml>';

//<!-- 回复图文消息 -->
public static $xml96 = '<xml>
    <ToUserName><![CDATA[toUser_96]]></ToUserName>
    <FromUserName><![CDATA[fromUser_96]]></FromUserName>
    <CreateTime>12345678</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>2</ArticleCount>
    <Articles>
        <item>
            <Title><![CDATA[title1]]></Title>
            <Description><![CDATA[description1]]></Description>
            <PicUrl><![CDATA[picurl]]></PicUrl>
            <Url><![CDATA[url]]></Url>
        </item>
        <item>
            <Title><![CDATA[title]]></Title>
            <Description><![CDATA[description]]></Description>
            <PicUrl><![CDATA[picurl]]></PicUrl>
            <Url><![CDATA[url]]></Url>
        </item>
    </Articles>
</xml>';
}
