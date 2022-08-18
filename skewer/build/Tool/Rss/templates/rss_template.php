<?php
use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Tool\Rss;
use skewer\components\design\Design;

$Logo = Site::httpDomain();
    $Logo .= SysVar::get('Rss.image')
        ? SysVar::get('Rss.image')
        : Design::getLogo();
?>
<rss xmlns:yandex="http://news.yandex.ru" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">
    <channel>
        <title>Новости</title>
        <link><?= Site::httpDomain(); ?></link>
        <description>Новости</description>
        <language>ru-ru</language>
        <pubDate><?= date('r'); ?></pubDate>
        <lastBuildDate><?= date('r'); ?></lastBuildDate>

        <generator>Skewer RSS 2.0</generator>
        <yandex:logo><?= $Logo; ?></yandex:logo>
        <yandex:logo type="square"><?= $Logo; ?></yandex:logo>
        <atom:link href="<?= Site::httpDomain(); ?><?= Rss\Api::getRssLink(); ?>" rel="self" type="application/rss+xml" />
        <image>
            <url><?= $Logo; ?></url>
            <title>Новости</title>
            <link><?= Site::httpDomain(); ?></link>
        </image>

        <?php
        /** @var \skewer\build\Adm\News\models\News $oItem */
        foreach ($aItems as $oItem): ?>
            <item>
                <title><?= $oItem->title; ?></title>
                <dc:creator><?=isset($oItem->author) ? $oItem->author : ''; ?></dc:creator>
                <link><?= Site::httpDomain() . Yii::$app->router->rewriteURL($oItem->getUrl()); ?></link>
                <description><![CDATA[ <?= $oItem->announce; ?> ]]></description>
                <pubDate><?= date('r', strtotime($oItem->publication_date)); ?></pubDate>
                <guid><?= Site::httpDomain() . Yii::$app->router->rewriteURL($oItem->getUrl()); ?></guid>
                <yandex:full-text><![CDATA[ <?= $oItem->full_text; ?> ]]></yandex:full-text>
            </item>
        <?php endforeach; ?>
    </channel>
</rss>

