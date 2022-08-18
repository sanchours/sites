/**
 * Вкладка для загрузки файлов
 */

/**
 * Форма редактора
 */
Ext.define('Ext.Adm.FileAddForm', {
    extend: 'Ext.form.Panel',

    height: '100%',
    width: '100%',
    autoScroll: true,
    saveResult: null,

    bodyPadding: 5,
    counter: 0,

    defaults: {
        labelAlign: 'left',
        anchor: '100%',
        listeners: {
            afterrender: function() {

            },
            render: function() {
                //self.fileInputEl.dom.click();
            },
            change: function() {
                this.cont.execute({},'upload');
            }
        }
    },

    items: [],

    initComponent: function() {
        var me = this;
        me.callParent();
    },

    execute: function( data, cmd ) {

        var form = this.getForm();

        switch ( cmd ) {

            // загрузить файлы
            case 'upload':

                // послать данные на сервер
                if ( form.isValid() ) {

                    var container = this.up('panel').up('panel');
                    container.setLoading( true );
                    var params = container.serviceData || {};
                    params = Ext.merge( params, {
                        sessionId: sessionId || '',
                        path: container.path,
                        cmd: 'upload'
                    });
                    form.submit({
                        url: buildConfig.request_script,
                        params: params,
                        success: this.onSuccess,
                        failure: this.onFailure
                    });

                }

                break;

            // добавить поле для файла
            case 'addField':

                this.addField();

                break;

            // инициализация
            case 'init':
            case '':

                this.addField();

                break;

        }


    },

    /**
     * Добавить поле к форме
     */
    addField: function() {

        var fieldId = this.counter++;

        // создать элемент формы - файл
        this.add({
            xtype: 'filefield',
            name: 'file'+fieldId.toString()+'[]',
            fieldLabel: this.lang.fileBrowserFile,
            buttonText: this.lang.fileBrowserSelect,
            labelWidth: 50,
            msgTarget: 'side',
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
            cont: this,
            allowBlank: fieldId
        });

    },


    /**
     * При удачной отправке запроса
     */
    onSuccess: function( form, action ) {

        processManager.onSuccess( action.response, { scope: processManager } );

    },

    /**
     * При НЕудачной отправке запроса
     */
    onFailure: function( form, action ){

        processManager.onSuccess( action.response );

    }

});
