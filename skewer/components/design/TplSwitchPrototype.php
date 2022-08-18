<?php

namespace skewer\components\design;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\ParamsAr as SectionParam;
use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\base\site\Site;
use skewer\build\Design\Zones;
use skewer\components\design\model\Params as CssParam;
use skewer\components\design\model\References;
use yii\base\UserException;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

/**
 * Прототип для класса замены шаблона вывода стандартного блока.
 */
abstract class TplSwitchPrototype
{
    /** @var bool если true, то будет отсутствовать в списке на переключение */
    public $bUse = true;

    /** @var BackupParams объект для сохранения данных до модификации */
    protected $oBackup;

    public function __construct()
    {
        $this->oBackup = new BackupParams();
    }

    /**
     * Отдает тип переключателя шаблонов.
     *
     * @return string
     */
    abstract protected function getType();

    /**
     * Отдает имя шаблона.
     *
     * @return string
     */
    protected function getName()
    {
        // отадает последнюю директорию из неймспейса целевого класса
        return StringHelper::basename(StringHelper::dirname(get_class($this)));
    }

    /**
     * Отдает имя название шаблона.
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Задает нужный шаблон.
     */
    public function setTpl()
    {
        // имя старого шаблона
        $sOldTpl = Parameters::getShowValByName(
            \Yii::$app->sections->root(),
            Zones\Api::layoutGroupName,
            $this->getType() . '_tpl',
            $this->getName()
        );

        // в бэкап добавляем имя старого шаблона
        $this->oBackup->setUserData('old_tpl', $sOldTpl);

        // стереть все включения определния шаблона
        Parameters::removeByName(
            $this->getType() . '_tpl',
            Zones\Api::layoutGroupName
        );

        // перекрыть шаблон в корневом разделе
        Parameters::setParams(
            \Yii::$app->sections->root(),
            Zones\Api::layoutGroupName,
            $this->getType() . '_tpl',
            $this->getName()
        );
    }

    /**
     * Заменяет набор модулей для сайта.
     */
    public function setModules()
    {
        $this->setModulesToLayout($this->getType(), $this->getModulesList());
    }

    /**
     * @param string $sLayout зона вывода
     * @param string $sModules набор модулей
     */
    protected function setModulesToLayout($sLayout, $sModules)
    {
        // запросить все, что есть
        $oOldModuleParams = SectionParam::findAll([
            'name' => $sLayout,
            'group' => Zones\Api::layoutGroupName,
        ]);

        // записать каждую запись в бэкап
        foreach ($oOldModuleParams as $oParam) {
            $this->oBackup->addParam($oParam);
        }

        // стереть все записи
        Parameters::removeByName($sLayout, Zones\Api::layoutGroupName);

        // заменить на новый в корне
        Parameters::setParams(\Yii::$app->sections->root(), Zones\Api::layoutGroupName, $sLayout, $sModules);
    }

    public function saveBackup()
    {
        Template::writeBackupFile($this->getType(), $this->getName(), $this->oBackup);
    }

    /**
     * Перестраивает CSS параметры
     * При необходимости функция перекрывется в нужном классе.
     */
    public function analyzeCssParams()
    {
    }

    /**
     * Задаёт значение для параметра.
     *
     * @param string $sGroup
     * @param string $sName
     * @param string $sValue
     *
     * @throws UserException если параметр не найден
     *
     * @return bool
     */
    protected function setBlockVal($sGroup, $sName, $sValue)
    {
        $sFullName = sprintf('%s.%s', $sGroup, $sName);

        $oCssParam = CssParam::findOne(['name' => $sFullName]);
        if (!$oCssParam) {
            throw new UserException("Parameter [{$sFullName}] not found");
        }
        $this->oBackup->addCssParam($oCssParam);

        $oCssParam->value = $sValue;

        return $oCssParam->save();
    }

    /**
     * Задаёт значение для css параметра.
     *
     * @param string $sPath
     * @param string $sValue
     *
     * @throws UserException если параметр не найден
     *
     * @return bool
     */
    protected function setCssVal($sPath, $sValue)
    {
        $oCssParam = CssParam::findOne(['name' => $sPath]);
        if (!$oCssParam) {
            throw new UserException("Parameter [{$sPath}] not found");
        }
        // старое значение в массив для восстановления
        $this->oBackup->addCssParam($oCssParam);

        // проверить наличие связи
        $aReferenceList = References::findAll(['descendant' => $sPath]);
        foreach ($aReferenceList as $oReference) {
            if ($oReference and $oReference->active) {
                // добавить в массив на восстановление связь
                $this->oBackup->addReference($oReference);

                // выключить связь
                $oReference->active = false;
                $oReference->save();
            }
        }

        $oCssParam->value = $sValue;

        return $oCssParam->save();
    }

    /**
     * Копирует файл в web директорию для использования
     * Добавляет в список для отката
     * Отдает путь от web директории до файла.
     *
     * @param string $sSourcePath путь исходного файла
     * @param string $sDestinationPath путь относительно web директории
     *
     * @throws UserException
     *
     * @return string путь от web директории до файла
     */
    public function copyFile($sSourcePath, $sDestinationPath)
    {
        if (!file_exists($sSourcePath)) {
            $sSourcePath = $this->switcherDir() . $sSourcePath;
        }

        if (!file_exists($sSourcePath)) {
            throw new UserException("No file [{$sSourcePath}]");
        }
        $sDstFullPath = WEBPATH . $sDestinationPath;

        $sDstDir = dirname($sDstFullPath);

        if (!is_dir($sDstDir)) {
            FileHelper::createDirectory($sDstDir);
        }

        $bRes = copy($sSourcePath, $sDstFullPath);

        if (!$bRes) {
            throw new UserException("Can not copy file [{$sSourcePath}] to [{$sDstFullPath}]");
        }
        $this->oBackup->addDelFile($sDestinationPath);

        return $sDestinationPath;
    }

    /**
     * Копируем файл из локальной директории шаблона в корневой web.
     *
     * @param $sFromDir
     * @param $sToDir
     *
     * @throws UserException
     */
    public function copyDirFiles($sFromDir, $sToDir)
    {
        if (!is_dir($sFromDir)) {
            $sFromDir = $this->switcherDir() . $sFromDir;
        }

        if (!is_dir($sFromDir)) {
            throw new UserException("No dir [{$sFromDir}]");
        }
        $aFileIgnore = ['.', '..'];
        $aListFiles = scandir($sFromDir);
        foreach ($aListFiles as $sFileName) {
            if (!in_array($sFileName, $aFileIgnore)) {
                $sFileName = \DIRECTORY_SEPARATOR . $sFileName;
                $this->copyFile($sFromDir . $sFileName, $sToDir . $sFileName);
            }
        }
    }

    protected function switcherDir()
    {
        return dirname(
            Site::getReleaseRootPath() .
            str_replace('\\', \DIRECTORY_SEPARATOR, get_called_class())
        ) . \DIRECTORY_SEPARATOR;
    }

    /**
     * Задает текст для указанного по имени "плавающего" блока
     * Можно использовать константы класса Block для именования.
     *
     * @param string $sName
     * @param string $sText
     */
    protected function setBlockText($sName, $sText)
    {
        $this->setParam(
            \Yii::$app->sections->root(),
            $sName,
            'source',
            null,
            $sText
        );

        \Yii::$app->db->createCommand()
            ->update(
                ParamsAr::tableName(),
                ['show_val' => $sText],
                [
                    'AND',
                        ['<>', 'parent', \Yii::$app->sections->root()],
                        ['group' => $sName],
                        ['name' => 'source'],
                ]
            )->execute();
    }

    /**
     * Задает параметр для раздела
     * При этом
     *
     * @param int $iSection
     * @param int $sGroup
     * @param int $sName
     * @param null|string [$sVal]
     * @param null|string [$sShowVal]
     * @param null|string [$sTitle]
     * @param null|int [$iAccessLevel]
     *
     * @return false|int
     */
    protected function setParam($iSection, $sGroup, $sName, $sVal = null, $sShowVal = null, $sTitle = null, $iAccessLevel = null)
    {
        // запросить старое значение
        $oOldParam = Parameters::getByName($iSection, $sGroup, $sName);

        // было - добавить в список на откат
        if ($oOldParam) {
            $this->oBackup->addParam($oOldParam);
        }

        // не было - в список на уделение
        else {
            $this->oBackup->addDelParam($iSection, $sGroup, $sName);
        }

        // сохранить новое
        return Parameters::setParams($iSection, $sGroup, $sName, $sVal, $sShowVal, $sTitle, $iAccessLevel);
    }

    /**
     * Отдает флаг наличия модуля.
     *
     * @param string $sModule имя модуля
     *
     * @return bool
     */
    public function moduleExists($sModule)
    {
        return \Yii::$app->register->moduleExists($sModule, Layer::PAGE);
    }

    /**
     * Задать набор настроек для модулей.
     */
    abstract public function setModuleSettings();

    /**
     * Задать настройки для типовых блоков.
     */
    abstract public function setBlocks();

    /**
     * Установить типовой контент
     * Выполняется только если при запуске переключения был задан соответствующий флаг.
     */
    abstract public function setContent();

    /**
     * Отдает набор меток модулей, которые должны быть выведены в шапку.
     */
    abstract protected function getModulesList();

    /**
     * Вызывается при переключении на другой шаблон
     * Откатывает специфичесткие настройки текущего шаблона в стандартные,
     *      если таковые были сделаны
     * Вызывается до стандартного метода вычищения установленных значений.
     *
     * @param BackupParamsPrototype $oBackup объект с данными отката !может отсутствовать
     */
    public function resetSettingsBeforeStandard(BackupParamsPrototype $oBackup = null)
    {
    }

    /**
     * Вызывается при переключении на другой шаблон
     * Откатывает специфичесткие настройки текущего шаблона в стандартные,
     *      если таковые были сделаны
     * Вызывается после стандартного метода вычищения установленных значений.
     *
     * @param BackupParamsPrototype $oBackup объект с данными отката !может отсутствовать
     */
    public function resetSettingsAfterStandard(BackupParamsPrototype $oBackup = null)
    {
    }

    /** Получить старый шаблон */
    public function getOldTpl()
    {
        // текущий шаблон в корневом разделе
        return Parameters::getShowValByName(
            \Yii::$app->sections->root(),
            Zones\Api::layoutGroupName,
            $this->getType() . '_tpl',
            true
        );
    }

    public static function getBackupClass()
    {
        return BackupParams::className();
    }
}
