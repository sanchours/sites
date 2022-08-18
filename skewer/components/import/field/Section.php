<?php

namespace skewer\components\import\field;

use skewer\base\section\Api;
use skewer\base\section\Api as SectionApi;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\components\catalog;
use skewer\components\import;
use yii\helpers\ArrayHelper;

/**
 * Обработчик разделов.
 */
class Section extends Prototype
{
    /**
     * Разделитель разделов.
     *
     * @var string
     */
    protected $delimiter = ';';

    public static $iCurrentSection = 0;

    /**
     * Разделитель пути.
     *
     * @var string
     */
    protected $delimiter_path = '/';

    /**
     * Корневой раздел.
     *
     * @var int
     */
    protected $baseId = '';

    /** @var int Каталожный раздел для выгрузки по умолчанию */
    protected $defImportSectionId = 0;

    /**
     * Создавать новые разделы.
     *
     * @var bool
     */
    protected $create = false;

    /**
     * Шаблон для создания разделов.
     *
     * @var int
     */
    protected $template = '';

    /**
     * Признак перенос товаров с двойки.
     *
     * @var int
     */
    protected $transference = 0;
    /**
     * @var array
     */
    private static $cache = [];

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

    /**
     * Список разделов.
     *
     * @var array
     */
    protected $sections = [];

    protected static $parameters = [
        'delimiter' => [
            'title' => 'field_section_delimiter',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => ';',
            'validator' => ['string', ['min' => 1]],
        ],
        'delimiter_path' => [
            'title' => 'field_section_delimiter_path',
            'datatype' => 's',
            'viewtype' => 'string',
            'default' => '/',
            'validator' => ['string', ['min' => 1]],
        ],
        'baseId' => [
            'title' => 'field_section_base_id',
            'datatype' => 'i',
            'viewtype' => 'select',
            'default' => '', // назначаем в конструктре
            'method' => 'getSectionList',
        ],
        'defImportSectionId' => [ // Каталожный раздел для выгрузки по умолчанию
            'title' => 'field_section_def_id',
            'datatype' => 'i',
            'viewtype' => 'select',
            'default' => '0',
            'method' => 'getCatalogSectionList',
        ],
        'create' => [
            'title' => 'field_section_create',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 0,
        ],
        'template' => [
            'title' => 'field_section_template',
            'datatype' => 'i',
            'viewtype' => 'select',
            'default' => '', // не всегда это так! назначаем в конструктре
            'method' => 'getTemplatesList',
        ],
        'transference' => [
            'title' => 'field_section_transference',
            'datatype' => 'i',
            'viewtype' => 'check',
            'default' => 0,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function isSection()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function skipField()
    {
        return !(bool) count($this->importFieldNames);
    }

    public function __construct(array $fields, $sFieldName, import\Task $oTask)
    {
        parent::__construct($fields, $sFieldName, $oTask);
        Api::$sDelimiter = $this->delimiter_path;
        $this->template = Template::getCatalogTemplate();
        self::$parameters['template']['default'] = Template::getCatalogTemplate();
        self::$parameters['baseId']['default'] = Template::getCatalogTemplate();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        self::$iCurrentSection = 0;

        $this->sections = [];

        //собираем все значения алиасов разделов
        $aAliasList = $this->getAliasList();

        if ($aAliasList) {
            foreach ($aAliasList as $sAlias) {
                if (!isset(self::$cache[$sAlias])) {
                    /* Ищем раздел */
                    if (is_numeric($sAlias)) {
                        // Если задан как id раздела
                        $iSection = ($aSection = Tree::getSection($sAlias)) ? $aSection['id'] : 0;
                        self::setCache($sAlias, $iSection);
                    } else {
                        // Если задан как ЗАГОЛОВОК или ПУТЬ с подразделами (а не alias) раздела
                        $iSection = SectionApi::getIdByAlias($sAlias, $this->baseId);
                        self::setCache($sAlias, $iSection);
                        self::setCache($iSection, $iSection);
                    }

                    if (!$iSection and $this->create) {
                        $iSection = $this->createSection($sAlias, $this->baseId, $this->template);
                        self::setCache($sAlias, $iSection);
                        self::setCache($iSection, $iSection);
                    }
                } else {
                    $iSection = self::getCache($sAlias);
                }

                if ($iSection) {
                    self::$iCurrentSection = $iSection;
                    $this->sections[] = $iSection;
                } else {
                    $this->logger->setListParam('no_section', $sAlias);
                }
            }
        }

        //Пропускаем, если разделов нет
        if (!$this->sections) {
            $this->skipCurrentRow(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        //pass
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        // Основной раздел должен проставиться сам
        $oGoodsRow = $this->getGoodsRow();
        $this->sections = array_unique($this->sections);
        if ($oGoodsRow) {
            $iMainSectionId = $oGoodsRow->setViewSection($this->sections);
            // Главный раздел может не установиться если импрорт создаёт разделы, а кэш в \skewer\base\section\Tree::$cache старый
            (in_array($iMainSectionId, $this->sections) !== false) or $oGoodsRow->setMainSection(reset($this->sections));
        }

        // установка карточки основному разделу и категориям
        if ($this->transference) {
            foreach ($this->sections as $iSectionId) {
                Parameters::setParams($iSectionId, 'content', 'defCard', $this->getCard());
            }
        }
    }

    /**
     * Список разделов для выбора базового.
     * ВНИМАНИЕ! Метод вызывается неявно!
     *
     * @return array
     */
    public static function getSectionList()
    {
        $aSections = Tree::getSectionList(\Yii::$app->sections->root());

        return ArrayHelper::map($aSections, 'id', 'title');
    }

    /**
     * Список каталожных разделов для выбра раздела по умолчанию
     * ВНИМАНИЕ! Метод вызывается неявно!
     *
     * @return array
     */
    public static function getCatalogSectionList()
    {
        return ['---'] + catalog\Section::getList();
    }

    /**
     * Список шаблонов каталожных разделов.
     *
     * @return array
     */
    public static function getTemplatesList()
    {
        $aSections = Tree::getSectionByParent(\Yii::$app->sections->templates());
        $aSections = ArrayHelper::map($aSections, 'id', 'id');

        $aParams = Parameters::getList($aSections)->group('content')->name(Parameters::object)->rec()->asArray()->get();

        foreach ($aParams as $k => $aParam) {
            if ($aParam['value'] != 'CatalogViewer') {
                unset($aParams[$k]);
            }
        }

        if ($aParams) {
            $aParams = ArrayHelper::map($aParams, 'parent', 'parent');
            $aSections = array_intersect($aSections, $aParams);
        }

        return Tree::getSectionsTitle($aSections);
    }

    /**
     * Создание раздела по пути от заданного.
     *
     * @param $sAlias
     * @param int $iBaseId
     * @param int $iTemplate
     *
     * @return false|int
     *  функция лежит здесь, а не в апи, так как нужно запоминать сколько и какие разделы созданы
     */
    protected function createSection($sAlias, $iBaseId = 0, $iTemplate = 0)
    {
        if (!$sAlias) {
            return false;
        }

        //разбираем путь на части
        $aAlias = explode($this->delimiter_path, $sAlias);
        $sPath = '';
        $iSection = $iBaseId;

        //идем по вложенности
        foreach ($aAlias as $alias) {
            $sPath .= $this->delimiter_path . $alias;
            $sPath = trim($sPath, $this->delimiter_path);

            $iParent = SectionApi::getIdByAlias($sPath, $iBaseId);
            //создаем раздел, если его нет
            if (!$iParent) {
                $iSection = SectionApi::addSection($iSection, $alias, $iTemplate);
                if ($iSection) {
                    Parameters::setParams($iSection, 'content', 'defCard', $this->getCard());

                    $this->logger->incParam('create_section');
                    $this->logger->setListParam('create_section_list', $sPath);
                }
            } else {
                $iSection = $iParent;
            }
        }

        if ($iSection == $iBaseId) {
            return false;
        }

        return $iSection;
    }

    /**
     * Собирает значения alias-ов разделов.
     *
     * @return array
     */
    protected function getAliasList()
    {
        $aAliasList = [];
        if ($this->values) {
            foreach ($this->values as $key => $sVal) {
                $aAliasList = array_merge($aAliasList, explode($this->delimiter, $sVal));
            }
        } elseif ($this->defImportSectionId and Tree::getCachedSection($this->defImportSectionId)) {
            $aAliasList[] = $this->defImportSectionId;
        }

        return $aAliasList;
    }

    public static function getParameters()
    {
        self::$parameters['template']['default'] = Template::getCatalogTemplate();
        self::$parameters['baseId']['default'] = \Yii::$app->sections->leftMenu();

        return static::$parameters;
    }
}
