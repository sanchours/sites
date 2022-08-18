<?php

namespace skewer\build\Adm\Tooltip;

use skewer\base\ui;
use skewer\build\Adm;
use skewer\components\ext\field\Wyswyg;
use yii\base\UserException;

/**
 * Система администрирования для модуля фотогаллереи.
 */
class Module extends Adm\Tree\ModulePrototype
{
    /**
     * Номер текущей страницы.
     *
     * @var int
     */
    protected $iPage = 0;

    /**
     * Сообщение об ошибке, если возникла.
     *
     * @var string
     */
    protected $sErrorText = '';

    /** @var bool Создавать альбом */
    protected $createAlbum = false;

    /** @var bool Флаг всплывающего окна */
    protected $popup = false;

    /* Methods */

    /**
     * Иницализация.
     */
    protected function preExecute()
    {
    }

    // func

    /**
     * Установка служебных данных.
     *
     * @param ui\state\BaseInterface $oIface
     */
    protected function setServiceData(ui\state\BaseInterface $oIface)
    {
    }

    // func

    /**
     * Вызывается в случае отсутствия явного обработчика.
     *
     * @return int
     */
    protected function actionInit()
    {
        $parts = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($parts['query'], $query);

        if (isset($query['tooltip_id']) && $query['tooltip_id']) {
            \Yii::$app->session->set('tooltips.choosen', $query['tooltip_id']);
        } else {
            \Yii::$app->session->set('tooltips.choosen', 0);
        }

        $this->title = null;

        if ($this->sErrorText) {
            return $this->showError();
        }

        return $this->actionGetList();
    }

    /**
     * @return int
     */
    protected function actionGetList()
    {
        $this->addCssFile('tooltip.css');

        $aItems = Adm\Tooltip\models\Tooltip::find()->asArray()->all();

        foreach ($aItems as &$item) {
            //подсовывание флага выбранности для интеграции с ckEditor
            if ($item['id'] === \Yii::$app->session->get('tooltips.choosen')) {
                $item['status'] = $item['id'] . '<span style="display:none">[+]</span>';
                $item['status_tmp'] = 1;
            } else {
                $item['status'] = $item['id'];
                $item['status_tmp'] = 0;
            }
        }

        $this->setCmd('show_list');

        $this->render(
            new Adm\Tooltip\view\GetList(
                [
                'items' => $aItems,
            ]
        )
        );

        return psComplete;
    }

    // func

    protected function actionForm()
    {
        /*Блокируем вызов модуля в визивиге*/
        Wyswyg::$bLockTooltipModule = true;

        $aData = $this->getInData();

        unset($aData['status']);
        if (isset($aData['id'])) {
            $iTooltipId = $aData['id'];
        } else {
            $iTooltipId = 0;
        }

        try {
            /* Получаем данные формата или заготовку под новый формат */
            $aValues = Adm\Tooltip\models\Tooltip::find()
                ->where(['id' => $iTooltipId])
                ->asArray()
                ->one();

            if (!$aValues) {
                $aValues = Adm\Tooltip\models\Tooltip::getDefaultValues();
            }

            $this->render(new Adm\Tooltip\view\Form([
                'iTooltipId' => $iTooltipId,
                'aValues' => $aValues,
            ]));
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        return psComplete;
    }

    // func

    protected function actionSave()
    {
        $aData = $this->get('data');

        if (!$aData['name']) {
            throw new UserException(\Yii::t('tooltip', 'enter_name'));
        }
        if (!$aData['text']) {
            throw new UserException(\Yii::t('tooltip', 'enter_text'));
        }
        $iTooltipId = ($aData['id']) ? $aData['id'] : false;

        if ($iTooltipId) {
            $oTooltip = Adm\Tooltip\models\Tooltip::find()
                ->where(['id' => $iTooltipId])
                ->one();
        } else {
            $oTooltip = new Adm\Tooltip\models\Tooltip();
        }

        if ($oTooltip) {
            $oTooltip->setAttributes($aData);
        }

        $oTooltip->save(false);

        $this->actionGetList();
    }

    // func

    protected function actionDelete()
    {
        $aData = $this->getInData();

        if (!isset($aData['id'])) {
            throw new UserException(\Yii::t('tooltip', 'no_id_to_delete'));
        }
        Adm\Tooltip\models\Tooltip::deleteAll(['id' => $aData['id']]);

        $this->actionGetList();
    }

    // func

    protected function actionCheck()
    {
        $aData = $this->getInData();

        if (!$aData['status_tmp']) {
            \Yii::$app->session->set('tooltips.choosen', 0);
        } else {
            $iId = $aData['id'];
            \Yii::$app->session->set('tooltips.choosen', $iId);
        }

        $this->actionGetList();
    }

    // func

    /**
     * Выдача ошибки.
     *
     * @return int
     */
    private function showError()
    {
        $this->title = \Yii::t('adm', 'error');

        $this->render(new Adm\Gallery\view\ShowError([
            'sErrorText' => $this->sErrorText,
        ]));

        return psComplete;
    }

    /**
     * Ищет в тексте упоминания подсказок и возвращает тот же текст с добавленным текстами подсказок.
     *
     * @param $sOutText
     *
     * @return mixed
     */
    public static function addTooltips($sOutText)
    {
        preg_match_all('/tooltip_id="[0-9]*"/', $sOutText, $matches, PREG_OFFSET_CAPTURE);

        $aTooltipIds = [];

        if (isset($matches[0])) {
            foreach ($matches[0] as $tooltip) {
                preg_match('/[0-9]+/', $tooltip[0], $id, PREG_OFFSET_CAPTURE);

                if (isset($id[0][0])) {
                    $aTooltipIds[] = $id[0][0];
                }
            }
        }

        $aTooltips = Adm\Tooltip\models\Tooltip::find()
            ->where(['id' => $aTooltipIds])
            ->asArray()
            ->all();

        $sTooltipText = \Yii::$app->getView()->renderFile(__DIR__ . '/view/Tooltips.php', ['tooltips' => $aTooltips]);

        $sOutText = str_replace('</body>', $sTooltipText . '</body>', $sOutText);

        return $sOutText;
    }
}
