<?php

namespace app\skewer\console;

use skewer\base\orm\Query;
use skewer\base\section\Parameters;
use skewer\base\section\Template;
use skewer\base\section\Tree;
use skewer\base\section\Visible;
use skewer\components\catalog\Card;
use skewer\components\catalog\Entity;
use skewer\components\catalog\GoodsRow;
use skewer\components\catalog\model\GoodsTable;
use skewer\helpers\Files;
use skewer\models\TreeSection;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с товарами.
 */
class GoodsController extends Prototype
{
    /**
     * Добавляет в зеденный раздел заданное количество рандомных товаров.
     *
     * @param int $section id разедеда
     * @param int $cnt количество добавляемых товаров
     * @param string $card имя карточки
     */
    public function actionGenerate($section, $cnt, $card = 'dopolnitelnye_parametry')
    {
        echo "\nДобавляем {$cnt} записей в раздел №{$section}\n\n";

        $sRand = random_int(10000, 99999);

        for ($i = 1; $i <= $cnt; ++$i) {
            $row = GoodsRow::create($card);
            $row->setData([
                'title' => sprintf('good %d/%d sect %d', $i, $sRand, $section),
                'price' => random_int(10000, 99999),
                'article' => sprintf('art%d-%d', $i, $sRand),
            ]);
            $row->save();
            $row->setViewSection([$section]);

            echo '.';

            if (!($i % 100)) {
                echo " № {$i}\n";
            }
        }

        echo "\n\nАвтоматическое добавление завершено\n";
    }

    /**
     * Добавляет в раздел верхнего меню заданное количество рандомных каталожных разделов.
     *
     * @param int $iCount количество добавляемых разделов
     * @param string $sCardName имя карточки
     */
    public static function actionGeneratetree($iCount, $sCardName = 'dopolnitelnye_parametry')
    {
        $iParentSection = \Yii::$app->sections->topMenu();

        echo "\nДобавляем {$iCount} каталожных разделов в раздел №{$iParentSection}\n\n";

        for ($i = 1; $i <= $iCount; ++$i) {
            $oSection = new TreeSection();
            $oSection->setAttributes([
                'parent' => $iParentSection,
                'type' => Tree::typeSection,
                'title' => "Test section №{$i}",
                'visible' => Visible::HIDDEN_FROM_MENU,
            ]);
            $oSection->save(true);
            $oSection->setTemplate(Template::getCatalogTemplate());

            Parameters::setParams($oSection->id, 'content', 'defCard', $sCardName);

            if (!($i % 20)) {
                echo " № {$i}\n";
            }
        }

        echo "\n\nАвтоматическое добавление завершено\n";
    }

    /**
     * Удаляет товары определенной расширенной карточки(указать id/тех. имя карточки).
     *
     * @param bool $card
     *
     * @throws \yii\db\Exception
     */
    public function actionDeleteGoodsByCard($card = false)
    {
        if (!$card) {
            echo "Не переданы данные о карточке товара \n";
            exit;
        }

        $oCard = Card::get($card);
        if (!$oCard) {
            echo  \Yii::t('card', 'error_card_not_found');
        }

        if ($oCard->type != Entity::TypeExtended) {
            echo 'Карточка должна быть расширенной. Удаление товаров по базовой карточке недоступно.';
            exit;
        }

        $aGoodsId = GoodsTable::find()->where('ext_card_id', $oCard->id)->asArray()->getAll();
        $aGoodsId = ArrayHelper::getColumn($aGoodsId, 'base_id');
        $count = 100;
        $time = ceil(count($aGoodsId) / $count);
        $aMiniGoodsId = array_chunk($aGoodsId, $count);

        foreach ($aMiniGoodsId as $item) {
            $sMiniGoodsId = implode(',', $item);

            //удаляем все связи
            $sQueryDel1 = "DELETE FROM seo_data WHERE `group` = 'good' and `row_id` IN (" . $sMiniGoodsId . ')';
            Query::SQL($sQueryDel1);

            $sQueryDel2 = 'DELETE photogallery_albums, photogallery_photos
                      FROM co_base_card
                      LEFT JOIN photogallery_albums ON photogallery_albums.id = co_base_card.gallery
                      LEFT JOIN photogallery_photos ON photogallery_photos.album_id = co_base_card.gallery
                      WHERE co_base_card.id IN (' . $sMiniGoodsId . ')';
            Query::SQL($sQueryDel2);

            /*Уничтожаем записи о товарах в поисковом индексе*/
            $sQueryDel3 = "DELETE FROM search_index WHERE `class_name`='CatalogViewer' and `object_id` IN (" . $sMiniGoodsId . ') ;';
            Query::SQL($sQueryDel3);

            //удаляем галереи
            $sQuerySelect = 'SELECT DISTINCT gallery FROM co_base_card WHERE co_base_card.id IN (' . $sMiniGoodsId . ')';
            $returnSel = Query::SQL($sQuerySelect);
            try {
                while ($fetch = $returnSel->fetchArray()) {
                    if ($fetch['gallery']) {
                        $sPath = FILEPATH . 'gallery/' . $fetch['gallery'] . '/';
                        Files::delDirectoryRec($sPath);
                    }
                }
            } catch (ErrorException $e) {
                echo 'Не удалена папка галереи ' . $sPath . "\n";
                echo 'Текст ошибки: ' . $e->getMessage() . "\n";
                echo "Процесс удаления товаров прерван. \n";
                exit;
            }

            $qClean = 'DELETE FROM c_goods WHERE base_id IN (' . $sMiniGoodsId . ')';
            Query::SQL($qClean);

            $qClean1 = 'DELETE FROM cl_section WHERE goods_id IN (' . $sMiniGoodsId . ')';
            Query::SQL($qClean1);

            $qClean2 = 'DELETE FROM cl_semantic WHERE parent_id IN (' . $sMiniGoodsId . ')';
            Query::SQL($qClean2);

            $qClean3 = 'DELETE FROM cl_semantic WHERE child_id IN (' . $sMiniGoodsId . ')';
            Query::SQL($qClean3);

            $qClean4 = 'DELETE FROM co_base_card WHERE id IN (' . $sMiniGoodsId . ')';
            Query::SQL($qClean4);

            //удаление дополнительных данных товара из карточке
            $qClean5 = 'DELETE FROM ce_' . $oCard->name . ' WHERE id IN (' . $sMiniGoodsId . ')';

            Query::SQL($qClean5);

            echo '.';

            if (!($time % 100)) {
                echo " № {$time}\n";
            }
            --$time;
        }

        echo "\nготово\n";
    }
}
