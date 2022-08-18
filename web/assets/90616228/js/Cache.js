/**
 * Панель сброса кэша в системе администрирования
 */
Ext.define('Ext.Cms.Cache',{
    extend: 'Ext.container.AbstractContainer',
    border: 0,
    margin: '0 0 3 0',
    padding: 0,

    currentLang: '',

    lang: {
        drop_cache_act: 'Drop cache'
    },

    childEls: ['body'],

    // шаблон для вывода
    renderTpl: [
        '<div class="cache-panel">',
        '<div id="{id}-body"></div>',
        '</div>'
    ],

    getTargetEl: function() {
        return this.body || this.frameBody || this.el;
    },

    initComponent:function(){

        var me = this;

        me.callParent();

        // добавляем кнопку сброса
        me.add( Ext.create('Ext.Button', {
            text: '',
            scale: 'medium',
            tooltip: me.lang.drop_cache_act,
            cls: 'drop-cache-button',
            iconCls: 'icon-reinstall',
            handler: function() {
                me.logOut();
            }
        }) );


    },

    execute: function( data ) {

        if ( data.error )
            sk.error( data.error );

        if ( processManager.existsProcess('out') )
            processManager.getProcess('out').setLoading( false );

    },

    /**
     * Сброс кэша
     */
    logOut: function(){

        var me = this;

        processManager.setData(me.path, {
            cmd: 'dropCache'
        });

        processManager.postData();

        if ( processManager.existsProcess('out') )
            processManager.getProcess('out').setLoading( true );

    }

});
