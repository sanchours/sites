<?php

namespace unit\base\ft;

use skewer\base\orm\Query;

/**
 * Класс для помощи в написании тестов
 * Class TestHelper.
 */
class TestHelper
{
    /**
     * Первичная инициализация данных
     * Создает чистые тестовые данные в базе.
     */
    public static function init()
    {
        require_once __DIR__ . '/test_model/testModel.php';

        Query::SQL('TRUNCATE test_ar');
        Query::SQL('TRUNCATE test_ar2');

        Query::SQL(
            "INSERT INTO test_ar (`id`,`a`,`b`,`c`,`date`,`string`,`text`) VALUES
                (NULL,0,0,0,NOW(),'str_1','aaaa11111'),
                (NULL,0,1,0,NOW(),'str_2','aaaa11112'),
                (NULL,0,0,1,NOW(),'str_3','aaaa11113'),
                (NULL,0,1,1,NOW(),'str_4','aaaa11114'),
                (NULL,1,0,0,NOW(),'str_5','aaaa11115'),
                (NULL,1,1,0,NOW(),'str_6','aaaa11116'),
                (NULL,1,0,1,NOW(),'str_7','aaaa11117'),
                (NULL,1,1,1,NOW(),'str_8','aaaa11118');
                "
        );

        Query::SQL(
            "INSERT INTO test_ar2 (`id`,`info`) VALUES (NULL,'smalltext1'), (NULL,'smalltext2');"
        );
    }
}
