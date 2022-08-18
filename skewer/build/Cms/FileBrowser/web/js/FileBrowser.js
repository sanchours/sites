/**
 * Библиотека для вывода раскладки файловго менеджера
 */
Ext.define('Ext.Cms.FileBrowser', {
    extend: 'Ext.Viewport',
    title: '',
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
        processManager.addEventListener('tabs_load', this.path, 'onSectionSelect');

        // событие при выборе файла во всплывающем окне
        processManager.addEventListener('select_file_set', this.path, 'onFileSelect');

        // идентификатор места возврата
        this.returnTo = this.parseUrl('returnTo');

    },

    execute: function( data, cmd ) {

        switch ( cmd ) {
            case 'openNode':
                this.showSection( data['nodeId'] );
                break;
            case 'init':
                // идентификатор имени модуля
                var folder_alias = this.parseUrl('module');

                // получить идентификатор раздела из настроек модуля
                var parent = window.opener;
                if (parent && parent['processManager'])
                    folder_alias = parent['processManager'].getEventValue('get_tab_param', '_filebrowser_section');

                if (folder_alias) {

                    // запрос id раздела для модуля
                    processManager.setData(this.path,{
                        cmd: 'getModuleNodeId',
                        folder_alias: folder_alias
                    });
                    processManager.postData();

                } else {

                    var iSectionId = this.parseUrl('section');

                    // если запущен в новой админке
                    if (!iSectionId && this.isNewAdminPanel()) {
                        iSectionId = parent.sk.getSectionId();
                    }

                    if ( !iSectionId ) {
                        // попытаться запросить дерево
                        parent = window.opener;

                        // если есть - выбрать раздел
                        if ( parent && parent['processManager'] ) {
                            iSectionId = parent['processManager'].getEventValue( 'get_section_id' );
                        }
                    }

                    if ( iSectionId )
                        this.showSection(iSectionId);
                    else {

                        // нет раздела - попытаться запросить модуль
                        folder_alias = this.getModuleName();

                        if (folder_alias) {
                            // запрос id раздела для модуля
                            processManager.setData(this.path,{
                                cmd: 'getModuleNodeId',
                                folder_alias: folder_alias
                            });
                            processManager.postData();
                        }
                    }
                }
                break;

        }

        if ( data.error )
            sk.error( data.error );
    },

    /**
     * Открывает раздел
     * @param iSectionId
     */
    showSection:function (iSectionId) {

        var tree = processManager.getProcess(this.path + '.tree');
        if (tree) {

            var bExpandBranch = false;

            if (!iSectionId) {
                iSectionId = this.defaultSection;
                bExpandBranch = true;
            }

            tree.findSection(iSectionId);

            if (bExpandBranch) {
                var node = tree.store.getNodeById(iSectionId);

                if (!node.isLoaded() && !node.isLoading()) {
                    // развернуть, если не раскрыто
                    if (!node.isExpanded()) {
                        node.expand(); // вызовет cms событие раскрытия и расставит ext параметры для ветки
                    }
                }

            }

        }
    },

    // при воборе раздела в дереве
    onSectionSelect: function ( iSectionId ) {

        this.setSectionToFilesPanel( iSectionId );

    },

    // установить значение раздела для панели с файлами
    setSectionToFilesPanel: function( iSectionId ) {

        if ( !iSectionId )
            return false;

        // попытаться запросить дерево
        var filesPanel = processManager.getProcess( this.path+'.files' );

        // если есть - выбрать раздел
        if ( filesPanel ) {

            processManager.setData(filesPanel.path,{
                cmd: 'list',
                sectionId: iSectionId
            });

            processManager.postDataIfExists();

        }

        return true;

    },

    /**
     * При воборе файла во всплывающем окне
     * @param value
     */
    onFileSelect: function( value ) {

        if ( !window.top.opener )
            return false;

        switch ( this.returnTo ) {

            case 'ckeditor':

                if ( !window.top.opener['CKEDITOR'] )
                    break;

                window.top.opener['CKEDITOR'].tools.callFunction(this.parseUrl('CKEditorFuncNum'), value );
                window.top.close();
                window.top.opener.focus();

                break;

            case 'fileSelector':

                // старая админка
                if ( window.top.opener['processManager'] ) {
                    window.top.opener['processManager'].fireEvent('set_file', {
                        ticket: this.parseUrl('ticket'),
                        value: value
                    });
                    window.top.close();
                    window.top.opener.focus();
                }

                // новая админка
                if ( window.top.opener['React'] ) {
                    window.top.opener['sk'].setField(
                        this.parseUrl('path'),
                        this.parseUrl('fieldName'),
                        value
                    );
                    window.top.close();
                    window.top.opener.focus();
                }

                break;

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
    },

    isNewAdminPanel: function () {
        return window.top.opener !== null && window.top.opener['React'] !== undefined;
    },

    getModuleName: function () {
        const parent = window.opener;
        let moduleName;

        if ( parent && parent['processManager'] ) {
            moduleName = parent['processManager'].getEventValue('get_module_name');
        }

        if (parent.location.search) {
            const result = /moduleName=([0-9a-zA-Z_]+)/.exec(parent.location.search)

            moduleName = result[1] ? result[1] : moduleName;
        }

        return moduleName;
    }
});
