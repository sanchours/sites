Ext.define("Ext.Adm.RunTestSuite", {
    extend: "Ext.Component",


    getClass: function (value, meta, item) {

        var testSuites = item.data;

        if (testSuites.autotest === 1) {
            this.items[1].tooltip = "Запустить";
            return "icon-reinstall";
        } else {
            this.items[1].tooltip = "";
            return "";
        }

    },
    handler: function (grid, rowIndex, colIndex, item) {
        var rootCont = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);
        var addParams = item.addParams || {};

        if (rec.get("autotest") === 1) {

            // данные к отправке
            var dataPack = rec.data;
            Ext.merge(
                dataPack,
                rootCont.serviceData,
                addParams,
                {
                    from: "list",
                    cmd: "runTestSuite"
                }
            );

            processManager.setData(rootCont.path, dataPack);

            rootCont.setLoading(true);

            if (item.doNotUseTimeout) {
                processManager.doNotUseTimeout();
            }

            processManager.postData();

            return true;
        }
    }

});
