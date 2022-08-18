/**
 * layout sk.center
 */
Ext.define('Ext.sk.layout.Center', {
    extend: 'Ext.layout.container.Fit',
    alias: 'layout.sk.center',
    widthRatio: 0.5,
    heightRatio: 0.5,

    // private
    setItemSize : function(item, width, height) {
        
        var me = this;
        
        me.owner.addCls('ux-layout-center');
        item.addCls('ux-layout-center-item');
        if (height > 0) {

            // рассчитать ширину и отступ слева
            if (width) {
                width = item.width;
                if (Ext.isNumber(item.widthRatio)) {
                    width = Math.round(me.owner.el.getWidth() * item.widthRatio);
                }
            }

            // рассчитать высоту и отступ сверху
            height = item.height;
            if (Ext.isNumber(item.heightRatio)) {
                height = Math.round(me.owner.el.getHeight() * item.heightRatio);
            }

            item.setSize(width, height);
            item.margins.left = Math.round((me.owner.el.getWidth() - width) * 0.5);
            item.margins.top = Math.round((me.owner.el.getHeight() - height) * 0.4);
        }

    }
});
