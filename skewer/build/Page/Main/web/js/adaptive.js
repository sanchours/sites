window.adaptive = (function(){

    var jqAdaptiveParamsContainer = $(".js_adaptive_params");

    var oData = {};

    if ( jqAdaptiveParamsContainer.length ){
        oData = jqAdaptiveParamsContainer.data('adaptive_parameters');
    }

    return {

        getData: function(){
            return oData;
        },

        getParam: function(sParamName, mDefValue){
            return oData[sParamName] !== undefined ? oData[sParamName] : mDefValue;
        },

        setParam: function(sParamName, mVal){
            oData.sParamName = mVal;
        },

        isMobile: function(){
            return $(window).width() < Number.parseInt(adaptive.getParam('break_tablet', 768));
        },

        isTablet: function(){
            var windowWidth = $(window).width();
            return (windowWidth < Number.parseInt(adaptive.getParam('break_desktop', 1240))) && (windowWidth >= Number.parseInt(adaptive.getParam('break_tablet', 768)));
        },

        isDesktop: function(){
            return $(window).width() >= Number.parseInt(adaptive.getParam('break_desktop', 1240));
        }

    };

})();