в дочернии разделы нужно добавить 2 параметра
- [.][category_img][Изображение категории][Файл (локальное поле)]
- [.][category_show][Выводить раздел в разводку][Галочка (локальное поле)]

В основной раздел добавить объект, который устанавливается патчем [21735]
$this->addParameter( $iMainSectionId, 'object', 'CategoryViewer', '', 'CategoryViewer' );
$this->addParameter( $iMainSectionId, '.title', 'Категории', '', 'CategoryViewer' );
$this->addParameter( $iMainSectionId, 'layout', 'content', '', 'CategoryViewer' );

