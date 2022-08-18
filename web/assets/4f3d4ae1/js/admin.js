Ext.Loader.setConfig({enabled: true});

// конфигурация слоя
var layerName = 'Cms';
var extPrefix = 'Ext.'+layerName+'.';

// инициализация базовых инструментов
var sk;
Ext.Loader.setPath('Ext.sk', pmDir+'/js');

// установка путей для слоя
Ext.Loader.setPath('Ext.'+layerName, rootPath);

//Ext.Loader.setPath('Ext.ux', '/skewer/build/libs/ExtJS/js/ux');
//Ext.require([ 'Ext.ux.CheckColumn' ]);

// конфигурация и языки
buildConfig = Ext.create(extPrefix+'Config');

Ext.onReady(function() {

    // инициализация основного набора параменных
    sk = Ext.create('Ext.sk.Init');
    processManager.addProcess('init',sk);

    // инициализация CKEditor'а
    //Ext.Loader.require('/skewer/build/libs/CKEditor/ckInit');

    // инициализация дополнительных и измененных ExtJS компонентов
    Ext.Loader.require('Ext.sk.field.MultiSelect');

    // инициализация браузера файлов и поля типа "файл"
    Ext.Loader.require('Ext.sk.field.FileSelector');
    processManager.addProcess('fileSelector',Ext.create('Ext.sk.FileSelector'));

    Ext.Loader.require('Ext.sk.field.ColorSelector');

    Ext.Loader.require('Ext.sk.field.TimeField');

    // инициализация браузера файлов и поля типа "галерея"
    Ext.Loader.require('Ext.sk.field.GallerySelector');
    processManager.addProcess('gallerySelector',Ext.create('Ext.sk.GallerySelector'));

    Ext.Loader.require('Ext.sk.field.MapListMarkers');
    processManager.addProcess('mapListMarkers',Ext.create('Ext.sk.MapListMarkers'));

    Ext.Loader.require('Ext.sk.field.MapSingleMarker');
    processManager.addProcess('mapSingleMarker',Ext.create('Ext.sk.MapSingleMarker'));



});

// удержание сессии в живых
setInterval(function(){
    Ext.Ajax.request({
        url: '/keepalive.php',
        method: 'GET',
        params: {ping: 1},
        success: function (result, request) {}
    });

}, 900000); // каждые 15 минут
