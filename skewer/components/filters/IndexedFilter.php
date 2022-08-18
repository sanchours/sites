<?php

namespace skewer\components\filters;

use skewer\base\section\Tree;
use yii\base\UserException;
use yii\helpers\StringHelper;

class IndexedFilter extends FilterPrototype
{
    protected function initData($sFilterConditions)
    {
        //костыль на разделение данных из post-a
        //#70912  #70886
        $aPost = \Yii::$app->request->post();
        if ($aPost && isset($aPost['data'])) {
            unset($aPost['data']);
        }

        // Если есть post-параметры(они приходят из полей, которые не имеют ссылки: input, слайдер цены), то
        // формируем ссылку и делаем редирект. На новой странице будем разбирать урл
        if ($aPost) {
            $aPostDataToInnerFormat = $this->canonizeToArrayFormat($aPost);
            $aFilteredValues = $this->filteringInputValues($aPostDataToInnerFormat);
            $aData4Url = $this->converArrayIdToAlias($aFilteredValues);
            $sUrl = $this->buildUrlByFilterData($aData4Url);

            \Yii::$app->response
                ->redirect($sUrl)
                ->send();
        }

        $aUrlParams = self::filterConditionToArray($sFilterConditions);
        $aUrlParams = $this->canonizeToArrayFormat($aUrlParams);
        $aUrlParams = $this->filteringInputValues($aUrlParams);
        $this->data = $this->convertArrayAliasToId($aUrlParams);
    }

    /**
     * Строит урл по массиву условий фильтра.
     *
     * @param array $aFilterData - массив условий фильтра
     *
     * @return string
     */
    public function buildUrlByFilterData($aFilterData)
    {
        ksort($aFilterData);

        $sUrl = '';
        foreach ($aFilterData as $sFieldName => $item) {
            // кодируем спец. символы
            $item = array_map(static function ($el) {
                return urlencode($el);
            }, $item);

            $Val = implode(',', $item);
            $sUrl .= sprintf(';%s=%s', $sFieldName, $Val);
        }

        $sUrl = trim($sUrl, ';');

        $sSectionPath = Tree::getSectionAliasPath($this->iShowSection, true, true);

        if ($sUrl) {
            $sUrl = $sSectionPath . "filter/{$sUrl}";
        } else {
            $sUrl = $sSectionPath;
        }

        // Убираем последние слеши
        $sUrl = rtrim($sUrl, '/');

        // Добавляем заключительный слеш
        $sUrl .= '/';

        return $sUrl;
    }

    /**
     * Проверяет может ли ссылка, построенная из массива $aFilterData индексироваться поисковыми системами.
     *
     * @param $aFilterData - массив, содержащий условия фильтра
     *
     * @return bool
     */
    public function canIndexUrlByFilterData($aFilterData)
    {
        $aIndexData = [];
        foreach ($aFilterData as $sNameField => $item) {
            if (!isset($this->aFilterFields[$sNameField])) {
                continue;
            }

            $oFilterField = $this->aFilterFields[$sNameField];

            if (!$oFilterField->canHaveTitle()) {
                continue;
            }

            $aIndexData[$sNameField] = $item;
        }

        $bCanIndex = true;
        if (count($aIndexData) > 4) {
            return false;
        }

        foreach ($aIndexData as $sNameField => $mValues) {
            if (is_array($mValues) && count($mValues) >= 2) {
                $bCanIndex = false;
                break;
            }
        }

        return $bCanIndex;
    }

    /**
     * Сформирует массив условий фильтра для значения $sFieldValue поля $sFieldName.
     *
     * @param string $sFieldName - имя поля карточки
     * @param string $sFieldValue - значение поля
     *
     * @return array
     */
    public function buildData4FilterValue($sFieldName, $sFieldValue)
    {
        $aFilterData = $this->data;

        if (isset($aFilterData[$sFieldName])) {
            if (($pos = array_search($sFieldValue, $aFilterData[$sFieldName])) !== false) {
                unset($aFilterData[$sFieldName][$pos]);
            } else {
                $aFilterData[$sFieldName][] = $sFieldValue;
            }
        } else {
            $aFilterData[$sFieldName] = [$sFieldValue];
        }

        $aData4FilterValue = $this->converArrayIdToAlias($aFilterData);

        return $aData4FilterValue;
    }

    /**
     * Конвертирует массив в формат alias(ключ)->id(значение).
     *
     * @param $aFilterData - конвертируемый массив
     * @param bool $bSkipEmpty - пропускать параметры с пустыми значениями
     *
     * @throws UserException
     *
     * @return array
     */
    public function convertArrayAliasToId($aFilterData, $bSkipEmpty = true)
    {
        return self::convertArray($aFilterData, $sFormat = 'alias->id', $bSkipEmpty);
    }

    /**
     * Конвертирует массив в формат id(ключ)->alias(значение).
     *
     * @param $aFilterData - конвертируемый массив
     * @param bool $bSkipEmpty - пропускать параметры с пустыми значениями
     *
     * @throws UserException
     *
     * @return array
     */
    public function converArrayIdToAlias($aFilterData, $bSkipEmpty = true)
    {
        return self::convertArray($aFilterData, $sFormat = 'id->alias', $bSkipEmpty);
    }

    /**
     * Конвертирует массив в формат id(ключ)->title(значение).
     *
     * @param $aFilterData - конвертируемый массив
     * @param bool $bSkipEmpty - пропускать параметры с пустыми значениями
     *
     * @throws UserException
     *
     * @return array
     */
    public function convertArrayIdToTitle($aFilterData, $bSkipEmpty = true)
    {
        return self::convertArray($aFilterData, $sFormat = 'id->title', $bSkipEmpty);
    }

    /**
     * Конвертирует массив в заданный формат
     *
     * @param $aFilterData - конвертируемый массив
     * @param $sFormat - новый формат
     * 1. alias->id - ссылочные поля(справочники/коллекции) будут преобразованы в формат  alias(ключ)->id(значение)
     * 2. id->alias - ссылочные поля(справочники/коллекции) будут преобразованы в формат  id(ключ)->alias(значение)
     * 3. id->title - ссылочные поля(справочники/коллекции) будут преобразованы в формат  id(ключ)->title(значение)
     * @param bool $bSkipEmpty - пропускать параметры с пустыми значениями
     *
     * @throws UserException
     *
     * @return array
     */
    private function convertArray($aFilterData, $sFormat, $bSkipEmpty = true)
    {
        $aOut = [];

        foreach ($aFilterData as $sFieldName => $aDataItem) {
            if (!isset($this->aFilterFields[$sFieldName])) {
                continue;
            }

            $oFilterField = $this->aFilterFields[$sFieldName];

            if ($bSkipEmpty) {
                if (is_array($aDataItem)) {
                    $aCountValues = array_count_values($aDataItem);

                    if (!$aCountValues) {
                        continue;
                    }

                    if ((count($aCountValues) == 1) && reset($aCountValues) && (key($aCountValues) == '')) {
                        continue;
                    }
                } elseif (!$aDataItem) {
                    continue;
                }
            }

            switch ($sFormat) {
                case 'alias->id':
                    $aConvertedValue = $oFilterField->convertValueToId($aDataItem);
                    break;
                case 'id->alias':
                    $aConvertedValue = $oFilterField->convertValueToAlias($aDataItem);
                    break;
                case 'id->title':
                    $aConvertedValue = $oFilterField->convertValueToTitle($aDataItem);
                    break;
                default:
                    throw new UserException(sprintf('Неизвестный формат %s', $sFormat));
            }

            if ($aConvertedValue !== false) {
                $aOut[$sFieldName] = $aConvertedValue;
            } else {
                $aOut[$sFieldName] = $aDataItem;
            }
        }

        return $aOut;
    }

    /**
     * Преобразует строку-условие для фильтра в массив.
     *
     * @param string $sFilterCondition - условие.
     * Пример: price=100,400;brand=casio;case_material=zoloto;discount=1
     *
     * @return array
     */
    public static function filterConditionToArray($sFilterCondition)
    {
        $aOut = [];

        $aFilterParams = StringHelper::explode($sFilterCondition, ';', true, true);

        foreach ($aFilterParams as $aFilterParam) {
            if (stristr($aFilterParam, '=') !== false) {
                list($sFieldName, $sFieldValue) = StringHelper::explode($aFilterParam, '=', true, false);

                $aFieldValue = (mb_strpos($sFieldValue, ',') !== false)
                    ? StringHelper::explode($sFieldValue, ',', true, true)
                    : [$sFieldValue];

                // Декодируем
                $aFieldValue = array_map(
                    static function ($item) {
                        return urldecode($item);
                    },
                    $aFieldValue
                );

                $aOut[$sFieldName] = $aFieldValue;
            }
        }

        return $aOut;
    }

    /**
     * Получить disallow правила фильтра для Robots.txt.
     *
     * @return array
     */
    public function getRobotsDisallowPatterns()
    {
        $aResult = [];

        foreach ($this->aFilterFields as $oFilterField) {
            // Поля, значения кот-х не имеют заголовков не индексируются(слайдер цен, input)
            if (!$oFilterField->canHaveTitle()) {
                $aResult[$oFilterField->getFieldName()] = '/*' . $oFilterField->getFieldName() . '=';
            }
        }

        return $aResult;
    }
}
