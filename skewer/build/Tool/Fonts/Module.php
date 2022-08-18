<?php

namespace skewer\build\Tool\Fonts;

use skewer\base\section\Tree;
use skewer\base\ui\ARSaveException;
use skewer\build\Adm\CategoryViewer\models\CategoryViewerCssParams;
use skewer\build\Page\CategoryViewer;
use skewer\build\Tool;
use skewer\components\design\model\Params;
use skewer\components\fonts\Api;
use skewer\components\fonts\models\Fonts;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Настройка дополнительных шрифтов для сайта
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    /**
     * {@inheritdoc}
     */
    protected function actionInit()
    {
        $aFontsFamily = $this->getListFonts();

        if (!$aFontsFamily) {
            throw new UserException('Нет доступных шрифтов');
        }

        $this->render(new Tool\Fonts\view\Index([
            'aFontsFamily' => $aFontsFamily,
        ]));
    }

    /**
     * Форма редактирования.
     */
    protected function actionShow()
    {
        $aData = $this->get('data');

        $iItemId = $aData['id'] ?? $this->getInnerDataInt('id', 0);

        /** @var Fonts $oRow */
        if (!($oRow = Fonts::findOne(['id' => $iItemId]))) {
            throw new UserException(\Yii::t('news', 'error_row_not_found', [$iItemId]));
        }
        if ($oRow->isInner()) {
            throw new UserException('Системные шрифты редактировать запрещено!');
        }

        $aDirs = Api::getDirectoriesDownloadedFonts();

        $this->render(new view\AddFontForm([
            'item' => $oRow,
            'aDirs' => array_combine($aDirs, $aDirs),
        ]));
    }

    /**
     * Вернет тестовое описание того, где используется шрифт
     *
     * @param string $sFontValue - шрифт
     *
     * @return string
     */
    private static function findUsageFont($sFontValue)
    {
        /** @var $aFonts Params[] */
        $aFonts = Params::find()
            ->where(['type' => 'family'])
            ->andWhere(['like', 'value', $sFontValue])
            ->all();

        $aUsageInDesignMode = [];
        foreach ($aFonts as $oFont) {
            $aUsageInDesignMode[] = $oFont->buildGroupPath();
        }

        $aWidgets = CategoryViewer\Api::getAvailableWidgets();

        $aParamsWidgets = [];

        foreach ($aWidgets as $sWidget) {
            $aParamsWidgets = array_merge($aParamsWidgets, CategoryViewer\Api::getCssParamsByWidget($sWidget));
        }

        $aFontsParamName = [];
        foreach ($aParamsWidgets as $aParamsWidget) {
            if ($aParamsWidget['typeParam'] == 'family') {
                $aFontsParamName[] = $aParamsWidget['paramName'];
            }
        }

        $aFontsParamName = array_unique($aFontsParamName);

        $aCategoryViewerCssParams = CategoryViewerCssParams::find()
            ->where(['paramName' => $aFontsParamName])
            ->where(['value' => $sFontValue])
            ->asArray()->all();

        $aSections = ArrayHelper::getColumn($aCategoryViewerCssParams, 'sectionId');

        $aUsageInCategoryViewer = [];
        foreach ($aSections as $iId) {
            if ($aSection = Tree::getCachedSection($iId)) {
                $aUsageInCategoryViewer[] = '[' . $iId . '] ' . $aSection['title'];
            }
        }

        $sText = \Yii::$app->view->renderPhpFile(
            __DIR__ . \DIRECTORY_SEPARATOR . 'templates' . \DIRECTORY_SEPARATOR . 'usage_font.php',
            [
                'aUsageInDesignMode' => $aUsageInDesignMode,
                'aUsageInCategoryViewer' => $aUsageInCategoryViewer,
            ]
        );

        return $sText;
    }

    /** Переключить активность шрифта */
    protected function actionToggleActive()
    {
        $aFamilyFont = $this->get('data');

        if ($sText = self::findUsageFont($aFamilyFont['name'])) {
            $this->addError(\Yii::t('fonts', 'font_is_used'), $sText, 8000);
        } else {
            $sFieldName = $this->get('field_name');

            /** @var Fonts $oRow */
            if (!($oRow = Fonts::findOne(['id' => $aFamilyFont['id']]))) {
                throw new UserException(\Yii::t('news', 'error_row_not_found', [$aFamilyFont['id']]));
            }
            $oRow->{$sFieldName} = $this->getInDataVal($sFieldName);

            if (!$oRow->save()) {
                throw new ARSaveException($oRow);
            }
            $this->actionInit();
        }
    }

    /** Интерфейс добавления шрифта */
    protected function actionAddFont()
    {
        $aDirs = Api::getDirectoriesDownloadedFonts();

        $this->render(new Tool\Fonts\view\AddFontForm([
            'aDirs' => array_combine($aDirs, $aDirs),
            'item' => Fonts::getNewRow(),
        ]));
    }

    /** Сохранение шрифта */
    protected function actionSaveFont()
    {
        $aFamilyFont = $this->get('data');

        if (!empty($aFamilyFont['id'])) {
            $oFont = Fonts::findOne($aFamilyFont['id']);
        } else {
            $oFont = Fonts::getNewRow();
        }

        $oFont->setAttributes($aFamilyFont);

        if (!$oFont->save()) {
            throw new ARSaveException($oFont);
        }

        $this->actionInit();
    }

    /**
     * Получить список шрифтов.
     *
     * @return array
     */
    protected function getListFonts()
    {
        $aFontsFamily = Fonts::find()->asArray()->all();

        foreach ($aFontsFamily as &$item) {
            if ($item['type'] == Api::TYPE_FONT_EXTERNAL) {
                $sPath = Api::getDirPathDownloadedFonts() . $item['path'];
            } else {
                $sPath = Api::getDirPathSystemFonts() . $item['path'];
            }

            // Флаг, указывает, что директория шрифта существует
            $item['correct'] = is_dir($sPath) ? '1' : '0';
        }

        return $aFontsFamily;
    }

    /**
     * Удаляет запись.
     */
    protected function actionDelete()
    {
        $iItemId = $this->getInDataValInt('id', 0);

        if (!($oFont = Fonts::findOne($iItemId))) {
            throw new UserException(\Yii::t('news', 'error_row_not_found', [$iItemId]));
        }
        if ($oFont->isInner()) {
            throw new UserException('Системные шрифты нельзя удалять!');
        }

        if ($sText = self::findUsageFont($oFont->name)) {
            $this->addError(\Yii::t('fonts', 'font_is_used'), $sText, 8000);
        } else {
            $oFont->delete();

            // вывод списка
            $this->actionInit();
        }
    }

    /** Добавить папку для загружаемых шрифтов */
    protected function actionAddFolder()
    {
        $bRes = Api::createDirectoryDownloadedFonts();

        if ($bRes) {
            $this->addMessage('Cообщение', 'Директория шрифтов создана - ' . str_replace(ROOTPATH, '', Api::getDirPathDownloadedFonts()));
        } else {
            $this->addMessage('Cообщение', 'Не удалось создать директорию шрифтов или выставить необходимые права');
        }
    }
}
