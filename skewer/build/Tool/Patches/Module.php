<?php

namespace skewer\build\Tool\Patches;

use skewer\base\ui\state\BaseInterface;
use skewer\build\Tool;
use skewer\components\config\UpdateException;
use Symfony\Component\Yaml\Yaml;
use yii\base\UserException;

class Module extends Tool\LeftList\ModulePrototype
{
    // число элементов на страницу
    protected $iOnPage = 100;

    // текущий номер страницы ( с 0, а приходит с 1 )
    protected $iPageNum = 0;

    protected function preExecute()
    {
        // номер страницы
        $this->iPageNum = $this->getInt('page');
    }

    protected function actionInit()
    {
        $this->actionList();
    }

    protected function actionList()
    {
        $this->setPanelName(\Yii::t('patches', 'availablepatches'));

        $aPaths = Api::getList(
            USECLUSTERBUILD
                ? CLUSTERSKEWERPATH . BUILDNUMBER . '/'
                : PATCHPATH
        );

        $this->render(new Tool\Patches\view\Index([
            'aItems' => $aPaths,
        ]));
    }

    protected function actionInstallPatchForm()
    {
        try {
            $this->setPanelName(\Yii::t('patches', 'installPatch'), false);
            $aData = $this->get('data');

            if (!isset($aData['file']) or
                empty($aData['file']) or
                !isset($aData['patch_uid']) or
                empty($aData['patch_uid'])
            ) {
                throw new UserException(\Yii::t('patches', 'patchError'));
            }
            /* Относительный путь к директории обновления */

            $aVal = [
                'patch_file' => $aData['file'],
                'patch_uid' => $aData['patch_uid'],
                'status' => ($aData['is_install']) ? \Yii::t('patches', 'installed') . $aData['install_date'] : $aData['install_date'],
                'description' => $aData['description'],
            ];

            $this->render(new Tool\Patches\view\InstallPatchForm([
                'aVal' => $aVal,
                'bDescriptionNotEmpty' => !empty($aData['description']),
                'bIsNotInstalled' => !$aData['is_install'],
            ]));
        } catch (UserException $e) {
            $this->addError($e->getMessage());
        }

        return psComplete;
    }

    protected function actionInstallPatch()
    {
        try {
            $aData = $this->get('data');

            if (!isset($aData['patch_file']) or empty($aData['patch_file'])) {
                throw new UpdateException('Wrong parameters!');
            }
            try {
                Api::installPatch($aData['patch_file'], false, $aMessages);
                $sUid = $aData['patch_file'];
                $this->addMessage(\Yii::t('patches', 'installed_patch', [$sUid]));
            } finally {
                if ($aMessages) {
                    foreach ($aMessages as $sMes) {
                        $this->addMessage($sMes);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
        }

        $this->actionList();
    }

    protected function actionInstallPatchesIgnoreErrors()
    {
        $this->actionInstallPatches(true);
    }

    protected function actionInstallPatches($bIgnoreErrors = false)
    {
        $aErrors = [];

        $aSuccess = [];

        $aAllMessages = [];

        if (USECLUSTERBUILD) {
            $sPath = CLUSTERSKEWERPATH . BUILDNUMBER . '/update.yaml';
        } else {
            $sPath = ROOTPATH . 'update/update.yaml';
        }

        if (!file_exists($sPath)) {
            throw new UserException(\Yii::t('patches', 'yaml_missing'));
        }
        $aUpdateInfo = Yaml::parse(file_get_contents($sPath));

        $aPatchesYaml = [];

        foreach ($aUpdateInfo as $key => $item) {
            if (is_numeric($key)) {
                $aPatchesYaml[] = $item;
            }
        }

        $aData = $this->get('data');

        if (!isset($aData['items'])) {
            throw new UserException(\Yii::t('patches', 'no_install'));
        }
        $aPatches = [];
        foreach ($aData['items'] as $aPatch) {
            if (!$aPatch['is_install']) {
                $aPatches[] = $aPatch['file'];
            }
        }

        /**
         * Локальная функция установки патча.
         *
         * @param string $item
         */
        $install = static function ($item) use (&$aErrors, &$aSuccess, &$aAllMessages) {
            try {
                Api::installPatch($item, false, $aMessages);
                $j = count($aSuccess) + 1;
                $aSuccess[] = "{$j}. {$item}";
            } catch (\Exception $e) {
                $i = count($aErrors) + 1;
                $aErrors[] = "<b>{$i}. {$item}</b>: " . $e->getMessage();
            } finally {
                if ($aMessages) {
                    $i = count($aAllMessages) + 1;
                    $aAllMessages[] = "<b>{$i}. {$item}</b>: <br />" . implode('<br />', $aMessages);
                }
            }
        };

        //имеем массив который надо установить ($aPatches);
        // и массив из update.yaml ($aPatchesYaml)
        //обходим список патчей из update.yaml
        foreach ($aPatchesYaml as $item) {
            if (!$bIgnoreErrors and $aErrors) {
                break;
            }
            //если патч в списке на установку есть. запускаем его
            $mPatchKey = array_search($item, $aPatches);
            if ($mPatchKey !== false) {
                $install($item);
                unset($aPatches[$mPatchKey]);
            }
        }

        /*Остались патчи которых нет в update.yaml.($aPatches). Доустановим их*/
        foreach ($aPatches as $item) {
            if (!$bIgnoreErrors and $aErrors) {
                break;
            }
            $install($item);
        }

        if (!empty($aErrors)) {
            $this->addError(\Yii::t('patches', 'patches_install_error'), implode('<br>', $aErrors));
        }

        if (!empty($aSuccess)) {
            $this->addMessage(\Yii::t('patches', 'patches_install_success'), implode('<br>', $aSuccess), 5000);
        }

        if (!empty($aAllMessages)) {
            $this->addMessage('Messages', implode('<br>', $aAllMessages), 5000);
        }

        $this->actionList();
    }

    /**
     * Деактивация патча.
     *
     * @param int $sUid
     *
     * @throws UpdateException
     */
    public function actionDeactivate($sUid = 0)
    {
        if (!$sUid) {
            $aData = $this->get('data');

            $sUid = $aData['patch_uid'];
        }

        if (!Api::alreadyInstalled($sUid)) {
            throw new UpdateException(\Yii::t('patches', 'installed_patch_error', [$sUid]));
        }
        $iRes = Tool\Patches\models\Patch::deleteAll(['patch_uid' => $sUid]);

        if ($iRes) {
            $this->addMessage(\Yii::t('patches', 'patch_delete', [$sUid]));
        } else {
            $this->addError(\Yii::t('patches', 'patch_delete_error', [$sUid]));
        }

        $this->actionInit();
    }

    /**
     * Переустановка патча.
     *
     * @throws UpdateException
     */
    public function actionReInstall()
    {
        $aData = $this->get('data');

        $sUid = $aData['patch_uid'];
        $sFile = $aData['file'];

        if (Api::alreadyInstalled($sUid)) {
            $this->actionDeactivate($sUid);
        }

        try {
            if (Api::installPatch($sFile, false, $aMessages)) {
                $this->addMessage(\Yii::t('patches', 'patch_reinstall_success', [$sUid]));
            } else {
                $this->addError(\Yii::t('patches', 'patch_reinstall_error', [$sUid]));
            }
        } catch (\Exception $e) {
            $this->addError(\Yii::t('patches', 'patch_reinstall_error', [$sUid]));
            $this->addError($e->getMessage());
        } finally {
            if ($aMessages) {
                foreach ($aMessages as $sMes) {
                    $this->addMessage($sMes);
                }
            }
        }

        $this->actionInit();
    }

    /**
     * Инофрмация об установленных патчах без исходников.
     */
    public function actionArchive()
    {
        $this->setPanelName(\Yii::t('patches', 'archive'));

        $aItems = Api::getAppliedPatches($this->iPageNum, $this->iOnPage);

        $iCount = Api::getAppliedCount();

        $this->render(new Tool\Patches\view\Archive([
            'aItems' => $aItems,

            'page' => $this->iPageNum,
            'onPage' => $this->iOnPage,
            'total' => $iCount,
        ]));
    }

    /**
     * Установка служебных данных.
     *
     * @param BaseInterface $oIface
     */
    protected function setServiceData(BaseInterface $oIface)
    {
        // установить данные для передачи интерфейсу
        $oIface->setServiceData([
            'page' => $this->iPageNum,
        ]);
    }
}
