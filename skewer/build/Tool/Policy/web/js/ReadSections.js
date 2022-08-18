/**
 * Специфическое поле для редактирования доступа к разделам
 */

Ext.define('Ext.Tool.ReadSections',{
    extend: 'Ext.tree.Panel',

    height: '100%',
    width: '100%',
    bodyStyle: 'border: 0',

    ALLOW: 1,
    DENY: -1,
    NONE: 0,

    itemsA: [],
    itemsD: [],

    rootVisible: false,
    rootSection: 0,
    mainSection: 0,
    displayField: 'title',
    hideHeaders: true,
    preventHeader: true,
    columnsModel: [],

    generateColumnsModel: function(){
        var me = this;
        me.columnsModel = [{
            xtype: 'treecolumn',
            text: 'Section',
            width: 400,
            sortable: false,
            dataIndex: 'title',
            renderer: function( value, meta, rec ) {

                // раскраска надписей разделов

                // текущее значение флага разрешения
                var allow = parseInt( rec.get('allow') || rec.get('showAllow') || 0 );

                // запрещено все, кроме разрешенного
                switch ( allow ) {
                    case 1:
                        meta.tdCls = 'policy-tree-row-allow';
                        break;
                    default:
                        meta.tdCls = 'policy-tree-row-deny';
                        break;
                }

                // стандартный класс для разделов
                meta.tdCls += ' tree-row-section';

                return value;
            }
        },{
            xtype: 'actioncolumn',
            width: 68,
            tdCls: 'policy-tree-row-col-hidden',
            items: [{
                tooltip: me.lang.polisyAllow,
                getClass: function() {
                    return 'policy-tree-icon-allow';
                },
                handler: function(grid, rowIndex) {
                    var cont = grid.up('panel');
                    cont.setAllowStatus(rowIndex,cont.ALLOW);
                }
            },{
                tooltip: me.lang.polisyNone,
                getClass: function() {
                    return 'policy-tree-icon-none';
                },
                handler: function(grid, rowIndex) {
                    var cont = grid.up('panel');
                    cont.setAllowStatus(rowIndex,cont.NONE);
                }
            },{
                tooltip: me.lang.polisyDeny,
                getClass: function() {
                    return 'policy-tree-icon-deny';
                },
                handler: function(grid, rowIndex) {
                    var cont = grid.up('panel');
                    cont.setAllowStatus(rowIndex,cont.DENY);
                }
            },{
                tooltip: me.lang.polisyMain,
                getClass: function() {
                    return 'policy-tree-icon-home';
                },
                handler: function(grid, rowIndex) {
                    var cont = grid.up('panel');
                    cont.setMainSection(rowIndex);
                }
            }]
        },{
            width: 30,
            sortable: false,
            dataIndex: 'id'
        },{
            xtype: 'actioncolumn',
            width: 36,
            tdCls: 'tree-row',
            items: [{
                getClass: function(icon,rowIndex,rec) {

                    // для разделов типа "директория"
                    var allow = rec.data['allow'] || '',
                        cls = ''
                        ;
                    switch ( allow ) {
                        case 1:
                            cls = 'policy-tree-icon-allow';
                            break;
                        case -1:
                            cls = 'policy-tree-icon-deny';
                            break;
                        default:
                            cls = 'policy-tree-icon-none';
                            break;
                    }
                    return cls;

                }
            },{
                getClass: function(icon,rowIndex,rec) {

                    var cont = this.up('panel');

                    // для разделов типа "директория"
                    if ( parseInt(rec.get('id')) == cont.mainSection ) {
                        return 'policy-tree-icon-home';
                    } else {
                        return false;
                    }

                }
            }]
        }];
    },

    initComponent: function(){

        var self = this;

        this.generateColumnsModel();

        // стандартный проход автопостроителя перекрывает переменную "columns"
        self.columns = self.columnsModel;

        // хранилище
        this.store = Ext.create('Ext.data.TreeStore', {
            id: processManager.getUniqueId(),
            fields: ['id','title','parent','allow','showAllow'],
            root: {
                id: self.rootSection,
                name: 'Sections',
                expanded: true
            },
            proxy: 'memory'
        });


        this.callParent();

    },

    execute: function( data, cmd ){

        var self = this,
            store = self.getStore(),
            itemsA,
            itemsD,
            node
        ;

        switch(cmd) {

            // первичная загрузка
            default:

                // инициализация переменных
                var rootName = store.getRootNode(),
                    sectionId,
                    key
                ;

                // устновка домашнего раздела
                self.mainSection = data.startSection || 0;

                // разрешенные разделы
                itemsA = data['itemsAllow'] || [];
                // запрещенные разделы
                itemsD = data['itemsDeny'] || [];

                // добавление элементов
                rootName.appendChild(data.items);

                // перебор разрешенных разделов
                for ( key in itemsA ) {
                    sectionId = parseInt(itemsA[key]);
                    node = store.getNodeById(sectionId);
                    if ( !node ) continue;
                    node.data['allow'] = self.ALLOW;
                }

                // перебор разрешенных разделов
                for ( key in itemsD ) {
                    sectionId = parseInt(itemsD[key]);
                    node = store.getNodeById(sectionId);
                    if ( !node ) continue;
                    node.data['allow'] = self.DENY;
                }

                // расставить остальные метки
                self.resetStatusForTree(0);

                self.expandAll();

                break;

            // сохранение
            case 'saveSection':

                // очистка глобальных контенеров
                self.itemsA = [];
                self.itemsD = [];

                // собрать разделы со статусами
                self.collectItemsWithSatus(0);

                // получить данные
                itemsA = self.itemsA;
                itemsD = self.itemsD;

                // собрать посылку
                var pack = Ext.merge(data.addParams||{}, {
                    cmd: 'saveSections',
                    startSection: self.mainSection,
                    itemsAllow: itemsA,
                    itemsDeny: itemsD
                });
                processManager.setData( data.path, pack );

                // отослать
                processManager.postData();

                break;

        }

    },

    findNode: function( sectionId ) {

        var self = this,
            store = self.getStore()
        ;

        // не задана вершина - взять корень
        if ( !sectionId ) sectionId = self.rootSection;

        // запроссить вершину
        if ( sectionId ) {
            return store.getNodeById(parseInt(sectionId));
        } else {
            return store.getRootNode();
        }

    },

    /**
     * Рекурсивно собирает разделы со статусами
     * @param sectionId
     */
    collectItemsWithSatus: function( sectionId ) {

        var self = this,
            node,
            allow,
            childId
        ;

        node = self.findNode( sectionId );
        if ( !node ) return;

        // обойти наследников
        node.eachChild(function( child ){

            // параметры записи
            allow = parseInt(child.get('allow'));
            childId = parseInt(child.get('id'));

            // если статус задан
            if ( allow ) {
                switch ( allow ) {
                    case self.ALLOW:
                        self.itemsA.push(childId);
                        break;
                    case self.DENY:
                        self.itemsD.push(childId);
                        break;
                }
            }

            // запустить рекурсию, если  есть подчиненные записи
            if ( child.hasChildNodes() )
                self.collectItemsWithSatus(childId);

        });

    },

    /**
     * Установить статус для запси
     * @param rowIndex
     * @param status
     */
    setAllowStatus: function(rowIndex,status) {

        var self = this,
            grid = self.down('treeview'),
            rec = grid.getStore().getAt(rowIndex),
            id = parseInt( rec.get('id') ) || 0,
            rebuildId
        ;
        rec.set('allow',status);

        // если заменятся на активный статус
        if ( status ) {
            // перестроение от текущего раздела
            rebuildId = id;
        } else {
            // перестроение от родительского раздела
            rebuildId = parseInt( rec.get('parent') ) || 0;
        }

        // вызвать перестроение
        self.resetStatusForTree( rebuildId );

    },

    /**
     * Помечает раздел как стартовый
     * @param rowIndex
     */
    setMainSection: function( rowIndex ) {

        // инициализация переменных
        var self = this,
            grid = self.down('treeview'),
            rec = grid.getStore().getAt(rowIndex),
            newMainId = parseInt(rec.get('id')) || 0,
            oldMainId = self.mainSection,
            oldNode = self.findNode( oldMainId ),
            newNode
        ;

        // установить стартовый раздел
        self.mainSection = newMainId;

        // убрать метку с главного раздела
        if ( oldNode )
            oldNode.afterEdit();

        // установить метку на новый раздел
        newNode = self.findNode( newMainId );
        newNode.afterEdit();

    },

    /**
     * Расставляет метки для всего дерева
     * @param sectionId с какой вершины провести раскаску
     */
    resetStatusForTree: function( sectionId ) {

        var self = this,
            node, allow
        ;

        node = self.findNode( sectionId );
        if ( !node ) return;

        allow = node.get('allow')||node.get('showAllow');

        // обойти наследников
        node.eachChild(function( child ){
            if ( !child.get('allow') )
                child.set('showAllow', allow );
            if ( child.hasChildNodes() )
                self.resetStatusForTree(child.get('id'));
        });

    }

});
