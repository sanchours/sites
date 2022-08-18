/**
 * Поле установки координат объекта
 */
Ext.define('Ext.sk.field.MapSingleMarker', {
    extend:'Ext.form.field.Base',//
    alias: ['widget.mapSingleMarker'],
    uses: ['Ext.button.Button'],//, 'Ext.layout.component.field.File'

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

    // создает кнопку
    createButton: function() {
        var me = this;

        // Элемент для группировки нескольких полей на одной строке
        me.button = Ext.create('Ext.form.Panel', {
            renderTo: me.bodyEl,
            parent_cont: me,
            width: 149,
            border: 0,
            items: [
                { // Кнопка открытия редактора карты
                    xtype: 'button',
                    name: 'buttonOpenEditorMap',
                    margin: '0px 0px 0px 3px',
                    width: 70,
                    text: dict.mapButtonText,
                    fieldCont: me,
                    selectMode: me.selectMode,
                    handler: me.onButtonClick
                },
                { // Кнопка очистить
                    xtype: 'button',
                    name: 'buttonClear',
                    margin: '0px 0px 0px 3px',
                    width: 73,
                    text: dict.mapButtonClean,
                    fieldCont: me,
                    selectMode: me.selectMode,
                    handler: me.onClean.bind(me)
                }
            ]
        });

    },

    // при нажатии кнопки выбора
    onButtonClick: function() {

        processManager.fireEvent( 'openEditorMap', {
            scope: this.fieldCont,
            mode: this.selectMode,
            fnc: 'onSetValue'
        } );

    },

    // при выборе файла
    onSetValue: function (value ) {

        this.setValue( value );
        // this.triggerBlur();

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
    },

    /**
     * Устанавливает в поле пустое значение
     */
    onClean: function () {
        this.setValue('');
    }

});