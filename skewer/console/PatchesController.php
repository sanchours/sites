<?php

namespace app\skewer\console;

use skewer\build\Tool\Patches\Api;
use skewer\build\Tool\Patches\models\Patch;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class PatchesController extends Prototype
{
    /**
     * Список установленных патчей.
     *
     * @param int $iPage Начало выборки
     * @param int $iOnPage Количество выбираемых
     */
    public function actionInstalled($iPage = 1, $iOnPage = 50)
    {
        $aPatches = Api::getAppliedPatches($iPage - 1, $iOnPage);

        if (!$aPatches) {
            return;
        }

        $this->stdout('Список установленных патчей:');

        foreach ($aPatches as $aPatch) {
            $this->stdout("\r\n" . $aPatch['install_date'] . ' ' . $aPatch['patch_uid'] . ' ' . $aPatch['description']);
        }
        $this->stdout("\r\n");
    }

    /**
     * Список патчей, доступных для установки.
     */
    public function actionAvailable()
    {
        $aPatches = Api::getAvailablePatches(PATCHPATH);

        if ($aPatches) {
            $this->stdout('Список доступных патчей:');
        }

        foreach ($aPatches as $sPatch) {
            $description = Api::getDescFormFile(PATCHPATH . $sPatch);

            $sPatch = mb_substr($sPatch, 0, mb_strpos($sPatch, '/'));

            $this->stdout("\r\n" . $sPatch . ' ' . $description);
        }
        $this->stdout("\r\n");
    }

    /**
     * Создание патча.
     *
     * @param int $uid Номер
     * @param string $description Описание
     */
    public function actionCreate($uid = 0, $description = '')
    {
        while (!$uid) {
            $uid = $this->prompt('Введите номер патча:');
        }

        $sPatchFile = PATCHPATH . $uid . \DIRECTORY_SEPARATOR . $uid . '.php';

        if (file_exists($sPatchFile)) {
            $this->stderr('Патч с номером ' . $uid . ' уже присутствует на площадке!' . "\r\n");

            return;
        }

        while (!$description) {
            $description = $this->prompt('Введите описание патча:');
        }

        if (!FileHelper::createDirectory(PATCHPATH . $uid)) {
            $this->stderr('Не удалось создать директорию!' . "\r\n");

            return;
        }

        if (file_put_contents($sPatchFile, $sOut = \Yii::$app->getView()->renderFile(BUILDPATH . 'common/templates/patches.php', ['description' => $description, 'number' => $uid]))) {
            $this->stdout('Патч ' . $uid . ' успешно создан!' . "\r\n");
        } else {
            $this->stderr('Не удалось создать файл патча!' . "\r\n");

            return;
        }
    }

    /**
     * Установка патча.
     *
     * @param $uid
     */
    public function actionInstall($uid = 0)
    {
        while (!$uid) {
            $uid = $this->prompt('Введите номер патча:');
        }

        $patch_file = $uid . \DIRECTORY_SEPARATOR . $uid . '.php';

        if (Api::alreadyInstalled($uid . '.php')) {
            $this->showError('Патч ' . $uid . ' уже установлен!');

            return;
        }

        try {
            Api::installPatch($patch_file);
            $this->showText('Патч установлен!');
        } catch (\Exception $e) {
            $this->showError('В ходе установки патча произошли ошибки:');
            $this->showError($this->ansiFormat($e->getMessage(), Console::FG_RED));
            $this->br();
            $this->showText((string) $e);

            return;
        }
    }

    /**
     * Стирает запись об установке патча из базы.
     * После этого можно установить повторно.
     *
     * @param int $id
     */
    public function actionDeactivate($id = 0)
    {
        while (!$id) {
            $id = $this->prompt('Введите номер патча:');
        }

        $uid = $id . '.php';
        if (!Api::alreadyInstalled($uid)) {
            $this->showError("Патч [{$id}] не установлен!");

            return;
        }

        $iRes = Patch::deleteAll(['patch_uid' => $uid]);

        if ($iRes) {
            $this->showText("Запись об установке патча [{$id}] удалена");
        } else {
            $this->showError("Ошибка удаления записи [{$id}]");
        }
    }

    /**
     * Переустанавливает патч.
     *
     * @param int $id
     */
    public function actionReInstall($id = 0)
    {
        while (!$id) {
            $id = $this->prompt('Введите номер патча:');
        }

        $uid = $id . '.php';

        if (Api::alreadyInstalled($uid)) {
            $this->actionDeactivate($id);
        }

        $this->actionInstall($id);
    }
}
