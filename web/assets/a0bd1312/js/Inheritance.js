Ext.define('Ext.Design.Inheritance', {

    extend: 'Ext.panel.Panel',
    title: 'Наследование',
    region: 'center',
    items: [],
    layout: 'border',
    refs: null,
    tpls: null,

    initComponent: function(){

        processManager.addEventListener('reload_inheritance',this.path, this.reloadData, this);

        this.items = [
            this.refs = Ext.create('Ext.Design.InheritanceRefs', {path: this.path})
        ];

        this.callParent();
    },

    execute: function( data, cmd ){

        switch ( cmd ) {

            case 'init':

                this.path = data.path;
                this.refs.loadData( data.exceptions );
                break;

        }

        this.setLoading( false );
        this.refs.setLoading( false );

        return true;
    },

    reloadData: function (){
       this.refs.setLoading( true );
       processManager.setData(this.path,{cmd: 'init'});
       processManager.postData();
    }


});