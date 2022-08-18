<?php

namespace skewer\build\Tool\Labels;

use skewer\build\Tool\Labels\models\Labels;
use skewer\build\Tool\Labels\view\Index;
use skewer\build\Tool\Labels\view\Show;
use skewer\build\Tool\LeftList\ModulePrototype;
use skewer\components\regions\models\RegionLabels;
use skewer\components\regions\RegionHelper;
use yii\base\UserException;

class Module extends ModulePrototype
{
    const CHANGE_ALIAS = 'change_alias';

    /**
     * Список меток.
     */
    protected function actionInit()
    {
        $search = $this->getStr('search');

        $labels = $search === null
            ? Labels::getAll()
            : Labels::getSearchLabels($search);

        $labels = array_map(static function ($label) {
            /* @var $label Labels */
            $label->default = trim(htmlspecialchars($label->default));

            return $label;
        }, $labels);

        return $this->render(new Index([
            'labels' => $labels,
            'search' => $search,
        ]));
    }

    /**
     * Добавление или редактирование меток.
     */
    protected function actionShow()
    {
        $id = $this->getInDataVal('id', '');

        $title = $id
            ? \Yii::t('labels', 'title_edit')
            : \Yii::t('labels', 'title_add');

        \Yii::$app->session->removeFlash(self::CHANGE_ALIAS);

        return $this->render(new Show([
            'title' => $title,
            'label' => Labels::getByIdAsArray($id),
        ]));
    }

    /**
     * Сохранение.
     *
     * @throws UserException
     */
    protected function actionSave()
    {
        $params = $this->get('data');
        $id = $this->getInDataVal('id', '');

        $label = Labels::getById($id);
        $label->setAttributes($params);

        $changeAlias = \Yii::$app->session->getFlash(self::CHANGE_ALIAS);
        if ($id && $label->isAttributeChanged('alias') && $changeAlias === null) {
            $old = $label->getOldAttribute('alias');
            $this->addWarning(
                \Yii::t('labels', 'change_alias_title'),
                \Yii::t('labels', 'change_alias_message', $old)
            );

            \Yii::$app->session->addFlash(self::CHANGE_ALIAS, $params['alias']);

            return psComplete;
        }

        if ($label->validate()) {
            $label->save();
        } else {
            $errors = $label->getFirstErrors();
            if ($errors) {
                $errors = array_values($errors);
                throw new UserException($errors[0]);
            }
        }

        return $this->actionInit();
    }

    /**
     * Удаление данных из таблиц.
     *
     * @throws \Exception
     */
    protected function actionDelete()
    {
        $id = $this->getInDataVal('id', '');

        $label = Labels::findOne($id);

        if ($label) {
            $label->delete();

            //Удаление всех заполненных данных в регионах
            if (RegionHelper::isInstallModuleRegion()) {
                RegionLabels::deleteByLabelId($id);
            }

            return $this->actionInit();
        }

        throw new UserException(\Yii::t('labels', 'not_label'));
    }
}
