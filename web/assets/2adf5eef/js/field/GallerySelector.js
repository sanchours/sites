/**
 * Поле для выбора файла
 */
Ext.define('Ext.sk.field.GallerySelector', {
    extend:'Ext.form.field.Picker',//
    alias: ['widget.selectgalleryfield'],

    // Отступ кнопки
    buttonMargin: 0,

    // Флаг "Только чтение"
    readOnly: true,
    hideTrigger: true,
    selectMode: '',

    // компонент вывода
    componentLayout: 'filefield',//buttonfield filefield

    // при генерации
    onRender: function() {
        var me = this;

        // вызвать родительский обработчик для создания поля ввода
        me.callParent(arguments);

        // создать элементы
        me.createButton();

        // деактивация, если нужна
        if (me.disabled) {
            me.disableItems();
        }

    },

    // создает кнопки
    createButton: function () {
        var me = this;

        var items = [
            { // Кнопка создания/редактирования альбома
                xtype: 'button',
                name: 'buttonShowAlbum',
                margin: '0px 0px 0px 3px',
                width: 60,
                text: dict.galleryBrowserSelect,
                fieldCont: me,
                selectMode: me.selectMode,
                handler: me.onShowAlbum
            }
        ];

        if (typeof(this.only_show_album)=='undefined'){
            items.push({ // Кнопка пересоздания альбома с новым профилем + редактирование
                xtype: 'button',
                name: 'buttonNewAlbum',
                margin: '0px 0px 0px 3px',
                width: 80,
                text: dict.galleryBrowserNew,
                fieldCont: me,
                selectMode: me.selectMode,
                handler: me.onNewAlbum
            });
        }

        // Элемент для группировки нескольких полей на одной строке
        me.button = Ext.create('Ext.form.Panel', {
            renderTo: me.bodyEl,
            parent_cont: me,
            width: 146,
            border: 0,
            items: items
        });
    },

    // при нажатии кнопки галереи
    onShowAlbum: function(that) {

        me = (that) ? that : this;

        processManager.fireEvent( 'edit_gallery', {
            scope: me.fieldCont,
            mode: me.selectMode,
            fnc: 'onGallerySelect'
        } );

    },

    // при нажатии кнопки создания галереи
    onNewAlbum: function() {

        // Если ещё нет альбома, то просто создать
        if (!this.fieldCont['value'])
            return this.fieldCont.onShowAlbum(this);

        sk.confirmWindow(sk.dict('confirmHeader'), dict.galleryBrowserNewConfirm, function(res){
            if ( res === 'yes' ) {

                processManager.fireEvent( 'edit_gallery', {
                    scope: this.fieldCont,
                    mode: this.selectMode,
                    gal_new_album: 1,
                    fnc: 'onGallerySelect'
                } );

            }
        }, this );

    },

    // при выборе файла
    onGallerySelect: function ( value ) {
        this.setValue( value );
        this.triggerBlur();

    },

    // при отключении
    onDisable: function(){
        this.callParent();
        this.disableItems();
    },

    // отключение подчиненных элементов
    disableItems: function(){
        var button = this.button;
        if (button) {
            button.disable();
        }
    },

    // при вкдючении
    onEnable: function(){
        var me = this;
        me.callParent();
        me.button.enable();
    },

    // при удалении элемента
    onDestroy: function(){
        Ext.destroyMembers(this, 'button');
        this.callParent();
    }

});