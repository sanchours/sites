
Ext.define("CmsSearchModel", {
    extend: 'Ext.data.Model',
    proxy: {
        type: 'direct',
        doRequest: function (operation, callback, scope) {

            var me = this;

            me.getContainer().setEndCallback(function () {
                callback.call(scope, operation);
            });

            if ( !operation.params.query ) {
                me.getContainer().executeEndCallback();
                return;
            }

            // собрать запрос
            processManager.setData(me.path,{
                cmd: 'search',
                data: operation.params
            });

            // отправить запрос
            processManager.postData();

        },

        getContainer: function () {
            return processManager.getProcess(this.path);
        }

    },

    fields: [
        {name: 'title', mapping: 'title'},
        {name: 'url', mapping: 'url'},
        {name: 'class', mapping: 'object_class'},
        {name: 'id', mapping: 'object_id'}
    ]
});

Ext.create('Ext.data.Store', {
    storeId:'CmsSearchStore',
    pageSize: 10,
    model: 'CmsSearchModel'
});

//noinspection JSUnusedGlobalSymbols
Ext.define('Ext.Cms.Search',{

    extend: 'Ext.container.AbstractContainer',
    // extend: 'Ext.panel.Panel',
    title: 'Search the Ext Forums',
    width: 490,
    //bodyPadding: 10,
    // margin: '10 350 0 0',
    cls: 'search-panel',

    layout: 'anchor',

    items: [{
        itemId: 'search_panel_field',
        xtype: 'combo',
        store: 'CmsSearchStore',
        displayField: 'title',
        typeAhead: false,
        hideLabel: true,
        hideTrigger:true,
        minChars: 2,
        typeAheadDelay: 0,
        emptyText: '',
        anchor: '100%',

        listeners:{
            select: function (combo, records) {

                // сбросить ткуцщие данные
                combo.setValue('');

                const url = String.prototype.replace.apply(records[0]['data']['url'], ['/admin/', '/oldadmin/']);

                if ( records[0] )
                    window.location = url;

            },
            specialkey: function (combo, e) {
                if ( e.getKey() == e.ENTER && !combo.isExpanded ) {
                    combo.expand();
                }
            }
        }
    }],

    initComponent:function(){

        var me = this;
        me.callParent();

        me.items.items[0].emptyText = me.lang.searchSubText;

        // прокинуть путь к объекту в запросник данных
        me.getComponent('search_panel_field').store.model.proxy.path = me.path;

    },

    execute: function( data, cmd ) {

        var me = this;

        switch ( cmd ) {

            case 'list':
                var store = processManager.getProcess(me.path).getComponent('search_panel_field').store;
                store.loadData(data.items);
                break;

        }

        me.executeEndCallback();

        var window_size = document.getElementsByTagName('body')[0].offsetWidth;

        var new_width = window_size - 790;

        me.width = new_width+'px';

    },

    /**
     * Функция возврата при завершении запроса
     */
    endCallback: undefined,

    /**
     * Устанавливает функцию завершения запроса
     */
    setEndCallback: function ( func ) {
        this.endCallback = func;
    },

    /**
     * Вызывает функцию завершения запроса
     */
    executeEndCallback: function () {
        var me = this;
        if (me.endCallback) {
            me.endCallback.apply();
            me.endCallback = undefined;
        }
    }

});
