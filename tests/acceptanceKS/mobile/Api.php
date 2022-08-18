<?php
/**
 * Created by PhpStorm.
 * User: simak
 * Date: 14.12.2017
 * Time: 11:38.
 */

namespace tests\acceptanceKS\mobile;

use skewer\base\section\models\ParamsAr;
use skewer\base\section\models\TreeSection;
use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\base\site_module\Parser;
use skewer\components\gallery\Album;
use skewer\components\gallery\Format;
use skewer\components\gallery\models\Profiles;
use skewer\components\gallery\Photo;
use skewer\components\gallery\Profile;
use skewer\helpers\Files;

class Api
{
    const PATH_TEST_MOBILE = 'tests/acceptanceKS/mobile';

    const MOBILE_FOLDER_NAME = 'mobile_app';

    const MOBILE_BANNER = 'banner';

    private static $idMobileSection = 0;

    public static function getPathTestMobile()
    {
        return ROOTPATH . self::PATH_TEST_MOBILE;
    }

    public static function createConfigAndSections()
    {
        if (self::setMobileSections()) {
            //получение id шаблона для секции
            $oNewsSection = ParamsAr::findOne(['group' => '.', 'name' => 'template', 'value' => \Yii::$app->sections->getValue('newsTpl')]);

            $idNewsSection = ($oNewsSection->parent) ?: '';
            $aParamConfigId = [
                'paramsId' => [
                    'catalog' => \Yii::$app->sections->leftMenu(),
                    'news' => $idNewsSection,
                    'addSection' => self::$idMobileSection,
                ],
            ];

            $sConfigTpl = Parser::parseTwig('config.twig', $aParamConfigId, __DIR__ . '/templates');
            $sColorConfigTpl = Parser::parseTwig('color_config.twig', [], __DIR__ . '/templates');
            $sPathMobileApp = FILEPATH . self::MOBILE_FOLDER_NAME;
            if (!is_dir($sPathMobileApp)) {
                Files::createFolderPath(self::MOBILE_FOLDER_NAME);
            }

            $toPathBanner = $sPathMobileApp . \DIRECTORY_SEPARATOR . self::MOBILE_BANNER;

            //тут ошибка с копированием банера
            if (!is_dir($toPathBanner)) {
                Files::createFolderPath(self::MOBILE_FOLDER_NAME . \DIRECTORY_SEPARATOR . self::MOBILE_BANNER);
            }

            $fromPathBanner = self::getPathTestMobile() . \DIRECTORY_SEPARATOR . self::MOBILE_BANNER . \DIRECTORY_SEPARATOR;
            $aFilesBanner = array_diff(scandir($fromPathBanner), ['..', '.']);
            foreach ($aFilesBanner as $nameFile) {
                copy($fromPathBanner . $nameFile, $toPathBanner . \DIRECTORY_SEPARATOR . $nameFile);
            }

            file_put_contents($sPathMobileApp . '/config.json', $sConfigTpl);
            file_put_contents($sPathMobileApp . '/color_config.json', $sColorConfigTpl);

            return true;
        }

        return false;
    }

    private static function setMobileSections()
    {
        if (!TreeSection::findOne(['title' => 'Для мобильного приложения', 'parent' => \Yii::$app->sections->root()])) {
            $idTplMobile = Tree::getSectionByAlias('dlya-mobilnogo-prilozheniya', \Yii::$app->sections->templates());
            TreeSection::updateAll(['visible' => '1'], ['id' => $idTplMobile]);

            self::$idMobileSection = Tree::addSection(\Yii::$app->sections->root(), 'Для мобильного приложения', $idTplMobile)->id;

            $oContMobile = Tree::addSection(self::$idMobileSection, 'Контакты', $idTplMobile);
            self::setAlbumWithImage($oContMobile->id, self::getPathTestMobile() . '/image-sections/icon_contacts.png', '.', 'image_mobile');
            $staticContentCont = Parser::parseTwig('contact.twig', [], __DIR__ . '/templates');
            Parameters::setParams($oContMobile->id, 'staticContent', 'source', '', $staticContentCont);

            $oDeliveryMobile = Tree::addSection(self::$idMobileSection, 'Доставка и оплата', $idTplMobile);
            self::setAlbumWithImage($oDeliveryMobile->id, self::getPathTestMobile() . '/image-sections/icon_delivery.png', '.', 'image_mobile');
            $staticContentDeliv = Parser::parseTwig('delivery.twig', [], __DIR__ . '/templates');
            Parameters::setParams($oDeliveryMobile->id, 'staticContent', 'source', '', $staticContentDeliv);

            return true;
        }

        return false;
    }

    private static function setAlbumWithImage($sectionId, $pathToImg, $sGroupParam, $sNameParamSection)
    {
        Profiles::updateAll(['active' => 1, 'default' => 1], ['alias' => Profile::TYPE_MOBILE]);
        $oProfile = Profile::getByAlias(Profile::TYPE_MOBILE);
        if ($oProfile) {
            $idProfile = $oProfile['id'];
            $iNewId = Album::setAlbum([
                'owner' => 'section',  // владелец
                'section_id' => $sectionId, // родительский раздел
                'profile_id' => $idProfile, // Профиль форматов
            ]);
            $aCrop = Format::getCropByIdProfile($idProfile);
            Photo::addPhotoInAlbum($pathToImg, $iNewId, $aCrop, $idProfile);

            Parameters::setParams($sectionId, $sGroupParam, $sNameParamSection, $iNewId);
        }
    }
}
