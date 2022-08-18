<?php

declare(strict_types=1);

namespace skewer\components\forms\service;

use skewer\base\section\Parameters;
use skewer\build\Design\Zones\Api as ZoneApi;
use skewer\components\ext\FormView;
use skewer\components\forms\components\dto\FieldFormBuilderByType;
use skewer\components\forms\entities\FormEntity;
use skewer\components\forms\forms\FormAggregate;
use skewer\components\forms\traits\ParserExtJsTrait;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class FormSectionService
{
    use ParserExtJsTrait;

    private $_idSection;

    public function __construct(int $idSection)
    {
        $this->_idSection = $idSection;
    }

    /**
     * Получение форм для раздела.
     *
     * @param string $moduleName
     * @param array $forms
     * @param bool $bSort
     *
     * @throws \Exception
     *
     * @return array|FormAggregate[]
     */
    public function get4Section(
        string $moduleName = 'Forms',
        &$forms = [],
        $bSort = false
    ) {
        /** Все области страницы */
        $zones = ZoneApi::getZoneList($this->_idSection);

        /** Параметры с объектом форм */
        $paramsForm = Parameters::getList($this->_idSection)
            ->name(Parameters::object)
            ->value($moduleName)
            ->index('group')
            ->rec()
            ->asArray()
            ->get();

        if (!$paramsForm) {
            return [];
        }

        /** Массив с ключами всех групп параметров, содержащих объект форм */
        $formsGroup = ArrayHelper::map($paramsForm, 'group', '');

        if ($bSort) {
            /** Список всех используемых групп в разделе */
            $labels = [];
            foreach ($zones as &$paramZone) {
                foreach (ZoneApi::getLabelList(
                    $paramZone['id'],
                    $this->_idSection
                ) as $label) {
                    $labels[$label['name']] = 0;
                }
            }

            // Отсортировать согласно следованию зон на странице, а отсутствующие группы добавить в конец
            $formsGroup = array_intersect_key(
                $labels,
                $formsGroup
            ) + array_diff_key($formsGroup, $labels);
        }

        /** Id всех используемых форм в разделе */
        $idsFormsFromParams = Parameters::getList($this->_idSection)
            ->group(array_keys($paramsForm))
            ->name('FormId')
            ->index('group')
            ->rec()
            ->asArray()
            ->get();

        $forms = [];
        $formAggregators = [];

        //сохранение стабильной сортировки групп форм и включения в результат групп без параметра FormId
        foreach ($formsGroup as $key => $value) {
            if (isset($idsFormsFromParams[$key])) {
                $forms[$key] = (int) ArrayHelper::getValue(
                    $idsFormsFromParams[$key],
                    'value'
                );

                if ($forms[$key] !== 0) {
                    try {
                        $formAggregators[] = new FormAggregate($forms[$key]);
                    } catch(UserException $e) {
                        // принудительное глушение исключения
                        // в случае отсутствия заданной формы (удалена) выдаст исключение
                        // форма будет пропущена и интерфейс отстроится без нее
                    }
                }
            } else {
                $forms[$key] = null;
            }
        }

        return $forms ? $formAggregators : [];
    }

    public function getFormsForSelection(): array
    {
        $notSystemForms = FormEntity::getNotSystemForms();
        $formsSelection = [
            0 => ' -- ' . \Yii::t('forms', 'form_not_selected') . ' --',
        ];

        foreach ($notSystemForms as $system) {
            $formsSelection[$system->id] = $system->title;
        }

        return FormView::markUniqueValue($formsSelection);
    }

    /**
     * @throws \Exception
     *
     * @return null|FormAggregate|mixed
     */
    public function getFormForCurrentSection()
    {
        $formsSection = $this->get4Section();

        return $formsSection ? array_shift($formsSection) : null;
    }

    /**
     * Отдает список форм в разделе,
     * которые добавляют данные в БД.
     *
     * @throws \Exception
     *
     * @return FormAggregate[]
     */
    public function getHandlerBaseFormsForSection(): array
    {
        $formAggregators = $this->get4Section();

        $handlerBaseForms = [];

        foreach ($formAggregators as $key => $formAggregator) {
            assert($formAggregator instanceof FormAggregate);

            if ($formAggregator->handler->isBaseType()) {
                $handlerBaseForms[$key] = $formAggregator;
            }
        }

        return $handlerBaseForms;
    }

    /**
     * @return \Generator
     */
    public function getAllHandlerBaseForms()
    {
        $entities = FormEntity::find()->select(['id'])->onlyBaseType()->all();
        /** @var FormEntity $entity */
        foreach ($entities as $entity) {
            yield $this->combineInOneArray(
                (new FormAggregate($entity->id))->getFullObject()
            );
        }
    }

    /**
     * Формирование сущности для построения интерфейса детальной заказов из форм
     *
     * @param FormAggregate $form
     * @param array $formOrder
     *
     * @throws \ReflectionException
     * @throws \yii\base\UserException
     *
     * @return \Generator
     */
    public function getFieldsForFormOrder(
        FormAggregate $form,
        array $formOrder = []
    ) {
        foreach ($form->getFields() as $field) {
            $fieldBuilder = new FieldFormBuilderByType();

            $fieldBuilder->id = $field->idField;
            $fieldBuilder->title = $field->settings->title;
            $fieldBuilder->slug = $field->settings->slug;

            $fieldBuilder->formParam = ArrayHelper::getValue(
                $formOrder,
                $field->settings->slug,
                null
            );

            $fieldBuilder->defaultValues = $field->type->default
                ? $field->parseDataAsList($field->type->default)
                : null;

            $fieldBuilder->fieldObject = $field->type->getFieldObject();

            yield $fieldBuilder;
        }
    }
}
