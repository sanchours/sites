/**
 * Основные конфигурационные константы CMS
 */
Ext.define('Ext.Cms.Config', {

    // версия движка
    cmsVersion: '3.0',

    // название слоя
    layerName: layerName,
    rootPath: rootPath,

    // имя основного файла для запросов
    request_script: '/oldadmin/index.php',

    request_dir: '/oldadmin/',

    // путь для вызова файлового браузера
    files_path: '/oldadmin/',

    // время ожидания ответа ajax запроса
    request_timeout: 300000, // 5 минут

    CKEditorLang: lang
});
