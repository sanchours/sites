/**
 * ComboBox с возможностью выбора нескольких элементов
 */
Ext.define('Ext.sk.field.MultiSelect', {
    alias: ['widget.multiselectfield'],
    extend: 'Ext.form.field.ComboBox',
    mode: 'local',
    triggerAction: 'all',
    forceSelection: false,
    allowBlank: true,
    editable: false,
    displayField: 'title',
    valueField:'type_id',
    queryMode: 'local',

    multiSelect: true,

    store: null,

    // Элементы, запрещенные к выбору
    disabledVariants: [],

    listeners: {
        beforeselect: function (sm, record) {

            // Данный элемент запрещен к выбору
            if ( this.disabledVariants.indexOf(record.get('v')) != -1 )
                return false;
        }
    },

    initComponent: function() {
        var me = this;

        me.callParent();
    },

    getValue: function() {
        return ( ( this.value instanceof Array ) ? this.value.join ( ',' ) : this.value );
    }

});