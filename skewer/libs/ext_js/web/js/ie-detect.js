/*
 * Вызвать после подключния ext-all.js
 * @returns {number}
 */

/**
 *  Отдает номер версии ie
 * @returns {number}
 */
function getInternetExplorerVersion() {
    var ua, re;
    var rv = -1;
    if (navigator.appName == 'Microsoft Internet Explorer') {
        ua = navigator.userAgent;
        re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat( RegExp.$1 );
    } else if (navigator.appName == 'Netscape') {
        ua = navigator.userAgent;
        re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat( RegExp.$1 );
    }
    return rv;
}

var ieVer = getInternetExplorerVersion();
if ( ieVer>0 ) {
    Ext.apply(Ext, {
        isIE : true,
        isIE11 : ieVer == 11,
        ieVersion : ieVer
    });
}
