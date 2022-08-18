<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 18.04.2017
 * Time: 16:29
 */
namespace skewer\libs\excel;

class Includer{

    public static function includeExcel(){

       include_once __DIR__.'/Classes/PHPExcel.php';

    }

}