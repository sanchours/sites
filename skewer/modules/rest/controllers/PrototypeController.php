<?php

namespace skewer\modules\rest\controllers;

use skewer\base\section\Parameters;
use skewer\base\section\params\ListSelector;
use skewer\base\site\Site;
use skewer\components\gallery\Photo;

/**
 * Прототип restApi контроллеров
 * Class PrototypeController.
 */
class PrototypeController extends \yii\rest\Controller
{
    /** Неизвестная ошибка */
    const ERR_OTHER = 'unknown';

    /** Текстовые сообщения ошибок для пользователя. Общие для всех контроллеров */
    private static $aErrorMess = [
        UsersController::ERR_AUTH => 'Указанная связка пользователя и пароля не существует либо пользователь не активен или забанен',
        UsersController::ERR_NOAUTH => 'Для проведения операции необходимо авторизоваться',
        UsersController::ERR_DATA => 'Данные заполнены не верно',
        UsersController::ERR_REGLOGIN => 'Пользователь с таким логином уже существует',
    ];

    /** Вывод сообщения об успешной операции */
    final protected function showSuccess()
    {
        return ['success' => 1];
    }

    /** Вывод ошибки */
    final protected function showError($sErrorCode, $sErrorMess = '')
    {
        $sErrorMess = $sErrorMess ?: @self::$aErrorMess[$sErrorCode] ?: '';

        return [
            'error' => $sErrorCode,
            'error_text_ru' => $sErrorMess,
        ];
    }

    /**
     * Установить данные постраничника в заголовок ответа.
     *
     * @param int $iCountTotal Всего позиций
     * @param int $iCountPage Всего страниц
     * @param int $iCurrentPage Текущая страница
     * @param int $iOnPage Число на странице
     */
    final protected function setPagination($iCountTotal, $iCountPage, $iCurrentPage, $iOnPage)
    {
        header('X_Pagination_Total_Count: ' . $iCountTotal);
        header('X_Pagination_Page_Count: ' . $iCountPage);
        header('X_Pagination_Current_Page: ' . $iCurrentPage);
        header('X_Pagination_Per_Page: ' . $iOnPage);
    }

    /** Получить массив изображений из альбома/альбомов */
    protected function getImages($mAlbumId, $bGetOne = true)
    {
        $aResult = [];

        if ($aImages = Photo::getFromAlbum($mAlbumId)) {
            foreach ($aImages as &$paImage) {
                foreach ($paImage['images_data'] as $sFormatName => $aFormatData) {
                    $aResult[$paImage['album_id']][$sFormatName] = Site::httpDomain() . $aFormatData['file'];
                }
            }
        }

        if ($bGetOne) {
            return $aResult ? reset($aResult) : '';
        }

        return $aResult ?: '';
    }
}
