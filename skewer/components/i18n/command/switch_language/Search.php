<?php

namespace skewer\components\i18n\command\switch_language;

use skewer\base\command\Action;
use skewer\components\search\models\SearchIndex;
use skewer\components\seo\Service;

/**
 * Сброс поиска.
 */
class Search extends Action
{
    /**
     * {@inheritdoc}
     */
    protected function init()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        Service::rebuildSearchIndex();
        SearchIndex::updateAll(['status' => 0], ['status' => 1]);
        Service::updateSearchIndex();
        Service::updateSiteMap();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
