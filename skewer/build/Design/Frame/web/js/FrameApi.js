/**
 *
 */
Ext.define('Ext.Design.FrameApi', {

    currentDiplayUrl: '',

    /**
     * Инициализация элемента
     */
    init: function() {

        var me = this;

        // глобальное событие при обновлении url страницы просмотра
        if ( me.getDisplayFrame() ) {
            me.currentDiplayUrl = me.getDisplayFrameUrl();
            var checkDisplayUrl = function(){
                var nowUrl = me.getDisplayFrameUrl( me.currentDiplayUrl );
                if ( nowUrl && nowUrl !== me.currentDiplayUrl ) {
                    me.currentDiplayUrl = nowUrl;
                    processManager.fireEvent('urlChange',nowUrl);
                }
                setTimeout(checkDisplayUrl,1000);
            };
            checkDisplayUrl();
        }

        processManager.addEventListener('error','design',this.showDesignFrame, this);

    },

    /**
     * открывает панель с дизайнерскими элементами интерфейса
     */
    showDesignFrame: function() {
        if ( window.top.openPanel )
            window.top.openPanel();
    },

    /**
     * Отдает url фрейма отображения
     * При выбрасывании исключения будет осуществлен переход на oldUrl
     */
    getDisplayFrameUrl: function( oldUrl ){

        // запросить элемент
        var displayFrame = this.getDisplayFrame(),
            url = ''
        ;

        if ( !displayFrame )
            return '';

        // взять url
        try {
            if ( displayFrame && displayFrame.contentWindow.location ) {
                url = displayFrame.contentWindow.location.href;
            }
            if ( displayFrame.contentWindow.location.host && displayFrame.contentWindow.location.host !== window.location.host ) {
                this.rollBackDisplayUrl(oldUrl);
            }

        } catch (e){
            if ( oldUrl ) {
                alert(e);
                this.rollBackDisplayUrl(oldUrl);
            }
        }

        return url;

    },

    /**
     * отдает ссылку на фрейма отображения
     */
    getDisplayFrame: function(){
        return window.top.document.getElementById( 'skDesignDisplayFrame' );
    },

    /**
     * Отдает id раздела фрейма отображения
     */
    getDisplaySectionId: function() {

        // выйти на уровень интерфейса отображения
        var displayFrame = this.getDisplayFrame();

        // запросить id
        if ( displayFrame && displayFrame.contentWindow['designObj'] )
            return displayFrame.contentWindow['designObj'].sectionId;
        else return undefined;

    },

    /**
     * Задать url для фрейма отображения
     * @param sUrl
     */
    setDisplayUrl: function( sUrl ){

        var displayFrame = this.getDisplayFrame();

        if ( displayFrame )
            displayFrame.src = sUrl;

    },

    /**
     * Откатить url фрейма отображения и выдать сообщение
     */
    rollBackDisplayUrl: function( sUrl ){
        this.setDisplayUrl( sUrl );
        alert('Заблокирован переход на сторонний ресурс!');
    },

    /**
     * Перегружает отображающую изменения часть
     */
    reloadDisplayFrame: function() {

        // запросить элемент
        var displayFrame = this.getDisplayFrame();

        // обновление
        if ( displayFrame ) {
            var contentWindow = displayFrame.contentWindow;
            if ( contentWindow['preReload'] )
                contentWindow['preReload']();
            contentWindow.location.reload();
        }

    },

    /**
     * перегружает весь интерфейс
     */
    reloadAll: function(){
        return window.top.location.reload();
    }

});
