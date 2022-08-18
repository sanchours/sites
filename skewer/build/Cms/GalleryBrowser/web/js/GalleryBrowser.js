/**
 * Библиотека для вывода раскладки файловго менеджера
 * Используется во всплывающем окне показа галерейного альбома
 */
Ext.define('Ext.Cms.GalleryBrowser', {
    extend: 'Ext.Viewport',
    title: 'gallery',
    height: '90%',
    width: '90%',
    layout: 'border',
    closeAction: 'hide',
    modal: true,
    componentsInited: false,

    senderData: {},

    defaults: {
        margin: '3 3 3 3'
    },

    defaultSection: 1,

    items: [{
        region: 'center',
        html: 'viewport'
    }],

    initComponent: function() {

        this.callParent();

        // событие при событии выбора раздела во всплывающем окне
        //processManager.addEventListener('tabs_load', this.path, 'onSectionSelect');

        // событие при выборе файла во всплывающем окне
        //processManager.addEventListener('select_file_set', this.path, 'onGallerySelect');

        processManager.addEventListener('request_add_info',this.path, 'showData');

    },

    showData: function( text ){
        sk.error( text );
    },


    execute: function( data, cmd ) {

        switch ( cmd ) {

            case 'findAlbum':

                var AlbumId = this.parseUrl('gal_album_id');
                var GalProfileId = this.parseUrl('gal_profile_id'); // Передать ID Профиля галереи
                var MakeNewAlbum = this.parseUrl('gal_new_album');
                var seoClass = this.parseUrl('seoClass');
                var iEntityId = this.parseUrl('iEntityId');
                var sectionId = this.parseUrl('sectionId');

                if (AlbumId) {
                    processManager.setData(this.path,{
                        cmd: 'showAlbum',
                        gal_album_id: AlbumId,
                        gal_profile_id: GalProfileId,
                        gal_new_album: MakeNewAlbum,
                        seoClass: seoClass,
                        iEntityId: iEntityId,
                        sectionId: sectionId
                    });
                    processManager.postData();
                } else if (sectionId) {
                    processManager.setData(this.path,{
                        cmd: 'showSection',
                        sectionId: sectionId
                    });
                    processManager.postData();
                }

                break;

            case 'showAlbum':

                var AlbumId = parseInt(data['album']);

                if (AlbumId)
                    this.onGallerySelect(AlbumId);

                break;
        }

        if ( data.error )
            sk.error( data.error );
    },

    /**
     * При воборе файла во всплывающем окне
     * @param value
     */
    onGallerySelect: function( value ) {

        if ( !window.top.opener )
            return false;

        // старая админка
        if ( window.top.opener['processManager'] ) {
            window.top.opener['processManager'].fireEvent('set_gallery', {
                ticket: this.parseUrl('ticket'),
                value: value
            });
        }

        // новая админка
        if ( window.top.opener['React'] ) {
            window.top.opener['sk'].setField(
                this.parseUrl('path'),
                this.parseUrl('fieldName'),
                value
            );
        }

        return true;

    },

    parseUrl: function(name) {
        name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp( regexS );
        var results = regex.exec( window.location.href );
        if (null == results) {
            return '';
        }
        return results[1];
    }

});
