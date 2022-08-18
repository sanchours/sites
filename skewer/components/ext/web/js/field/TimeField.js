Ext.define('Ext.sk.field.TimeField',{
    override: 'Ext.form.field.Time',

    getSubmitValue: function() {
        var me = this,
            format = me.submitFormat || me.format,
            value = me.getValue();

        return value ? Ext.Date.format(value, format) : "";
    }
});