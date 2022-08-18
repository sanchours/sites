Ext.define('Ext.Design.ParamEditorGrid', {

    extend: 'Ext.grid.Panel',

    alias: 'widget.propertygrid',

    alternateClassName: 'Ext.grid.PropertyGrid',
    dockedItems: [{
        xtype: 'toolbar',
        dock: 'top',
        items: [{
            id: 'search_field',
            itemId: 'search_panel_field',
            xtype: 'textfield',
            displayField: 'title',
            typeAhead: false,
            hideLabel: true,
            hideTrigger:true,
            width: 260,
            typeAheadDelay: 0,
            emptyText: 'Поиск параметров',
            anchor: '100%',
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        processManager.fireEvent( 'list_filter', this.getValue() );
                    }
                }
            }
        }]
    }],
    uses: [
        'Ext.grid.plugin.CellEditing',
        'Ext.grid.property.Store',
        'Ext.grid.property.HeaderContainer',
        'Ext.XTemplate',
        'Ext.grid.CellEditor',
        'Ext.form.field.Date',
        'Ext.form.field.Text',
        'Ext.form.field.Number'
    ],

    /**
     * @cfg {String} valueField
     * Optional. The name of the field from the property store to use as the value field name. Defaults to <code>'value'</code>
     * This may be useful if you do not configure the property Grid from an object, but use your own store configuration.
     */
    valueField: 'value',

    /**
     * @cfg {String} nameField
     * Optional. The name of the field from the property store to use as the property field name. Defaults to <code>'name'</code>
     * This may be useful if you do not configure the property Grid from an object, but use your own store configuration.
     */
    nameField: 'name',

    /**
     * @cfg {Number} nameColumnWidth
     * Optional. Specify the width for the name column. The value column will take any remaining space. Defaults to <tt>115</tt>.
     */

    // private config overrides
    enableColumnMove: false,
    columnLines: true,
    stripeRows: false,
    trackMouseOver: false,
    clicksToEdit: 1,
    enableHdMenu: false,

    // private
    initComponent : function(){
        var me = this;

        me.addCls(Ext.baseCSSPrefix + 'property-grid');
        me.plugins = me.plugins || [];

        // Enable cell editing. Inject a custom startEdit which always edits column 1 regardless of which column was clicked.
        me.plugins.push(Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: me.clicksToEdit,

            // Inject a startEdit which always edits the value column
            startEdit: function(record,act) {
                if ( act.is('actioncolumn') )
                    return false;
                // Maintainer: Do not change this 'this' to 'me'! It is the CellEditing object's own scope.
                return this.self.prototype.startEdit.call(this, record, me.headerCt.child('#' + me.valueField));
            }
        }));

        me.selModel = {
            selType: 'cellmodel',
            onCellSelect: function(position) {
                if (position.column != 1) {
                    position.column = 1;
                }
                return this.self.prototype.onCellSelect.call(this, position);
            }
        };
        me.customRenderers = me.customRenderers || {};
        me.customEditors = me.customEditors || {};

        // Create a property.Store from the source object unless configured with a store
        if (!me.store) {
            //noinspection JSCheckFunctionSignatures
            me.propStore = me.store = Ext.create('Ext.grid.property.Store', me, me.source);
        }

        //me.store.sort('name', 'ASC');
        //noinspection JSCheckFunctionSignatures
        me.columns = Ext.create('Ext.Design.ParamEditorGridColumns', me, me.store);

        me.addEvents(
            'beforepropertychange',
            'propertychange'
        );
        me.callParent();

        // Inject a custom implementation of walkCells which only goes up or down
        me.getView().walkCells = this.walkCells;

        // Set up our default editor set for the 4 atomic data types
        me.editors = {
            'date'    : Ext.create('Ext.grid.CellEditor', { field: Ext.create('Ext.form.field.Date',   {selectOnFocus: true})}),
            'string'  : Ext.create('Ext.grid.CellEditor', { field: Ext.create('Ext.form.field.Text',   {selectOnFocus: true})}),
            'number'  : Ext.create('Ext.grid.CellEditor', { field: Ext.create('Ext.form.field.Number', {selectOnFocus: true})}),
            'boolean' : Ext.create('Ext.grid.CellEditor', { field: Ext.create('Ext.form.field.ComboBox', {
                editable: false,
                store: [[ true, me.headerCt.trueText ], [false, me.headerCt.falseText ]]
            })})
        };

        // Track changes to the data so we can fire our events.
        me.store.on('update', me.onUpdate, me);

        processManager.addEventListener( 'list_filter',this.path,this.filterList);
    },

    // private
    onUpdate : function(store, record, operation) {
        var me = this,
            v, oldValue;

        if (operation == Ext.data.Model.EDIT) {
            v = record.get(me.valueField);
            oldValue = record.modified.value;
            if (me.fireEvent('beforepropertychange', me.source, record.getId(), v, oldValue) !== false) {
                if (me.source) {
                    me.source[record.getId()] = v;
                }
                record.commit();
                me.fireEvent('propertychange', me.source, record.getId(), v, oldValue);
            } else {
                record.reject();
            }
        }
    },

    // Custom implementation of walkCells which only goes up and down.
    walkCells: function(pos, direction, e, preventWrap, verifierFn, scope) {
        if (direction == 'left') {
            direction = 'up';
        } else if (direction == 'right') {
            direction = 'down';
        }
        pos = Ext.view.Table.prototype.walkCells.call(this, pos, direction, e, preventWrap, verifierFn, scope);
        if (!pos.column) {
            pos.column = 1;
        }
        return pos;
    },

    // private
    // returns the correct editor type for the property type, or a custom one keyed by the property name
    getCellEditor : function(record) {

        var me = this,
            propName = record.get(me.nameField),
            val = record.get(me.valueField),
            editor = me.customEditors[propName];

        // A custom editor was found. If not already wrapped with a CellEditor, wrap it, and stash it back
        // If it's not even a Field, just a config object, instantiate it before wrapping it.
        if (editor) {
            if (!(editor instanceof Ext.grid.CellEditor)) {
                if (!(editor instanceof Ext.form.field.Base)) {
                    editor = Ext.ComponentManager.create(editor, 'textfield');
                }
                editor = me.customEditors[propName] = Ext.create('Ext.grid.CellEditor', { field: editor });
            }
        } else if (Ext.isDate(val)) {
            editor = me.editors.date;
        } else if (Ext.isNumber(val)) {
            editor = me.editors.number;
        } else if (Ext.isBoolean(val)) {
            editor = me.editors['boolean'];
        } else {
            editor = me.editors.string;
        }

        // Give the editor a unique ID because the CellEditing plugin caches them
        editor.editorId = propName;
        return editor;
    },

    beforeDestroy: function() {
        var me = this;
        me.callParent();
        me.destroyEditors(me.editors);
        me.destroyEditors(me.customEditors);
        delete me.source;
    },

    destroyEditors: function (editors) {
        for (var ed in editors) {
            if (editors.hasOwnProperty(ed)) {
                Ext.destroy(editors[ed]);
            }
        }
    },

    setSource: function(source) {
        this.source = source;
        this.propStore.setSource(source);
    },

    /**
     * Gets the source data object containing the property data.  See {@link #setSource} for details regarding the
     * format of the data object.
     * @return {Object} The data object
     */
    getSource: function() {
        return this.propStore.getSource();
    },

    /**
     * Sets the value of a property.
     * @param {String} prop The name of the property to set
     * @param {Object} value The value to test
     * @param {Boolean} create (Optional) True to create the property if it doesn't already exist. Defaults to <tt>false</tt>.
     */
    setProperty: function(prop, value, create) {
        this.propStore.setValue(prop, value, create);
    },

    /**
     * Removes a property from the grid.
     * @param {String} prop The name of the property to remove
     */
    removeProperty: function(prop) {
        this.propStore.remove(prop);
    },

    /**
     * Выполнение фильтрации для набора полей
     */
    filterList: function( text ) {

        // собрать посылку к серверу
        processManager.setData(this.path,{
            cmd: 'findParam',
            text: text
        });

        this.setLoading(true);

    }

});
