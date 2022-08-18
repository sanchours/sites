<?php

namespace skewer\build\Page\Poll;

use skewer\base\section\Tree;
use skewer\base\site_module;
use skewer\build\Tool\Poll\models\Poll;

class Module extends site_module\page\ModulePrototype implements site_module\Ajax
{
    public $Location = 'right';
    /* @var null|Api */
    public $oPollApi;
    private $template;
    private $sFileAnswerTemplate;
    /**
     * @var int ID главной страницы
     */
    private $defaultSection = 0;

    public function init()
    {
        $this->oPollApi = new Api();
        $this->setParser(parserTwig);
        $this->defaultSection = \Yii::$app->sections->main();
        $this->template = 'poll_' . $this->Location . '.twig';
        $this->sFileAnswerTemplate = 'answer_' . $this->Location . '.twig';
    }

    public function execute()
    {
        $sCmd = $this->getStr('cmd', 'show');

        switch ($sCmd) {
            case 'show':
            default:

                $aParams = [];
                $aParams['location'] = $this->Location;
                $aParams['current_section'] = $this->sectionId();

                if ($this->sectionId() == $this->defaultSection) {
                    $aPolls = $this->oPollApi->getPollsOnMain($aParams);
                } else {
                    $aParentSections = Tree::getSectionParents($this->sectionId());
                    if ($aParentSections) {
                        $aParams['parent_sections'] = implode(',', $aParentSections);
                    } else {
                        $aParams['parent_sections'] = $this->sectionId();
                    }

                    $aPolls = $this->oPollApi->getPollsOnInternal($aParams);
                }

                if ($aPolls) {
                    $this->setData('aPolls', $aPolls);
                }

                $this->setTemplate($this->template);

                \Yii::$app->router->setLastModifiedDate(Poll::getMaxLastModifyDate());

                return psComplete;

            break;

            case 'vote_ajax':

                $iPollId = $this->getInt('poll');
                $iAnswerId = $this->getInt('answer');
                $aOut = [];

                $this->oPollApi->addVote(['poll' => $iPollId, 'answer' => $iAnswerId]);

                $aOut['aPoll'] = $this->oPollApi->getPollHeader($iPollId);
                $aAnswers = $this->oPollApi->getAnswers($iPollId);
                $aOut['aAnswers'] = $aAnswers['items'];
                $aOut['iAllCount'] = $aAnswers['answers_count'];

                $sRendered = $this->renderTemplate($this->sFileAnswerTemplate, $aOut);
                $this->setData('out', $sRendered);

                return psRendered;
            break;
        }
    }
}// class
