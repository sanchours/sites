<?php

namespace skewer\components\design;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\Parameters;
use skewer\components\design\model\Params as CssParam;
use skewer\components\design\model\References;
use yii\base\Configurable;
use yii\base\UserException;

/**
 * Класс для хранения и восстановления параметров, измененных при смене шаблона.
 */
class BackupParams extends BackupParamsPrototype implements Configurable
{
    /**
     * Добавляет запись о параметре
     * На это значение надо при восстановлении заменить текущее.
     *
     * @param ParamsAr $oOldParam
     */
    public function addParam(ParamsAr $oOldParam)
    {
        $this->aData[] = [
            'type' => 'set_param',
            'data' => [
                'parent' => $oOldParam->parent,
                'group' => $oOldParam->group,
                'name' => $oOldParam->name,
                'value' => $oOldParam->value,
                'show_val' => $oOldParam->show_val,
            ],
        ];
    }

    /**
     * Добавляет запись о добавленном параметре
     * Такой параметр при восстановлении надо стереть.
     *
     * @param $iSection
     * @param $sGroup
     * @param $sName
     */
    public function addDelParam($iSection, $sGroup, $sName)
    {
        $this->aData[] = [
            'type' => 'del_param',
            'data' => [
                'parent' => $iSection,
                'group' => $sGroup,
                'name' => $sName,
            ],
        ];
    }

    /**
     * Добавляет запись о css параметре
     * На это значение надо при восстановлении заменить текущее.
     *
     * @param CssParam $oCssParam
     */
    public function addCssParam(CssParam $oCssParam)
    {
        $this->aData[] = [
            'type' => 'set_css_param',
            'data' => [
                'name' => $oCssParam->name,
                'value' => $oCssParam->value,
            ],
        ];
    }

    /**
     * Добавляет запись о файле, который должен быть удален.
     *
     * @param string $sPath путь относительно WEBPATH
     *
     * @throws UserException
     */
    public function addDelFile($sPath)
    {
        $this->aData[] = [
            'type' => 'del_file',
            'data' => [
                'file' => $sPath,
            ],
        ];
    }

    public function addReference(References $oReference)
    {
        $this->aData[] = [
            'type' => 'activate_reference',
            'data' => [
                'descendant' => $oReference->descendant,
            ],
        ];
    }

    /**
     * Откатывает данные по внутреннему массиву.
     */
    public function revertData()
    {
        foreach ($this->aData as $aParam) {
            $type = $aParam['type'];
            $data = $aParam['data'];
            switch ($type) {
                case 'set_param':
                    Parameters::setParams(
                        $data['parent'],
                        $data['group'],
                        $data['name'],
                        $data['value'],
                        $data['show_val']
                    );
                    break;
                case 'del_param':
                    Parameters::removeByName(
                        $data['name'],
                        $data['group'],
                        $data['parent']
                    );
                    break;
                case 'set_css_param':
                    CssParam::updateAll(
                        ['value' => $data['value']],
                        ['name' => $data['name']]
                    );
                    break;
                case 'del_file':
                    if (is_file(WEBPATH . $data['file'])) {
                        unlink(WEBPATH . $data['file']);
                    }
                    break;
                case 'activate_reference':
                    $oReferences = References::findAll(['descendant' => $data['descendant']]);
                    foreach ($oReferences as $oReference) {
                        $oReference->active = true;
                        $oReference->save();
                    }
                    break;
            }
        }
    }
}
