<?php

namespace skewer\build\Adm\HTMLBanners\models;

use skewer\components\ActiveRecord\ActiveRecord;
use Yii;

/**
 * This is the model class for table "banners".
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $active
 * @property int $on_main
 * @property int $on_allpages
 * @property int $on_include
 * @property string $location
 * @property int $section
 * @property int $sort
 * @property string $last_modified_date
 */
class Banners extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['content'], 'string'],
            [['active', 'on_main', 'on_allpages', 'on_include', 'section', 'sort'], 'integer'],
            [['title'], 'string', 'max' => 250],
            [['location'], 'string', 'max' => 20],
            [['last_modified_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'content' => 'Content',
            'active' => 'Active',
            'on_main' => 'On Main',
            'on_allpages' => 'On Allpages',
            'on_include' => 'On Include',
            'location' => 'Location',
            'section' => 'Section',
            'sort' => 'Sort',
            'last_modified_date' => 'Last modified date',
        ];
    }

    public function beforeSave($insert)
    {
        $this->last_modified_date = date('Y-m-d H:i:s', time());

        return parent::beforeSave($insert);
    }

    /**
     * @static Выбираем баннеры для внутренних страниц
     * Условие, по которому баннер выводится на внутренней странице содержит в себе следующие составные части:
     * 1. Активность баннера - баннер должен быть активным
     * 2. Или раздел вывода, определенный для баннера должен совпадать с текущим разделом
     *    или должен стоять флаг "Выводить на внутренних страницах" и раздел вывода, указанный для баннера, должен быть в числе родительских для текущего раздела
     *    или должен стоять флаг "Выводить на всех страницах"
     * 3. Конкретная заданная позиция вывода, для которой выбираются баннеры
     *
     * @param int $iSection Текущий раздел
     * @param array $aParentSections Родительские разделы
     * @param string $sLocation Расположение
     *
     * @return array
     */
    public static function getBanners($iSection, $aParentSections, $sLocation)
    {
        return self::find()
            ->where(['active' => 1])
            ->andWhere(
                ['or',
                    ['section' => $iSection],
                    ['and',
                        ['on_include' => 1],
                        ['section' => $aParentSections],
                    ],
                    ['on_allpages' => 1],
                ]
            )
            ->andWhere(['location' => $sLocation])
            ->orderBy('sort')
            ->asArray()
            ->all();
    }

    /**
     * @static Выбираем баннеры на главную страницу
     * Условие, по которому баннер выводится на главную страницу содержит в себе следующие составные части:
     * 1. Активность баннера - баннер должен быть активным
     * 2. Или определенный для баннера раздел вывода должен совпадать с текущим разделом, который по факту является главной страницей
     *    или должен стоять флаг "выводить на главную"
     * 3. Конкретная заданная позиция вывода, для которой выбираются баннеры
     *
     * @param array $aParams Входные параметры
     * @param mixed $iSectionId
     * @param mixed $Location
     *
     * @return array
     */
    public static function getBannersOnMain($iSectionId, $Location)
    {
        $aBanners = self::find()
            ->where(['active' => 1])
            ->andWhere(['on_main' => 1])
            ->andWhere(['location' => $Location])
            ->orderBy('sort')
            ->asArray()
            ->all();

        return $aBanners;
    }

    // function getBannersOnMain()

    public static function getNewRow($aData = [])
    {
        $oRow = new Banners();

        if ($aData) {
            $oRow->setAttributes($aData);
        }

        return $oRow;
    }

    /**
     * Создает баннер со значениями по умолчанию.
     *
     * @return Banners
     */
    public static function getBlankBanner()
    {
        $oBanner = new self();

        $maxSort = self::find()
            ->max('sort');

        $oBanner->setAttributes([
            'title' => \yii::t('HTMLBanners', 'new_banner'),
            'active' => 1,
            'location' => 'left',
            'section' => \Yii::$app->sections->root(),
            'sort' => $maxSort + 1,
            'on_main' => 1,
            'on_allpages' => 1,
            'on_include' => 1,
        ]);

        return $oBanner;
    }

    /**
     * @static Метод для получения максимального значения поля "sort" для текущей позиции
     *
     * @param string $sLocation Позиция, для которой выбирам максимальный порядок
     *
     * @return mixed
     */
    public static function getMaxOrder($sLocation)
    {
        $row = self::find()
            ->where(['like', 'location', $sLocation])
            ->max('sort')
            ->asArray();
        if ($row) {
            return $row['sort'];
        }

        return 0;
    }

    // function getMaxOrder()

    public function afterDelete()
    {
        Yii::$app->router->updateModificationDateSite();
        parent::afterDelete();
    }

    /**
     * Возвращает максимальную дату модификации сущности.
     *
     * @return array|bool
     */
    public static function getMaxLastModifyDate()
    {
        return (new \yii\db\Query())->select('MAX(`last_modified_date`) as max')->from(self::tableName())->one();
    }
}// class
