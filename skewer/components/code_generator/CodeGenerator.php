<?php
/**
 * Генератор кода.
 *
 * @example
 * $s = new skewer\components\code_generator\CodeGenerator('/var/skewerCluster/', '0008', 'canape3');
 *
 *
 *      $s->add(new skewer\components\code_generator\templates\ConstantsTpl('config/constants.generated.php', $aData));\
 *      if(!$s->make()) throw new Exception();
 *
 * } catch(Exception $e) {
 *      die('~!');
 * }
 *
 * @class skewer\components\code_generator\CodeGenerator
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project Skewer
 */

namespace skewer\components\code_generator;

/**
 * В конструкторе - корневой путь к кластеру и версию сборки заменить на путь к корневой директории шаблонов и
 * путь к корневой директории площадки.
 *
 * Все остальные зависимости будут разруливаться уровнем выше.
 */
class CodeGenerator
{
    /**
     * Массив создаваемых компонентов.
     *
     * @var TplPrototype[]
     */
    protected $aTplChilds = [];

    /**
     * Массив запущенных компонентов.
     *
     * @var TplPrototype[]
     */
    protected $aCompleteChilds = [];

    /**
     * Путь к корневой директории с шаблонами для генератора.
     *
     * @var string
     */
    protected $sTplPath = '';

    /**
     * Путь к корневой директории целевого хоста.
     *
     * @var string
     */
    protected $sSiteRootPath = '';

    /** @var \Exception Выброшенное в процессе выполнения исключение */
    private $oException;

    /**
     * Инициализирует текущий экземпляр codeGenerator. Для корректной работы требуется указать все параметры.
     *
     * @param string $sTplPath Путь к корневой директории с шаблонами для генератора форм
     * @param string $sDestinationRootPath Путь к корневой директории целевого хоста (сайта)
     *
     * @throws Exception
     */
    public function __construct($sTplPath, $sDestinationRootPath)
    {
        if (!is_dir($sTplPath)) {
            throw new Exception('CodeGen init error: tpl dir [' . $sTplPath . '] is not found!');
        }
        if (!is_dir($sDestinationRootPath)) {
            throw new Exception('CodeGen init error: host dir [' . $sDestinationRootPath . '] is not found!');
        }
        $this->sTplPath = $sTplPath;
        $this->sSiteRootPath = $sDestinationRootPath;
    }

    // constructor

    /**
     * Добавляет в список на генерацию экземпляр шаблона.
     *
     * @param TplPrototype $oCodeTpl
     *
     * @return CodeGenerator Возвращает текущий экземпляр codeGenerator (для возможности
     * использования DSL записи)
     */
    public function add(TplPrototype $oCodeTpl)
    {
        $oCodeTpl->setSiteRootPath($this->sSiteRootPath);
        $oCodeTpl->setTplRootPath($this->sTplPath);
        $this->aTplChilds[] = $oCodeTpl;

        return $this;
    }

    // func

    /**
     * Запускает на создание цепочку ранее добавленных компонентов
     * После запуска происходит последовательное выполнение метода make для каждого из элементов.
     * Если в процессе выполнения любого из элементов списка произошла ошибка и метод make вернул
     * false, то происходит прерывание генерации, ранее созданные компоненты удаляются средствами
     * последовательного вызова метода remove каждого из объектов.
     *
     * @throws Exception
     *
     * @return bool Возвращает true, если генерация прошла успешно либо false в случае ошибки
     */
    public function make()
    {
        try {
            if (count($this->aTplChilds)) {
                foreach ($this->aTplChilds as $oCodeTpl) {
                    $this->aCompleteChilds[] = $oCodeTpl;
                    if (!$oCodeTpl->make()) {
                        throw new Exception();
                    }
                }
            }// each tpl
        } catch (\Exception $e) {
            $this->oException = $e;

            if (count($this->aCompleteChilds)) {
                foreach ($this->aCompleteChilds as $oCodeTpl) {
                    /* @var \skewer\components\code_generator\TplPrototype $oCodeTpl */
                    $oCodeTpl->remove();
                }
            }

            return false;
        }

        return true;
    }

    // func

    /**
     * Возвращает путь до корневой директории целевого хоста.
     *
     * @return string
     */
    public function getSiteRootPath()
    {
        return $this->sSiteRootPath;
    }

    // func

    /**
     * Отдает выброшенное исключение.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->oException;
    }
}
