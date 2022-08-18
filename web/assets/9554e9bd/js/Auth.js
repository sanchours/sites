/**
 * Панель авторизации в шапке cms
 */
Ext.define('Ext.Cms.Auth',{
    extend: 'Ext.container.AbstractContainer',
    border: 0,
    margin: '0 0 3 0',
    padding: 0,

    //шаблонные данные для рендеринга
    renderData: {
        username: '---',
        lastlogin: '---'
    },

    childEls: ['body'],

    // шаблон для вывода
    renderTpl: [

    ],

    getTargetEl: function() {
        return this.body || this.frameBody || this.el;
    },

    generateTpl: function(){
        var me = this;
        this.renderTpl = [
            '<div class="login">',
            '<p class="usrname">{username}</p>',
            '<p class="lastvisit">',
            me.lang.authLastVisit,
            ': {lastlogin}',
            '</p>',
            '<div id="{id}-body" class="logoutbtn"></div>',
            '</div>'
        ];
    },
    initComponent:function(){
        var me = this;

        me.generateTpl();
        me.callParent();

        // добавляем кнопку выхода
        me.add( Ext.create('Ext.Button', {
            text: me.lang.authLogoutButton,
            cls: 'logoutbutton',
            handler: function() {
                me.logOut();
            }
        }) );

    },

    /**
     * Выход из админского интерфейса
     */
    logOut: function(){

        var me = this;

        processManager.setData(me.path, {
            cmd: 'logout'
        });

        processManager.postData();

    }

});
