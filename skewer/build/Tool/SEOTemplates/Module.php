<?php

namespace skewer\build\Tool\SEOTemplates;

use skewer\base\section\Tree;
use skewer\build\Catalog\Goods\SeoGood;
use skewer\build\Tool;
use skewer\build\Tool\SEOTemplates\view\CloneForm;
use skewer\components\catalog\Card;
use skewer\components\seo;
use yii\base\UserException;

/**
 * Модуль редактирования шаблонов для SEO параметров
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /**
     * @const название seo шаблона для каталожного раздела
     */
    const TITLE_SEOTEMPLATE4SECTION = 'SEO.name_template_catalog_section';

    /**
     * @const название seo шаблона для карточки
     */
    const TITLE_SEOTEMPLATE4CARD = 'SEO.name_template_catalog_card';

    public function actionInit()
    {
        $this->actionList();
    }

    /**
     * Список шаблонов.
     */
    public function actionList()
    {
        $this->setPanelName('', true);

        $aItems = seo\Template::getList();

        $aData = [];
        /** @var seo\TemplateRow $oItem */
        foreach ($aItems as $oItem) {
            $aTmp = $oItem->getData();
            $aTmp['name'] = self::getNameTemplate($oItem);
            $aTmp['fullAlias'] = ($oItem->extraalias) ? $oItem->alias . ':' . $oItem->extraalias : $oItem->alias;
            $aTmp['title'] = $aTmp['fullAlias'];
            $aData[] = $aTmp;
        }

        $this->render(new view\TemplatesList([
            'data' => $aData,
        ]));
    }

    /**
     * ФОрма редактирование seo шаблона.
     */
    public function actionEditForm()
    {
        $iTplId = $this->getInDataValInt('id', 0);
        $oTpl = seo\Template::find($iTplId);

        /** @var seo\TemplateRow $oTpl */
        if (!$iTplId || !$oTpl) {
            throw new UserException(\Yii::t('SEO', 'template_not_found'));
        }

        $oTpl->info = seo\Template::getLabelsInfo();
        $oTpl->name = self::getNameTemplate($oTpl);

        $oSeo = seo\SeoPrototype::getInstanceByAlias($oTpl->alias);

        $this->setPanelName(\Yii::t('SEO', 'editseo'), true);

        if ($oSeo === null) {
            throw new UserException(\Yii::t('SEO', 'unknown_template'));
        }

        $this->render(new view\EditForm([
            'tpl' => $oTpl,
            'seo' => $oSeo,
        ]));
    }

    /** Состояние клонирования seo-шаблона */
    public function actionCloneForm()
    {
        $this->render(new CloneForm());
    }

    /** Метод ajax-обновления формы клонирования шаблона */
    public function actionUpdateCloneForm()
    {
        $aData = $this->get('formData', []);

        $this->render(
            new CloneForm([
            'aValues' => $aData,
            ])
        );
    }

    /**
     * Метод клонирования seo-шаблона.
     *
     * @throws UserException
     */
    public function actionClone()
    {
        $aData = $this->getInData();

        if (empty($aData['section']) && empty($aData['card'])) {
            throw new UserException(\Yii::t('SEO', 'must_specify_one_parameter'));
        }
        if ((!empty($aData['section']))) {
            $sExtraAlias = $aData['section'];
            $sNameTemplate = self::TITLE_SEOTEMPLATE4SECTION;
        } else {
            $sExtraAlias = $aData['card'];
            $sNameTemplate = self::TITLE_SEOTEMPLATE4CARD;
        }

        if ($oRow = seo\Template::getByAliases(SeoGood::getAlias(), $sExtraAlias)) {
            throw new UserException(\Yii::t('SEO', 'template_already_exist'));
        }
        /** @var seo\TemplateRow $oRow */
        $oRow = seo\Template::getByAliases(SeoGood::getAlias());

        $oRow->id = 'NULL';
        $oRow->name = $sNameTemplate;
        $oRow->alias = SeoGood::getAlias();
        $oRow->extraalias = $sExtraAlias;
        $oRow->undelitable = 0;
        $oRow->save();

        $this->actionInit();
    }

    public function actionDelete()
    {
        try {
            $aData = $this->getInData();

            $iTplId = $aData['id'] ?? 0;

            if (!$iTplId) {
                $this->addError('SEO Tempate not found!');
            }

            $oTpl = seo\Template::find($iTplId);

            if ($oTpl->undelitable) {
                throw new \Exception(\Yii::t('SEO', 'undelitable_template'));
            }
            if (!$oTpl->delete()) {
                throw new \Exception(\Yii::t('SEO', 'template_not_deleted'));
            }
            $this->addModuleNoticeReport(\Yii::t('SEO', 'template_deleting'), ['id' => $iTplId]);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();

        return psComplete;
    }

    public function actionUpdate()
    {
        try {
            $aData = $this->getInData();

            $iTplId = $aData['id'] ?? 0;

            if (!$iTplId) {
                $this->addError('SEO Tempate not found!');
            }

            $oTpl = seo\Template::find($iTplId);

            $oTpl->setData($aData);

            if (!$oTpl->save()) {
                throw new \Exception(\Yii::t('SEO', 'template_not_saved'));
            }
            $this->addModuleNoticeReport(\Yii::t('SEO', 'template_changing'), ['id' => $iTplId]);
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();

        return psComplete;
    }

    /**
     * Получить имя шаблона.
     *
     * @param seo\TemplateRow $oTemplate
     *
     * @return string
     */
    public static function getNameTemplate(seo\TemplateRow $oTemplate)
    {
        if ($oTemplate->name === self::TITLE_SEOTEMPLATE4CARD) {
            $sCardTitle = Card::getTitle($oTemplate->extraalias);

            return  \Yii::tSingleString($oTemplate->name, ['title' => $sCardTitle]);
        }
        if ($oTemplate->name === self::TITLE_SEOTEMPLATE4SECTION) {
            $sSectionTitle = Tree::getSectionTitle($oTemplate->extraalias, true);

            return  \Yii::tSingleString($oTemplate->name, ['title' => $sSectionTitle]);
        }

        return \Yii::tSingleString($oTemplate->name);
    }
}
