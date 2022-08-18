// Запрос id шаблона по псевдониму 
$iSection = (int)Tree::getByAlias( 'photos', \Yii::$app->sections->topMenu() );

if ( !$iSection )
    $this->fail('Нет шаблона с псевдонимом [photos]');

$aSections = array(
    $iSection
);

// Запрос разделов, унаследованных от него
$aSectionList = Parameters::getByFilter( array(
    'group' => '.',
    'name' => 'template',
    'value' => $iSection
) );

foreach ( $aSectionList as $iSubSection ) {
    $aSections[] = (int)$iSubSection['parent'];
}

// установка модуля
$this->installModule( 'MiniCart', Layer::PAGE );

// запрос основных разделов
\Yii::$app->sections->tplNew(); // там куча методов на основные разделы

// изменение файлов есть в релизе 20.1 в патче 23684

// копирование файлов - 21 патч 24513