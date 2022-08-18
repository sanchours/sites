Ext.Loader.setConfig({enabled: true});

// конфигурация слоя
var layerName = 'Design';
var extPrefix = 'Ext.'+layerName+'.';

// инициализация базовых инструментов
var sk, frameApi;
Ext.Loader.setPath('Ext.sk', pmDir+'/js');
Ext.Loader.setPath('Ext.Cms', rootCmsPath+'/js');

// установка путей для слоя
Ext.Loader.setPath('Ext.'+layerName, rootPath);

buildConfig = Ext.create(extPrefix+'Config');
designLang = Ext.create(extPrefix + 'Lang' + lang);

// функция вызова клобального события
var firePanelEvent = function() {};

Ext.onReady(function() {

    // инициализация основного набора параменных
    sk = Ext.create('Ext.sk.Init');
    frameApi = Ext.create(extPrefix+'FrameApi');
    frameApi.init();
    frameApi.setDisplayUrl('/');
    processManager.setProcess('init',sk);

    //// инициализация CKEditor'а
    //Ext.Loader.require('/skewer/build/libs/CKEditor/ckInit');

    // инициализация браузера файлов и поля типа "файл"
    processManager.setProcess('fileSelector',Ext.create('Ext.sk.FileSelector'));

    // инициализация браузера файлов и поля типа "галерея"
    Ext.Loader.require('Ext.sk.field.GallerySelector');
    processManager.addProcess('gallerySelector',Ext.create('Ext.sk.GallerySelector'));

    firePanelEvent = function( eventName ) {
        processManager.fireEvent.apply( processManager, arguments );
    };

});

// удержание сессии в живых
setInterval(function(){
    Ext.Ajax.request({
        url: '/keepalive.php',
        method: 'GET',
        params: {ping: 1},
        success: function () {}
    });

}, 900000); // каждые 15 минут
