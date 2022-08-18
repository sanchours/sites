var picDDGroup;
/**
 * Библиотека для отображения списка фотографий галереи
 */

Ext.define('Ext.Adm.PhotoAlbumList',{

    extend: 'Ext.panel.Panel',

    height: '100%',
    width: '100%',
    border: 0,
    autoScroll : true,
    addText: '',

    picDDGroup: null,
    viewPanel: null,
    items: [],

    initComponent: function() {

        var me = this;

        me.items = [
            me.viewPanel = Ext.create('Ext.Adm.PhotoAlbumListView',{
                path: me.path,
                lang: me.lang
            })
        ];

        me.callParent();

        processManager.addEventListener( 'gallery_sort',this.Builder.path, this.sortImages, this )

    },

    sortImages: function( ) {

        if ( !this.picDDGroup.curPack.itemId )
            return;

        processManager.setData(this.path, {
            cmd: 'SortAlbums',
            itemId: this.picDDGroup.curPack.itemId,
            targetId: this.picDDGroup.curPack.targetId,
            orderType: this.picDDGroup.curPack.orderType
        });


    },

    execute: function( data, cmd ) {

        var me = this;

        switch ( cmd ) {

            case 'show_albums_list':

                // данные даны для примера. их нужно взять из пришедшего массива data
                me.viewPanel.getStore().loadData(data['albums'] || []);

                me.viewPanel.clickAction = data['clickAction'];

                window.setTimeout(function(){
                    me.picDDGroup = Ext.create('Ext.Adm.PhotoSorter', {
                        container : me.viewPanel.id,
                        handles : true,
                        horizontal : true,
                        dragGroups : ['picDDGroup']
                    });
                }, 500);

                break;

            case 'del_selected':

                var delList = me.viewPanel.getSelectedIdList();

                if ( !delList.length ) {
                    sk.error( me.lang.galleryNoItems );
                    break;
                }

                var text;
                if ( delList.length === 1 ) {
                    text = me.lang.galleryDeleteAlbum;
                } else {
                    text = me.lang.galleryDeleteAlbums + ' ('+delList.length + me.lang.galleryDeleteMeasure + ')?';
                }

                Ext.MessageBox.confirm(me.lang.galleryDeleteConfirm, text, function(res){
                    if ( res !== 'yes' ) return;

                    processManager.sendDataFromMainContainer(me, {
                        cmd: 'groupAlbumDel',
                        delItems: delList
                    });

                });


                break;

        }

    }


});
