<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\Parameters;
use yii\helpers\ArrayHelper;

/**
 * Копирование разводок категорий.
 */
class CategoryViewer extends Prototype
{
    protected $copyList = [];

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        parent::init();

        $this->listenTo(CopySections::COPY_SECTIONS, 'setCopySections');
    }

    public function setCopySections($aParams)
    {
        $this->copyList = $aParams;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->copyList) {
            return;
        }

        /**
         * Обойдем новые разделы и проставим правильную разводку категорий.
         */
        $aParams = Parameters::getList()->group('CategoryViewer')->asArray()->get();
        $aParams = ArrayHelper::map($aParams, 'name', 'value', 'parent');

        foreach ($this->copyList as $iSource => $iSection) {
            if (isset($aParams[$iSection]) and isset($aParams[$iSection]['category_from'])) {
                if ($aParams[$iSection]['category_from'] && isset($this->copyList[$aParams[$iSection]['category_from']])) {
                    Parameters::setParams($iSection, 'CategoryViewer', 'category_from', $this->copyList[$aParams[$iSection]['category_from']]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }
}
