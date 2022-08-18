<?php
/**
 * This is the template for generating a routing class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_module\Generator
 */
$className = $generator->moduleName;
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
            '/dict_alias/' => 'viewByAlias',
            '/dict_id(int)/' => 'viewById',
            '/*page/page(int)/',
            '/*page/page(int)/!response/',
            '/dict_alias/!response/' => 'viewByAlias',
            '/dict_id(int)/!response/' => 'viewById',
            '/!response/'
        );
    }

}
