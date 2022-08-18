/**
 * Система управления набором вкладок дизайнерского режима
 */
Ext.define('Ext.Design.Tabs', {
    extend: 'Ext.tab.Panel',
    margin: 0,
    region: 'center',
    title: '',
    deferredRender: false,
    activeTab: 0,
    //tabPosition: 'bottom',
    defaults: {
        padding: 5
    },
    items: [],

    initComponent: function() {

        var me = this;

        processManager.addEventListener('reload_display_form', me.path, me.reloadDisplayForm );
        processManager.addEventListener('select_label', me.path, me.onModuleControlCall );
        processManager.addEventListener('select_editor', me.path, me.onEditorCall );
        processManager.addEventListener('urlChange',me.path,me.onUrlChange);
        processManager.addEventListener( 'get_section_id',this.path,'getSectionId');
        processManager.addEventListener( 'get_tab_param',me.path,me.getTabParam);

        me.callParent();

    },

    execute: function( data, cmd ) {

        var me = this;

        if ( data.error )
            sk.error( data.error );

        switch ( cmd ) {

            // загрузка управляющей части модуля
            case 'load_module':

                // процесс новой вкладки
                var process = processManager.getProcess( data['tabPath'] );

                // флаг внешнего контроллера
                process.isExtModuleControl = true;

                // выбрать новую вкладку
                me.setActiveTab( process );

                break;

            // закрытие вкладок
            case 'close_module':

                var i;
                for ( i in data.list )
                    me.remove( processManager.getProcess(data.list[i]) );

                break;

        }

        me.setLoading( false );

        if ( data.error )
            sk.error( data.error );

    },

    /**
     * Обновляет фрейм отображения
     */
    reloadDisplayForm: function() {
        frameApi.reloadDisplayFrame();
    },

    /**
     * При смене раздела у фрейма отображения
     */
    onUrlChange: function() {

        var me = this;



        // найти все вкладки внешнех контроллеров
        var list = [];
        var i;
        for ( i in me.items.getRange() ) {
            var item = me.items.getAt( i );
            if ( item.isExtModuleControl )
                list.push( item.path )
        }

        // если есть сторонние компоненты
        if ( list.length ) {

            me.setLoading( true );

            // запустить процесс очистки
            processManager.setData(me.path,{
                cmd: 'delModule',
                items: list
            });

        }

    },

    /**
     * При открытии режима редактирования модуля
     * @param label
     * @param sectionId
     */
    onModuleControlCall: function( label, sectionId ) {

        var me = this;

        me.setLoading( true );

        processManager.setData(me.path,{
            cmd: 'addModule',
            labelName: label,
            sectionId: sectionId
        });

    },

    /**
     * При открытии режима редактирования параметра
     * @param editorId
     * @param sectionId
     */
    onEditorCall: function( editorId, sectionId ) {

        var me = this;

        me.setLoading( true );

        processManager.setData(me.path,{
            cmd: 'addEditor',
            editorId: editorId,
            sectionId: sectionId
        });

    },

    /**
     * Отдает значение текущего раздела
     */
    getSectionId: function() {
        return frameApi.getDisplaySectionId();
    },

    /**
     * для события получения параметра модуля, переданного в интерфейс
     * @return {string|undefined}
     */
    getTabParam: function(name) {
        if ( !this.activeTab.serviceData )
            return undefined;
        return this.activeTab.serviceData[name] || undefined;
    }
});
