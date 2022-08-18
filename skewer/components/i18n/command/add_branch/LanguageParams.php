<?php

namespace skewer\components\i18n\command\add_branch;

use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\section\params\Type;

/**
 * Команда, отмечающая параметры в шаблоне, которые будут тянуться из языковых веток.
 */
class LanguageParams extends Prototype
{
    /** @var int Id Секции откуда копируются параметры */
    private $iSourceSection;

    /**
     * Список параметров настроек сайта, которые должны быть уникальные для каждой языковой ветки.
     *
     * @var array
     */
    protected $sysParameters = [
        'site_name',
        'site_nlogo',
    ];

    /** @var array имена групп, содержащие параметры source, которые должны быть уникальными для каждой языковой ветки */
    protected $aSysTextBlock = [
        'copyright',
        'contacts',
        'counters',
        'headtext1',
        'headtext2',
        'headtext3',
        'headtext4',
        'headtext5',
        'copyright_dev',
        'footertext4',
        'footertext5',
    ];

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $this->iSourceSection = \Yii::$app->sections->getValue(Page::LANG_ROOT, $this->getSourceLanguageName());
        } catch (\Exception $e) {
            $this->iSourceSection = \Yii::$app->sections->root();
        }

        /** @var \skewer\base\section\models\ParamsAr[] $aParams */
        $aParams = \skewer\base\section\models\ParamsAr::find()
            ->where(['group' => Parameters::settings, 'parent' => $this->iSourceSection, 'name' => $this->sysParameters])
            ->all();

        /* Добавить языковые параметры */

        /** @var \skewer\base\section\models\ParamsAr[] $aLangParams */
        $aLangParams = \skewer\base\section\models\ParamsAr::find()
            ->where(['access_level' => Type::paramLanguage])
            ->orWhere(['access_level' => -Type::paramLanguage])
            ->all();

        /** @var \skewer\base\section\models\ParamsAr[] $aFoundLangParams */
        $aFoundLangParams = [];
        foreach ($aLangParams as $iKey => $oLangParam) {
            if ($oParam = Parameters::getByName($this->iSourceSection, $oLangParam->group, $oLangParam->name)) {
                // Защита через уникальный ключ массива от повторения языковых параметров в разных разделах
                $aFoundLangParams[$oLangParam->name . '|' . $oLangParam->group] = $oParam;
            }
        }

        /** @var \skewer\base\section\models\ParamsAr[] $aParams */
        $aParamsTextBlock = \skewer\base\section\models\ParamsAr::find()
            ->where(['group' => $this->aSysTextBlock, 'parent' => $this->iSourceSection, 'name' => 'source'])
            ->all();

        $aParams = array_merge($aParams, $aFoundLangParams, $aParamsTextBlock);

        foreach ($aParams as $oParam) {
            // Создать языковой параметр для нужного системного параметра, если его ещё нет
            if ((($oParam->group == Parameters::settings) and (in_array($oParam->name, $this->sysParameters))) ||
                (in_array($oParam->group, $this->aSysTextBlock) and ($oParam->name == 'source'))) {
                Parameters::setParams(\Yii::$app->sections->tplNew(), $oParam->group, $oParam->name, '', '', $oParam->title, Type::paramLanguage);
            }

            /* Скопировать/обновить языковой параметр в создаваемую языковую ветку */

            if (abs($oParam->access_level) == Type::paramLanguage) {
                $oParam->access_level = Type::paramSystem;
            }

            $oNewParam = Parameters::copyToSection($oParam, $this->getRootSection());
            if (!$oNewParam) {
                $oNewParam = Parameters::getByName($this->getRootSection(), $oParam->group, $oParam->name);
                if ($oNewParam) {
                    $oNewParam->value = $oParam->value;
                    $oNewParam->show_val = $oParam->show_val;
                    $oNewParam->title = $oParam->title;
                    $oNewParam->access_level = $oParam->access_level;
                    $oNewParam->save();
                }
            }

            // Если копируемый параметр это параметр из рутовского раздела, то удалить его по причине большей ненадобности
            if ($oParam->parent == \Yii::$app->sections->root()) {
                $oParam->delete();
            }
        }

        /* Порядок полей */
        $this->setOrderField();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback()
    {
    }

    private function setOrderField()
    {
        $aOrderFields = [
            'site_name',
            'site_nlogo',
            'copyright',
            'contacts',
            'headtext1',
            'headtext2',
            'headtext3',
            'headtext4',
            'headtext5',
        ];

        /**
         * Сделаем человеческий порядок.
         */
        $oParam = Parameters::getByName($this->getRootSection(), Parameters::settings, 'field_order');
        if (!$oParam) {
            $oParam = Parameters::createParam([
                'parent' => $this->getRootSection(),
                'group' => Parameters::settings,
                'name' => 'field_order',
            ]);
        }

        if ($oParam) {
            $val = $oParam->show_val;
            $val = trim($val);
            $val = preg_replace('/\x0a+|\x0d+/Uims', '', $val);
            $aFields = explode(';', $val);

            if ($aFields) {
                foreach ($aOrderFields as $sField) {
                    if (($key = array_search('.:' . $sField, $aFields)) !== false) {
                        unset($aFields[$key]);
                    }
                }
            }

            foreach (array_reverse($aOrderFields) as $sField) {
                array_unshift($aFields, '.:' . $sField);
            }

            $oParam->show_val = implode(";\r\n", $aFields);
            $oParam->save();
        }
    }
}
