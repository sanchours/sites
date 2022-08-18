/**
 * Класс регистрирует события открытия/закрытия дочернего фрейма редактирования карты,
 * а также служит для передачи параметров между главным окном и дочерним фреймом
 *
 */
Ext.define('Ext.sk.MapSingleMarker',{

    path: 'mapSingleMarker',
    layerName: 'sk',
    extend: 'Ext.Component',
    moduleName: 'mapSingleMarker',

    // хранилище тикетов для передачи параметров дочернему окну
    ticketStorage: {},

    initComponent: function() {

        processManager.addEventListener( 'openEditorMap',   this.path, 'onOpenChildFrame'  );
        processManager.addEventListener( 'closeEditorMap',  this.path, 'onCloseChildFrame' );

    },

    onOpenChildFrame: function( data ) {

        // проверка наличия необходимых переменных
        if ( !data['scope'] || !data['fnc'] ) {
            sk.error('Wrong init map select data.');
            return false;
        }

        var ticket = processManager.getUniqueNum();
        this.ticketStorage[ticket] = data;

        var queryParams,
            href,
            sAddressWithId = data['scope']['value'],
            regex = /[^;]*;\s*\[id=([0-9]*)\]/gi,
            match,
            getObjectId;

        if ((match = regex.exec(sAddressWithId)) !== null) {
            if ( match[1] )
                getObjectId = match[1];
        }

        queryParams = {
            mode:   data['mode'] ? data['mode'] : 'editorMap',
            cmd:   'edit',
            ticket: ticket,
            mapMode: 'single',
            geoObjectId: getObjectId ?  getObjectId : ''
        };

        // собрать ссылку
        href = buildConfig.files_path + '?' + Ext.urlEncode(queryParams);

        // открыть в новом окне
        sk.newWindow( href );

        return true;
    },

    onCloseChildFrame: function( data ) {

        var ticket = data['ticket'];
        var value = data['value'];

        // проверка наличия необходимых переменных
        if ( !ticket ) {
            sk.error('Wrong set map data.');
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
    }

});