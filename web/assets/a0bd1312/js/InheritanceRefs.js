Ext.define('Ext.Design.InheritanceRefs', {

    extend: 'Ext.panel.Panel',
    //title: 'Наследование',
    items: [],
    region: 'center',
    exceptions: [],
    path: null,

    initComponent: function() {

        var me = this;

        this.store = Ext.create('Ext.data.ArrayStore', {
            fields: [
                {name: 'ancestor_title'},
                {name: 'descendant_title'},
                {name: 'link_ids'}
            ],
            sorters: [{
                property: 'basic',
                direction: 'ASC'
            }],
            data: this.exceptions || []
        });

        var grid = Ext.create('Ext.grid.Panel', {
            store: this.store,
            columns: [
                {
                    text: 'Источник (исходный параметр)',
                    flex: 1,
                    menuDisabled: true,
                    sortable: true,
                    dataIndex: 'basic'
                },
                {
                    text: 'Цель (зависимый параметр)',
                    flex: 1,
                    sortable: true,
                    menuDisabled: true,
                    dataIndex: 'extend'
                },
                {
                    xtype: 'actioncolumn',
                    sortable: true,
                    menuDisabled: true,
                    width: 30,
                    align: 'center',
                    dataIndex: 'active',

                    getClass: function(value, meta, rec) {

                        var me = this;

                        if ( parseInt(rec.get('active')) ) {
                            me.tooltip = 'Деактивировать связь';
                            return 'icon-visible'; // icon-link
                        } else {
                            me.tooltip = 'Активировать связь';
                            return 'icon-hidden'; //icon-linkbreak
                        }
                    },
                    handler: function(grid, rowIndex) {

                        var rec = grid.getStore().getAt(rowIndex);

                        processManager.setData(
                            me.path,{
                                cmd: 'setLinkActive',
                                id: rec.data.id,
                                active: parseInt(rec.data.active) ? 0 : 1
                            }
                        );

                        processManager.postData();

                    }
                }
            ],
            height: '100%',
            width: '100%'
        });

        this.items = [ grid ];

        this.callParent();
    },

    loadData: function( items ) {
        var me = this;
        processManager.getProcess( me.path).setLoading( true );
        this.store.loadData( items );
    }
});