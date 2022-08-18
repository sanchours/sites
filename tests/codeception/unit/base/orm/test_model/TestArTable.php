<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 20.06.13
 * Time: 11:47
 * To change this template use File | Settings | File Templates.
 */

namespace unit\base\orm\test_model;

use skewer\base\ft as ft;
use skewer\base\orm\TablePrototype;

/**
 * Class TestArTable.
 */
class TestArTable extends TablePrototype
{
    /** @var string Имя таблицы */
    protected static $sTableName = 'test_ar';

    protected static function initModel()
    {
        if (!ft\Cache::exists('test_ar')) {
            include_once __DIR__ . '/testModel.php';
        }

        return ft\Cache::get('test_ar');
    }

    /**
     * Отдает новую запись языка.
     *
     * @param array $aData
     *
     * @return TestArRow
     */
    public static function getNewRow($aData = [])
    {
        return new TestArRow($aData);
    }

//    /**
//     * Отдает модель текущей сущности
//     * @throws \Exception
//     * @return ft\Model
//     */
//    function getModelObject() {
//
//        if ( !ft\Cache::exists( 'test_ar' ) )
//            include_once( __DIR__ . '/testModel.php' );
//
//        return ft\Cache::get( 'test_ar', 'tests\\build\\libs\\ft\\testModel' );
//
//    }
//
//    /**
//     * Отдает имя таблицы для модели
//     * @return string
//     */
//    function getArClassName() {
//        return 'unit\\build\\libs\\ft\\test_model\\TestArRow';
//    }
}
