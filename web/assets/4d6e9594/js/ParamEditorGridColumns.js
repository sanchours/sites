/**
 * Продублирован код Ext.grid.property.Grid
 * Внесены изменения по составу полей
 */

Ext.define('Ext.Design.ParamEditorGridColumns', {

    extend: 'Ext.grid.header.Container',

    nameWidth: 115,

    // private - strings used for locale support
    nameText : designLang.paramsNameTitle,
    valueText : designLang.paramsValueTitle,
    dateFormat : 'dd.mm.yy',
    trueText: 'true',
    falseText: 'false',

    // private
    nameColumnCls: Ext.baseCSSPrefix + 'grid-property-name',

    /**
     * Creates new HeaderContainer.
     */
    constructor : function(grid, store) {
        var me = this;

        me.grid = grid;
        me.store = store;
        me.callParent([{
            items: [{
                width: 70,
                menuDisabled :true,
                itemId: 'grid-ac',
                xtype: 'actioncolumn',
                align: 'center',
                items: [{
                    getClass: function() {
                        return 'icon-reload';
                    },
                    handler: function(grid, rowIndex) {
                        var panel = this.up('gridpanel');
                        var rec = panel.store.getAt(rowIndex);
                        if ( panel.onRevert )
                            panel.onRevert( rec.data );
                        return false;
                    }
                }, {
                    getClass: function () {
                        var panel = this.up('gridpanel');
                        if (panel.canDelete)
                            return 'icon-delete';
                        else
                            return '';
                    },
                    handler: function (grid, rowIndex) {
                        var panel = this.up('gridpanel');

                        if (!panel.canDelete)
                            return false;

                        var rec = panel.store.getAt(rowIndex);
                        if (panel.onRemove)
                            panel.onRemove(rec.data);
                        return false;
                    }
                },{
                        getClass: function(value, meta, rec) {
                            var panel = this.up('gridpanel');
                            var name = rec.data.name;
                            var active = panel.actives[name];
                            if (active !== null) {

                                if (active === '1') {
                                    return 'icon-link';
                                }
                                else if (active === '0') {
                                    return 'icon-linkbreak';
                                }
                            }

                        },
                        handler: function(grid, rowIndex) {

                            var panel = this.up('gridpanel');
                            var rec = panel.store.getAt(rowIndex);
                            var id = rec.data.name;
                            var active = parseInt(panel.actives[id]);

                            if (isNaN(active)) {
                                return false;
                            }

                            var rootCont = processManager.getMainContainer(grid);

                            var search_field = document.getElementById('search_field').getElementsByTagName('input')[0];

                            processManager.setData(
                                rootCont.path,
                                {
                                    cmd: 'activeLink', 
                                    active: active ? 0 : 1,
                                    id: id,
                                    search_text: search_field.value
                                }
                            );
                            processManager.postData();
                        }
                    }

                ]
            }, {
                header: me.nameText,
                width: grid.nameColumnWidth || me.nameWidth,
                sortable: true,
                dataIndex: grid.nameField,
                renderer: Ext.Function.bind(me.renderProp, me),
                itemId: grid.nameField,
                menuDisabled :true,
                tdCls: me.nameColumnCls,
                flex: 1,
            }, {
                header: me.valueText,
                renderer: Ext.Function.bind(me.renderCell, me),
                getEditor: Ext.Function.bind(me.getCellEditor, me),
                flex: 1,
                fixed: true,
                dataIndex: grid.valueField,
                itemId: grid.valueField,
                menuDisabled: true
            }]
        }]);
    },

    getCellEditor: function(record){
        var panel = this.up('gridpanel');
        // блокируем редактирование если связь активна
        if(panel.actives[record.data.name]!=='1')
            return this.grid.getCellEditor(record, this);
        else
            return null;
    },

    // private
    // Render a property name cell
    renderProp : function(v) {
        return this.getPropertyName(v);
    },

    // private
    // Render a property value cell
    renderCell : function(val, meta, rec) {
        var me = this,
            renderer = me.grid.customRenderers[rec.get(me.grid.nameField)],
            result = val;

        if (renderer) {
            return renderer.apply(me, arguments);
        }
        if (Ext.isDate(val)) {
            result = me.renderDate(val);
        } else if (Ext.isBoolean(val)) {
            result = me.renderBool(val);
        }
        return Ext.util.Format.htmlEncode(result);
    },

    // private
    renderDate : Ext.util.Format.date,

    // private
    renderBool : function(bVal) {
        return this[bVal ? 'trueText' : 'falseText'];
    },

    // private
    // Renders custom property names instead of raw names if defined in the Grid
    getPropertyName : function(name) {
        var pn = this.grid.propertyNames;
        return pn && pn[name] ? pn[name] : name;
    }

});
