<?php

namespace unit\build\libs\ft\test_model;

use skewer\base\ft as ft;

/*
 * Модель новостной системы
 */

ft\Entity::get('test_ar')
    ->clear()

    ->setTablePrefix('')

    ->setNamespace(__NAMESPACE__)

    ->addField('a', 'int(1)', 'a')
    ->setDefaultVal(1)
    ->addField('b', 'float(1)', 'b')
    ->setDefaultVal(0.0)
    ->addField('c', 'bool', 'c')
    ->setDefaultVal(true)

    ->addField('date', 'date', 'Дата')

    ->addField('string', 'varchar(255)', 'string')
    ->addValidator('set')

    ->addField('text', 'text', 'text')
    ->setDefaultVal('new text')

    ->addColumnSet('editor', 'date,string,text')

    ->addDefaultProcessorSet()

    ->save()
    ->build();

ft\Entity::get('test_ar2')
    ->clear()

    ->setTablePrefix('')

    ->setNamespace(__NAMESPACE__)

    ->addField('info', 'varchar(255)', 'string')
    ->addValidator('set')

    ->addColumnSet('editor', 'info')

    ->addDefaultProcessorSet()

    ->save()
    ->build();
