/**
 * Кнопка дополнительной панели спискового интерфейса автопостроителя
 */
Ext.define('Ext.Builder.ListFilterButton', {
    extend: 'Ext.button.Button',
    text: '',
    textConfirm: '',
    addParams: [],

    handler: function( button ){

        // если есть подтверждение
        if ( button.textConfirm ) {

            // вызвать подтверждение
            Ext.MessageBox.confirm(sk.dict('confirmHeader'), button.textConfirm, function(res){
                if ( res !== 'yes' ) return false;
                // выполнить действие
                button.doAction();
            } );

        } else {

            // иначе просто выполнить действие
            button.doAction();

        }

    },

    /**
     * Выполнить основное действие
     */
    doAction: function(){

        // собрать посылку
        processManager.setDataFromParent(this,this.addParams);

        // отослать её
        processManager.postData();

    }

});
