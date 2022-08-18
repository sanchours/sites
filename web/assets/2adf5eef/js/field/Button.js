/**
 * Поле для выбора файла
 */
Ext.define('Ext.sk.field.Button', {
    extend:'Ext.form.field.Picker',
    alias: ['widget.buttonfield'],
    uses: ['Ext.button.Button'],

    disabled: false,

    // Текст на кнопке выбора
    buttonText: '---',

    // компонент вывода
    componentLayout: 'buttonfield',

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
        /** @namespace me.buttonConfig */
        /** @namespace me.bodyEl */
        me.button = Ext.widget('button', Ext.apply({
            ui: me.ui,
            fieldCont: me,
            selectMode: me.selectMode,
            renderTo: me.bodyEl,
            text: me.buttonText,
            cls: Ext.baseCSSPrefix + 'form-file-btn',
            preventDefault: false,
            style: 'margin-left:' + me.buttonMargin + 'px',
            listeners: { click: me.onButtonClick }
        }, me.buttonConfig))

    },

    // при нажатии кнопки выбора
    onButtonClick: function() {

        processManager.fireEvent( 'press_button', {
            scope: this.fieldCont,
            mode: this.selectMode,
            fnc: 'onButtonPress'
        } );

    },

    // при выборе файла
    onButtonPress: function ( value ) {

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
