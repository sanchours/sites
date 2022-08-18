<?php

namespace skewer\build\Page\Title;

use skewer\base\section\Tree;
use skewer\base\site;
use skewer\base\site_module;

class Module extends site_module\page\ModulePrototype
{
    public $hideTitle;
    public $altTitle = '';

    /** @var string шаблон title */
    public $template = 'view.twig';

    public function init()
    {
        if (($sImpTitle = $this->getEnvParam('title4section', null)) !== null) {
            $this->altTitle = $sImpTitle;
        }
        $this->setParser(parserTwig);
    }

    public function execute()
    {
        $oProcessContent = site\Page::getMainModuleProcess();
        if ($oProcessContent) {
            if ($oProcessContent->getStatus() == psNew) {
                return psWait;
            }
        }

        if (!empty($this->altTitle)) {
            $this->setData('title', $this->altTitle);
        } else {
            // если null, а не пустая строка, а не false, то скрытие делается принудительно
            if ($this->altTitle === false) {
                $this->hideTitle = true;
            }

            $oSection = Tree::getSection($this->sectionId());
            $this->setData('title', $oSection ? $oSection->title : '');
        }

        $this->setData('hideTitle', $this->hideTitle);
        $this->setTemplate($this->template);

        return psComplete;
    }

    /** {@inheritdoc} */
    public function canHaveContent()
    {
        return false;
    }
}//class
