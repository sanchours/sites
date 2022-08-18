Ext.define('Ext.Tool.ImportType', {
    extend: 'Ext.form.ComboBox',
    type_id: 9,
    mode: 'local',
    triggerAction: 'all',
    forceSelection: true,
    allowBlank: false,
    editable: false,
    displayField: 'value',
    valueField:'value',
    queryMode: 'local',
    saveKeys: 'ctrl_enter',
    store:  {
        fields: ['value'],
        data: []
    },

    name: 'type',
    fieldLabel: '',
    title: '',
    value: 0,

    initField : function(){
        this.fieldLabel = this.title;
    },

    afterRender: function(){
        this.showFields();
    },

    isDirty : function() {
        var me = this;
        return !me.disabled && !me.isEqualAsString(me.getValue(), me.originalValue);
    },

    select : function( combo, records, eOpts ){

        this.showFields();

    },

    showFields: function(){
        var me = this;

        //Форма
        var form = me.up('fieldset');

        //Поля формы
        var items = form.items;

        Ext.each(items.items, function(item){

            if ( me.value == 1 ){
                if ( item.name == 'source_str' ){
                    item.hide();
                }
                if ( item.name == 'source_file' ){
                    item.show();
                }
            }else{
                if ( item.name == 'source_str' ){
                    item.show();
                }
                if ( item.name == 'source_file' ){
                    item.hide();
                }
            }

        });
    }

});