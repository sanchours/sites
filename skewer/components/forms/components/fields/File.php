<?php

declare(strict_types=1);

namespace skewer\components\forms\components\fields;

use skewer\base\ui\builder\FormBuilder;
use skewer\components\forms\components\dto\FieldFormBuilderByType;
use skewer\components\forms\components\typesOfValid;
use skewer\components\forms\entities\FieldEntity;
use skewer\components\forms\entities\FormOrderEntity;
use yii\helpers\ArrayHelper;
use skewer\components\forms\components\typesOfValid\File as FileTypeOfValid;
use yii\helpers\FileHelper;

class File extends TypeFieldAbstract
{
    protected $typeExtJs = 'file';

    public $needSaveFile = true;

    public $hasExtraFile = true;

    protected $typeDB = 'text';

    public function setFieldValue(string $name, string $value): string
    {
        if (isset($_FILES[$name])) {
            return $_FILES[$name]['name'];
        }

        return parent::setFieldValue($name, $value);
    }

    public function addFieldInFormInterface(
        FormBuilder &$form,
        FieldFormBuilderByType $fieldFormBuilder
    ) {
        if ($fieldFormBuilder->formParam !== null) {
            $form->field(
                $fieldFormBuilder->slug,
                $fieldFormBuilder->title,
                'hide'
            );

            $filename = basename($fieldFormBuilder->formParam);
            $href = "/local/?ctrl=FormOrders&&field={$fieldFormBuilder->id}&&fileName={$filename}";

            $form->fieldLink(
                $fieldFormBuilder->slug . '_show',
                $fieldFormBuilder->title,
                \Yii::t('forms', 'link_file') . $filename,
                $href
            );
        } else {
            $form->field(
                $fieldFormBuilder->slug,
                $fieldFormBuilder->title,
                $this->typeExtJs,
                ['disabled' => 1]
            );
        }
    }

    public function skipOnList()
    {
        return true;
    }

    public function deleteExtraData(int $idForm, string $slugForm, int $idOrder)
    {
        $aParam = FieldEntity::getFieldsByFormIdAndType(
            $idForm,
            $this->getName()
        );
        $slugs = ArrayHelper::getColumn($aParam, 'slug');

        if (!empty($slugs)) {
            $formOrder = new FormOrderEntity($idForm);
            $files = $formOrder
                ->selectFrom()
                ->fields($slugs)
                ->where('id', $idOrder)
                ->getAll();

            foreach ($files[0] as $file) {
                if ($file) {
                    if (file_exists(ROOTPATH . $file)) {
                        unlink(ROOTPATH . $file);
                    }
                }
            }
        }
    }

    public function clearExtraData(int $idForm, string $fieldName)
    {
        $creator = new FormOrderEntity($idForm);
        if ($creator->hasFieldByName($fieldName)) {
            $creator->clearFieldValue($fieldName);
        }
    }

    /**
     * @param $path
     *
     * @throws \yii\base\ErrorException
     */
    public function deletePrivateFiles($path)
    {
        FileHelper::removeDirectory(PRIVATE_FILEPATH . $path);
    }

    public function getValidateRules(int $maxLength): array
    {
        $maxFileSize = typesOfValid\File::getMaxFileSize($maxLength);
        /* Передаю 2 параметра, по первому - js валидация, второй для красивого сообщения об ошибке */
        $rules['filesize'] = [$maxFileSize * 1024 * 1024, $maxFileSize];
        $rules['extension'] = \Yii::$app->getParam([
            'upload',
            'allow',
            'files_form',
        ]);

        return $rules;
    }

    public function getExtraData($param_name)
    {
        return (isset($_FILES[$param_name]['tmp_name']) && $_FILES[$param_name]['tmp_name'])
            ? $_FILES[$param_name]['tmp_name']
            : null;
    }

    public function getParseData4CRM(string $title, string $value): string
    {
        return "{$title}: {$value}";
    }

    public function getValueForLetter($sParamValue, $sParamDefault)
    {
        $start = mb_strrpos($sParamValue, '/');

        return mb_substr(
            $sParamValue,
            $start + 1,
            mb_strlen($sParamValue) - $start
        );
    }

    /**
     * Список разрешенных валидаторов
     * @return array
     */
    protected function getAvailableTypesOfValid(): array
    {
        return [FileTypeOfValid::class];
    }

}
