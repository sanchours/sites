/**
 * Панель для выбора и загрузки файла на сервер
 */
Ext.define('Ext.Adm.PhotoAddField',{

    extend: 'Ext.form.Panel',

    border: 0,
    margin: 0,
    padding: 0,

    baseCls: '',

    width: 120,

    initData: {
        text: 'Upload...'
    },

    items: [{
        xtype: 'filefield',
        name: 'uploadFile[]',
        hideLabel: true,
        buttonText: 'Upload...',
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
            iconCls: 'icon-add',
            width: 120
        },
        listeners: {
            change: function(me){
                me.up().onUpload();
            }
        }

    }],

    initComponent: function() {

        this.items[0].buttonText = this.initData.text;

        this.callParent();

        if ( this.addText ) {
            this.add( {
                border: 0,
                margin: 10,
                baseCls: 'js_adm_gallery_info',
                html: this.addText
            } );
        }

    },

    onUpload: function(){

        var me = this,
            container = processManager.getMainContainer(me)
        ;

        var params = {};
        Ext.merge( params, container.serviceData || {}, {
            sessionId: sessionId || '',
            path: container.path,
            cmd: 'uploadImage'
        });
        me.submit({
            waitMsg: me.lang.galleryUploadingImage,
            url: buildConfig.request_script,
            params: params,
            success: me.onSuccess,
            failure: me.onFailure
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

        processManager.onFailure( action.response );

    }

});
