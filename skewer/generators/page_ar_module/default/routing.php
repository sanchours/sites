<?php
/**
 * This is the template for generating a routing class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_ar_module\Generator
 */
$className = $generator->moduleName;
$nameAR = mb_strtolower($generator->nameAR);
$fullClassName = $generator->getModulePath();
$ns = 'skewer\build\Page\\' . $className;

echo "<?php\n";
?>

namespace skewer\build\Page\<?= $className; ?>;

use skewer\base\router\RoutingInterface;


class Routing implements RoutingInterface {

    /**
     * Возвращает паттерны разбора URL
     * @static
     * @return bool | array
     */
    public static function getRoutePatterns() {

        return array(
            '/<?=$nameAR; ?>_alias/' => 'viewByAlias',
            '/<?=$nameAR; ?>_id(int)/' => 'viewById',
            '/*page/page(int)/',
            '/*page/page(int)/!response/',
            '/<?=$nameAR; ?>_alias/!response/' => 'viewByAlias',
            '/<?=$nameAR; ?>_id(int)/!response/' => 'viewById',
            '/!response/',
            <?php if ($generator->aNameARs !== []):
            foreach ($generator->aNameARs as $item):
            $lowerItem = mb_strtolower($item); ?>

            //<?=$lowerItem; ?>

            '/<?=$nameAR; ?>_alias/*<?=$lowerItem; ?>/<?=$lowerItem; ?>_alias/' => '<?=$lowerItem; ?>ViewByAlias',
            '/<?=$nameAR; ?>_id(int)/*<?=$lowerItem; ?>/<?=$lowerItem; ?>_alias/' => '<?=$lowerItem; ?>ViewByAlias',
            '/<?=$nameAR; ?>_alias/*<?=$lowerItem; ?>/<?=$nameAR; ?>_id(int)/' => '<?=$lowerItem; ?>ViewById',
            '/<?=$nameAR; ?>_id(int)/*<?=$lowerItem; ?>/<?=$nameAR; ?>_id(int)/' => '<?=$lowerItem; ?>ViewById',

            '/<?=$nameAR; ?>_alias/*<?=$lowerItem; ?>/*page/page(int)/',
            '/<?=$nameAR; ?>_id(int)/*<?=$lowerItem; ?>/*page/page(int)/',
            '/<?=$nameAR; ?>_alias/*<?=$lowerItem; ?>/*page/page(int)/!response/',
            '/<?=$nameAR; ?>_id(int)/*<?=$lowerItem; ?>/*page/page(int)/!response/',

            '/<?=$nameAR; ?>_alias/*<?=$lowerItem; ?>/<?=$lowerItem; ?>_alias/!response/' => '<?=$lowerItem; ?>ViewByAlias',
            '/<?=$nameAR; ?>_id(int)/*<?=$lowerItem; ?>/<?=$lowerItem; ?>_alias/!response/' => '<?=$lowerItem; ?>ViewByAlias',
            '/<?=$nameAR; ?>_alias/*<?=$lowerItem; ?>/<?=$lowerItem; ?>_id(int)/!response/' => '<?=$lowerItem; ?>ViewById',
            '/<?=$nameAR; ?>_id(int)/*<?=$lowerItem; ?>/<?=$lowerItem; ?>_id(int)/!response/' => '<?=$lowerItem; ?>ViewById',
            <?php endforeach; endif; ?>

        );
    }// func

}
