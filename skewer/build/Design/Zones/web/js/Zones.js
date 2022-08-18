/**
 * Система управления набором вкладок дизайнерского режима
 */
Ext.define('Ext.Design.Zones', {
    extend: 'Ext.panel.Panel',
    padding: 0,
    margin: 0,
    title: designLang.zonesTabTitle,
    region: 'center',
    layout: 'border',

    tplId: 0,
    zoneId: 0,

    initComponent: function(){

        var me = this;

        me.items = [
            Ext.create('Ext.Design.ZoneTemplates',{
                panelType: 'tpl',
                width: '100%',
                margin: '2 0 2 2',
                collapsible: false,
                region: 'west'
            }),
            Ext.create('Ext.Design.ZoneSelector',{
                panelType: 'zone',
                margin: '2 0 2 0',
                collapsible: false,
                region: 'center'
            }),
            Ext.create('Ext.Design.ZoneLabels',{
                panelType: 'label',
                margin: '2 2 2 0',
                width: '100%',
                collapsible: false,
                region: 'east'
            })
        ];

        processManager.addEventListener('urlChange',me.path,me.onUrlChange);

        processManager.addEventListener('select_zone',me.path,me.onExternalZoneSelect);

        me.callParent();

    },

    execute: function( data ){

        var me = this,
            tplList = data['tplList'],
            zoneList = data['zoneList'],
            labelAddList = data['labelAddList'],
            labelList = data['labelList']
        ;

        if ( data.error )
            sk.error( data.error );

        // установить набор шаблонов, если заданы
        if ( tplList !== undefined )
            me.setTplItems( tplList );

        // установить набор зон, если заданы
        if ( zoneList !== undefined ) {
            me.setZoneItems( zoneList );
            me.highlightZone( me.zoneId );
        }

        // установить набор меток, если заданы
        if ( labelList !== undefined )
            me.setLabelItems( labelList );

        // установить набор меток для добавления, если заданы
        if ( labelAddList !== undefined )
            me.setAddLabelItems( labelAddList );

        // подсветка зоны
        if ( data['selectZone'] ) {
            me.zoneId = data['selectZone'];
            me.highlightZone( me.zoneId );
        }

        // подсветка зоны
        if ( data['selectTpl'] ) {
            me.tplId = data['selectTpl'];
            me.highlightTpl( me.tplId );
        }

        // перегрузить фрейм отображения
        if ( data.reload )
            frameApi.reloadDisplayFrame();

        me.setLoading(false);

    },

    /**
     * При смене url страницы просмотра
     * @param newUrl
     */
    onUrlChange: function( newUrl ) {

        var me = this;

        processManager.setData(me.path,{
            cmd: 'reloadTplList',
            showUrl: newUrl
        });

    },

    /**
     * При воборе зоны из внешнего модуля
     * @param zoneName
     */
    onExternalZoneSelect: function( zoneName ) {

        var me = this;

        // выбрать текущую вкладку
        var tabs = me.up('panel');
        tabs.setActiveTab(me);

        processManager.setData(me.path,{
            cmd: 'selectZoneByName',
            zoneName: zoneName,
            showUrl: frameApi.getDisplayFrameUrl()
        });

        me.setLoading(true);

    },

    /**
     * Подсветка зоны в списке
     * @param id
     */
    highlightZone: function( id ) {
        this.getZonePanel().highlightZone( id );
    },

    /**
     * Подсветка шаблона в списке
     * @param id
     */
    highlightTpl: function( id ) {
        this.getTplPanel().highlightTpl( id );
    },

    /**
     * Выбор шалона
     */
    selectTemplate: function( id ) {
        this.tplId = id;
        processManager.setData(this.path,{
            cmd: 'selectTemplate',
            tplId: id
        });
        this.setLoading(true);
        processManager.postData();
    },

    /**
     * Выбор зоны
     */
    selectZone: function( id ) {
        this.zoneId = id;
        processManager.setData(this.path,{
            cmd: 'selectZone',
            tplId: this.tplId,
            zoneId: id
        });
        this.setLoading(true);
        processManager.postData();
    },

    /**
     * Сбросить перекрытие зоны
     */
    revertZone: function( zone ) {

        var me = this;

        Ext.MessageBox.confirm(designLang.zoneDelZoneHeader, designLang.zoneDelZoneText+' "'+zone.title+'"?',function(res){
            if ( res !== 'yes' ) return;

            processManager.setData(me.path,{
                cmd: 'deleteZone',
                tplId: me.tplId,
                zoneId: zone.id,
                showUrl: frameApi.getDisplayFrameUrl()
            });
            me.setLoading(true);
            processManager.postData();

        });

    },

    /**
     * Удаление зоны
     * @param label object
     */
    deleteLabel: function( label ) {

        var me = this;

        Ext.MessageBox.confirm(designLang.zoneDelLabelHeader, designLang.zoneDelLabelText+' "'+label.title+'"?',function(res){
            if ( res !== 'yes' ) return;

            processManager.setData(me.path,{
                cmd: 'deleteLabel',
                labelName: label.name,
                tplId: me.tplId,
                zoneId: me.zoneId
            });
            me.setLoading(true);
            processManager.postData();

        });

    },

    /**
     * Сохоранение набора меток
     */
    saveLabels: function() {

        var me = this,
            items = []
        ;

        // взять набор меток
        me.getLabelPanel().grid.getStore().each(function( row ){
            items.push( row.data.name );
        });

        // отправить в посылке
        processManager.setData(me.path,{
            cmd: 'saveLabels',
            items: items,
            tplId: me.tplId,
            zoneId: me.zoneId
        });
        this.setLoading(true);
        processManager.postData();

    },

    /**
     * Задает значения для панели шаблонов
     * @param items
     */
    setTplItems:function (items) {
        this.getTplPanel().grid.getStore().loadData(items);
    },

    /**
     * Задает значения для панели зон
     * @param items
     */
    setZoneItems:function (items) {
        this.getZonePanel().grid.getStore().loadData(items);
    },

    /**
     * Задает значения для панели меток
     * @param items
     */
    setLabelItems:function (items) {
        this.getLabelPanel().grid.getStore().loadData(items);
    },

    /**
     * Задает значения допустимых для добавления меток
     * @param items
     */
    setAddLabelItems:function (items) {
        this.getLabelPanel().gridAdd.getStore().loadData(items);
    },

    /**
     * Отдает панель по заданному типу
     * @param type
     * @return {Number}
     */
    getPanelByType: function( type ){

        var me = this,
            itemKey,
            item
        ;

        for ( itemKey in me.items.items ) {
            item = me.items.items[itemKey];
            if ( item.panelType === type )
                return item;
        }

        return null;

    },

    /**
     * Отдает панель шаблонов
     */
    getTplPanel: function(){
        return this.getPanelByType('tpl');
    },

    /**
     * Отдает панель зон
     */
    getZonePanel: function(){
        return this.getPanelByType('zone');
    },

    /**
     * Отдает панель меток
     */
    getLabelPanel: function(){
        return this.getPanelByType('label');
    }

});
