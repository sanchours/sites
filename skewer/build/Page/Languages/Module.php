<?php

namespace skewer\build\Page\Languages;

use skewer\base\site\Site;
use skewer\base\site_module;
use skewer\components\i18n\Languages;
use yii\helpers\ArrayHelper;

class Module extends site_module\page\ModulePrototype
{
    /** @var string шаблон выбора языка */
    public $template = 'languageSwitch.php';

    public function init()
    {
        $this->setParser(parserPHP);
    }

    public function execute()
    {
        $oMain = $this->getProcess('out');
        if ($oMain->getStatus() != psComplete) {
            return psWait;
        }

        $aLanguages = ArrayHelper::index(Languages::getAllActive(), 'name');

        $names = \Yii::$app->sections->getByValue($this->sectionId());

        $aLinks = [];
        if ($names) {
            $name = array_shift($names);
            $aLinks = \Yii::$app->sections->getValues($name);
        }

        if (isset($aLanguages[\Yii::$app->language])) {
            $aLanguages[\Yii::$app->language]['current'] = true;
        }

        $aLangLinks = [];

        foreach (\Yii::$app->sections->getValues('main') as $lang => $main) {
            if (isset($aLanguages[$lang])) {
                $aLanguages[$lang]['main'] = $main;
            }

            if (isset($aLinks[$lang])) {
                $aLangLinks[] = [
                    'hreflang' => $lang,
                    'href' => Site::httpDomain() . \Yii::$app->router->rewriteURL('[' . $aLinks[$lang] . ']'),
                ];
            }
        }

        $oMain->setData('aLangLinks', $aLangLinks);

        $this->setData('Languages', $aLanguages);
        $this->setTemplate($this->template);

        return psComplete;
    }
}
