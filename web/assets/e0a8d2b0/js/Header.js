/**
 * Шапка cms
 */
Ext.define('Ext.Cms.Header',{
    extend: 'Ext.container.AbstractContainer',
    region: 'north',
    baseCls: 'b-header-panel',
    border: 0,
    margin: '0 0 3 0',
    padding: 0,

    renderData: {
        href: '',
        logoImg: ''
    },

    childEls: ['body'],

    renderTpl: [
        '<div class="b-header__rama">',
            '<div class="b-headerbox">',
                '<div class="logo">',
                    '<a href="{href}"><img src="{logoImg}" alt="" /></a>',
                '</div>',
                '<div id="{id}-body" class="auth-headerbox-body"></div>',
            '</div>',
        '</div>'
    ],

    getTargetEl: function() {
        return this.body || this.frameBody || this.el;
    },

    /**
     * Перекрытие id, иначе подчиненные элементы теряются
     */
    initRenderData: function() {
        var ret = this.callParent();
        ret.id = this.id;
        return ret;
    }

});
