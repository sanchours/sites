/**
 * Класс для инициализации системы выбора и загрузки файлов
 */
Ext.define('Ext.sk.GallerySelector',{

    path: 'gallerySelector',
    layerName: 'sk',
    extend: 'Ext.Component',
    moduleName: 'GallerySelector',

    /**
     * Хранилище ярлыков для установки файлов
     */
    ticketStorage: {},

    initComponent: function() {

        //this.callParent();

        processManager.addEventListener( 'edit_gallery', this.path, 'onGalleryEditorStart' );//select_file
        processManager.addEventListener( 'set_gallery', this.path, 'onGalleryEditorEnd' );//set_file

    },

    /**
     * При начале выбора файла
     */
    onGalleryEditorStart: function( data ) {

        // проверка наличия необходимых переменных
        if ( !data['scope'] || !data['fnc'] ) {
            sk.error('Wrong init gallery select data.');
            return false;
        }

        // уникальный ключ
        var ticket = processManager.getUniqueNum();

        this.ticketStorage[ticket] = data;

        var selectMode = data['mode'] ? data['mode'] : 'galleryBrowser';

        var selectValue = data['scope']['value'] ? data['scope']['value'] : '0';
        var GalProfileId = data['scope']['gal_profile_id'] ? data['scope']['gal_profile_id'] : '0';
        var seoClass = data['scope']['seoClass'] ? data['scope']['seoClass'] : '';
        var iEntityId = data['scope']['iEntityId'] ? data['scope']['iEntityId'] : 0;
        var makeNewAlbum = data['gal_new_album'] ? data['gal_new_album'] : '0';
        var sectionId = data['scope']['sectionId'] ? data['scope']['sectionId'] : 0;

        // собрать ссылку
        var href = buildConfig.files_path+'?mode='+selectMode+'&cmd=showAlbum&gal_album_id='+selectValue+'&gal_profile_id='+GalProfileId+'&ticket='+ticket+'&gal_new_album='+makeNewAlbum+'&seoClass='+seoClass+'&iEntityId='+iEntityId+'&sectionId='+sectionId;

        // открыть в новом окне
        sk.newWindow( href );

        return true;

    },

    /**
     * При выборе файла
     */
    onGalleryEditorEnd: function( data ) {

        var ticket = data['ticket'];
        var value = data['value'];

        // проверка наличия необходимых переменных
        if ( !ticket || !value ) {
            sk.error('Wrong set file data.');
            return false;
        }

        // найти вызвавший объект
        var caller = this.ticketStorage[ticket];

        // выйти, если не найден
        if ( !caller ) {
            sk.error('No data in ticket storage for file selector');
            return false;
        }

        // вызвать функцию обработки
        caller['scope'][caller['fnc']](value);

        // удалить ярлык
        delete this.ticketStorage[ticket];

        return true;

    }

});
