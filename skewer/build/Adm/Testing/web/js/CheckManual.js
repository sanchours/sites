Ext.define("Ext.Adm.CheckManual", {
    extend: "Ext.Component",


    getClass: function (value, meta, item) {

        var testSuites = item.data;

        if (testSuites.autotest === 1) {
            this.items[0].tooltip = "";
            return "";
        } else {
            this.items[0].tooltip = "Изменить состояние";
            return testSuites.manual ? "icon-checked" : "icon-unchecked";
        }

    },
    handler: function (grid, rowIndex, colIndex, item) {
        var rootCont = processManager.getMainContainer(grid);
        var rec = grid.getStore().getAt(rowIndex);
        var addParams = item.addParams || {};

        if (rec.get("autotest") !== 1) {

            // данные к отправке
            var dataPack = rec.data;
            Ext.merge(
                dataPack,
                rootCont.serviceData,
                addParams,
                {
                    from: "list",
                    cmd: "ChangeStateManualTest"
                }
            );

            processManager.setData(rootCont.path, dataPack);

            rootCont.setLoading(true);

            if (item.doNotUseTimeout) {
                processManager.doNotUseTimeout();
            }

            processManager.postData();

            var rowEl = grid.all.elements[rowIndex];
            if (rec.get("manual")) {
                var manualCheck = rowEl.getElementsByClassName("icon-checked")[0];
                manualCheck.classList.add("icon-unchecked");
                manualCheck.classList.remove("icon-checked");
                rec.set("manual", false);
            } else {
                var manualUncheck = rowEl.getElementsByClassName("icon-unchecked")[0];
                manualUncheck.classList.add("icon-checked");
                manualUncheck.classList.remove("icon-unchecked");
                rec.set("manual", true);
            }

            return true;
        }
    }

});
