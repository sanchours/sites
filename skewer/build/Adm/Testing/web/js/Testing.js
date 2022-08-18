/**
 *  Библиотека для работы с деревом автотестов
 */
Ext.define("Ext.Adm.Testing", {
    extend: "Ext.tree.Panel",
    split: true,
    minWidth: 275,
    maxWidth: 400,
    collapsible: true,
    animCollapse: false,
    sectionId: 0,
    nowSectionId: 0,

    useHistory: true,
    showButtons: true,
    eventPrefix: "testing",

    rootVisible: false,
    rootSection: 0,
    multiTree: false,
    nodeToSelect: 0,
    addToRootNode: false,
    form: null,

    displayField: "title",
    hideHeaders: true,
    columns: [],
    viewConfig: {
        plugins: [{
            ddGroup: "organizerDD",
            ptype: "treeviewdragdrop"
        }],
        listeners: {
            beforedrop: function (node, data, overModel, dropPosition) {
                return this.up("panel").onBeforeDrop(node, data, overModel, dropPosition);
            }
        }
    },

    // инициализация
    initComponent: function () {

        var self = this;

        this.columns = [{
            xtype: "treecolumn",
            text: "Tests",
            flex: 5,
            dataIndex: "title",
            renderer: function (value, meta, rec) {

                if (rec.get("visible")) {
                    meta.tdCls = "tree-row-section";
                }

                meta.tdCls += " sk-test-" + rec.get("id");

                return value;
            }
        }, {
            xtype: "actioncolumn",
            width: 36,
            tdCls: "tree-row",

            items: [{
                getClass: function () {
                    return "";
                },
                handler: function (grid, rowIndex) {
                    var rec = grid.getStore().getAt(rowIndex);
                    var visible = parseInt(rec.get("visible"));
                    if (visible > 0) {
                        return false;
                    }

                    return true;
                }
            }],
            sortable: false
        }];

        // набор кнопок
        this.addDockedButtons();

        // хранилище
        this.store = Ext.create("Ext.data.TreeStore", {
            id: processManager.getUniqueId(),
            fields: ["id", "title", "path", "parent", "visible"],
            root: {
                id: self.rootSection,
                name: "testing",
                expanded: true,
                lang: this.lang
            },
            proxy: "memory"
        });

        // ext события
        self.on({
            scope: self,
            beforeload: self.onLoad
        });
        // клик по элементу
        this.on("itemclick", this.onItemClick, this);
        this.on('collapse', this.onDeactivate, this);

        this.callParent();

        processManager.addEventListener('location_render', this.path, 'processToken');
        processManager.addEventListener('location_set_value', this.path, 'setToken');
        processManager.addEventListener('get_section_id', this.path, 'getSectionId');
        processManager.addEventListener('reload_section', this.path, 'reloadCurrentSection');
        processManager.addEventListener('reload_tree', this.path, 'reloadTree');

    },

    execute: function (data, cmd) {

        var key, parentPath;

        if (data.error) {
            sk.error(data.error);
        }

        switch (cmd) {

            // загрузка подчиненных элементов
            case "loadItems":

                // перебрать все пришедшие строки
                this.nowSectionId = this.itemId;
                if (data.items) {
                    var items = data.items;

                    for (key in items) {

                        // значения строки
                        row = items[key];

                        // запись с таким id
                        var rowWithId = this.store.getNodeById(row.id);
                        if (rowWithId) {
                            rowWithId.destroy();
                        }

                        // id родителя
                        parentPath = row.parent;

                        // родительская запись
                        parent = parentPath
                            ? this.store.getNodeById(parentPath)
                            : this.store.getRootNode();

                        // есть - добавить к ней запись
                        if (parent) {
                            parent.appendChild(row);
                        }

                        if (row.id === this.nowSectionId) {
                            this.nowSectionId = 0;
                            this.findSection(row.id);
                        }

                    }

                }

                break;

            case "loadTree":

                var node, row, parent, parentId;

                if (data['dropAll']) {
                    this.store.getRootNode().removeAll();
                }

                this.sectionId = data.sectionId;

                if (data.items) {

                    for (key in data.items) {

                        // значения строки
                        row = data.items[key];
                        parentId = row.parent;

                        // текущая запись
                        node = this.store.getNodeById(row.id);

                        // нет - добавить
                        if (!node) {

                            // родительская запись
                            parent = parentId
                                ? this.store.getNodeById(parentId)
                                : this.store.getRootNode();

                            // есть - добавить к ней запись
                            if (parent) {
                                parent.appendChild(row);
                            }
                        }
                    }
                }

                if (data['dropAll']) {
                    this.findSection(data.sectionId);
                } else {
                    this.selectNode(data.sectionId);
                }

                break;

            // выбор раздела
            case "selectNode":

                this.selectNode(data.sectionId);

                break;
            // разворот дерева TestSuites, загрузка таба с детальной информацией
            case "loadCheckListAndItems":

                if (data.items) {
                    var items = data.items;

                    for (key in items) {

                        // значения строки
                        row = items[key];

                        // id родителя
                        parentPath = row.parent;

                        // родительская запись
                        parent = parentPath
                            ? this.store.getNodeById(parentPath)
                            : this.store.getRootNode();

                        // есть - добавить к ней запись
                        if (parent) {
                            parent.appendChild(row);
                        }

                        if (row.id === this.nowSectionId) {
                            this.nowSectionId = row.id;
                            this.findSection(row.id);
                        }

                    }

                    //установка id для сохранения ссылки
                    processManager.fireEvent("tabs_load", data.id, this.path);
                }

                break;
        }

        this.unsetLoadingIndicator();

    },

    /**
     * При сворачивании в интерфейсе
     */
    onDeactivate: function () {
        // чтобы при разворачивании сам загрузился
        this.itemId = 0;
        this.nowSectionId = 0;
        return true;
    },

    setLoadingIndicator: function () {
        this.setLoading(true);
    },

    unsetLoadingIndicator: function () {
        this.setLoading(false);
    },

    addDockedButtons: function () {

        this.tbar = [{
            text: "Сформировать файл общего запуска",
            iconCls: "icon-reinstall",
            scope: this,
            handler: this.runAll,
            cls: "sk-testing-run"
        }];
    },

    // событие при клике по элементу
    runAll: function () {

        this.setLoadingIndicator();

        // создать посылку
        processManager.setData(this.path, {
            cmd: "createScriptRunAll"
        });

        this.setLoadingIndicator();

        // отправить посылку
        processManager.postData();
    },

    // событие при клике по элементу
    onItemClick: function (view, node) {
        var newId, oldId;

        // поставить блокировку отправки
        processManager.setBlocker();

        // развернуть
        if (!node.isLoaded() && !node.isLoading()) {
            // развернуть, если не раскрыто
            if (!node.isExpanded()) {
                // вызовет событие раскрытия и расставит ext параметры для ветки
                node.expand();
            }
        }

        newId = node.getId();
        oldId = this.sectionId;

        // выбранный раздел
        this.sectionId = newId;
        this.nowSectionId = oldId;

        if (newId !== oldId) {
            // если используется история
            if (this.useHistory) {

                // изменить контрольную точку страницы
                processManager.fireEvent("location_change");

            } else {
                // иначе просто перейти к разделу
                this.findSection(newId);
            }
        } else {
            processManager.fireEvent('tabs_load', newId, this.path);
        }

        // снять блокировку отправки
        processManager.unsetBlocker();

        // отправить данные
        processManager.postDataIfExists();

        return true;
    },

    /**
     * Выбор раздела в дереве
     * @param iSectionId
     */
    selectNode: function (iSectionId) {

        const node = this.store.getNodeById(iSectionId);

        if (node) {
            if (this.collapsed) {
                this.nodeToSelect = iSectionId;
                return false;
            }
            if (!this.rendered) {
                this.nodeToSelect = iSectionId;
                return false;
            }

            this.selectPath(node.getPath());
            this.sectionId = iSectionId;
            this.nowSectionId = iSectionId;
            return true;
        }

        return false;
    },

    /**
     * Выбирает заданный раздел для дерева
     * @param id
     */
    selectItem: function (id) {

        this.nodeToSelect = id;
        pageHistory.locationChange();

    },

    /**
     * Находит в дереве раздел и автоматически его открывает
     * @param testSuiteId
     */
    findSection: function (testSuiteId) {

        var me = this,
            reSelect = false
        ;

        if (me.nowSectionId === testSuiteId) {
            return false;
        }

        if (!me.selectNode(testSuiteId)) {
            // собрать посылку на загрузку ветки
            if (me.nodeToSelect === testSuiteId) {
                reSelect = true;
                me.nodeToSelect = 0;
            }

            processManager.setData(me.path, {
                cmd: reSelect ? "selectNode" : "getTree",
                id: testSuiteId,
            });
            me.setLoadingIndicator();
        }


        // вызвать событие выбора раздела
        processManager.fireEvent('tabs_load', testSuiteId, me.path);

        return true;

    },

    reopenItem: function (id) {
        var me = this;

        me.sectionId = 0;
        me.nowSectionId = 0;
        me.findSection(id);
    },

    /**
     * Рекурсивно открывает набор разделов
     * @param aSectionIds
     */
    recOpenSection: function (aSectionIds) {

        if (!aSectionIds.length)
            return;

        // взять первый раздел
        var iSectionId = aSectionIds.pop();

        var tree = this;

        var parentNode = tree.store.getNodeById(iSectionId);
        parentNode.expand(false, function () {
            tree.recOpenSection(aSectionIds);
        });

    },


    /**
     * Метод, вызываемый при событии "загрузка ветки"
     *
     * @param store - хранилище
     * @param options - параметры
     */
    onLoad: function (store, options) {

        options = options || {};
        options.params = options.params || {};

        var object = options.node.data;

        const cmd = object.visible ? "selectNode" : "getSubItems";

        // создать посылку
        processManager.setData(this.path, {
            cmd: cmd,
            id: object.id
        });

        if (object.visible) {
            processManager.fireEvent("tabs_load", object.id, this.path);
        }

        this.setLoadingIndicator();

        // выполнить подписанные события
        processManager.fireEvent(this.eventPrefix + "_list_load", object.id);

    },

    /**
     * обработка токена истории
     */
    processToken: function (data) {

        var newId, oldId,
            me = this
        ;

        // идентификатор раздела
        newId = data[me.path];
        oldId = me.itemId;

        // проверки
        if (!newId) return false;
        if (newId === oldId)
            return false;

        // выбранный раздел
        me.itemId = newId;

        // открыть раздел
        this.findSection(newId);

        return true;

    },

    /**
     * обработка добавления данных в токен истории страниц
     */
    setToken: function () {

        var me = this;

        if (!this.useHistory || this.collapsed) {
            return false;
        }

        // если установлен "следующий" - взять его
        var id = me.nodeToSelect ? me.nodeToSelect : me.sectionId;
        me.nodeToSelect = 0;

        // задать данные
        processManager.setData(this.path, id, 'locPack');

        return true;

    },

    /**
     * для события запроса id раздела
     * @return {Number|undefined}
     */
    getSectionId: function () {
        if (!this.collapsed) {
            return this.sectionId;
        }

        return undefined;
    },


    /**
     * Полностью перезагружает дерево разделов
     */
    reloadTree: function () {

        // основные данные операции
        var tree = this;

        // собрать посылку
        processManager.setData(tree.path, {
            cmd: 'reloadTree',
            id: this.sectionId
        });

        this.sectionId = 0;
        this.nowSectionId = 0;

        processManager.postData();

    },

    /**
     * Перезагружает выбранный раздел
     */
    reloadCurrentSection: function () {
        if (!this.collapsed) {
            var section = this.getSectionId();
            if (section) {
                processManager.fireEvent('tabs_load', section, this.path);
            }
        }
    }

});
