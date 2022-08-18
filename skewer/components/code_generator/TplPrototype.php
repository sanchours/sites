<?php

namespace skewer\components\code_generator;

use skewer\base\Twig;
use skewer\helpers\Files;

/**
 * @class skewer\components\code_generator\codeTplPrototype
 *
 * @author ArmiT, $Author$
 *
 * @version $Revision$
 * @date $Date$
 * @project JetBrains PhpStorm
 */
abstract class TplPrototype implements TplInterface
{
    /**
     * Путь к корневой директории целевого хоста.
     *
     * @var string
     */
    protected $sSiteRootPath = '';

    /**
     * Путь к корневой директории с шаблонами файлов.
     *
     * @var string
     */
    protected $sTplRootPath = '';

    /**
     * Создает файл $sFileName путем генерации на основе шаблона $sTplName и данных $aData.
     * Если Указано $bIfNotExists то файл создается только в том случае, если он не был создан ранее.
     * В противном случае происходит перезапись.
     *
     * @param string $sFileName Путь и имя создаваемого файла
     * @param string $sTplName Имя файла шаблона. по-умолчанию шаблоны лежат и ищутся в директории
     *  <buildPath>/common/templates/
     * @param array $aData Массив данных для вставки в шаблон
     * @param bool $bIfNotExists Флаг, указывающий на необходимость создания файла только в том
     * случае, если он не существует
     *
     * @throws Exception
     *
     * @return bool Возвращает true, если создание прошло успешно и false в противном случае
     */
    public function createFileByTpl($sFileName, $sTplName, $aData, $bIfNotExists = true)
    {
        if (!file_exists($this->sTplRootPath . $sTplName)) {
            throw new Exception('CodeTpl error: Template file [' . $this->sTplRootPath . $sTplName . '] not found!');
        }
        /* Если файл уже существует и не нужна перезапись - выходим */
        if (file_exists($sFileName)) {
            if ($bIfNotExists) {
                return true;
            }
        }

        Twig::enableDebug();
        Twig::setPath([$this->sTplRootPath]);

        if (count($aData)) {
            foreach ($aData as $sKey => $mValue) {
                Twig::assign($sKey, $mValue);
            }
        }

        $sContent = Twig::render($sTplName);
        Twig::disableDebug();

        if (!$sContent) {
            throw new Exception();
        }
        $rF = fopen($sFileName, 'w+');
        if (!$rF) {
            throw new Exception();
        }
        fwrite($rF, $sContent);
        fclose($rF);

        return true;
    }

    // func

    public function createFile($sFileName, $sContent, $bIfNotExists = true)
    {
    }

    // func

    public function createDirectory($sDirectoryPath, $bIfNotExists = true)
    {
    }

    // func

    /**
     * Удаляет файл $sFileName.
     *
     * @param $sFileName
     *
     * @return bool
     */
    public function removeFile($sFileName)
    {
        return Files::remove($sFileName);
    }

    // func

    public function removeDirectory($sDirectoryName)
    {
    }

    // func

    public function add(TplPrototype $oChild)
    {
        $oChild->make();
    }

    // func

    /**
     * Устанавливает $sSiteRootPath в качестве пути к корневой директории целевого хоста.
     *
     * @param string $sSiteRootPath Абсолютный путь
     */
    public function setSiteRootPath($sSiteRootPath)
    {
        $this->sSiteRootPath = $sSiteRootPath;
    }

    // func

    /**
     * Возвращает путь к корневой директории целевого хоста.
     *
     * @return string
     */
    public function getSiteRootPath()
    {
        return $this->sSiteRootPath;
    }

    /**
     * Устанавливает $sTplRootPath в качестве пути к корневой директориии с шаблонами генератора.
     *
     * @param string $sTplRootPath
     */
    public function setTplRootPath($sTplRootPath)
    {
        $this->sTplRootPath = $sTplRootPath;
    }

    /**
     * Возвращает путь к корневой директории с шаблонами для генератора.
     *
     * @return string
     */
    public function getTplRootPath()
    {
        return $this->sTplRootPath;
    }

    // func
}// class
