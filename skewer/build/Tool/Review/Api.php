<?php

namespace skewer\build\Tool\Review;

use skewer\base\section\Tree;
use skewer\base\site\Site;
use skewer\base\site_module\Parser;
use skewer\build\Adm\GuestBook\models\GuestBook;
use skewer\build\Page\GuestBook\ReviewForm;
use skewer\build\Tool\Review\gallery\GalleryOnReview;
use skewer\build\Tool\Review\gallery\GalleryOnReviewBubble;
use skewer\build\Tool\Review\gallery\GalleryOnReviewGray;
use skewer\build\Tool\Review\gallery\GalleryOnReviewSingle;
use skewer\components\catalog\GoodsSelector;
use skewer\components\GalleryOnPage\GetGalleryEvent;
use skewer\components\i18n\ModulesParams;
use skewer\components\rating\Rating;
use skewer\helpers\Mailer;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class Api
{
    /** @const Тип вывода "Список" */
    const TYPE_SHOW_LIST = 'list';

    /** @const Тип вывода "Кавычки" */
    const TYPE_SHOW_QUOTES_CAROUSEL = 'quotes_carousel';

    /** @const Тип вывода "Рамка" */
    const TYPE_SHOW_FRAME_CAROUSEL = 'frame_carousel';

    /** @const Тип вывода "Тень" */
    const TYPE_SHOW_SHADOW_CAROUSEL = 'shadow_carousel';

    /** @const Тип вывода "Серый: карусель" */
    const TYPE_SHOW_GRAY_CAROUSEL = 'gray_carousel';

    /** @const Тип вывода "Пузырь: карусель" */
    const TYPE_SHOW_BUBBLE_CAROUSEL = 'bubble_carousel';

    /** @const Тип вывода "Одиночный: карусель" */
    const TYPE_SHOW_SINGLE_CAROUSEL = 'single_carousel';

    /** @const Тип вывода "Карусель" */
    const TYPE_SHOW_CAROUSEL = 'carousel';

    public static function className()
    {
        return get_called_class();
    }

    public static function registerGallery(GetGalleryEvent $oEvent)
    {
        $oEvent->addGalleryList([
            GalleryOnReview::className(),
            GalleryOnReviewGray::className(),
            GalleryOnReviewBubble::className(),
            GalleryOnReviewSingle::className(),
        ]);
    }

    /**
     * @var array Шаблоны типа показа отзывов
     */
    public static $aTypeShowReviews = [
        'column' => [
                self::TYPE_SHOW_LIST => [
                    'title' => 'Review.field_in_column',
                    'file' => 'column_list.twig',
                ],
                self::TYPE_SHOW_QUOTES_CAROUSEL => [
                    'title' => 'Review.field_quotes_carousel',
                    'file' => 'column_quotes.twig',
                ],
                self::TYPE_SHOW_FRAME_CAROUSEL => [
                    'title' => 'Review.field_frame_carousel',
                    'file' => 'column_frame.twig',
                ],
                self::TYPE_SHOW_SHADOW_CAROUSEL => [
                    'title' => 'Review.field_shadow_carousel',
                    'file' => 'column_shadow.twig',
                ],
            ],
        'content' => [
                self::TYPE_SHOW_LIST => [
                    'title' => 'Review.field_in_column',
                    'file' => 'content_list.twig',
                ],
                self::TYPE_SHOW_CAROUSEL => [
                    'title' => 'Review.field_in_carousel',
                    'file' => 'content_carousel.twig',
                ],
                self::TYPE_SHOW_GRAY_CAROUSEL => [
                    'title' => 'Review.field_gray_carousel',
                    'file' => 'content_gray.twig',
                ],
                self::TYPE_SHOW_BUBBLE_CAROUSEL => [
                    'title' => 'Review.field_bubble_carousel',
                    'file' => 'content_bubble.twig',
                ],
                self::TYPE_SHOW_SINGLE_CAROUSEL => [
                    'title' => 'Review.field_single_carousel',
                    'file' => 'content_single.twig',
                ],
            ],
    ];

    /**
     * Отправка письма админу.
     *
     * @param GuestBook $rowGuestBook
     * @param ReviewForm $oForm
     *
     * @return bool
     */
    public static function sendMailToAdmin($rowGuestBook, $oForm)
    {
        $aParams = ModulesParams::getByModule('review');

        // берем заголовок письма из базы
        if ($rowGuestBook->isGoodReviews()) {
            $sTitle = ArrayHelper::getValue($aParams, 'mail.catalog.title', '');
            $sContent = ArrayHelper::getValue($aParams, 'mail.catalog.content', '');
        } else {
            $sTitle = ArrayHelper::getValue($aParams, 'mail.title' . '');
            $sContent = ArrayHelper::getValue($aParams, 'mail.content', '');
        }

        $aUserLabels = \Yii::$app->getI18n()->getValues('review', 'label_user');
        foreach ($aUserLabels as $sLabel) {
            $aParams[$sLabel] = $rowGuestBook->name;
        }
        if ($rowGuestBook->isGoodReviews()) {
            $goods = GoodsSelector::get($rowGuestBook->parent, 1);
            $aOrderLabels = \Yii::$app->getI18n()->getValues('review', 'label_order');
            foreach ($aOrderLabels as $sLabel) {
                $aParams[$sLabel] = $goods['title'];
            }
        }
        $aParams['email'] = $rowGuestBook->email;
        $sNameModule = Module::getNameModule();
        $aParams['link'] = Site::admUrl($sNameModule, 'tools', $rowGuestBook->id);

        $sTemplateDir = RELEASEPATH . 'build/Page/Forms/templates';
        $noSendData = $oForm->getFormParam('form_notific');
        $sBody = Parser::parseTwig('letter.twig', [
            'oForm' => $oForm,
            'sIntroduction' => '',
            'sShowLinkAdd' => $sContent,
            'bTableHide' => false,
            'sFormNotif' => $noSendData,
        ], $sTemplateDir);

        $photoGallery = $oForm->getField('photo_gallery');
        if (!$noSendData && $photoGallery && $photoGallery->param_value) {
            $extraData = $photoGallery->field_object->getExtraData($photoGallery->param_name);
            if ($extraData) {
                $aAttachFile[$photoGallery->param_value] = $extraData;
            }
        }

        return
            isset($aAttachFile)
            ? Mailer::sendMailWithAttach(Site::getAdminEmail(), $sTitle, $sBody, $aParams, $aAttachFile)
            : Mailer::sendMailAdmin($sTitle, $sBody, $aParams);
    }

    /**
     * Отправка письма клиенту.
     *
     * @param GuestBook $rowGuestBook
     * @param $iStatus
     *
     * @return bool
     */
    public static function sendMailToClient($rowGuestBook, $iStatus = null)
    {
        $aParams = ModulesParams::getByModule('review');

        if ($iStatus === null) {
            $iStatus = $rowGuestBook->status;
        } else {
            if ($iStatus == $rowGuestBook->status) {
                return false;
            }

            //отправлять уведомление пользователю или нет
            if ($rowGuestBook->isGoodReviews()) {
                $sOnNotif = ArrayHelper::getValue($aParams, 'mail.catalog.onNotif', false);
            } else {
                $sOnNotif = ArrayHelper::getValue($aParams, 'mail.onNotif', false);
            }

            if (!$sOnNotif) {
                return false;
            }
        }

        if ($rowGuestBook->isGoodReviews()) {
            switch ($iStatus) {
                case GuestBook::statusApproved:
                    $sTitle = ArrayHelper::getValue($aParams, 'mail.catalog.notifTitleApprove', '');
                    $sContent = ArrayHelper::getValue($aParams, 'mail.catalog.notifContentApprove', '');
                    break;
                case GuestBook::statusRejected:
                    $sTitle = ArrayHelper::getValue($aParams, 'mail.catalog.notifTitleReject', '');
                    $sContent = ArrayHelper::getValue($aParams, 'mail.catalog.notifContentReject', '');
                    break;
                case GuestBook::statusNew:
                default:
                    $sTitle = ArrayHelper::getValue($aParams, 'mail.catalog.notifTitleNew', '');
                    $sContent = ArrayHelper::getValue($aParams, 'mail.catalog.notifContentNew', '');
                    break;
            }
        } else {
            switch ($iStatus) {
                case GuestBook::statusApproved:
                    $sTitle = ArrayHelper::getValue($aParams, 'mail.notifTitleApprove', '');
                    $sContent = ArrayHelper::getValue($aParams, 'mail.notifContentApprove', '');
                    break;
                case GuestBook::statusRejected:
                    $sTitle = ArrayHelper::getValue($aParams, 'mail.notifTitleReject', '');
                    $sContent = ArrayHelper::getValue($aParams, 'mail.notifContentReject', '');
                    break;
                case GuestBook::statusNew:
                default:
                    $sTitle = ArrayHelper::getValue($aParams, 'mail.notifTitleNew', '');
                    $sContent = ArrayHelper::getValue($aParams, 'mail.notifContentNew', '');
                    break;
            }
        }

        $aMailParams = [];

        $aUserLabels = \Yii::$app->getI18n()->getValues('review', 'label_user');

        foreach ($aUserLabels as $sLabel) {
            $aMailParams[$sLabel] = $rowGuestBook->name;
        }
        if ($rowGuestBook->isGoodReviews()) {
            $goods = GoodsSelector::get($rowGuestBook->parent, 1);
            $aOrderLabels = \Yii::$app->getI18n()->getValues('review', 'label_order');
            foreach ($aOrderLabels as $sLabel) {
                $aMailParams[$sLabel] = $goods['title'];
            }
        }
        $aMailParams['email'] = $rowGuestBook->email;

        return Mailer::sendMail($rowGuestBook->email, $sTitle, $sContent, $aMailParams);
    }

    /**
     * Массив отзывов.
     *
     * @param $onPage
     * @param $sectionNow - текущий раздел
     * @param int $sectionFrom - раздел из которого достать отзывы
     *
     * @return array|mixed|\skewer\base\orm\state\StateSelect
     */
    public static function getArrayReviews($onPage, $sectionNow, $sectionFrom = 0)
    {
        $aOut = [];

        $oQuery = GuestBook::find()
            ->where([
                'status' => GuestBook::statusApproved,
                'parent_class' => '',
            ]);

        if ($sectionNow == \Yii::$app->sections->main()) {
            $oQuery->andWhere(['on_main' => 1]);
        }

        // Если указан раздел - тащим из раздела, если нет - по всему сайту
        if ($sectionFrom) {
            $oQuery->andWhere(['parent' => $sectionFrom]);
        } else {
            $aSections = Tree::getAllSubsection(\Yii::$app->sections->languageRoot());
            if ($aSections) {
                $oQuery->andWhere(['parent' => $aSections]);
            }
        }

        $aReviews = $oQuery->orderBy(['date_time' => SORT_DESC])
            ->limit($onPage)
            ->all();

        foreach ($aReviews as $oReview) {
            if ($oReview->rating_id) {
                $oReview->rating_id = Rating::getRateById($oReview->rating_id);
            }

            $aTmpData = $oReview->toArray([], ['link']);
            $aOut[] = array_merge($aTmpData, [
                'ratingHtml' => trim(Rating::parseSimpleRating($oReview->rating_id)) ?: '',
            ]);
        }

        return $aOut;
    }

    /**
     * Список доступных типов отображения отзывов.
     *
     * @param string $sZone
     *
     * @return array
     */
    public static function getTypeShowReviews($sZone = 'content')
    {
        $aReviews = (isset(self::$aTypeShowReviews[$sZone])) ?
                            ArrayHelper::getColumn(self::$aTypeShowReviews[$sZone], 'title') : [];

        return $aReviews;
    }

    /**
     * Получить список отзывов.
     *
     * @param string $sParentClass - тип родитеской сущности
     * @param int $iParentId - id родительской сущности
     * @param int $iPage - номер страницы
     * @param int $iOnPage - количество записей на странице
     * @param int $iTotalCount - общее количество записей, удолетвовяющих условию
     *
     * @return array
     */
    public static function getReviewList($sParentClass, $iParentId, $iPage, $iOnPage, &$iTotalCount)
    {
        $aOut = [];

        $query = GuestBook::find()
            ->where(['status' => GuestBook::statusApproved])
            ->andWhere(['parent' => $iParentId])
            ->andWhere(['parent_class' => $sParentClass])
            ->limit($iOnPage)
            ->offset(($iPage - 1) * $iOnPage)
            ->orderBy(['date_time' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $iOnPage,
                'page' => $iPage - 1,
            ],
        ]);

        $aData = $dataProvider->getModels();

        /** @var GuestBook[] $aData */
        foreach ($aData as $aItem) {
            if ($aItem->rating_id) {
                $aItem->rating_id = Rating::getRateById($aItem->rating_id);
            }

            $aTmpData = $aItem->toArray();
            $aOut[] = array_merge($aTmpData, [
                'ratingHtml' => trim(Rating::parseSimpleRating($aItem->rating_id)) ?: '',
            ]);
        }

        $iTotalCount = $dataProvider->getTotalCount();

        return $aOut;
    }
}
