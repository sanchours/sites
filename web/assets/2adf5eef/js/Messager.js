var msgCt = null;
Ext.define('Ext.sk.Messager', {
    createBox: function(t, s, addCls){
        return '<div class="msg'+(addCls?' '+addCls:'')+'">'+(t?'<h3>'+t+'</h3>':'')+'<p>' + s + '</p></div>';
    },

    msg : function(title, text, addCls, time){
        if(!msgCt){
            msgCt = Ext.core.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
        }
        if ( !text || typeof text === 'undefined' ){
            text = title;
            title = '';
        }

        if ( !time )
            time = 2000;       // 2 секунды


        var m = Ext.core.DomHelper.append(msgCt, this.createBox(title, text, addCls), true);
        m.hide();
        var slide = m.slideIn('t');
        if ( time>=0 )
            slide.ghost("t", { delay: time, remove: true});
    },

    init : function(){}

});
