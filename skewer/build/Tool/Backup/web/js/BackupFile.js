/**
 * Библиотека для отображения картинки в заданном формате
 */
Ext.define('Ext.Tool.BackupFile',{

    extend: 'Ext.AbstractComponent',
    border: 0,
    padding: 5,
    value: false,
    html: 'загрузка резервной копии',

    execute: function( data ) {

        // вызвать нужное состояние
        //sk.message( data.link );
        window.open(data.link,'_blank');

        var cont = processManager.getMainContainer(this);

        processManager.setData(cont.path,{cmd: 'init'});
        processManager.postData();


    }

});
