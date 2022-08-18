<?php

namespace skewer\base\ft\converter;

use skewer\base\ft;
use Symfony\Component\Yaml\Yaml as sfYaml;

/**
 * Класс для преобразования yaml ft описания в класс skewer\base\ft\Model и обратно.
 */
class Yaml implements ConverterInterface
{
    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param string $sIn входные данные
     *
     * @return ft\Model
     */
    public function dataToFtModel($sIn)
    {
        return new ft\Model(sfYaml::parse($sIn));
    }

    /**
     * Преобрзовывает данные в ft модель.
     *
     * @param ft\Model $oModel модель данных для экспорта
     *
     * @return string
     */
    public function ftModelToData(ft\Model $oModel)
    {
        $aModel = $oModel->getModelArray();

        // вычленить поля
        $sFields = '';
        if (isset($aModel['fields'])) {
            $aFieldList = $this->cutFieldListData($aModel['fields']);
            $sFields = sfYaml::dump($aFieldList);
            $sFields = str_replace("\n", "\n    ", $sFields);
            unset($aModel['fields']);
        }

        // вычленить индексы
        $sIndexes = '';
        if (isset($aModel['indexes'])) {
            $sIndexes = sfYaml::dump($aModel['indexes']);
            $sIndexes = str_replace("\n", "\n    ", $sIndexes);
            unset($aModel['indexes']);
        }

        // собрать основную модель
        $sModel = sfYaml::dump($aModel);

        // добавить поля, если есть
        if ($sFields) {
            $sModel .= "\nfields:\n    " . $sFields;
        }
        // добавить индексы, если есть
        if ($sIndexes) {
            $sModel .= "\nindexes:\n    " . $sIndexes;
        }

        return $sModel;
    }

    /**
     * Проходится по подмассивам описаний полей и удаляет то, что можно.
     *
     * @param array $aFields
     *
     * @return array
     */
    private function cutFieldListData($aFields)
    {
        foreach ($aFields as $sKey => $sVal) {
            foreach (['type', 'required', 'fictitious'] as $sName) {
                if (isset($sVal[$sName]) and !$sVal[$sName]) {
                    unset($aFields[$sKey][$sName]);
                }
            }

            foreach (['hide', 'widget', 'validator', 'modificator', 'multilang'] as $sName) {
                if (isset($sVal[$sName]) and !$sVal[$sName]) {
                    unset($aFields[$sKey][$sName]);
                }
            }
        }

        return $aFields;
    }
}
