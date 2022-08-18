<?php

namespace skewer\components\config;

/**
 * Класс контейнер для обновления конфигурационных данных.
 */
class ConfigUpdater
{
    /** @var BuildRegistryUpdater реестр сборки */
    protected static $oBuildRegistry;

    /**
     * Список резервно склонированных объектов, хранящих реестр
     *
     * @var array
     */
    protected static $backups = [];

    /**
     * Инициализация. без нее работать не будет
     */
    public static function init()
    {
        if (self::$oBuildRegistry === null) {
            self::$oBuildRegistry = new BuildRegistryUpdater();
        }
    }

    /**
     * Отдает объект конфинурации сборки.
     *
     * @throws Exception
     *
     * @return BuildRegistryUpdater
     */
    public static function buildRegistry()
    {
        if (self::$oBuildRegistry === null) {
            throw new Exception('Build Registry not inited');
        }

        return self::$oBuildRegistry;
    }

    /**
     * Сохраняет измененные данные конфигов.
     *
     * @return bool
     */
    public static function commit()
    {
        $bRes = self::buildRegistry()->commitChanges();
        \Yii::$app->register->reloadData();

        return $bRes;
    }

    /**
     * Откатывает назад изменения реестра, если они были сделаны.
     */
    public static function revert()
    {
        self::buildRegistry()->revertChanges();
    }

    /**
     * Фиксирует текущее состояние реестра в области резервного копирования. Ассоциирует копию с именем $name.
     *
     * @param string $name
     */
    public static function createBackup($name)
    {
        self::$backups[$name] = [
            'build' => clone self::$oBuildRegistry,
        ];
    }

    /**
     * Восстанавливает реестр, содержащийся в ConfigUpdater до состояния резервной копии с именем $name.
     *
     * @param string $name
     *
     * @throws Exception В случае, если резервная копия с именем $name отсутствует, будет выброшено исключение
     */
    public static function recoverBackup($name)
    {
        if (!isset(self::$backups[$name])) {
            throw new Exception("Backup with name [{$name}] does not exist");
        }
        self::$oBuildRegistry = clone self::$backups[$name]['build'];
    }
}
