<?php

namespace skewer\build\Adm\ParamSettings;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\base\section\params\Type;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\ui;
use skewer\helpers\Transliterate;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Прототип управляющего класса для настройки параметров модуля из
 * специального интерфейса на главной странице (таб "Настройка параметров").
 */
abstract class Prototype
{
    /**
     * @var string Имя группы параметров
     */
    public $sGroupName = '';

    /**
     * @var string Заголовок группы параметров (параметр .title)
     */
    public $sLabelTitle = '';

    /**
     * @var int Id Раздела
     */
    public $iParent = 0;

    /** Группы параметров в формате '<имя группы>' => '<заголовок группы>' */
    public static $aGroups = [];

    /** Индекс сортировки текущией группы в общем списке групп */
    public static $iGroupSortIndex = 0;

    /**
     * Отдает массив с элементами для редактирования.
     * Каждый элемент идет в виде массива с параметрами:
     *      * name - имя параметра
     *      * group - имя группы (для объединения параметров по общим признакам)
     *      * [label] - если задано, то будет использовано в качестве сетки параметра (поле group),
     *      *       если не задано, то будет использовано значение group
     *      *       добавлено, чтобы разделить группу для вывода в интерфейсе и группу параметров для хранения
     *      * [title] - название / языковая метка
     *      * [section] - main - главная, lang - корневая для языковой метки, all - все разделы, root - корневой раздел. По умолчанию = lang
     *      * [editor] - имя редактора для специфического вывода (метод текущего класса)
     *      * [options] - Для редактора типа select в данное поле записываются варианты значений в формате массива "<ключ> => <заголовок>".
     *      * [default] - Для записи значения параметру в случае если он будет создаваться новый
     *      * [settings] - Массив дополнительных настроек поля
     * Каждый элемент может содержать специфические параметры, которые будут
     * использоваться внутренними обработчиками.
     *
     * @return []
     */
    abstract public function getList();

    /**
     * Событие сохранения данных.
     */
    abstract public function saveData();

    /**
     * Возвращет параметры для установки модуля на страцицу.
     *
     * @return mixed
     */
    abstract public function getInstallationParam();

    public function setGroupName($sGroupName)
    {
        $this->sGroupName = $sGroupName;
    }

    public function setLabelTitle($sLabelTitle)
    {
        $this->sLabelTitle = $sLabelTitle;
    }

    public function setParent($iParentId)
    {
        $this->iParent = $iParentId;
    }

    public function getGroupName()
    {
        return $this->sGroupName;
    }

    /**
     * Устанавливает модуль(добавляет группу параметров метки) в текущий раздел.
     *
     * @param string $sSubType - подтип модуля
     *
     * @throws UserException
     * @throws ui\ARSaveException
     *
     * @return array масиив id разделов в которые установился модуль
     */
    public function install($sSubType = 'base')
    {
        /** Обработка тех.имени */
        $sAlias = (!empty($this->sGroupName)) ? $this->sGroupName : $this->sLabelTitle;

        $sAlias = Transliterate::change($sAlias, false);
        $sAlias = Transliterate::changeDeprecated($sAlias);
        $sAlias = Transliterate::mergeDelimiters($sAlias);
        $sAlias = trim($sAlias, '-');
        $sAlias = str_replace('-', '_', $sAlias);

        // Разделы-наследники
        $aInheritorSections = Template::getSubSectionsByTemplate($this->iParent);

        if (!$sAlias) {
            throw new UserException(\Yii::t('ZonesEditor', 'error_module_name_empty'));
        }
        if (mb_strlen($sAlias) > 50) {
            throw new UserException(\Yii::t('ZonesEditor', 'error_module_name_len', [50]));
        }
        $oParam = ParamsAr::find()
            ->where(['group' => $sAlias])
            ->andFilterWhere(['parent' => $aInheritorSections])
            ->one();

        if ($oParam) {
            throw new UserException(\Yii::t('ZonesEditor', 'error_module_name_collision'));
        }
        $this->sGroupName = $sAlias;

        $aParamList = $this->getInstallationParam();

        $aParamList = ArrayHelper::getValue(ArrayHelper::index($aParamList, 'name'), $sSubType . '.parameters', []);

        $aBlankParam = [
            'parent' => $this->iParent,
            'group' => $this->sGroupName,
            'name' => '',
            'value' => null,
            'show_val' => null,
            'title' => null,
            'access_level' => null,
        ];

        foreach ($aParamList as $aParam) {
            $oParam = new ParamsAr();
            $oParam->setAttributes(array_merge($aBlankParam, $aParam));

            if (!$oParam->save()) {
                throw new ui\ARSaveException($oParam);
            }
        }

        /*
         * 1. Получить параметры установки и отбросить те которые определяют поведение модуля, (оставить только те которые хранят данные)
         * 2. Установить эти параметры в разделы-наследники
         */

        foreach ($aInheritorSections as $itemSection) {
            $aBlankParam = [
                'parent' => $itemSection,
                'group' => $this->sGroupName,
                'name' => '',
                'value' => null,
                'show_val' => null,
                'title' => null,
                'access_level' => null,
            ];

            foreach ($aParamList as $aParam) {
                if ($aParam['access_level'] == Type::paramSystem) {
                    continue;
                }

                $oParam = new ParamsAr();
                $oParam->setAttributes(array_merge($aBlankParam, $aParam));

                if (!$oParam->save()) {
                    throw new ui\ARSaveException($oParam);
                }
            }
        }

        $aInheritorSections[] = $this->iParent;

        return $aInheritorSections;
    }

    /**
     * Выполняет удаление модуля(группы параметров метки)
     * без удаления метки из зоны.
     *
     * @return array массив id раделов из которых модуль удален
     */
    public function delete()
    {
        $aTemplates = Tree::getSubSections(\Yii::$app->sections->templates(), true, true);

        return in_array($this->iParent, $aTemplates)
            ? $this->deleteFromTemplate()
            : $this->deleteFromSection();
    }

    /**
     * Выполняет удаление модуля из раздела.
     *
     * @return array массив id раделов из которых модуль удален
     */
    protected function deleteFromSection()
    {
        $aParam = Parameters::getList(Parameters::getParentTemplates($this->iParent))
            ->group($this->sGroupName)
            ->asArray()->get();

        if ($aParam) {
            ParamsAr::deleteAll([
                'parent' => $this->iParent,
                'group' => $this->sGroupName,
                'access_level' => Type::paramSystem,
            ]);
        } else {
            ParamsAr::deleteAll([
                'parent' => $this->iParent,
                'group' => $this->sGroupName,
            ]);
        }

        return [$this->iParent => $this->iParent];
    }

    /**
     * Выполняет удаление модуля из шаблона.
     *
     * @return array массив id раделов из которых модуль удален
     */
    protected function deleteFromTemplate()
    {
        $aSections = Template::getSubSectionsByTemplate($this->iParent);

        // Определяем разделы-наследники с переопределенной группой
        $aTempParams = ParamsAr::find()
            ->select('parent, count(`id`) as countSysParam')
            ->andWhere(['parent' => $aSections])
            ->andWhere(['group' => $this->sGroupName])
            ->andWhere(['access_level' => Type::paramSystem])
            ->groupBy('parent')
            ->asArray()->all();

        // Разделы с переопределенной группой
        $aOverrideSections = ArrayHelper::getColumn($aTempParams, 'parent');

        /** в разделы с переопределенной группой добавить недостающие параметры */

        // канонический набор параметров, с которым будем сравнивать
        $aCannonicalParams = Parameters::getList($this->iParent)
            ->group($this->sGroupName)
            ->index('name')
            ->rec()->get();

        foreach ($aOverrideSections as $item) {
            $aParamsSectionItem = Parameters::getList($item)
                ->group($this->sGroupName)
                ->index('name')
                ->asArray()->get();

            foreach ($aCannonicalParams as $name => $param) {
                if (!in_array($name, $aParamsSectionItem)) {
                    Parameters::copyToSection($aCannonicalParams[$name], $item);
                }
            }
        }

        // Разделы с унаследованной группой
        $aInheritedSections = array_diff($aSections, $aOverrideSections);

        $aSection4Delete = $aInheritedSections;
        $aSection4Delete[] = $this->iParent;

        // Удаляем группу в текущем разделе и разделах-наследниках
        ParamsAr::deleteAll(['parent' => $aSection4Delete, 'group' => $this->sGroupName]);

        return $aSection4Delete;
    }

    /**
     *  Скопировать модуль в данный раздел
     *  Из шаблона раздела копируются недостающие параметры.
     */
    public function copy()
    {
        $iTpl = Parameters::getTpl($this->iParent);

        // канонический набор параметров из шаблона
        $aCannonicalParams = Parameters::getList($iTpl)
            ->group($this->sGroupName)
            ->index('name')
            ->rec()->get();

        $aParamsSection = Parameters::getList($this->iParent)
            ->group($this->sGroupName)
            ->index('name')
            ->asArray()->get();

        foreach ($aCannonicalParams as $name => $param) {
            if (!in_array($name, $aParamsSection)) {
                Parameters::copyToSection($aCannonicalParams[$name], $this->iParent);
            }
        }
    }

    /**
     * Вернет имя класса из которого был вызван метод.
     *
     * @return string имя класса
     */
    public static function className()
    {
        return get_called_class();
    }
}
