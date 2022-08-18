//noinspection JSUnusedGlobalSymbols
/**
 * Система управления набором вкладок CMS
 */
Ext.define('Ext.Cms.Tabs', {
    extend: 'Ext.tab.Panel',
    bodyPadding: 5,
    margin: 0,
    region: 'center', // a center region is ALWAYS required for border layout
    deferredRender: false,
    activeTab: 0,     // first tab initially active
    items: [],
    cls: 'sk-tabs',

    // флаг использования истории
    useHistory: true,

    // вкладка, котокую нужно выбрать. устанавливается при клике
    tabToSet: '',

    // вкладка, котокую нужно выбрать, но она еще только создается
    // или будет создана - используется для первичной загрузки
    tabToSelect: '',

    // id текущего набора
    nowItemId: 0,
    lastItemId: 0,
    nowModule: '',
    lastModule: '',

    lang: {
        tabsSectionTitlePrefix: 'tabsSectionTitlePrefix'
    },

    /**
     * Инициализировать объект
     */
    initComponent: function() {

        var me = this;

        // загрузка набора вкладок
        processManager.addEventListener( 'tabs_load', this.path, 'loadTabs' );
        processManager.addEventListener( 'tabs_reload', this.path, 'loadTabsForSet' );
        processManager.addEventListener( 'tabs_reload_current', this.path, 'reloadCurrentTab' );

        // событие после обновления
        processManager.addEventListener( 'close_all_tabs', this.path, 'closeAll' );

        processManager.addEventListener( 'location_render', me.path, 'processToken' );
        processManager.addEventListener( 'location_set_value',me.path,'setToken');
        processManager.addEventListener( 'tab_to_select',me.path,me.getTabNameToSet);
        processManager.addEventListener( 'get_tab_param',me.path,me.getTabParam);

        me.on( 'beforetabchange', me.onBeforeTabChange );

        this.callParent();

    },

    /**
     * Обработка пришедшей посылки
     */
    execute: function( params ){

        var title = params.title || '',
            me = this
        ;

        if ( title )
            me.setTitle( me.lang.tabsSectionTitlePrefix+title );
        me.nowSectionId = params['sectionId'] || 0;

        me.nowItemId = params['itemId'] || 0;
        me.nowModule = params['module'] || '';

        if ( params.items ) {

            if ( me.nowModule != me.lastModule )
                me.closeAll();

            for ( var key in params.items ) {

                if ( !params.items.hasOwnProperty(key) )
                    continue;

                me.add(Ext.Object.merge({
                    closable: false,
                    autoScroll: true
                },params.items[key]));

            }

        } else {

            if ( !this.getActiveTab() )
                this.setActiveTab(1);

        }

        /**
         * удаление отсутствующих объектов
         */

        // обойти все вкладки
        for ( var i=me.items.getCount()-1 ; i>=0 ; i-- ) {

            var tabItem = me.items.get(i);
            if ( !tabItem )
                continue;

            // внутреннее имя подключенного модуля
            var tabName = tabItem.path.substr( me.path.length+1 );

            // если его нет в пришедшем массиве
            if ( !sk.inArray( tabName, params.children ) ) {

                // удалить
                tabItem.destroy();

            }

        }

        if ( me.tabToSelect )
            me.setTabByName( me.tabToSelect );

        me.lastItemId = params['itemId'] || 0;
        me.lastModule = params['module'] || '';

        if ( params.error )
            sk.error( params.error );

        me.setLoading(false);

    },

    // закрыть все вкладки
    closeAll: function() {

        this.setActiveTab(0);

        var node = this.getComponent('welcome_tab');

        // удалить все кроме первой вкладки
        if ( node ) {
            while ( node = node.nextNode() ) {
                this.remove( node );
            }
        } else {
            // если её нет, то вообще все
            this.removeAll();
        }

    },

    /**
     * Загружает набор вкладок для заданного ресурса
     */
    loadTabs: function( id, module ) {

        var me = this;

        // выйти, если там же
        if ( (me.nowItemId == id) && (me.nowModule == module) )
            return false;

        // выполнить загрузку вкладок
        this.loadTabsForSet( id, module );

        return true;

    },

    /**
     * Посылает запрос вкладок
     * @param id
     * @param module
     */
    loadTabsForSet: function( id, module ) {

        var self = this;

        // приведение к нужному типу
        module = processManager.getModuleAlias(module);

        // увязать на отправку посылки
        processManager.onDataSend( function() {
            self.setLoading(true);
        } );

        // сборка запроса
        processManager.setData(this.path, {
            cmd: 'loadTabs',
            itemId: id,
            module: module || '',
            tab: this.getActiveTabName()
        });

    },

    /**
     * Открывает заново текущую вкладку
     */
    reloadCurrentTab: function() {
        if ( this.getActiveTab() )
            this.getActiveTab().reInitModule();
    },

    /**
     * Событие перед сменой вкладки. нужно для работы с историей
     */
    onBeforeTabChange: function(tabPanel, tab) {

        var me = this;
        var to = '';

        // если используем историю
        if (!tabPanel.useHistory) return true;


        if (me.tabToSelect) {
            to = me.tabToSelect;
        }else if ( tab && tab.path ) {
            to = tab.path.substr( tabPanel.path.length+1 );
        }

        // и есть правильная вкладка
        if ( to ) {

            // задаем вкладку для перехода
            tabPanel.tabToSet = to;

            // вызываем переход через историю
            processManager.fireEvent('location_change');

            // прерываем стандартный переход по вкладке
            return !((me.lastItemId==me.nowItemId)&&(me.lastModule==me.nowModule));

        }

        // возврат к обычному выбору вкладки
        return true;

    },

    /**
     * обработка токена истории
     */
    processToken: function( data ){

        var me = this,
            newTabName
        ;

        if ( !this.useHistory ) return false;

        // идентификатор раздела
        newTabName = data[me.path] || '';
        if ( !newTabName ) return false;

        me.setTabByName( newTabName );

        return true;

    },

    /**
     * обработка добавления данных в токен истории страниц
     */
    setToken: function(){

        if ( !this.useHistory ) return false;

        var me = this,
            newTabName;

        newTabName = me.getActiveTabName();

        me.tabToSet = '';

        // нет имени - выйти
        if ( !newTabName )
            return false;

        // задать данные
        processManager.setData(me.path,newTabName,'locPack');

        return true;

    },

    /**
     * Отдает имя активной вкладки
     * @return string
     */
    getActiveTabName: function() {

        // выбираем текущую вкладку
        var me = this,
            tab = me.getActiveTab(),
            newTabName = ''
            ;

        // если задан праметр "выбираемая вкладка"
        if ( me.tabToSet ) {

            // выбираем заданное имя вкладки
            newTabName = me.tabToSet;

        } else {

            // если есть правильная вкладка
            if ( tab && tab.path )
                newTabName = tab.path.substr( me.path.length+1 );

        }

        return newTabName;

    },

    setTabByName: function( newTabName ){

        var me = this,
            tab;

        // выбрать вкладку
        tab = processManager.getProcess( me.path+'.'+newTabName );
        if ( !tab ) {
            me.tabToSelect = newTabName;
            me.tabToSet = newTabName;
            return false;
        }

        // установить её как активную
        me.useHistory = false;
        me.setActiveTab( tab );
        me.useHistory = true;

        // сброс параметра для выбора вкладки
        me.tabToSelect = '';

        return true;

    },

    /**
     * Отдает имя вкладки запланированной для установки
     * @return {String}
     */
    getTabNameToSet: function() {
        var me = this;
        return me.tabToSelect || me.tabToSet || '';
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
