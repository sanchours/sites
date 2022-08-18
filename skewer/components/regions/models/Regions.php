<?php

namespace skewer\components\regions\models;

use skewer\components\ActiveRecord\ActiveRecord;
use skewer\components\config\Exception;
use yii\base\UserException;

/**
 * This is the model class for table "regions".
 *
 * @property int $id
 * @property string $domain
 * @property string $utm
 * @property string $city
 * @property string $region
 * @property string $fed_district
 * @property int $default
 * @property int $active
 */
class Regions extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%regions}}';
    }

    /**
     * @param $idRegion
     *
     * @throws \yii\db\Exception
     *
     * @return bool
     */
    public static function setDefaultRegion($idRegion)
    {
        $transaction = self::getDb()->beginTransaction();

        try {
            self::updateAll(['default' => 0]);

            $region = self::findOne(['id' => $idRegion]);
            $region->default = 1;
            $region->active = 1;
            $region->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            return false;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            return false;
        }

        return true;
    }

    public static function isActiveDomain($domain)
    {
        $region = Regions::find()
            ->select('active')
            ->where(['domain' => $domain])
            ->one();

        return $region instanceof Regions ?
            (bool) $region->active
            : false;
    }

    public static function getAll()
    {
        return self::find()->all();
    }

    public static function getNewRow($params = [])
    {
        $region = new Regions();
        if ($params) {
            $region->setAttributes($params);
        }

        return $region;
    }

    /**
     * @param $id
     *
     * @return Regions
     */
    public static function getById($id)
    {
        $label = self::findOne(['id' => $id]);

        return $label ?: self::getNewRow();
    }

    public static function getActiveRegionByDomain($domain)
    {
        return self::findOne(['domain' => $domain, 'active' => 1]);
    }

    public static function getActiveRegionByUtm($utm)
    {
        return self::findOne(['utm' => $utm, 'active' => 1]);
    }

    public static function getRegionsWithoutCurrent($domainCurrent)
    {
        return self::find()
            ->where(['!=', 'domain', $domainCurrent])
            ->andWhere(['active' => 1])
            ->orderBy('city')
            ->asArray()
            ->all();
    }

    public static function getActiveDomainByCondition($condition)
    {
        $region = Regions::find()
            ->select(['domain'])
            ->where($condition)
            ->andWhere(['active' => 1])
            ->one();

        return $region instanceof Regions ? $region->domain : null;
    }

    /**
     * @return null|ActiveRecord
     * @throws UserException
     */
    public static function getDefaultRegion()
    {
        $region = Regions::findOne(['default' => 1, 'active' => 1]);

        if ($region === null) {
            $region = Regions::find()
                ->where(['active' => 1])
                ->orderBy(['id' => SORT_ASC])
                ->one();
        }

        if ($region === null) {
            throw new UserException(
                'Не найдено ни одного активного региона. Модуль регионов не был полностью настроен.'
            );
        }

        return $region;
    }

    public function rules()
    {
        return [
            [['city'], 'required'],
            [['default', 'active'], 'integer'],
            [['domain', 'utm', 'city', 'region', 'fed_district'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'domain' => \Yii::t('regions', 'domain'),
            'utm' => \Yii::t('regions', 'utm'),
            'city' => \Yii::t('regions', 'city'),
            'region' => \Yii::t('regions', 'region'),
            'fed_district' => \Yii::t('regions', 'fed_district'),
            'default' => \Yii::t('regions', 'default'),
            'active' => \Yii::t('regions', 'active'),
        ];
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if (!$this->active && $this->default) {
            $this->addError(
                'active',
                \Yii::t('Regions', 'error_not_active_default')
            );

            return false;
        }

        return parent::validate($attributeNames, $clearErrors);
    }
}
