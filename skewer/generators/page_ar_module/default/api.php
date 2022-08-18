<?php
/**
 * This is the template for generating a module class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_ar_module\Generator
 */
$moduleName = $generator->moduleName;
$nameAR = $generator->nameAR;
$lomerNameAR = mb_strtolower($generator->nameAR);
$descAR = $generator->aDescARs[$nameAR];
$pathAR = $generator->pathAR;
$fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Page\\' . $moduleName;
    echo "<?php\n";
?>

namespace <?= $ns; ?>;

/**
 *  Class Api
 * @package skewer\build\Page\<?= $moduleName . "\n"; ?>
 */
class Api{

   public static function getUrl($value, $param = 'id') {

        if(!$value){
            return '';
        }

        if($param !== 'alias' || $param !== 'id'){
            $param = 'id';
        }

        return '<?=$lomerNameAR; ?>_'.$param.'='.$value;
    }

<?php if ($generator->aNameARs !== []):
foreach ($generator->aNameARs as $item):
    $lomerItem = mb_strtolower($item);
        ?>
    public static function get<?=$item; ?>Url(array $data) {

        if(!isset($data['item']) && !is_array($data['item']))
            return '';

        if($data['<?=$lomerNameAR; ?>_alias']){
            $url= '<?=$lomerNameAR; ?>_alias='.$data['<?=$lomerNameAR; ?>_alias'];
        }else{
            $url= '<?=$lomerNameAR; ?>_id='.$data['<?=$lomerNameAR; ?>_id'];
        }

        if(isset($data['item']['alias']) && $data['item']['alias']){
            $url=$url.'&<?=$lomerItem; ?>_alias='.$data['item']['alias'];
        }else{
            $url=$url.'&<?=$lomerItem; ?>_id='.$data['item']['id'];
        }

        return $url;
    }
<?php endforeach; endif; ?>
}