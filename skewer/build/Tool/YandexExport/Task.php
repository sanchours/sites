<?php

namespace skewer\build\Tool\YandexExport;

use skewer\base\log\Logger;
use skewer\base\queue;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\base\SysVar;
use skewer\components\catalog\GoodsSelector;

/**
 * Задача обновления файла выгрузки для Yandex Market
 * Class Task.
 */
class Task extends queue\Task
{
    /**
     * @const string Путь к файлу выгрузки
     */
    const sFilePath = 'export/store.xml';

    /**
     * @const string Путь к временному файлу выгрузки
     */
    const sTmpFilePath = 'export/store_tmp.xml';

    /**
     * @var bool Флаг соответствующей последней итерации экспорта
     */
    private $bLastIteration = false;

    /**
     * @var int Количество выгружаемых товаров за итерацию
     */
    private $iMaxCountGood = 100;

    /**
     * @var int Счетчик итераций
     */
    private $iIteration = 1;

    /**
     * @var array Массив ошибок
     */
    private $aErrors = [];

    public function init()
    {
        SysVar::set('Yandex.log', '');
        SysVar::set('YandexExport.count', 0);

        $sDir = WEBPATH . 'export';

        try {
            if (!file_exists($sDir)) {
                if (!@mkdir($sDir)) {
                    throw new \Exception(\Yii::t('yandexExport', 'error_unable_create_dir', [$sDir]));
                }
                chmod($sDir, 0777);
            }
        } catch (\Exception $e) {
            Logger::dumpException($e);
            $this->fail($e->getMessage());
        }

        return true;
    }

    public function recovery()
    {
        $args = func_get_args();

        $this->iIteration = $args[0]['iIteration'] ?? 1;
    }

    public function reservation()
    {
        $this->setParams(['iIteration' => $this->iIteration]);
    }

    public function execute()
    {
        $aTree = Api::getSections();

        if (!$aTree) {
            $this->setStatus(static::stComplete);

            return true;
        }

        try {
            $rExportFile = ($this->iIteration > 1)
                ? @fopen(WEBPATH . self::sTmpFilePath, 'a+')
                : @fopen(WEBPATH . self::sTmpFilePath, 'w+');

            if ($rExportFile === false) {
                throw new \Exception(\Yii::t('yandexExport', 'error_unable_open_file', [WEBPATH . self::sTmpFilePath]));
            }
        } catch (\Exception $e) {
            Logger::dumpException($e);
            $this->fail($e->getMessage());

            return true;
        }

        $query = GoodsSelector::getList4Section(array_keys($aTree))
            ->condition('in_yandex', 1)
            ->condition('active', 1)
            ->condition('price > ?', 0)
            ->limit($this->iMaxCountGood, $this->iIteration)
            ->sort('base_id');

        if ($this->iIteration == 1) {
            $aData = [
                'domain' => Site::httpDomain(),
                'categories' => $aTree,
                'date' => date('Y-m-d H:i'),
                'shopName' => SysVar::getSafe('YandexExport.shopName', ''),
                'companyName' => SysVar::getSafe('YandexExport.companyName', ''),
                'localDeliveryCost' => SysVar::getSafe('YandexExport.localDeliveryCost', ''),
            ];

            fwrite($rExportFile, Parser::parseTwig('store_head.twig', $aData, __DIR__ . '/templates'));
        }

        $iCount = 0;
        while ((($aGood = $query->parseEach()) !== false)) {
            ++$iCount;

            fwrite(
                $rExportFile,
                Parser::parseTwig(
                    'store_item.twig',
                    ['item' => $aGood, 'domain' => Site::httpDomain()],
                    __DIR__ . '/templates'
            )
            );
        }

        ++$this->iIteration;

        if ($this->bLastIteration = ($iCount < $this->iMaxCountGood)) {
            fwrite($rExportFile, Parser::parseTwig('store_footer.twig', [], __DIR__ . '/templates'));
        }

        fclose($rExportFile);

        $iCount = $iCount + SysVar::get('YandexExport.count');
        SysVar::set('YandexExport.count', $iCount);

        /** Генерируем отчет */
        $sReportText = Parser::parseTwig(
            'report.twig',
            [
                'date' => date('Y-m-d H:i:s'),
                'count' => SysVar::get('YandexExport.count', 0),
                'url' => Site::httpDomainSlash() . self::sFilePath,
                'statusText' => ($this->bLastIteration) ? \Yii::t('yandexExport', 'export_status_complete') : \Yii::t('yandexExport', 'export_status_process'),
                'categories' => $aTree,
            ],
            __DIR__ . '/templates'
        );

        SysVar::set('Yandex.report', $sReportText);

        if ($this->bLastIteration) {
            $this->setStatus(static::stComplete);

            return true;
        }

        return true;
    }

    /** Метод, выполняемый после завершения импорта */
    public function complete()
    {
        copy(WEBPATH . self::sTmpFilePath, WEBPATH . self::sFilePath);
    }

    public function error()
    {
        $sLogs = Parser::parseTwig(
            'log.twig',
            ['errors' => $this->aErrors],
            __DIR__ . '/templates'
        );

        SysVar::set('Yandex.log', $sLogs);
    }

    /**
     * Записывает ошибку в лог,
     * устанавливает статус stError текущей задаче.
     *
     * @param $sMessage
     */
    public function fail($sMessage)
    {
        $this->aErrors[] = $sMessage;
        $this->setStatus(queue\Task::stError);
    }

    /**
     * Возвращает конфиг текущей задачи.
     *
     * @return array
     */
    public static function getConfig()
    {
        return [
            'title' => 'Обновление выгрузки для Yandex Market',
            'name' => 'updateYandexMarket',
            'class' => self::className(),
            'parameters' => [],
            'priority' => Task::priorityLow,
            'resource_use' => Task::weightLow,
            'target_area' => 1, // область применения - площадка
        ];
    }

    /**
     * Данное сообщение будет показано пользователю,
     * если была попытка обновления yandexMarket выгрузки, но мьютекс(локальный или глобальный) был занят
     */
    public function getUserMessageOnMutexBusy()
    {
        return \Yii::t('yandexExport', 'prev_task_not_completed');
    }

    /**
     * Получить имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }
}
