/**
 * Класс для инициализации системы выбора и загрузки файлов
 */
Ext.define('Ext.sk.FileSelector',{

    path: 'fileSelector',
    layerName: 'sk',
    extend: 'Ext.Component',
    moduleName: 'FileSelector',

    /**
     * Хранилище ярлыков для установки файлов
     */
    ticketStorage: {},

    initComponent: function() {

        //this.callParent();

        processManager.addEventListener( 'select_file', this.path, 'onFileSelectStart' );
        processManager.addEventListener( 'set_file', this.path, 'onFileSelectEnd' );

    },

    /**
     * При начале выбора файла
     */
    onFileSelectStart: function( data ) {

        // проверка наличия необходимых переменных
        if ( !data['scope'] || !data['fnc'] ) {
            sk.error('Wrong init file select data.');
            return false;
        }

        // уникальный ключ
        var ticket = processManager.getUniqueNum();

        this.ticketStorage[ticket] = data;

        var selectMode = data['mode'] ? data['mode'] : 'fileBrowser';

        // собрать ссылку
        var href = buildConfig.files_path+'?mode='+selectMode+'&type=file&returnTo=fileSelector&ticket='+ticket;

        // открыть в новом окне
        sk.newWindow( href );

        return true;

    },

    /**
     * При выборе файла
     */
    onFileSelectEnd: function( data ) {

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
