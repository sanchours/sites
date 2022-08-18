<?php

namespace skewer\base\section;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\ParamsAr as Params;
use skewer\base\section\params\ListSelector;
use skewer\base\section\params\Type;
use skewer\build\Design\Zones;
use skewer\components\forms\Api as ApiForm;
use yii\helpers\ArrayHelper;

/**
 * Фасад для работы с параметрами
 * Class Parameters.
 */
class Parameters
{
    /** Название параметра-шаблона раздела */
    const template = 'template';

    /** Имя группы настроек раздела */
    const settings = '.';

    /** Метка модуля */
    const object = 'object';

    /** Метка админского модуля */
    const objectAdm = 'objectAdm';

    /** Имя группы */
    const groupName = '.groupTitle';

    /**
     * Имя спец. параметра. Параметр запрещает показ остальных параметров группы в модуле "Редактор",
     * если данная группа не используется ни в одном layout-е раздела.
     */
    const excludedGroup = '.showGroupIfInstall';

    /** Название параметра - зона модуля */
    const layout = 'layout';

    /** Название параметра - название модуля */
    const titleName = '.title';

    /** имя параметра "выводить шаблонами для подразделлов - наследников шаблона текущей страницы" */
    const SubSectonTpl = '__section_sub_tpl';

    /** имя параметра "выводить допустимые родительские разделы" */
    const HideParents = '__section_hide_parents';

    /** имя параметра "Добавлять в родительский раздел" */
    const AddToParent = '__section_add_to_parent';

    /** имя параметра 'Скрыть вкладку "Редактор"' */
    const HideEditor = '__section_hide_editor';

    /** Название параметра языка */
    const language = 'language';

    /**
     * @var array
     */
    private static $cache = [];

    const Delimiter = '~';

    /**
     * @param mixed $key
     *
     * @return string
     */
    private static function getCache($key)
    {
        return (isset(self::$cache[$key])) ? self::$cache[$key] : '';
    }

    /**
     * @param array $cache
     * @param mixed $key
     * @param mixed $value
     */
    private static function setCache($key, $value = '')
    {
        self::$cache[$key] = $value;
    }

    public static function clearCache()
    {
        self::$cache = [];
    }

    /**
     * Создает параметр с данными.
     *
     * @param array $aData
     *
     * @return Params
     */
    public static function createParam($aData = [])
    {
        $oParam = new Params();
        $oParam->setAttributes($aData);

        return $oParam;
    }

    public static function addParam($aData)
    {
        $oParam = self::getParamByName($aData['parent'], $aData['group'], $aData['name']);

        if ($oParam !== false) {
            Params::updateAll(
                [
                'value' => $aData['value'],
                ],
                [
                    'id' => $oParam->getAttribute('id'),
                ]
            );
        } else {
            $oParam = new Params();
            $oParam->setAttributes($aData);
            $oParam->save(false);
        }

        return $oParam->getAttribute('id');
    }

    /**
     * Копирует параметр $oParam в раздел $iNewSection, возвращает новый параметр или false.
     * Не заменяет существующие параметры!
     *
     * @param Params $oParam
     * @param $iNewSection
     * @param $value
     *
     * @return bool|Params
     */
    public static function copyToSection(models\ParamsAr $oParam, $iNewSection, $value = null)
    {
        $oParamFromCurSection = self::getByName($iNewSection, $oParam->group, $oParam->name);

        // Если в текущем разделе имеется одноименный наследуемый параметр, то перекрываем его
        if ($oParamFromCurSection and in_array($oParamFromCurSection->access_level, [Type::paramInherit, Type::paramInheritFromSection])) {
            $oNewParam = $oParamFromCurSection;
            $oNewParam->access_level = $oParam->access_level;
            $oNewParam->value = $oParam->value;
            $oNewParam->show_val = $oParam->show_val;
        } else {
            $oNewParam = self::createParam($oParam->getAttributes(null, ['id', 'parent']));
        }

        $oNewParam->parent = $iNewSection;
        if ($value !== null) {
            $oNewParam->value = $value;
        }

        return ($oNewParam->save()) ? $oNewParam : false;
    }

    /**
     * Получение параметра по id.
     *
     * @param $id
     *
     * @return null|Params
     */
    public static function getById($id)
    {
        $id = (int) $id;

        return Params::findOne($id);
    }

    /**
     * Выбор параметра по разделу, имени и группе.
     *
     * @param $iParent
     * @param $sGroupName
     * @param $sParamName
     * @param bool $bRec
     * @param array $aSelect - список выбираемых полей
     * @param bool $bUseLangVal Получить языковой параметр из языковой ветки?
     * @param bool $bUseInherited - учитывать логику параметров типа "наследуемый" и "наследуемый от указ.раздела"
     *
     * @return false|Params
     */
    private static function getParamByName($iParent, $sGroupName, $sParamName, $bRec = false, $aSelect = [], $bUseLangVal = false, $bUseInherited = false)
    {
        $key_cache = $iParent . self::Delimiter . $sGroupName . self::Delimiter . $sParamName . self::Delimiter . $bRec . implode(self::Delimiter, $aSelect) . self::Delimiter . $bUseLangVal . self::Delimiter . $bUseInherited;

        /** @var false|ParamsAr */
        $cache = self::getCache($key_cache);
        if (!empty($cache)) {
            return $cache;
        }

        if ($aSelect) {
            if ($bUseLangVal) {
                $aSelect = array_merge($aSelect, ['access_level', 'parent']);
            }

            if ($bUseInherited) {
                $aSelect = array_merge($aSelect, ['access_level', 'parent', 'value']);
            }
        }

        $iParent = (int) $iParent;

        $oQuery = Params::find()
            ->where(['parent' => $iParent, 'name' => $sParamName, 'group' => $sGroupName]);

        if ($aSelect && is_array($aSelect)) {
            $oQuery->select(array_intersect($aSelect, Params::getAttributeList()));
        }

        $oParam = $oQuery->one();

        if ($bRec && $oParam === null) {
            $iTpl = self::getTpl($iParent);
            if ($iTpl) {
                $oParam = self::getParamByName($iTpl, $sGroupName, $sParamName, true, $aSelect);
            }
        }

        if ($bRec and $bUseInherited and $oParam) {
            if ($oParam->access_level == Type::paramInherit) {
                $aParentSections = Tree::getSectionParents($iParent);

                foreach ($aParentSections as $iParentSection) {
                    $oTplParam = self::getParamByName($iParentSection, $sGroupName, $sParamName, false, $aSelect);

                    if ($oTplParam and $oTplParam->access_level != Type::paramInherit) {
                        $oParam = $oTplParam;
                        break;
                    }
                }
            } elseif ($oParam->access_level == Type::paramInheritFromSection) {
                if ($oParam->value !== '') {
                    $oParam = self::getParamByName($oParam->value, $sGroupName, $sParamName, false);
                }
            }
        }

        // Получить языковой параметр из языковой ветки
        if ($bUseLangVal and $oParam and ($oParam->access_level == Type::paramLanguage) and ($oParam->parent != \Yii::$app->sections->languageRoot())) {
            $oParam = self::getParamByName(\Yii::$app->sections->languageRoot(), $sGroupName, $sParamName, false, $aSelect);
        }

        self::setCache($key_cache, $oParam ?: false);

        return $oParam ?: false;
    }

    /**
     * Выбор параметра по разделу, имени и группе.
     *
     * @param $iParent
     * @param $sGroupName
     * @param $sParamName
     * @param $bRec
     * @param bool $bUseLangVal Получить языковой параметр из языковой ветки?
     * @param bool $bUseInherited - учитывать логику параметров типа "наследуемый" и "наследуемый от указ.раздела"
     *
     * @return false|Params
     */
    public static function getByName($iParent, $sGroupName, $sParamName, $bRec = false, $bUseLangVal = false, $bUseInherited = false)
    {
        return self::getParamByName($iParent, $sGroupName, $sParamName, $bRec, [], $bUseLangVal, $bUseInherited);
    }

    /**
     * Возвращает значение параметра по разделу, имени и группе или false, если параметр не найден.
     *
     * @param $iParent
     * @param $sGroupName
     * @param $sParamName
     * @param $bRec
     * @param bool $bUseLangVal Получить языковой параметр из языковой ветки?
     * @param bool $bUseInherited - учитывать логику параметров типа "наследуемый" и "наследуемый от указ.раздела"
     *
     * @return false|string
     */
    public static function getValByName($iParent, $sGroupName, $sParamName, $bRec = false, $bUseLangVal = false, $bUseInherited = false)
    {
        /** @var Params $oParam */
        $oParam = self::getParamByName($iParent, $sGroupName, $sParamName, $bRec, ['value'], $bUseLangVal, $bUseInherited);

        return $oParam ? $oParam->value : false;
    }

    /**
     * Возвращает текстовое значение параметра по разделу, имени и группе или false, если параметр не найден.
     *
     * @param $iParent
     * @param $sGroupName
     * @param $sParamName
     * @param $bRec
     * @param bool $bUseLangVal Получить языковой параметр из языковой ветки?
     * @param bool $bUseInherited - учитывать логику параметров типа "наследуемый" и "наследуемый от указ.раздела"
     *
     * @return false|string
     */
    public static function getShowValByName($iParent, $sGroupName, $sParamName, $bRec = false, $bUseLangVal = false, $bUseInherited = false)
    {
        /** @var Params $oParam */
        $oParam = self::getParamByName($iParent, $sGroupName, $sParamName, $bRec, ['show_val'], $bUseLangVal, $bUseInherited);

        return $oParam ? $oParam->show_val : false;
    }

    /**
     * Возвращает объект для выбоки списка параметров.
     *
     * @param int $iParent
     *
     * @return ListSelector
     */
    public static function getList($iParent = null)
    {
        if ($iParent !== null) {
            return (new ListSelector())->parent($iParent);
        }

        return new ListSelector();
    }

    /**
     * Список разделов, для которых данные разделы присутствуют в цепочке наследования по шаблонам
     *
     * @param $mParent
     *
     * @return array|bool
     */
    public static function getChildrenList($mParent)
    {
        if (!is_array($mParent)) {
            $mParent = [(int) $mParent];
        }

        $aTplParams = Params::find()
            ->where(['name' => static::template, 'group' => static::settings, 'value' => $mParent])
            ->select(['parent'])
            ->asArray()
            ->all();

        $aTplParams = ArrayHelper::map($aTplParams, 'parent', 'parent');

        if ($aTplParams) {
            $aSubTplParams = self::getChildrenList(array_diff($aTplParams, $mParent));
            if ($aSubTplParams) {
                return array_unique(array_merge($aTplParams, $aSubTplParams));
            }

            return $aTplParams;
        }

        return false;
    }

    /**
     * Список разделов с модулем в группе.
     *
     * @param $sModule
     * @param $sGroupName
     * @param $sName
     *
     * @return array
     */
    public static function getListByModule($sModule, $sGroupName, $sName = Parameters::object)
    {
        $aParams = Params::find()
            ->select('parent')
            ->where(['group' => $sGroupName, 'name' => $sName, 'value' => $sModule])
            ->asArray()
            ->all();

        $aParams = ArrayHelper::map($aParams, 'parent', 'parent');

        if ($aParams) {
            $aTplParams = self::getChildrenList($aParams);
            if ($aTplParams) {
                /** Исключаем разделы с перекрытым модулем */
                $aModulesSections = Params::find()
                    ->select('parent')
                    ->where(['group' => $sGroupName, 'name' => $sName])
                    ->asArray()
                    ->all();

                $aModulesSections = ArrayHelper::map($aModulesSections, 'parent', 'parent');
                $aParams = array_merge($aParams, array_diff($aTplParams, $aModulesSections));
            }
        }

        return $aParams;
    }

    /**
     * Возвращает шаблон раздела.
     *
     * @param $iSection
     *
     * @return false|int
     */
    public static function getTpl($iSection)
    {
        $oParam = self::getParamByName($iSection, static::settings, static::template, false, ['value']);

        return $oParam ? (int) $oParam->value : false;
    }

    /**
     * Цепочка шаблонов, от которого наследуется раздел
     * Выводит в порядке от самого нижнего к самому верхнему.
     *
     * @param $iSection
     *
     * @return int[] массив id шаблонных разделов|пустой массив, если нет
     */
    public static function getParentTemplates($iSection)
    {
        $iTpl = self::getTpl($iSection);

        if ($iTpl) {
            $aParents = self::getParentTemplates($iTpl);
            if ($aParents) {
                $aParents[] = $iTpl;

                return $aParents;
            }

            return [$iTpl];
        }

        return [];
    }

    /**
     * Обновляет значения параметров по группе и имени, возвращает количество измененных параметров.
     *
     * @param $sParamGroup
     * @param $sParamName
     * @param $value
     *
     * @return int
     */
    public static function updateByName($sParamGroup, $sParamName, $value)
    {
        if (!$sParamName || !$sParamGroup) {
            return 0;
        }

        return Params::updateAll(['value' => $value], ['group' => $sParamGroup, 'name' => $sParamName]);
    }

    /**
     * обновить параметр в конкретном(ых) разделе(ах).
     *
     * @param $sParamGroup
     * @param $sParamName
     * @param $value
     * @param array|string $sections
     *
     * @return int
     */
    public static function updateByNameInSections($sParamGroup, $sParamName, $value, $sections)
    {
        if (!$sParamName || !$sParamGroup || !$sections) {
            return 0;
        }

        return Params::updateAll(['value' => $value], ['group' => $sParamGroup, 'name' => $sParamName, 'parent' => $sections]);
    }

    /**
     * Установка параметра. Ищет параметр по родителю, группе и имени или создает новый
     * и сохраняет его с указанными атрибутами
     * Возвращает id параметра или false.
     *
     * @param $iSection
     * @param $sGroup
     * @param $sName
     * @param $sVal
     * @param string $sShowVal
     * @param string $sTitle
     * @param int $iAccessLevel
     *
     * @return false|int
     */
    public static function setParams($iSection, $sGroup, $sName, $sVal = null, $sShowVal = null, $sTitle = null, $iAccessLevel = null)
    {
        $oParam = self::getByName($iSection, $sGroup, $sName);
        if (!$oParam) {
            $oParam = self::createParam(['parent' => $iSection, 'group' => $sGroup, 'name' => $sName]);
        }
        if ($oParam) {
            if ($sVal !== null) {
                $oParam->value = $sVal;
            }
            if ($sShowVal !== null) {
                $oParam->show_val = $sShowVal;
            }
            if ($sTitle !== null) {
                $oParam->title = $sTitle;
            }
            if ($iAccessLevel !== null) {
                $oParam->access_level = $iAccessLevel;
            }

            if ($oParam->save()) {
                return $oParam->id;
            }
        }

        return false;
    }

    /**
     * Удаление параметра по родителю имени и группе.
     *
     * @param string $sName Имя параметра
     * @param string $sGroup Имя группы
     * @param int $iParent Id раздела. 0 = все разделы
     *
     * @return int
     */
    public static function removeByName($sName, $sGroup, $iParent = 0)
    {
        return Params::deleteAll(
            [
                                     'name' => $sName,
                                     'group' => $sGroup,
                                 ] +
                                 ($iParent ? ['parent' => $iParent] : [])
        );
    }

    /**
     * Удаление параметра по имени группы.
     *
     * @param string $sGroup Имя группы
     * @param int $iParent Id раздела. 0 = все разделы
     *
     * @return int
     */
    public static function removeByGroup($sGroup, $iParent = 0)
    {
        return Params::deleteAll(
            [
                                     'group' => $sGroup,
                                 ] +
                                 ($iParent ? ['parent' => $iParent] : [])
        );
    }

    /**
     * Удаление разделов по id.
     *
     * @param $mId
     *
     * @return int
     */
    public static function removeById($mId)
    {
        return Params::deleteAll([
            'id' => $mId,
        ]);
    }

    /**
     * Язык раздела.
     * Ищет в разделе или его родителях.
     *
     * @param $iPage
     *
     * @return string
     */
    public static function getLanguage($iPage)
    {
        $aParentSections = Tree::getSectionParents($iPage, -1, true);
        array_unshift($aParentSections, $iPage);

        $aLangParams = self::getList($aParentSections)
            ->group(self::settings)
            ->name(self::language)
            ->asArray()
            ->get();

        $aLangParams = ArrayHelper::map($aLangParams, 'parent', 'value');

        foreach ($aParentSections as $iSection) {
            if (isset($aLangParams[$iSection])) {
                return $aLangParams[$iSection];
            }
        }

        return '';
    }

    /**
     * Возвращает список параметров областей вывода объектов с присутствующей группой.
     *
     * @param string $sLabelName Название группы для поиска
     * @param array $aLayouts Список областей страницы для поиска
     *
     * @return Params[]
     */
    public static function getListByLayoutLabels($sLabelName, array $aLayouts)
    {
        if (!$sLabelName or !$aLayouts) {
            return [];
        }

        return Params::findBySql('
            SELECT * FROM ' . Params::tableName() . "
            WHERE
             (`group` = '" . Zones\Api::layoutGroupName . "') AND
             (`name` IN ('" . implode("','", $aLayouts) . "')) AND
             (CONCAT(',', `value`, ',') LIKE '%,{$sLabelName},%')
        ")->all();
    }

    /**
     * Деактивировать форму $formId в разделах.
     *
     * @param int|string $formId - ид формы
     */
    public static function deactivateFormInTrees($formId)
    {
        /** @var ParamsAr[] $aParamsObjectForms */
        $aParamsObjectForms = ParamsAr::find()
            ->where([
                'name' => 'object',
                'value' => 'Forms',
            ])->select(['group'])
            ->all();

        foreach ($aParamsObjectForms as $oParamObjectForms) {
            /** @var ParamsAr $oParamFormId */
            $oParamFormId = ParamsAr::find()
                ->where([
                    'group' => $oParamObjectForms->group,
                    'name' => 'FormId',
                    'value' => $formId,
                ])->select(['parent'])
                ->one();

            if ($oParamFormId) {
                ApiForm::link2Section('', $oParamFormId->parent, $oParamObjectForms->group);
            }
        }
    }

    /**
     * @param $sGroupName
     * @param $sParamName
     *
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getSectionIdListByParamName($sGroupName, $sParamName)
    {
        $aSectionList = Params::find()
            ->select(['parent'])
            ->where(['name' => $sParamName, 'group' => $sGroupName])
            ->asArray()
            ->all();

        $aSectionList = ArrayHelper::getColumn($aSectionList, 'parent');

        return $aSectionList;
    }
}
