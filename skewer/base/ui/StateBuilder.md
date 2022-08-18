StateBuilder
============

Класс для построения интерфейсных состояний админских контролеров модулей

Пример инициализации

```php
// инициализация списка
$oFormBuilder = ui\StateBuilder::newList();
// ...
// ... $oFormBuilder->getForm() ...

// инициализация списка
$oFormBuilder = ui\StateBuilder::newEdit();
// ...
// ... $oFormBuilder->getForm() ...
```

Сборка списка
-------------

- [[ addHeadText( title ) ]]: Добавление описания в заголовок
- [[ addField( name, title, type, widget_type, params ) ]]: Добавление поля
- [[ setFields() ]]: Добавление полей в интерфейс (Вызывается после декларирования всех полей)
- [[ addWidget( field_name, class_name, method_name ) ]]: Добавление функции на обработку поля перед выводом
- [[ setEditableFields( fields_arr, method_name ) ]]: Добавление редактируемых в списке полей
- [[ enableDragAndDrop( method_name ) ]]: Добавление возможности D&D в списке
- [[ setGroups( field_name ) ]]: Ввводит группировку в списке по полю
- [[ setValue( values_arr ) ]]: Добавление контента
- [[ addButton( title, action, icon ) ]]: Добавление кнопку в панель
- [[ addBtnSeparator() ]]: Добавление разделителя в панель кнопок
- [[ addRowButtonUpdate( action ) ]]: Добавление кнопку редактирования в список
- [[ addRowButtonDelete( action ) ]]: Добавление кнопку удаления в список


Пример использования

```php
// LIST
$oFormBuilder = ui\StateBuilder::newList();

// добавляем поля
$oFormBuilder
    ->addField( 'title', \Yii::t('slider', 'title'), 's', 'string' )
    ->addField( 'preview_img', '', 's', 'addImg', array('listColumns' => array('flex' => 1)) )
    ->addField( 'active', \Yii::t('slider', 'active'), 'i', 'check' )
    ->addWidget( 'preview_img', 'skewer\\build\\Adm\\Slider\\Banner', 'getPreviewImg' )
    ->setEditableFields( array( 'active' ), 'saveBanner' );
;

$aItems = Banner::find()->getAll();

// добавляем данные
$oFormBuilder->setValue( $aItems );

// элементы управления
$oFormBuilder
    ->addButton( \Yii::t('slider', 'addBanner'), 'editBannerForm', 'icon-add' )
    ->addButton( \Yii::t('slider', 'displaySettings'), 'toolsForm', 'icon-edit' )
    ->buttonRowUpdate( 'SlideList' )
    ->buttonRowDelete( 'delBanner' )
;

// сформировать интерфейс
$this->setInterface( $oFormBuilder->getForm() );
```


Сборка формы
------------

- [[ addField( name, title, type, widget_type, params ) ]]: Добавление поля
- [[ addFieldWithValue( name, title, type, widget_type, value, params ) ]]: Добавление поля со значением
- [[ addSelectField( name, title, type, value_arr ) ]]: Добавление поля типа выпадающий список
- [[ addMultiSelectField( name, title, data, values_arr, params ) ]]: Добавление поля типа выпадающий список с выбором нескольких полей
- [[ addSpecField( name, title, class, value ) ]]: Добавление специфического поля
- [[ setFields() ]]: Добавление полей в интерфейс (Вызывается после декларирования всех полей)
- [[ setValue( values_arr ) ]]: Добавление контента
- [[ setSimpleValue( values_arr ) ]]: Добавление контента для одной сущности (todo)
- [[ addButton( title, action, icon ) ]]: Добавление кнопку в панель


Пример использования

```php
// FORM
// создаем форму
$oFormBuilder = ui\StateBuilder::newEdit();

// добавляем поля
//id	title	section	on_include
$oFormBuilder
    ->addField( 'id', 'id', 'i', 'hide' )
    ->addField( 'title', \Yii::t('slider', 'title'), 's', 'string', array('listColumns' => array('flex' => 1)) )
    ->addSelectField( 'section', \Yii::t('slider', 'section'), 's', Banner::getSectionTitle() )
    ->addField( 'on_include', \Yii::t('slider', 'on_include'), 'i', 'check' )
    ->addField( 'bullet', \Yii::t('slider', 'bullet'), 'i', 'check' )
    ->addField( 'scroll', \Yii::t('slider', 'scroll'), 'i', 'check' )
    ->addField( 'active', \Yii::t('slider', 'active'), 'i', 'check' )
;

// устанавливаем значения
$oFormBuilder->setValue( $oBannerRow );

// добавляем элементы управления
$oFormBuilder->addButton( '_save', 'saveBanner', 'icon-save' );
if ( $iBannerId )
    $oFormBuilder->addButton( \Yii::t('slider', 'editSlides'), 'slideList', 'icon-edit' );
$oFormBuilder->addButton( '_cancel', 'bannerList', 'icon-cancel' );


// сформировать интерфейс
$this->setInterface( $oFormBuilder->getForm() );
```