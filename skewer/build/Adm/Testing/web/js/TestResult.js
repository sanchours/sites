Ext.define('Ext.Adm.TestResult', {

    extend: 'Ext.tab.Panel',
    height: 800,
    items: [],
    initComponent: function () {

        var src = this.value ? this.value.src : '';

        this.items = [{
            layout: 'fit',
            title: 'Результат последнего запуска',
            items: {
                xtype: 'component',
                autoEl: {
                    src: src,
                    tag: 'iframe'
                }
            }
        }];
        this.callParent(arguments);
    },
    renderTo: Ext.getBody(),
});

Ext.create('Ext.Adm.TestResult');