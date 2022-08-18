<?php

namespace skewer\build\Page\Text;

use skewer\base\section\Page;
use skewer\base\site_module;
use skewer\components\design\Design;
use yii\helpers\ArrayHelper;

/**
 * Публичный модуль добавления текста, обернутого в шаблон в зону вывода.
 *
 * Все параметры из метки кроме системных (см. параметр $aSystemNames)
 * будут переданы на парсинг в шаблон
 */
class Module extends site_module\page\ModulePrototype
{
    public $template;

    /**
     * Набор системных имен параметрв,
     * которые в парсинг не передаются.
     *
     * @var string[]
     */
    private static $aSystemNames = [
        'object',
        '.layout',
        '.title',
        'template',
    ];

    public function init()
    {
        $this->setParser(parserPHP);

        return true;
    }

    // func

    public function execute()
    {
        // достаем все параметры метки
        $aParameters = Page::getByGroup($this->getLabel());

        // Данные перекрытые из других модулей
        $aEnvData = $this->getEnvDataByLabel($this->getLabel());

        if ($aEnvData) {
            $aParameters = $aEnvData;
        }

        $bDesignMode = Design::modeIsActive();

        // перебираем их
        foreach ($aParameters as &$aParam) {
            // системные параметры пропускаем
            if (in_array($aParam['name'], self::$aSystemNames)) {
                continue;
            }

            //Вырезаем дизайнерские метки из статика для блоков из генератора контента
            if (!$bDesignMode and !empty($aParam['show_val'])) {
                //Поиск вхождения всех строк с тегом sktag с любыми данными внутри
                preg_match_all('/sktag=\"([^\"]*)\"/im', $aParam['show_val'], $aPregRes);
                if (!empty($aPregRes[0])) {
                    foreach ($aPregRes as $replaceText) {
                        $aParam['show_val'] = str_replace($replaceText, ' ', $aParam['show_val']);
                    }
                }
            }
            // остальные добавляем в вывод
            $this->setData($aParam['name'], $aParam);
        }
        unset($aParam);

        $this->setTemplate($this->template);

        return psComplete;
    }

    /**
     * Получить параметры окружения для метки вызова данного модуля.
     *
     * @param string $sLabel - метка вызова
     * @param mixed $mDefault - значение по умолчанию
     *
     * @return mixed
     */
    public function getEnvDataByLabel($sLabel, $mDefault = null)
    {
        $aModuleParams = \Yii::$app->environment->get('moduleParams');

        if (!$aModuleParams) {
            return $mDefault;
        }

        $aLabelsToData = ArrayHelper::map($aModuleParams, 'label', 'data', 'nameModule');

        $aData4Label = ArrayHelper::getValue($aLabelsToData, self::getNameModule() . ".{$sLabel}", $mDefault);

        return ($aData4Label) ? $aData4Label : $mDefault;
    }
}
