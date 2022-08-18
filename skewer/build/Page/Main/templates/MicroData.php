<?php

    use skewer\base\site\Site;

    ?>
<div itemscope itemtype="<?=WEBPROTOCOL; ?>schema.org/WebSite" style="display: none;">
    <a itemprop="url" href="<?= Site::httpDomainSlash(); ?>"></a>
    <form itemprop="potentialAction" itemscope itemtype="<?=WEBPROTOCOL; ?>schema.org/SearchAction">
        <meta itemprop="target" content="<?= Site::httpDomainSlash(); ?>search/?search_text={search_term_string}"/>
        <input type="text" itemprop="query-input" name="search_term_string" />
    </form>
</div>