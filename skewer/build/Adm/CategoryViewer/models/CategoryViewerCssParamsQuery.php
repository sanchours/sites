<?php

namespace skewer\build\Adm\CategoryViewer\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[CategoryViewerCssParams]].
 *
 * @see CategoryViewerCssParams
 */
class CategoryViewerCssParamsQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     *
     * @return array|CategoryViewerCssParams[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     *
     * @return array|CategoryViewerCssParams
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function section($iSectionId)
    {
        return $this->andWhere(['sectionId' => $iSectionId]);
    }

    public function group($sGroup)
    {
        return $this->andWhere(['group' => $sGroup]);
    }
}
