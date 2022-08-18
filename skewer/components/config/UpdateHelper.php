<?php

namespace skewer\components\config;

use skewer\base\log\Logger;
use skewer\base\orm\Query;
use skewer\base\section;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site\Layer;
use skewer\build\Tool\Patches;
use skewer\components\auth\Policy;
use skewer\components\code_generator;
use skewer\components\gateway\Server;
use skewer\helpers\Files;

/**
 * Системный хелпер, предоставляющий набор методов для установки модулей и патчей.
 */
class UpdateHelper
{
    /* Работа с БД */

    /**
     * Выполняет SQL запросы из файла $sFile.
     *
     * @param string $sFile Путь к файлу с SQL инструкциями
     *
     * @throws UpdateException
     *
     * @return bool
     *
     * @deprecated
     */
    public function executeSQLFile($sFile)
    {
        if (!file_exists($sFile)) {
            throw new UpdateException(\Yii::t('app', 'updateError_sqlfile_notfound', $sFile));
        }
        $sSQLQueries = file_get_contents($sFile);

        if (empty($sSQLQueries)) {
            return false;
        }

        if (!Query::SQL($sSQLQueries)) {
            throw new UpdateException(\Yii::t('app', 'updateError_wrong_query'));
        } //$this->oDB->error
        return true;
    }

    // func

    /**
     * Выполняет запрос $sQuery с данными $aData к БД.
     *
     * @param string $sQuery Выполняемый SQL запрос
     * @param array $aData Данные для подстановки вместо placeholders
     *
     * @throws Exception
     * @throws UpdateException
     *
     * @return \skewer\base\orm\service\DataBaseAdapter
     */
    public function executeSQLQuery($sQuery, $aData = [])
    {
        if (empty($sQuery)) {
            throw new UpdateException(\Yii::t('app', 'updateError_wrong_query'));
        }
        $rRes = Query::SQL($sQuery, $aData);

        return $rRes;
    }

    // func

    /**
     * Выполняет запрос на добавление поля и возвращает его ID.
     *
     * @param $sQuery
     * @param array $aData
     *
     * @return mixed
     */
    public function executeSQLInsert($sQuery, $aData = [])
    {
        $rRes = $this->executeSQLQuery($sQuery, $aData);

        return $rRes->lastId();
    }

    // func

    /**
     * Выполняет запрос на выборку и возвращает первый картеж.
     *
     * @param $sQuery
     * @param array $aData
     *
     * @return mixed
     */
    public function executeSQLSelect($sQuery, $aData = [])
    {
        $rRes = $this->executeSQLQuery($sQuery, $aData);

        $aRow = $rRes->fetchArray();

        return $aRow;
    }

    // func

    /**
     * Перенос полей из одной таблицы в другую.
     *
     * @param $sFromTable
     * @param $sTargetTable
     * @param $aFields
     * @param int|string $mCondition
     *
     * @return bool
     */
    public function transferRowsForDB($sFromTable, $sTargetTable, $aFields, $mCondition = '1')
    {
        $sFields = '`' . implode('`,`', $aFields) . '`';

        $sQuery =
            "INSERT INTO {$sTargetTable} ({$sFields})
              (SELECT {$sFields} FROM {$sFromTable} WHERE {$mCondition})";

        $this->executeSQLQuery($sQuery);

        $sQuery =
            "DELETE FROM {$sFromTable} WHERE {$mCondition}";

        $this->executeSQLQuery($sQuery);

        return true;
    }

    /* Работа с Разделами */

    /**
     * Добавляет подраздел к $iParent с названием $sTitle.
     *
     * @param int $iParent Id родительского раздела
     * @param string $sTitle Название раздела
     * @param string $sAlias Псевдоним для раздела. Если не указан - формируется из Названия раздела
     * @param bool $bVisible Флаг вида отображения в системе
     * @param string $sLink Внешняя (redirect) ссылка с раздела
     *
     * @throws UpdateException
     *
     * @return bool|int Возвращает Id созданного раздела либо false в случае ошибки
     */
    public function addSection($iParent, $sTitle, $sAlias = '', $bVisible = true, $sLink = '')
    {
        $oSection = Tree::addSection($iParent, $sTitle, 0, $sAlias, $bVisible, $sLink);

        if (!$oSection) {
            throw new UpdateException(\Yii::t('app', 'updateError_sectionNotAdded', $sTitle));
        }

        return $oSection->id;
    }

    // func

    /**
     * Добавялет подраздел к $iParent на основе раздела-шаблона $iTemplateId.
     *
     * @param int $iParent Id родительского раздела
     * @param int $iTemplateId Id Раздела-шаблона
     * @param string $sAlias Псевдоним для раздела. Если не указан - формируется из Названия раздела
     * @param string $sTitle Название раздела
     * @param int $bVisible Флаг вида отображения в системе
     * @param string $sLink Внешняя (redirect) ссылка с раздела
     *
     * @throws UpdateException
     *
     * @return bool|int  Возвращает Id созданного раздела либо false в случае ошибки
     */
    public function addSectionByTemplate($iParent, $iTemplateId, $sAlias, $sTitle, $bVisible = section\Visible::VISIBLE, $sLink = '')
    {
        $oSection = Tree::addSection($iParent, $sTitle, $iTemplateId, $sAlias, $bVisible, $sLink);

        if (!$oSection) {
            throw new UpdateException(\Yii::t('app', 'updateError_tplNotFound', $iTemplateId));
        }

        return $oSection->id;
    }

    /**
     * Возвращает Id раздела по пути псевдонимов $sAliasPath.
     *
     * @param string $sAliasPath путь до раздела по дереву псевдонимов
     *
     * @throws UpdateException
     *
     * @return bool|int Возвращает Id раздела либо false, если таковой небыл найден
     */
    public function getSectionIdByAlias($sAliasPath)
    {
        $iSectionId = Tree::getSectionByPath($sAliasPath);

        if (!$iSectionId) {
            throw new UpdateException(\Yii::t('app', 'updateError_AliasNotFound', $sAliasPath));
        }

        return $iSectionId;
    }

    // func

    public function updateSection()
    {
    }

    // func

    /**
     * Удаляет раздел $iSectionId.
     *
     * @param int $iSectionId Id удаляемого раздела
     *
     * @throws UpdateException
     *
     * @return bool
     */
    public function removeSection($iSectionId)
    {
        if (!Tree::removeSection($iSectionId)) {
            throw new UpdateException(\Yii::t('app', 'updateError_SectionNotDeleted', $iSectionId));
        }

        return true;
    }

    // func

    /**
     * @param $iSectionId
     *
     * @return bool
     */
    public function isSection($iSectionId)
    {
        return (bool) Tree::getSection($iSectionId);
    }

    public function applyAccessToSection()
    {
    }

    // func

    /* Работа с параметрами */

    /**
     * Добавляет параметр в раздел $iParent с именем $sName и значением $mValue в группу $sGroup. Если указаны, дополнительные параметры - они
     * так же будут добавлены. Если соответствующего параметра нет в базе - добавит его.
     *
     * @param int $iParent Id родительского раздела для параметра
     * @param string $sName Имя параметра
     * @param mixed $mValue Значение параметра
     * @param string $mShowVal Расширенное значение параметра
     * @param string $sGroup Группа параметра (по-умолчанию добавялет в .)
     * @param string $sTitle Название параметра
     * @param int $iAccessLevel Тип параметра
     *
     * @throws UpdateException
     *
     * @return bool|int Возвращает Id созданного параметра либо false в случае ошибки
     */
    public function addParameter($iParent, $sName, $mValue, $mShowVal = '', $sGroup = Parameters::settings, $sTitle = '', $iAccessLevel = 0)
    {
        if (!(int) $iParent or empty($sName)) {
            throw new UpdateException(\Yii::t('app', 'ParamsWrongData', $sName, $iParent));
        }
        $iNewParamId = Parameters::setParams($iParent, $sGroup, $sName, $mValue, $mShowVal, $sTitle, $iAccessLevel);

        if (!$iNewParamId) {
            throw new UpdateException(\Yii::t('app', 'ParamNotSaved', $sName, $iParent));
        }

        return $iNewParamId;
    }

    // func

    /**
     * Обновляет данные параметра $sName группы $sGroup в разделе $iParent. Если Указаны дополнительные параметры, то они так же будут сохранены
     * как свойства параметра. Если соответствующего параметра нет в базе - упадет с ошибкой.
     *
     * @param int $iParent Id родительского раздела для параметра
     * @param string $sName Имя параметра
     * @param string $sGroup Группа параметра (по-умолчанию добавялет в .)
     * @param null|mixed $mValue Значение параметра
     * @param null|mixed $mShowVal Расширенное значение параметра
     * @param null|string $sTitle Название параметра
     * @param null|int $iAccessLevel Тип параметра
     *
     * @throws UpdateException
     *
     * @return bool|int Возвращает Id обновленного параметра либо false в случае ошибки
     */
    public function updateParameter($iParent, $sName, $sGroup, $mValue = null, $mShowVal = null, $sTitle = null, $iAccessLevel = null)
    {
        if (!(int) $iParent or empty($sName) or empty($sGroup)) {
            throw new UpdateException(\Yii::t('app', 'ParamsWrongData', [$sName, $iParent]));
        }
        $oParam = Parameters::getByName($iParent, $sGroup, $sName);
        if (!$oParam) {
            throw new UpdateException(\Yii::t('app', 'ParamNotFound', [$sName, $iParent]));
        }
        $oParam->value = $mValue;
        $oParam->title = $sTitle;
        $oParam->show_val = $mShowVal;
        $oParam->access_level = $iAccessLevel;

        if (!$oParam->save()) {
            throw new UpdateException(\Yii::t('app', 'ParamNotSaved', [$sName, $iParent]));
        }

        return $oParam->id;
    }

    // func

    /**
     * Обновляет/добавляет данные параметра $sName группы $sGroup в разделе $iParent.
     * Если Указаны дополнительные параметры, то они так же будут сохранены как свойства параметра.
     *
     * @param int $iParent Id родительского раздела для параметра
     * @param string $sName Имя параметра
     * @param string $sGroup Группа параметра (по-умолчанию добавялет в .)
     * @param null|mixed $mValue Значение параметра
     * @param null|mixed $mShowVal Расширенное значение параметра
     * @param null|string $sTitle Название параметра
     * @param null|int $iAccessLevel Тип параметра
     *
     * @throws UpdateException
     *
     * @return bool|int Возвращает Id обновленного параметра либо false в случае ошибки
     */
    public function setParameter($iParent, $sName, $sGroup, $mValue = null, $mShowVal = null, $sTitle = null, $iAccessLevel = null)
    {
        if ($this->isSetParameterInSection($iParent, $sName, $sGroup)) {
            return $this->updateParameter($iParent, $sName, $sGroup, $mValue, $mShowVal, $sTitle, $iAccessLevel);
        }

        return $this->addParameter(
            $iParent,
            $sName,
            $mValue,
            $mShowVal ? $mShowVal : '',
            $sGroup ? $sGroup : '.',
            $sTitle ? $sTitle : '',
            $iAccessLevel ? $iAccessLevel : 0
            );
    }

    /**
     * Обновляет значение на $mValue параметра $sName в группе $sGroup из раздела $iParent.
     *
     * @param int $iParent Id Раздела
     * @param string $sName Имя параметра
     * @param string $sGroup Группа параметра
     * @param mixed $mValue Новое значение параметра
     *
     * @return bool|int
     */
    public function updateParameterValue($iParent, $sName, $sGroup, $mValue)
    {
        return $this->updateParameter($iParent, $sName, $sGroup, $mValue);
    }

    // func

    /**
     * Обновляет расширенное значение на $mText параметра $sName в группе $sGroup из раздела $iParent.
     *
     * @param int $iParent Id Раздела
     * @param string $sName Имя параметра
     * @param string $sGroup Группа параметра
     * @param mixed $mText Новое расширенное значение параметра
     *
     * @return bool|int
     */
    public function updateParameterText($iParent, $sName, $sGroup, $mText)
    {
        return $this->updateParameter($iParent, $sName, $sGroup, null, $mText);
    }

    // func

    /**
     * Обновляет название на $sNewTitle параметра $sName в группе $sGroup из раздела $iParent.
     *
     * @param int $iParent Id Раздела
     * @param string $sName Имя параметра
     * @param string $sGroup Группа параметра
     * @param string $sNewTitle Новое название параметра
     *
     * @return bool|int
     */
    public function updateParameterTitle($iParent, $sName, $sGroup, $sNewTitle)
    {
        return $this->updateParameter($iParent, $sName, $sGroup, null, null, $sNewTitle);
    }

    // func

    /**
     * Обновляет тип на $iNewType параметра $sName в группе $sGroup из раздела $iParent.
     *
     * @param int $iParent Id Раздела
     * @param string $sName Имя параметра
     * @param string $sGroup Группа параметра
     * @param int $iNewType Новый тип параметра
     *
     * @return bool|int
     */
    public function updateParameterType($iParent, $sName, $sGroup, $iNewType)
    {
        return $this->updateParameter($iParent, $sName, $sGroup, null, null, null, $iNewType);
    }

    // func

    /**
     * Удаляет параметр $sName группы $sGroup из раздела $iParent.
     *
     * @param int $iParent Id родительского раздела
     * @param string $sName Имя удаляемого параметра
     * @param string $sGroup Имя родительской группы
     *
     * @return bool
     */
    public function removeParameter($iParent, $sName, $sGroup = Parameters::settings)
    {
        return Parameters::removeByName($sName, $sGroup, $iParent);
    }

    // func

    /**
     * Удаляет параметр с Id = $iParamId.
     *
     * @param int $iParamId
     *
     * @throws UpdateException
     *
     * @return bool|int
     */
    public function removeParameterById($iParamId)
    {
        if (!(int) $iParamId) {
            throw new UpdateException(\Yii::t('app', 'ParamNotDeleted', $iParamId));
        }

        return Parameters::removeById($iParamId);
    }

    // func

    public function isSetParameter($iParent, $sName, $sGroup = Parameters::settings)
    {
        return (bool) Parameters::getByName($iParent, $sGroup, $sName, true);
    }

    public function isSetParameterInSection($iParent, $sName, $sGroup = Parameters::settings)
    {
        return (bool) Parameters::getByName($iParent, $sGroup, $sName, false);
    }

    /* Работа с реестром */

    /**
     * Добавляет ключ $sKey со значением $mValue в реестр. Добавление ведется в реестр настроек
     * площадки с именем, включающим в себя номер текущей версии сборки build_<build version> либо в указанный в $sStorageName
     * Если реестр с таким именем не был найден формируется исключение типа skewer\components\config\UpdateException.
     *
     * @example build_0008
     *
     * Запись ключей пути ведется от корневого зарезервированного ключа "buildConfig"
     * @example buildConfig.funcPolicy.Page.items
     *
     * @param string $sKey Имя ключа, в который будут добавлены данные
     * @param mixed $mValue Добавляемые в ключ данные
     *
     * @throws UpdateException
     *
     * @return bool
     */
    public function addBuildRegistryKey($sKey, $mValue)
    {
        return ConfigUpdater::buildRegistry()->append($sKey, $mValue);
    }

    /**
     * Возвращает данные, хранящиеся в ключе $sKey. В качестве текущего реестра выбирается реестр настроек
     * площадки с именем, включающим в себя номер текущей версии сборки build_<build version> либо в указанный в $sStorageName
     * Если реестр с таким именем не был найден формируется исключение типа skewer\components\config\UpdateException.
     *
     * @example build_0008
     *
     * @param string $sKey Имя иcкомого ключа
     *
     * @return null|mixed
     */
    public function getBuildRegistryKey($sKey)
    {
        return ConfigUpdater::buildRegistry()->get($sKey);
    }

    /**
     * Сохраняет измененное на $mValue значение ключа $sKey. В качестве текущего реестра выбирается реестр настроек
     * площадки с именем, включающим в себя номер текущей версии сборки build_<build version> либо в указанный в $sStorageName
     * Если реестр с таким именем не был найден формируется исключение типа skewer\components\config\UpdateException.
     *
     * @param string $sKey
     * @param mixed $mValue
     *
     * @throws UpdateException
     *
     * @return null|bool
     */
    public function updateBuildRegistryKey($sKey, $mValue)
    {
        return ConfigUpdater::buildRegistry()->set($sKey, $mValue);
    }

    /**
     * Возвращает true если ключ $sKey реестра найден либо false в противном случае. В качестве текущего
     * реестра выбирается реестр настроек площадки с именем, включающим в себя номер текущей версии сборки
     * build_<build version> либо в указанный в $sStorageName. Если реестр с таким именем не был найден
     * формируется исключение типа skewer\components\config\UpdateException.
     *
     * @param string $sKey
     *
     * @return null|bool
     */
    public function existsBuildRegistryKey($sKey)
    {
        return ConfigUpdater::buildRegistry()->exists($sKey);
    }

    /**
     * Удаляет из реестра ключ $sKey.  В качестве текущего реестра используется реестр настроек площадки с именем,
     * включающим в себя номер текущей версии сборки build_<build version> либо в указанный в $sStorageName.
     * Если реестр с таким именем не был найден формируется исключение типа skewer\components\config\UpdateException.
     *
     * @param string $sKey Имя удаляемого ключа
     *
     * @throws UpdateException
     *
     * @return null|bool
     */
    public function removeBuildRegistryKey($sKey)
    {
        return ConfigUpdater::buildRegistry()->remove($sKey);
    }

    /* Работа с файловой системой */

    /**
     * Создает директорию $sLocalPath. Поиск ведется от корневой директории сайта.
     *
     * @param string $sLocalPath путь и имя создаваемой директории
     *
     * @throws UpdateException
     *
     * @return bool|string Возвращает полный путь к созданной директории либо false в случае ошибки
     */
    public function makeDirectory($sLocalPath)
    {
        if (!$sNewPath = Files::makeDirectory($sLocalPath)) {
            throw new  UpdateException(\Yii::t('app', 'FolderNotCreated', $sLocalPath));
        }

        return $sNewPath;
    }

    // func

    /**
     * Перемещает директорию или файл $sOldPath по пути $sNewPath.
     *
     * @param string $sOldPath перемещаемая папка или файл
     * @param string $sNewPath новые путь и имя перемещаемой папки либо файла
     *
     * @throws UpdateException
     *
     * @return bool Возвращает true если перемещение прошло успешно либо false в случае ошибки
     */
    public function moveDirectory($sOldPath, $sNewPath)
    {
        if (!rename($sOldPath, $sNewPath)) {
            throw new UpdateException(\Yii::t('app', 'FolderNotMoved', $sOldPath, $sNewPath));
        }

        return true;
    }

    // func

    /**
     * Рекурсивно удаляет директорию $sPath.
     *
     * @param string $sPath путь к удаляемой директории, включая ROOTPATH
     *
     * @throws UpdateException
     *
     * @return bool Возвращает true, если удаление прошло успешно либо false в противном случае
     */
    public function removeDirectory($sPath)
    {
        if (!Files::delDirectoryRec($sPath)) {
            throw new UpdateException(\Yii::t('app', 'FolderNotRemoved', $sPath));
        }

        return true;
    }

    // func

    /**
     * Удаляет файл $sFilePath. Возвращает true, если удаление прошло успешно либо false в случае ошибки.
     *
     * @param string $sFilePath Полный путь к удаляемому файлу
     *
     * @throws UpdateException
     *
     * @return bool
     */
    public function removeFile($sFilePath)
    {
        if (!Files::remove($sFilePath)) {
            throw new UpdateException(\Yii::t('app', 'FileNotRemoved', $sFilePath));
        }

        return true;
    }

    // func

    /**
     * Перемещает файл $sFile в $sMovePath. Если указан $bHardSet, то при наличии по месту назначения
     * ранее созданного файла, оный будет заменен перемещаемым.
     *
     * @param string $sFile Перемещаемый файл
     * @param string $sMovePath Место назначения
     * @param bool $bHardSet Указатель на необходимость замены файла при наличии идентичного
     *
     * @throws UpdateException
     *
     * @return bool Возвращает true если перемещение прошло успешно либо false в случа ошибки
     */
    public function moveFile($sFile, $sMovePath, $bHardSet = true)
    {
        /* Перемещаемый файл существует */
        if (!file_exists($sFile) or
            empty($sMovePath)) {
            throw new UpdateException(\Yii::t('app', 'FileNotFound', $sFile));
        }
        /* Место назначения корректно */
        if (file_exists($sMovePath)) {
            if (!$bHardSet) {
                return false;
            }
        }

        if (!unlink($sMovePath)) {
            throw new UpdateException(\Yii::t('app', 'FileNotRemoved'));
        }
        /* Перемещаем */
        return rename($sFile, $sMovePath);
    }

    // func

    /**
     * Копирует файл $sFile в $sMovePath. Если указан $bHardSet, то при наличии по месту назначения
     * ранее созданного файла, оный будет заменен копируемым.
     *
     * @param string $sFile Перемещаемый файл
     * @param string $sMovePath Место назначения
     * @param bool $bHardSet Указатель на необходимость замены файла при наличии идентичного
     *
     * @throws UpdateException
     *
     * @return bool Возвращает true если копирование прошло успешно либо false в случа ошибки
     */
    public function copyFile($sFile, $sMovePath, $bHardSet = true)
    {
        /* Перемещаемый файл существует */
        if (!file_exists($sFile) or
            empty($sMovePath)) {
            throw new UpdateException(\Yii::t('app', 'FileNotFound', $sFile));
        }
        /* Место назначения корректно */
        if (file_exists($sMovePath)) {
            if (!$bHardSet) {
                return false;
            }
        }

        if (!unlink($sMovePath)) {
            throw new UpdateException(\Yii::t('app', 'FileNotRemoved'));
        }
        /* Копируем */
        return copy($sFile, $sMovePath);
    }

    // func

    /* Работа с .htaccess */

    /**
     * Заполняет шаблон файла .htaccess данными и перезаписывает в текущую обновляемую площадку в качестве корневого.
     * Шаблон лежит в директории "common/templates" сборки.
     * Внимание! Перезапись ведется в любом случае.
     *
     * @param string $sTplPath Путь к директории с шаблоном
     * @param array $aData Данные, вставляемые в шаблон
     *
     * @throws UpdateException
     *
     * @return bool
     *
     * @uses /skewerBuild/common/templates/htaccess.twig
     */
    public function updateHtaccess($sTplPath, $aData = [])
    {
        /* Генерируем файл с константами для площадки */

        $oCodeGen = new code_generator\CodeGenerator($sTplPath, ROOTPATH);

        $oCodeGen->add(new code_generator\templates\HtaccessTpl('.htaccess', $aData));

        if (!$oCodeGen->make()) {
            throw new UpdateException($oCodeGen->getException()->getMessage(), 0, $oCodeGen->getException());
        }

        return true;
    }

    // func

    /* Работа с константами системы */

    /**
     * Осуществляет синтаксичесикй разбор шаблона файла констант (по-умолчанию <buildPath>/common/templates/constants.twig)
     * и замену меток на их значения из $aData. Собранный файл записывается в директорию "config"
     * площадки и используется в качестве источника основных констант
     *
     * @param string $sTplPath Путь к директории с шаблоном
     * @param array $aData Массив данных для замены в шаблоне
     *
     * @throws UpdateException
     *
     * @return bool
     */
    public function updateConstants($sTplPath, $aData)
    {
        /* Генерируем файл с константами для площадки */
        $oCodeGen = new code_generator\CodeGenerator($sTplPath, ROOTPATH);

        $oCodeGen->add(new code_generator\templates\ConstantsTpl('config/constants.generated.php', $aData));

        if (!$oCodeGen->make()) {
            throw new UpdateException($oCodeGen->getException()->getMessage(), 0, $oCodeGen->getException());
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return true;
    }

    // func

    public function closeSite()
    {
    }

    // func

    public function openSite()
    {
    }

    // func

    /* Работа с политиками доступа */

    public function addAuthPolicy()
    {
    }

    // func

    public function updateAuthPolicy()
    {
    }

    // func

    public function removeAuthPolicy()
    {
    }

    // func

    /**
     * Устанавливает патч по номеру, если тот еще не стоял.
     *
     * @param int $sPatchNumber номер патча
     *
     * @throws UpdateException
     */
    public function installPatchByNumber($sPatchNumber)
    {
        $sPatchFile = $sPatchNumber . \DIRECTORY_SEPARATOR . $sPatchNumber . '.php';
        $this->installPatch($sPatchFile);
    }

    /**
     * Устанавливает патч, если тот еще не стоял.
     *
     * @param string $sPatchFile путь от директории PATCHPATH
     * @param bool $bUseLocalDir если стоит, то будет использована локальная директория с обновлениями для пориска файлов
     *
     * @return array|bool true/false/массив для отправки
     */
    public function installPatch($sPatchFile, $bUseLocalDir = false)
    {
        $mResult = Patches\Api::installPatch($sPatchFile, $bUseLocalDir);

        if (is_array($mResult)) {
            $mResult = json_encode($mResult);
        }

        return $mResult;
    }

    /**
     * Проверяет нлаличие таблицы по имени.
     *
     * @param string $sTableName
     *
     * @return bool
     */
    public static function tableExists($sTableName)
    {
        $rList = Query::SQL('SHOW TABLES');
        while ($row = $rList->fetchArray(\PDO::FETCH_NUM)) {
            if ($sTableName == $row[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Очищает директорию кеша для шаблонизатора.
     *
     * @throws UpdateException
     *
     * @return bool Возвращает true в случае успешного завершения либо исключение в случае ошибки
     */
    public function clearTwigCache()
    {
        $sTwigCachePath = \Yii::$app->getParam(['cache', 'rootPath']) . 'Twig' . \DIRECTORY_SEPARATOR;

        if (!is_dir($sTwigCachePath)) {
            throw new UpdateException('Twig cache directory does not exists or not readable!');
        }
        if (!is_writable($sTwigCachePath)) {
            throw new UpdateException('Twig cache directory does not cleared. Check rights!');
        }
        if (!Files::delDirectoryRec($sTwigCachePath, false)) {
            throw new UpdateException('Twig cache directory does not removed!');
        }

        return true;
    }

    /**
     * Выкидывание ошибки при установке сайта.
     *
     * @param $sMessage
     * @param null $mData
     * @param bool $bCritical - флаг критичности
     *
     * @throws UpdateException
     */
    protected function fail($sMessage, $mData = null, $bCritical = false)
    {
        Logger::dump(sprintf('Patch error: %s', $sMessage));
        Logger::error(sprintf('Patch error: %s', $sMessage));
        if ($mData) {
            Logger::dump('Error data:', $mData);
            Logger::error('Error data:', $mData);
        }

        if ($bCritical) {
            Server::$bHaveCriticalError = true;
        }

        $e = new UpdateException($sMessage);

        Logger::dumpException($e);
        throw $e;
    }

    /**
     * Устанавливает модуль, если тот еще не стоял.
     *
     * @param $sModuleName
     * @param $sLayer
     */
    protected function installModule($sModuleName, $sLayer)
    {
        $installer = new installer\Api();
        if (!$installer->isInstalled($sModuleName, $sLayer)) {
            $installer->install($sModuleName, $sLayer);
        }
    }

    /**
     * Обновляет конфиги для модуля.
     *
     * @param string $sModuleName имя модуля
     * @param string $sLayer имя слоя
     */
    protected function updateModuleConfig($sModuleName, $sLayer)
    {
        $installer = new installer\Api();
        $installer->updateConfig($sModuleName, $sLayer);
    }

    /**
     * Обновляет словари для модуля.
     *
     * @param string $sModuleName имя модуля
     * @param string $sLayer имя слоя
     */
    protected function updateModuleLang($sModuleName, $sLayer)
    {
        $installer = new installer\Api();
        $installer->updateLanguage($sModuleName, $sLayer, true);
    }

    /**
     * Обновляет CSS настройки для модуля.
     *
     * @param string $sModuleName имя модуля
     * @param string $sLayer имя слоя
     */
    protected function updateModuleCss($sModuleName, $sLayer)
    {
        $installer = new installer\Api();
        $installer->updateCss($sModuleName, $sLayer);
    }

    /**
     * @param $sModuleName
     *
     * @throws UpdateException
     * @throws \skewer\components\config\Exception
     */
    protected function addToolModuleForAdmin($sModuleName)
    {
        $oConfig = \Yii::$app->register->getModuleConfig($sModuleName, Layer::TOOL);

        if ($oConfig) {
            Policy::addModule(1, $sModuleName, $oConfig->getTitle());
            Policy::incPolicyVersion();
        } else {
            throw new UpdateException('not found module');
        }
    }

    /**
     * Установка записи системного раздела.
     *
     * @param $sName
     * @param $sTitle
     * @param $iValue
     * @param $sLanguage
     *
     * @return bool
     */
    protected function setServiceSections($sName, $sTitle, $iValue, $sLanguage)
    {
        return \Yii::$app->sections->setSection($sName, $sTitle, $iValue, $sLanguage);
    }
}// class
