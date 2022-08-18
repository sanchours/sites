<?php

namespace skewer\build\Adm\Tree;

use skewer\base\section\models\TreeSection;
use skewer\base\section\Tree;
use skewer\base\ui\builder\FormBuilder;
use skewer\build\Page\Main\Seo;
use skewer\build\Tool\SeoGen\Api as SeoGenApi;
use skewer\build\Tool\SeoGen\importer\Api as ImporterApi;
use skewer\build\Tool\SeoGen\importer\Prototype;
use skewer\components\seo\Service;
use skewer\helpers\Transliterate;
use yii\helpers\ArrayHelper;

class Importer extends Prototype
{
    public $arrayConnectionIds = [];

    /** @var int */
    public $iSectionId;

    /** @var bool */
    public $bEnableStaticContents;

    /**
     * {@inheritdoc}
     */
    public function validateParams($aData, &$aErrors)
    {
        try {
            $sFile = ArrayHelper::getValue($aData, 'file', '');

            if (!$sFile) {
                throw new \Exception('Не загружен файл');
            }

            if (!preg_match('{[^\.]\.(xls|xlsx)$}i', $sFile)) {
                throw new \Exception('Загрузите файл с расширением [.xls|xlsx]');
            }

            $iSectionId = ArrayHelper::getValue($aData, 'sectionId', 0);

            if (!$iSectionId) {
                throw new \Exception('Укажите раздел, в который будут импортированы данные');
            }
        } catch (\Exception $e) {
            $aErrors[] = $e->getMessage();

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        $aFields = [
            'type',
            'id',
            'parent',
            'template',
            'visible',
            'name',
            'alias',
            'url',
            'h1',
            'title',
            'description',
            'keywords',
        ];

        if ($this->bEnableStaticContents) {
            $aFields = array_merge($aFields, [
                'staticContent',
                'staticContent2',
            ]);
        }

        return $aFields;
    }

    /**
     * {@inheritdoc}
     */
    public function validateDataFields(&$aData, &$aErrors)
    {
        if (($iTypeVisible = SeoGenApi::getIdTypeVisible($aData['visible'])) === false) {
            $aErrors[] = sprintf('Неизвестный тип видимости [%s]', $aData['visible']);
        } else {
            $aData['visible'] = $iTypeVisible;
        }

        if (($iTemplateId = SeoGenApi::getIdTemplate($aData['template'])) === false) {
            $aErrors[] = sprintf('Неизвестный тип шаблона [%s]', $aData['template']);
        } else {
            $aData['template'] = $iTemplateId;
        }

        if (empty($aData['url']) && empty($aData['name'])) {
            $aErrors[] = 'Невозможно создать раздел с пустым названием';
        }

        if (!empty($aData['url']) && empty($aData['id'])) {
            $aErrors[] = 'Невозможно обновить раздел. Не указан id';
        }

        return empty($aErrors);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableSeoEntity()
    {
        return [
            Seo::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initParams($aParams)
    {
        $this->iSectionId = (int) ArrayHelper::getValue($aParams, 'sectionId', 0);
        $this->bEnableStaticContents = (bool) ArrayHelper::getValue($aParams, 'enable_staticContents', false);
        $this->arrayConnectionIds = ArrayHelper::getValue($aParams, 'arrayConnectionIds', []);
    }

    public function saveRow($aBuffer, &$aErrors)
    {
        // Создаем раздел
        if (empty($aBuffer['url'])) {
            // создание и обновление
            $this->createSection($aBuffer, $aErrors);

            if (!$aErrors) {
                $sResultCode = self::ADDED_STATUS;
            } else {
                $sResultCode = self::NOT_ADDED_STATUS;
            }
        } else {
            // обновление раздела
            $iUpdatedSectionId = $this->updateRecord($aBuffer, $aErrors);

            if ($iUpdatedSectionId && !$aErrors) {
                $this->addLinkSections($aBuffer['id'], $iUpdatedSectionId);

                $sResultCode = self::UPDATE_STATUS;
            } else {
                $sResultCode = self::NOT_UPDATE_STATUS;
            }
        }

        return $sResultCode;
    }

    /** Сохранение одной записи(в режиме создания разделов)
     * @param array $aBuffer - массив с данными
     * @param array $aErrors
     */
    private function createSection($aBuffer, &$aErrors)
    {
        /** @var bool Разрешить коллизии разделов? (seo-шники пока запретили) */
        $bAllowCollisionSection = false;

        try {
            $sTitleSection = mb_substr($aBuffer['name'], 0, 100);

            if ($aBuffer['alias']) {
                $sAlias = $aBuffer['alias'];
            } else {
                // Транслитерируем название и получаем alias
                $sAlias = Transliterate::generateAlias($sTitleSection);
                $sAlias = mb_substr($sAlias, 0, 60);
            }

            if ($aBuffer['parent']) {
                if (($iParentSection = $this->getSystemSectionId($aBuffer['parent'])) == false) {
                    throw new \Exception(sprintf('%s - Не найден родительский раздел', $aBuffer['name']));
                }
            } else {
                $iParentSection = $this->iSectionId;
            }

            $sNewAlias = Service::generateAlias($sAlias, 0, $iParentSection, 'Page');

            // Коллизии запрещены и произошла коллизия
            if (!$bAllowCollisionSection && Service::$bAliasChanged) {
                $sParentPath = Tree::getSectionAliasPath($iParentSection);
                throw new \Exception(sprintf('%s - Объект с alias [ %s ] уже существует в разделе [ %s ]', $aBuffer['name'], $sAlias, $sParentPath));
            }

            // Добавляем раздел
            $oNewSection = new TreeSection([
                'title' => $sTitleSection,
                'alias' => $sNewAlias,
                'parent' => $iParentSection,
                'visible' => $aBuffer['visible'],
            ]);

            if (!$oNewSection->save()) {
                throw new \Exception(sprintf('%s - Ошибки валидации при сохранении раздела', $aBuffer['name']));
            }

            $oNewSection->setTemplate($aBuffer['template']);

            // сохраняем связку [id из файла => id в cms]
            $this->addLinkSections($aBuffer['id'], $oNewSection->id);

            // Пишем данные в раздел
            ImporterApi::updateEntity(Seo::className(), $oNewSection->id, $oNewSection->id, $aBuffer);
        } catch (\Exception $e) {
            $aErrors[] = $e->getMessage();
        }
    }

    /**
     * Отдаст id раздела в CMS по id разделу указанному в файле.
     *
     * @param $iSectionIdFromFile
     *
     * @return bool Вернет false если соответствующий id не найден
     */
    private function getSystemSectionId($iSectionIdFromFile)
    {
        return isset($this->arrayConnectionIds[$iSectionIdFromFile]) ? $this->arrayConnectionIds[$iSectionIdFromFile] : false;
    }

    /** Добавить связку id раздела в файле => id раздела в CMS
     * @param int $iIdFromFile - id раздела в файле
     * @param int $iSystemId - id раздела в CMS
     */
    private function addLinkSections($iIdFromFile, $iSystemId)
    {
        $this->arrayConnectionIds[$iIdFromFile] = $iSystemId;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams4Save()
    {
        return [
            'arrayConnectionIds' => $this->arrayConnectionIds,
        ];
    }

    public function beforeInitImport()
    {
        $this->loadSectionIds();
    }

    public function loadSectionIds()
    {
        $aSections = array_merge(Tree::getSubSections($this->iSectionId, true, true), [$this->iSectionId]);

        $this->arrayConnectionIds = array_combine($aSections, $aSections);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sections';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Разделы';
    }

    /**
     * {@inheritdoc}
     */
    public function buildFieldInForm(FormBuilder $oForm)
    {
        $oForm
            ->fieldString('sectionId', 'Введите Id раздела')
            ->fieldCheck('enable_staticContents', 'Обновить данные из полей "текст раздела" и "текст раздела 2"');
    }

    /**
     * {@inheritdoc}
     */
    public function doSkipRow($aData)
    {
        return false;
    }
}
