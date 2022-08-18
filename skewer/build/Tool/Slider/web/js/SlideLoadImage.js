/**
 * Панель для выбора и загрузки файла на сервер
 */
Ext.define('Ext.Tool.SlideLoadImage',{

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
        name: 'uploadFile',
        hideLabel: true,
        buttonText: 'Upload...',
        msgTarget: 'side',
        allowBlank: true,
        buttonOnly: true,
        slideId: 0,
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

        var params = Ext.merge( {}, container.serviceData || {}, {
            sessionId: sessionId || '',
            path: container.path,
            slideId: this.initData.addParams.slideId,
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

