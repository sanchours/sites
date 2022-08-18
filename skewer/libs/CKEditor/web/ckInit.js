/**
 * CKEditor надстройка для ExtJS
 */
Ext.form.CKEditor = function(config){

    this.config = config;

    Ext.form.CKEditor.superclass.constructor.call(this, config);
    this.on('destroy', function (ct) {
        ct.destroyInstance();
    });

};

Ext.define('Ext.form.CKEditor', {

    extend:'Ext.form.TextArea',

    alias:'widget.ckeditor',

    grow: true,

    isIE9: function() {
        if (navigator.appVersion.indexOf("MSIE")>0) return 1;

        return 0;
    },

    onRender:function (ct, position) {

        var me = this;

        if (!this.el) {

            //noinspection JSUnusedGlobalSymbols
            this.defaultAutoCreate = {
                tag:'textarea',
                autocomplete:'off'
            };

        }

        Ext.form.TextArea.superclass.onRender.call(this, ct, position);
        if (!this.config.CKConfig) this.config.CKConfig = {};
        var config = {};
        var defConfig = {
            resize_enabled: me.grow,
            on:{
                // maximize the editor on startup
                'instanceReady':function (evt) {
                    var editor = Ext.get('cke_'+evt.editor.name);
                    var fieldDom = editor.dom.parentElement.parentElement;
                    var field = Ext.ComponentManager.get(fieldDom.id);
                    evt.editor.resize((evt.editor.element.$.style.width ? evt.editor.element.$.style : '100%'), field.height);
                    evt.editor.is_instance_ready = true;
                },
                change: function() {
                    var field = this.field ? this.field : this.field = Ext.ComponentManager.get(editor.dom.parentElement.parentElement.id);
                    field.fireEvent('change', field, field.getValue());
                },
                resize: function( evt ) {
                    var editor = this.editor ? this.editor : this.editor = Ext.get('cke_'+evt.editor.name);
                    var field = this.field ? this.field : this.field = Ext.ComponentManager.get(editor.dom.parentElement.parentElement.id);
                    field.setHeight( editor.getHeight() );
                    field.autoSize();
                },
                key: function( evt ) {
                    if ( evt.data.keyCode == CKEDITOR.CTRL + 13 /* ENTER */ ) {
                        me.execSave();
                        evt.cancel();
                    }
                }
            }
        };

        Ext.merge(
            config,
            this.config.CKConfig,
            defConfig,
            this['addConfig'] ? this['addConfig'] : {}
        );
        config.language = buildConfig.CKEditorLang;
        // если задана дополнительная конфигурация
        this.elementId = this.getInputId();

        CKEDITOR.config.autoParagraph = false;

        CKEDITOR.lang.load(config.language, 'en', function(){
            if (typeof CKEDITOR.lang[String(config.language)] != 'undefined'){
                CKEDITOR.lang[String(config.language)] = Ext.merge({},
                    CKEDITOR.lang[String(config.language)],
                    config.addLangParams
                )
            }
        });

        CKEDITOR.on('dialogDefinition', function( ev ) {
            var dialogName = ev.data.name;
            var dialogDefinition = ev.data.definition;

            if(dialogName === 'table') {
                var infoTab = dialogDefinition.getContents('info');
                var cellSpacing = infoTab.get('txtCellSpace');
                cellSpacing['default'] = "";
                var cellPadding = infoTab.get('txtCellPad');
                cellPadding['default'] = "";
                var border = infoTab.get('txtBorder');
                border['default'] = "";
            }
        });

        CKEDITOR.dtd.$removeEmpty['i'] = false;
        CKEDITOR.dtd.$removeEmpty['span'] = false;
        CKEDITOR.dtd.$removeEmpty['div'] = false;

        CKEDITOR.sysmode = config.sysmode;
        CKEDITOR.lock_tooltip_module = config.lock_tooltip_module;
        CKEDITOR.video_section = config.video_section;
        CKEDITOR.p_block = config.p_block;
        CKEDITOR.replace(this.elementId, config);


        if (me.isIE9()){
            if (!me.getValue().trim()){
                me.setValue("&nbsp;&nbsp;");
            }
        }
    },

    execSave: function() {
        var form = this.up('form');
        if ( form && form.callSaveState )
            form.callSaveState();
    },

    /**
     * Проверка на изменения
     * @returns bool
     */
    isDirty: function() {
        return CKEDITOR.instances[this.elementId].checkDirty();
    },

    onResize:function (width, height) {
        Ext.form.TextArea.superclass.onResize.call(this, width, height);
        if (CKEDITOR.instances[this.elementId].is_instance_ready) {
            CKEDITOR.instances[this.elementId].resize(width, height);
        }
    },

    setValue:function (value) {
        if (!value) value = ' ';
        Ext.form.TextArea.superclass.setValue.apply(this, arguments);
        if (CKEDITOR.instances[this.elementId]) CKEDITOR.instances[this.elementId].setData(value);
    },

    getValue:function () {
        if ( Ext.isIE7 )
            return '';
        if (CKEDITOR.instances[this.elementId]) CKEDITOR.instances[this.elementId].updateElement();
        return Ext.form.TextArea.superclass.getValue.call(this);
    },


    getRawValue:function () {
        if (CKEDITOR.instances[this.elementId]) CKEDITOR.instances[this.elementId].updateElement();
        return Ext.form.TextArea.superclass.getRawValue.call(this);
    },

    destroyInstance:function () {
        if (CKEDITOR.instances[this.elementId]) {
            delete CKEDITOR.instances[this.elementId];
        }
    }

});
