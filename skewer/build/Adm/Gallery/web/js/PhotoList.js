var picDDGroup;
/**
 * Библиотека для отображения списка фотографий галереи
 */

Ext.define('Ext.Adm.PhotoList', {

    extend: 'Ext.panel.Panel',

    height: '100%',
    width: '100%',
    border: 0,
    autoScroll: true,
    addText: '',

    picDDGroup: null,
    viewPanel: null,
    items: [],

    initComponent: function() {

        var me = this;

        me.items = [
            me.viewPanel = Ext.create('Ext.Adm.PhotoListView', {
                path: me.path,
                lang: me.lang
            })
        ];

        me.callParent();

        processManager.addEventListener('gallery_sort', this.Builder.path, this.sortImages, this)

    },

    sortImages: function () {

        if (!this.picDDGroup.curPack.itemId)
            return;

        processManager.setData(this.path, {
            cmd: 'SortImages',
            itemId: this.picDDGroup.curPack.itemId,
            targetId: this.picDDGroup.curPack.targetId,
            orderType: this.picDDGroup.curPack.orderType
        });


    },

    execute: function (data, cmd) {

        var me = this;

        switch (cmd) {

            case 'show_photos_list':

                // данные даны для примера. их нужно взять из пришедшего массива data
                me.viewPanel.getStore().loadData(data['images'] || []);

                me.viewPanel.clickAction = data['clickAction'];

                window.setTimeout(function () {
                    me.picDDGroup = Ext.create('Ext.Adm.PhotoSorter', {
                        container: me.viewPanel.id,
                        handles: true,
                        horizontal: true,
                        dragGroups: ['picDDGroup']
                    });
                }, 500);

                break;

            case 'del_selected':

                var delList = me.viewPanel.getSelectedIdList();

                if (!delList.length) {
                    sk.error( me.lang.galleryNoItems );
                    break;
                }

                var text;

                if (delList.length === 1) {
                    text = me.lang.galleryDelImg;
                } else {
                    text = me.lang.galleryMultiDelImg + ' (' + delList.length + ')?';
                }


                Ext.MessageBox.confirm(me.lang.galleryDeleteConfirm, text, function (res) {
                    if (res !== 'yes') return;

                    processManager.sendDataFromMainContainer(me, {
                        cmd: 'groupDel',
                        delItems: delList
                    });

                });


                break;

            case 'closeWindow':
                window.close();
                break;

        }

    }


});
