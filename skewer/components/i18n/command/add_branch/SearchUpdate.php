<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\components\search\models\SearchIndex;
use skewer\components\seo\Service;

/**
 * Перестроение поиска.
 */
class SearchUpdate extends Prototype
{
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
