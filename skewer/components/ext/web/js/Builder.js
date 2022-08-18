/**
 * Класс для автопостроения интерфейсов
 *
 * При отправке данных в посылку включаются помимо данных переменные:
 *  cmd - команда для выполнения
 *  data - передаваемые данные
 *  from - описание интерфейса, из которого пришла посылка
 *      list - из списка
 *      form - из формы редактирования
 *      field - из списка при редактировании поля (edit on place)
 *
 */
Ext.define('Ext.Builder.Builder',{

    extend: 'Ext.panel.Panel',
    region: 'center',

    ifaceData: {},
    serviceData: {},
    dirName: '/skewer/build/libs/ExtBuilder',

    preventHeader: true,

    nowComponent: null,
    nowComponentName: '',

    title: '-',
    actionText: '', // текст, выводимый в режиме allowDo кнопок
    path: '',
    width: '100%',
    closable: false,
    autoScroll: true,
    layout: 'fit',
    items: [],
    initOnActivate: true,

    // флаг выполнения метода execute
    executed: false,

    /**
     * Инициализация
     */
    initComponent: function(){

        var me = this;
        var data = me.ifaceData;

        // уникальный класс для контейнера
        me.cls = 'sk-tab-'+me.className;
        // уникальный класс для вкладки-заголовка
        me.tabConfig = {
            cls: 'sk-tabHeader-'+me.className
        };

        // набор инициализационных данных
        me.serviceData = data['serviceData'] || {};

        if (me.serviceData.closable){
            me.closable = true;
        }

        // заголовок вкладки
        if ( data['componentTitle'] === null)
            me.title = '';
        else
            me.title = data['componentTitle'] || me.path;

        // событие при активации вкладки
        me.on( 'activate', me.onActivate );

        // генерация объекта
        me.callParent();

    },

    /**
     * Выполнение пришедших команд
     */
    execute: function( data, cmd ) {

        var me = this;
        me.executed = true;

        if ( !data ) {
            sk.error('ExtBuilder. Wrong input.');
            return false;
        }

        if ( data['error'] ) {
            sk.error(data['error']);
        }

        // дополнительные библиотеки: заданы - прописать пути загрузки
        var extModuleName;
        if ( typeof data['subLibs'] == 'object' )
            for ( var subLibName in data['subLibs'] ) {
            if ( !data['subLibs'].hasOwnProperty(subLibName) ) continue;
            if ( !data['subLibs'][subLibName] ) continue;
                extModuleName = 'Ext.Builder.'+data['subLibs'][subLibName];
                Ext.Loader.setPath(extModuleName, this.dirName+'/js/'+data['subLibs'][subLibName]+'.js');
            }

        // переменные для работы
        var component = me.nowComponent;

        // сервисные данные
        if ( data['serviceData'] )
            me.serviceData = data['serviceData'];

        var componentName;
        // если родной компонент построителя
        if ( data['extComponent'] ) {
            componentName = data['extComponent'];
            extModuleName = 'Ext.Builder.'+componentName;
        }
        // иначе инициализировать компонент слоя, если есть имя модуля
        else if ( data['componentName'] ) {
            extModuleName = 'Ext.'+this.layerName+'.'+data['componentName'];
            componentName = extModuleName;
        }

        else if ( data['skipInit'] ) {
            me.initOnActivate = false;
            return false;
        }

        // иначе, если стоит флаг загрузки при активации
        else if ( data['initTabFlag'] ) {

            // иначе ответ пришел без определения нового интерфейса

            // если вкладка выбрана
            if ( !me.isVisible() ) {
                // поставить галку обновления при активации
                this.initOnActivate = true;

                if ( me.cont ) {
                    me.removeAll();
                    me.cont.destroy();
                    me.cont = null;
                }


            }
            me.setLoading(false);
            return false;
        } else {
            me.setLoading(false);
            component.execute( data, data['cmd'] || '' );
            return false;
        }

        if ( !me.cont ) {

            me.cont = Ext.create('ExtBuilderContainer',{
                title: this.title,
                path: this.path
            });
            me.add( me.cont );

        }

        var cont = me.cont;

        // заголовок вкладки
        if ( data['panelTitle'] === null)
            cont.setTitle( '' );
        else
            cont.setTitle( data['panelTitle'] || this.path );

        var doReload = !data['doNotReload'];

        // проверить необходимость переинициализации
        if ( me.nowComponentName != componentName || doReload ) {

            // удалить
            cont.removeAll();

            // создать основной компонент
            var props = {
                ifaceData: data,
                border: 0,
                layerName: me.layerName,
                serviceData: data['serviceData'],
                Builder: this
            };

            if ( data['columnsModel'] )
                props.columns = data['columnsModel'];

            // дополнительные параметры
            Ext.merge( props, data['init'] );

            var init = data['init'] || [];
            var lang = init['lang'] || [];

            component = Ext.create(extModuleName, props);
            me.nowComponent = component;
            me.nowComponentName = componentName;

            // добавление кнопок
            cont.resetDocked( data['dockedItems'], lang );

            if ( data['addText'] ) {

                cont.add( {
                    xtype: 'panel',
                    border: 0,
                    width: '100%',
                    items: {
                        border: 0,
                        padding: 5,
                        html: data['addText']
                    }
                } );

                // вставить разделитель, если он не запрещен
                if ( !component.dropPanelDelimiter ) {
                    cont.add({
                        height: 1,
                        width: '100%'
                    });
                }

            }

            cont.add( component );

        }

        // проверка наличия компонента
        if ( !component ) {
            sk.error('ExtBuilder. No component.');
            return false;
        }

        // сообщения
        var item, itemId;
        if ( data['pageMessages'] ) {
            for ( itemId in data['pageMessages'] ) {
                if ( !data['pageMessages'].hasOwnProperty(itemId) ) continue;
                item = data['pageMessages'][itemId];
                sk.message( item[0], item[1], '', item[2] );
            }
        }

        // сообщения об ошибках
        if ( data['pageErrors'] ) {
            for ( itemId in data['pageErrors'] ) {
                if ( !data['pageErrors'].hasOwnProperty(itemId) ) continue;
                item = data['pageErrors'][itemId];
                sk.error( item[0], item[1], item[2] );
            }
        }

        // выполнить
        data.path = me.path;
        component.execute( data, data['cmd'] || '' );
        me.setLoading(false);

        return true;

    },

    /**
     * Вызывается после установки процесса
     */
    afterProcessSet: function() {
        var me = this;
        if ( me.nowComponent && me.nowComponent['afterProcessSet'] )
            me.nowComponent['afterProcessSet']( arguments );
    },

    onActivate: function() {
        var me = this;

        // если не надо инициализировать при активации - выйти
        if ( !me.initOnActivate )
            return false;

        // вкладка для открытия
        var tabToSelect = processManager.getEventValue('tab_to_select');

        // имя текущей вкладки
        var thisTabName = processManager.getModuleAlias(me.path);

        // если нужно открыть вкладку и не текущую - выйти
        if ( tabToSelect && tabToSelect!==thisTabName )
            return false;

        // убрать флаг инициализации при активации
        me.initOnActivate = false;

        // переинициализировать модуль
        me.reInitModule();

        return true;
    },

    /**
     * Вызвать инициализацию первичного интерфейса модуля
     */
    reInitModule: function() {
        var me = this;
        me.setLoading(true);

        var data = { cmd: 'init' };

        var init_param = processManager.getEventValue( 'get_module_init_param' );
        if ( init_param )
            data.init_param = init_param;

        processManager.setData(me.path,Ext.merge(data, me.serviceData));
        me.setLoading(true);
        processManager.postData();
    }

});

Ext.define('ExtBuilderContainer',{

    extend: 'Ext.panel.Panel',
    layout: {
        type: 'vbox',
        align: 'center'
    },
    border: true,
    path: '',

    /**
     * Переустановить набор кнопок для состояния
     * @param newItems
     * @param lang
     */
    resetDocked: function( newItems, lang ) {

        var dItem;
        var me = this;
        var dItems = me.getDockedItems('toolbar');

        for ( dItem in dItems ) {
            if ( !dItems.hasOwnProperty(dItem) ) continue;
            me.removeDocked( dItems[dItem] );
        }

        // добавление элементов
        newItems = newItems || [];
        for ( var sDock in newItems ) {

            if ( !newItems.hasOwnProperty(sDock) ) continue;

            var itemsToAdd = [];
            for ( dItem in newItems[sDock] ) {
                if ( !newItems[sDock].hasOwnProperty(dItem) ) continue;
                var item = newItems[sDock][dItem];
                item.cls = item.addActionParamJs
                    ? 'sk-tab-btn sk-tab-btn-' + item.action + "-" + item.addActionParamJs
                    : 'sk-tab-btn sk-tab-btn-' + item.action
                ;
                item.handler = me.dockedItemHandler;

                if ( item['userFile'] ) {
                    var builder = processManager.getMainContainer( this );
                    var btnLayer = item['layer'] ? item['layer'] : builder.layerName;
                    item = Ext.create( 'Ext.'+btnLayer+'.'+item['userFile'], {
                        initData: item,
                        lang: lang
                    } );
                }

                // модифицированное значение вернуть назад
                if ( item === '-&gt;' )
                    item = '->';

                // добавить элемент к набору
                itemsToAdd.push( item );
            }

            // добавление набора кнопок для состояния
            me.addDocked( {
                xtype: 'toolbar',
                dock: sDock,
                items: itemsToAdd
            } );

        }

        var dockedItems = me.getDockedItems();
        Ext.each(dockedItems, function(dockedItem){
            Ext.each(dockedItem.items.items, function(item){
                if (item.multiple){
                    item.setDisabled(true)
                }
            })
        });

        me.doLayout();

    },

    /**
     * Обработчик для управляющих элементов
     */
    dockedItemHandler: function(){

        var self = this,
            state = self.state || '',
            action = self.action || '',
            confirm = self['confirmText'] || '',
            skipData = self['skipData'] || false,
            container = self.up('panel').up('panel'),
            serviceData = container.serviceData || {},
            addParams = self.addParams||{},
            dataPack = {}
        ;

        if ( action ) {

            // команда
            dataPack.cmd = action;

            // данные от компонента
            var component = container.nowComponent,
                componentData = {};
            if ( component && !skipData ) {
                if ( component.getData )
                    componentData = component.getData( this );
                if ( component.getType )
                    dataPack.from = component.getType();
            }

            // функция отправки данных
            function postData(){
                Ext.merge( dataPack, serviceData, addParams, componentData );
                processManager.setData(container.path, dataPack);
                container.setLoading(true);
                if ( self.doNotUseTimeout )
                    processManager.doNotUseTimeout();
                processManager.postData();
            }


            switch (state) {
                /* Спрашиваем разрешение на удаление элемента */
                case 'delete':
                    var row_text = '';

                    var item = [];
                    if ( componentData.data.items ) {
                        if ( (typeof(componentData.data.items) === 'object')&& componentData.data.items.length == 1 )
                            item = componentData.data.items[0];
                    } else {
                        item = componentData.data;
                    }
                    
                    if ( item )
                        row_text = item['title'] || item['name'] || '';

                    if ( !row_text )
                        row_text = sk.dict('delRowNoName');

                    var sHeader = sk.dict('delRowHeader');
                    if (container.nowComponent.multiSelect){
                        if (container.nowComponent.getView().getSelectionModel().getSelection().length > 1){
                            sHeader = sk.dict('delRowsHeader');
                            row_text = sk.dict('delRowsNoName');
                        }
                    }
                    var sText = sk.dict('delRow').replace('{0}', row_text);

                    sk.confirmWindow(sHeader, sText, function(res){
                        if ( res !== 'yes' ) return;
                        postData();
                    }, this );

                    break;
                /* Спрашиваем разрешение на выполнения действия (текст в self.actionText) */
                case 'allow_do':

                    sk.confirmWindow(sk.dict('allowDoHeader'),self.actionText, function(res){
                        if ( res !== 'yes' ) return;
                        postData();
                    }, this );

                    break;

                default:

                    if ( confirm ) {
                        sk.confirmWindow(sk.dict('allowDoHeader'),confirm, function(res){
                            if ( res !== 'yes' ) return;
                            postData();
                        }, this );
                    } else {
                        postData();
                    }

                    break;

            }// state switch

        }

        // иначе попробовать вызвать состояние компонента
        else if ( state ) {

            // данные
            var data = {
                path: container.path,
                serviceData: serviceData,
                addParams: Ext.merge(dataPack, serviceData, addParams)
            };

            // выполнить состояние компонента
            container.nowComponent.execute( data, state );

        }


    }

});
