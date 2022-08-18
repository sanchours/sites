Ext.define('Ext.Cms.TooltipBrowser', {
    extend: 'Ext.Viewport',
    title: 'gallery',
    height: '450px',
    width: '550px',
    layout: 'border',
    closeAction: 'hide',
    modal: true,
    componentsInited: false,
    header:'',
    headerAsText:'',
    senderData: {},
    hideHeaders: true,

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

    },

    showData: function( text ){
        sk.error( text );
    },


    execute: function( data, cmd ) {

        if ( data.error )
            sk.error( data.error );
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
    }

});
