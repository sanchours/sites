/**
 * Перекрытый компонент для обработки верстки для 2х кнопок
 */
Ext.define('Ext.layout.component.field.MyFile', {
    alias: ['layout.filefield'],
    extend: 'Ext.layout.component.field.Field',

    type: 'filefield',

    sizeBodyContents: function(width, height) {
        var me = this,
            owner = me.owner;

        if (!owner.buttonOnly) {

            width = Ext.isNumber(width) ? width - owner.button.getWidth() - owner.buttonMargin : width;

            if ( owner.fastbutton )
                width = Ext.isNumber(width) ? width - owner.fastbutton.getWidth() - owner.buttonMargin * 2 : width;

            me.setElementSize(owner.inputEl, width);
        }
    }
});


/**
 * Компонент для быстрой загрузки файла
 */

Ext.define('Ext.layout.component.field.FastFileUpload',{
    extend: 'Ext.form.Panel',
    border: 0,
    margin: '0 0 0 6px',
    padding: 0,
    baseCls: Ext.baseCSSPrefix + 'btn',
    parent_cont: null,
    //baseCls: Ext.baseCSSPrefix + 'form-file-btn',
    width: 42,
    height: 22,

    items: [{
        xtype: 'filefield',
        name: 'uploadFile[]',
        hideLabel: true,
        buttonText: sk.dict('fileBrowserFile'),
        msgTarget: 'side',
        allowBlank: true,
        buttonOnly: true,
        multiple: true,
        createFileInput : function() {
            var me = this;
            me.fileInputEl = me.button.el.createChild({
                name: me.getName(),
                cls: Ext.baseCSSPrefix + 'form-file-input',
                tag: 'input',
                type: 'file',
                size: 1,
                multiple: 'multiple'
            }).on('change', me.onFileChange, me);
        },
        buttonConfig: {
//            iconCls: 'icon-add',
//            width: 28
        },
        listeners: {
            change: function(me){
                me.up().onUpload();
            }
        }

    }],

    initComponent: function() {

        this.callParent();

//        if ( this.addText ) {
//            this.add( {
//                border: 0,
//                margin: 0,
//                baseCls: '',
//                html: this.addText
//            } );
//        }

    },

    onUpload: function() {

        var me = this;
        var iSectionId = this.parseUrl('section');

        var parent = window.opener;
        try {
            var ProcMgr = parent ? parent['processManager'] : processManager;
        } catch(e) {
            ProcMgr = processManager;
        }

        if (!iSectionId) iSectionId = ProcMgr.getEventValue( 'get_section_id' );

        var folder_alias = ProcMgr.getEventValue('get_tab_param', '_filebrowser_section');

        var params = {
            cmd: 'uploadImage',
            section: iSectionId,
            folder_alias: folder_alias,
            selectMode: me.parent_cont.selectMode
        };

        me.submit({
            waitMsg: 'loading...',
            url: '/ajax/uploader.php',
            params: params,
            success: me.onSuccess,
            failure: me.onFailure
        });

    },

    /**
     * При удачной отправке запроса
     */
    onSuccess: function( form, action ) {
        form.parent_cont.onFileSelect( action.result.file );
    },

    /**
     * При НЕудачной отправке запроса
     */
    onFailure: function( form, action){
        sk.error(action.result.message);
    },

    parseUrl: function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( window.location.href );
        if (null == results) {
            return '';
        }
        return results[1];
    }



});


/**
 * Поле для выбора файла
 */
Ext.define('Ext.sk.field.FileSelector', {
    extend:'Ext.form.field.Picker',
    alias: ['widget.selectfilefield'],
    uses: ['Ext.button.Button', 'Ext.layout.component.field.MyFile'],

    // Текст на кнопке выбора
    buttonText: sk.dict('fileBrowserSelect'),

    // Отступ кнопки
    buttonMargin: 3,

    // Флаг "Только чтение"
    readOnly: false,
    hideTrigger: true,
    selectMode: '',

    // компонент вывода
    componentLayout: 'filefield',

    // при генерации
    onRender: function() {
        var me = this;

        // вызвать родительский обработчик для создания поля ввода
        me.callParent(arguments);

        // создать элементы
        me.createFastButton();
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

    // создает кнопку быстрой загрузки
    createFastButton: function() {
        var me = this;
        /** @namespace me.buttonConfig */
        /** @namespace me.bodyEl */

//        me.fastbutton = Ext.widget('button', Ext.apply({
//            ui: me.ui,
//            fieldCont: me,
//            selectMode: me.selectMode,
//            renderTo: me.bodyEl,
//            text: me.buttonFastText,
//            cls: Ext.baseCSSPrefix + 'form-file-btn',
//            preventDefault: false,
//            style: 'margin-left:' + me.buttonMargin + 'px',
//            listeners: { click: me.onFastButtonClick }
//        }, me.buttonConfig))

        me.fastbutton = Ext.create(
            'Ext.layout.component.field.FastFileUpload',
            {
                renderTo: me.bodyEl,
                parent_cont: me

            }
        );

    },

    // при нажатии кнопки выбора
    onButtonClick: function() {

        processManager.fireEvent( 'select_file', {
            scope: this.fieldCont,
            mode: this.selectMode,
            fnc: 'onFileSelect'
        } );

    },

    // при выборе файла
    onFileSelect: function ( value ) {

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
        var fastbutton = this.fastbutton;
        if (fastbutton) {
            fastbutton.disable();
        }
    },

    // при вкдючении
    onEnable: function(){
        var me = this;
        me.callParent();
        me.button.enable();
        me.fastbutton.enable();
    },

    // при удалении элемента
    onDestroy: function(){
        Ext.destroyMembers(this, 'button');
        Ext.destroyMembers(this, 'fastbutton');
        this.callParent();
    }

});
