/**
 * Объект для работы с дизайнерским режимом
 */

var designObj = {

    // окно дизайнерского режима
    designInterfaceFrame: null,

    // окно дизайнерского режима
    designInterfaceWindow: null,

    // id текущего раздела
    sectionId: 0,

    /**
     * Инициализация
     */
    init: function(){

        var me = this;

        // фрейм дизайнерских параметров
        me.designInterfaceFrame = window.top.document.getElementById( 'skDesignEditorFrame' );
        if ( !me.designInterfaceFrame )
            return false;

        me.designInterfaceWindow = me.designInterfaceFrame.contentWindow;

        me.sectionId = $('body').attr('sectionid');
        if ( !me.sectionId )
            alert( 'Не найден id раздела для текущей страницы.' );

        return me.designInterfaceWindow;

    },

    sendCSSParams: function( items ) {

        this.designInterfaceFrame.contentWindow.processManager.fireEvent('save_css_params', items);
//        ..;

    },

    // счетчик уникальных идентификаторов
    iUniqueId: 0,

    /**
     * Выдает уникальный ID
     * @return {Number}
     */
    getUniqueId: function(){
        return ++this.iUniqueId;
    },

    /**
     * Собирает элементы меню для различных блоков
     */
    collectMenuItems: function( data ) {

        var me = this;
        var menuList = [];
        var layoutData = me.getHTMLElements( data.menu );
        var modulesData = data.modules;

        // собираем меню для всех зон
        $('[sktag], [sklayout], [sklabel], [skeditor]').each(function(){

            var uniqueId = me.getUniqueId();

            $(this).data('elID',uniqueId);

            var liSet = '';

            // добавление элементов css ссылок
            liSet += me.addMenu4Tag( this, layoutData );

            // добавление элементов наборов модулей
            liSet += me.addMenu4Layout( this );

            // добавление редакторов
            liSet += me.addMenu4Editor( this );

            // добавление элементов конечных модулей
            liSet += me.addMenu4Module( this, modulesData );

            // занесение в общий массив
            if ( liSet )
                menuList[uniqueId] = liSet;

        });

        return menuList;

    },

    /**
     * Отдает набор li элементов для заданного элемента dom
     * @param self
     * @param layoutData
     * @return {String}
     */
    addMenu4Tag: function( self, layoutData ) {

        var liSet = '';

        var elemName = $(self).attr('sktag');

        // если есть данные о меню
        if ( elemName && layoutData[elemName] )
            liSet = layoutData[elemName];

        // родительски меню
        $(self).parents('[sktag]').each(function(){
            var parentElemName = $(this).attr('sktag');
            if ( layoutData[parentElemName] )
                liSet += layoutData[parentElemName];
        });

        return liSet;

    },

    /**
     * Отдает набор li элементов для слоев
     * @param self
     * @return {String}
     */
    addMenu4Layout: function( self ) {

        var liSet = '';
        var layout;

        if ( $(self).is('[sklayout]') )
            layout = $(self);
        else {
            layout = $(self).parents('[sklayout]:first');
            if ( !layout.length ){
                layout = null;}
        }

        if ( layout ) {
            liSet += '<li class="separator"></li>';
            liSet += '<li layoutName="'+layout.attr('sklayout')+'">Модули</li>';
        }

        return liSet;

    },


    /**
     * Добавление редакторов
     * @param self
     */
    addMenu4Editor: function( self ) {

        var liSet = '';
        var layout;

        if ( $(self).is('[skeditor]') )
            layout = $(self);

        if ( layout ) {
            liSet += '<li class="separator"></li>';
            liSet += '<li editorId="'+layout.attr('skeditor')+'"';
            liSet += '>Редактировать</li>';
        }

        return liSet;

    },

    /**
     * Отдает набор li элементов для конкретного модуля
     * @param self
     * @param modulesData
     * @return {String}
     */
    addMenu4Module: function( self, modulesData ) {

        var liSet = '';
        var label;

        if ( $(self).is('[sklabel]') ) {
            label = $(self);
        } else {
            label = $(self).parents('[sklabel]:first');
            if ( !label.length ){
                label = null;
            }
        }

        if ( label ) {
            var name = modulesData[label.attr('sklabel')];
            if ( name ) {
                liSet += '<li class="separator"></li>';
                liSet += '<li labelName="'+label.attr('sklabel')+'">Управление: '+name+'</li>';
            }
        }

        return liSet;

    },

    /**
     Создает набор html элементв для контекстного меню
     * @param {Object} data
     * @return {Object}
     */
    getHTMLElements: function( data ) {

        var outData = {};
        var me = this;

        if ( typeof data !== 'object' )
            return outData;

        var key;

        // перебор данных
        for ( key in data ) {
            outData[key] = me.getLiElements(data[key]);
        }

        return outData;

    },

    /**
     * Создает html текст элементв li для контекстного меню
     * @param {Object} item
     * @return {String}
     */
    getLiElements: function( item ) {

        var me = this;

        if ( (typeof item !== 'object') )
            return '';

        var sub,
            subKey,
            subList,
            id = item.id,
            title = item.title || id
            ;

        if ( item.items ) {

            subList = [];

            // перебор вложенных данных
            for ( subKey in item.items ) {
                subList.push(me.getLiElements(item.items[subKey]));
            }

            sub = '<ul>'+subList.join('')+'</ul>';
        } else {
            sub = '';
        }

        return '<li groupId="'+id+'">'+title+sub+'</li>';

    },

    /**
     * Добавление элементов меню к html коду
     * @param data
     */
    addMenuItemsToBody: function( data ) {

        var me = this;

        // собираем элементы меню для различных блоков
        var menuList = me.collectMenuItems( data );

        var menuId;
        var menuHTML = '';
        for ( menuId in menuList ) {
            menuHTML += '<ul class="jeegoocontext cm_default" id="cm_'+menuId+'">'+menuList[menuId]+'</ul>';
        }
        $('body').append( menuHTML );

        // добавить событий к элементтам с тегами
        me.addTagEvents();

    },

    /**
     * Добавление событий к элементтам с тегами
     */
    addTagEvents: function() {

        var me = this;

        // добавить события
        $('[sktag], [sklayout], [sklabel], [skeditor]').each(function(){

            var elementID = $(this).data('elID');

            if ( !elementID )
                console.log($(this));

            //noinspection JSUnresolvedFunction
            $(this).jeegoocontext('cm_'+elementID, {
                widthOverflowOffset: 0,
                heightOverflowOffset: 1,
                submenuLeftOffset: -4,
                submenuTopOffset: -5,
                onSelect: me.onMenuItemSelect
            });

        });

    },

    /**
     * При выбор выпадающего пункта меню
     */
    onMenuItemSelect: function(){

        var groupId = $(this).attr('groupId');
        var layoutName = $(this).attr('layoutName');
        var labelName = $(this).attr('labelName');
        var editorId = $(this).attr('editorId');

        if ( groupId ) {

            //noinspection JSUnresolvedFunction
            designObj.designInterfaceWindow.firePanelEvent( 'select_group', groupId );

        } else if ( layoutName ) {

            //noinspection JSUnresolvedFunction
            designObj.designInterfaceWindow.firePanelEvent( 'select_zone', layoutName );

        } else if ( labelName ) {

            //noinspection JSUnresolvedFunction
            designObj.designInterfaceWindow.firePanelEvent( 'select_label', labelName, designObj.sectionId );

        } else if ( editorId ) {

            //noinspection JSUnresolvedFunction
            designObj.designInterfaceWindow.firePanelEvent( 'select_editor', editorId, designObj.sectionId );

        } else {
            return false;
        }

        designObj.showDesignPanel();

    },


    /**
     * Открытвает дизайнерскую панель
     */
    showDesignPanel: function() {

        if ( window.top.openPanel )
            window.top.openPanel();

    }

};
