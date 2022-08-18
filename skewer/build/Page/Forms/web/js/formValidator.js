var bFormSubmitAllow = [];

$(function(){

    /* применяем правила валидации к полям */
    $('form').each( function() {
        var formId = this.id;
        if ( /form_(.+?)/ , formId ) { // отсекли левые формы типа поиска
            updateFromValidator( formId );
        }
    });

    $(document).on('submit',$('.js-form'),function (objectSubmit) {
        var idForm = objectSubmit.target.id;
        if (idForm && ( /form_(.+?)/ , idForm ) && $(objectSubmit.target).data('ajaxform')){
            updateFromValidator(idForm);
            return false;
        }
    });

    /* релоад каптчи */
    $(document).on( 'click', '.img_captcha', function() {
        return reloadImg( this );
    });

    function isMobile(){
        return window.innerWidth < $('#js-adaptive-min-form-width').val();
    }


    if (!isMobile()){
            $( ".js_calendar" ).each(function( index ) {
                $(this).attr('type','text');
                $(this).addClass("js_init_datepicker");
                $(this).removeClass("js_calendar");

                /* calendar - инициализация календарика */
                if ( typeof $.datepicker !== 'undefined' ) {
                    $.datepicker.setDefaults({
                        dateFormat: 'dd.mm.yy'
                    });

                    $('.js_init_datepicker').datepicker({
                        nextText: '',
                        prevText: ''
                    });
                    $('.js_init_datepicker').click(function () {
                        var paramDatepicker = $('.ui-datepicker').offset().top;
                        var paramInput = $('.js_init_datepicker').offset().top;

                        if (paramDatepicker < paramInput) {
                            $('.ui-datepicker').removeClass('ui-datepicker-after')
                            $('.ui-datepicker').addClass('ui-datepicker-before')
                        } else {
                            $('.ui-datepicker').removeClass('ui-datepicker-before')
                            $('.ui-datepicker').addClass('ui-datepicker-after')
                        }
                    });

                    $('body').on('click', '.js_ui-datepicker-trigger', function() {
                        $( '.js_init_datepicker', $(this).parent('div') ).focus();
                    });
                }
            });
        }

    jQuery.validator.addMethod( 'date', function( value ) {

        if ( value == '' ) return true;

        const regex = /^(\d{1,2})[-|.|/](\d{1,2})[-|.|/](\d{2}|\d{4})$/gm;
        var match = regex.exec(value);

        if ( match === null ) {
            const regexSpec = /^(\d{4})-(\d{2})-(\d{2})$/gm;
            var matchSpec = regexSpec.exec(value);
            if ( matchSpec === null )
                return false;
            else
                match = matchSpec.reverse();
        } else
            match.splice(0,1);

        if( parseInt(match[2]) < 1000 || parseInt(match[2]) > 9999 ) return false;
        if( parseInt(match[1]) < 1 || parseInt(match[1]) > 12 ) return false;
        if( parseInt(match[0]) < 1 || parseInt(match[0]) > 31 ) return false;
        var tmpDate = new Date( match[2], parseInt(match[1]) - 1, match[0], 12 );


        return !/Invalid|NaN/.test(tmpDate);
    });

    $.validator.addMethod('filesize', function(value, element, param) {
        if ($(element).attr('type') != "file"){
            return true;
        }
        if (element.files.length > 0){
            return this.optional(element) || (element.files[0].size <= param[0])
        }
        return true;
    });

    $.validator.addMethod('extension', function (value, element, param) {
        if ($(element).attr('type') != "file") {
            return true;
        }

        if (element.files.length === 0) {
            return true;
        }

        var extension = value.substr(value.lastIndexOf('.') + 1);

        if (param.indexOf(extension) === -1) {
            return false;
        }

        return true;
    });

});


function updateFromValidator ( formId ) {

    /*Добавим в валидатор метод который валидирует группу галочек*/
    $.validator.addMethod('require_checkbox_group', function(value) {
        if (typeof(value)=='undefined')
            return false;
        else
            return true;
    });

    var form = $( '#' + formId );
    var sRules = $('._rules', form).val();

    if ( sRules ) {

        sRules = $.parseJSON( sRules );

        /*Обойдем ВСЕ группы галочек которые обязательны и добавим им метод для валидации*/
        $('.js_required_group').each( function() {
            var field_name = $(this).find('input')[0].name;
            sRules.rules[field_name] = {'require_checkbox_group':true};
        });

        bFormSubmitAllow[ formId ] = false;

        sRules.errorPlacement = function( error, element ) {
            $(element).closest(".form__item").addClass('form__item--error');
            $(element).closest(".form__item").find(".form__icon").hide();
            $(element).closest(".form__item").removeClass('form__item--success');
        };

        sRules.unhighlight = function(element, error, valid, _orig) {
            if ($(element).closest(".form__item").hasClass('form__item--error')) {
                $(element).closest(".form__item").find(".form__icon").show('fa-check');
                $(element).closest(".form__item").addClass('form__item--success');
            }
            $(element).closest(".form__item").removeClass('form__item--error');
        };

        /*Назначим обработчик который соберет список ошибок*/
        sRules.invalidHandler = function(event, validator) {

            bFormSubmitAllow[ formId ] = false;

            /*Cброс подсветки полей с предыдущей валидации*/
            $('.form__item').removeClass('form__item--error');


            var errors = validator.numberOfInvalids();
            if (errors) {

                var form_hash = $(validator.currentForm).data('hash');
                // var form_hash = $(validator.errorList[0].element).data('hash');

                /*Сброс блока с ошибками*/
                $('#js-error_required_'+form_hash).empty();
                $('#js-error_valid_'+form_hash).empty();

                var aRequired = [];
                var aValid = [];

                for (var i = 0; i < validator.errorList.length; i++) {

                    var error_content = validator.errorList[i];

                    var error_text='"'+$(error_content.element).data('name')+'" '+error_content.message;

                    if (error_content.method=='required' || error_content.method=='require_checkbox_group'){
                        //в блок обязательных
                        aRequired.push("<li>"+error_text+"</li>");
                    } else {
                        //в блок прочего
                        aValid.push("<li>"+error_text+"</li>");
                    }
                }

                for (var i = 0; i < aRequired.length; i++) {
                    $( '#js-error_required_'+form_hash ).append( aRequired[i] );
                }

                if (aRequired.length) {
                    $("#js-error_required_title_"+form_hash).show();
                    $("#js-error_required_"+form_hash).show();
                } else {
                    $("#js-error_required_title_"+form_hash).hide();
                    $("#js-error_required_"+form_hash).hide();
                }

                if (aValid.length) {
                    $("#js-error_valid_title_"+form_hash).show();
                    $("#js-error_valid_"+form_hash).show();
                    for (var i = 0; i < aValid.length; i++) {
                        $('#js-error_valid_' + form_hash).append(aValid[i]);
                    }
                } else {
                    $("#js-error_valid_title_"+form_hash).hide();
                    $("#js-error_valid_"+form_hash).hide();
                }

                $("#js-error_block_"+form_hash).show();

                hidePreloader(form);
            }

        };


        $( form ).unbind( 'submit' );

        form.submit(function (objectSubmit) {
            showPreloader(form);
            return formSubmit(objectSubmit);
        });

        var oValidator = $(form).validate(sRules);

        // Для обновления размеров fancybox окна после jquery валидации
        if ( $( form ).data( 'ajaxform' ) == 1 ) {

            oValidator.showErrors = function (errorMap, errorList) {
                var errors = errorMap;
                if ( errors ) {
                    var validator = this;
                    validator.findByName('captcha')[0].value = '';
                    // Add items to error list and map
                    $.extend( this.errorMap, errors );
                    this.errorList = $.map( this.errorMap, function( message, name ) {
                        return {
                            message: message,
                            element: validator.findByName( name )[ 0 ]
                        };
                    } );

                    // Remove items from success list
                    this.successList = $.grep( this.successList, function( element ) {
                        return !( element.name in errors );
                    } );
                }
                if ( this.settings.showErrors ) {
                    this.settings.showErrors.call( this, this.errorMap, this.errorList );
                } else {
                    this.defaultShowErrors();
                }
            };
        }

        function formSubmit(objectSubmit){

            $('.js-agreed_text').css('display','none');

            var form = $(objectSubmit.target);

            if (bFormSubmitAllow[formId] && ($(form).data('popup_result_page') != '1')) {
                return true;
            }

            if ($( form ).data( 'ajaxform' ) != 1) return true;

            // if ( oValidator.errorList.length != 0 )
            //   return false;

            if ( $( ".img_captcha", $(form) ).length ) {
                if ($(form).find('#captcha').val() == '') {
                    return true;
                }

                var sCode = $( "input[name=captcha]", $(form) ).val();
                var sHash = $(form).attr('id');
                if ( sHash)
                    sHash = sHash.substr(5);
                bFormSubmitAllow[ formId ] = true;

                $.post( '/ajax/ajax.php',{
                        moduleName: 'Forms',
                        cmd: 'captchaAjax',
                        code: sCode,
                        hash: sHash,
                        language: $('#current_language').val()
                    },
                    function ( mCaptchaResponse ) {
                        if ( !mCaptchaResponse ) {
                            alert( 'Error: message not sent.' );
                            return false;
                        }

                        var oResponse = $.parseJSON( mCaptchaResponse );
                        var sResponse = oResponse.data.out;

                        if ( sResponse == '1' ) {

                            if ( oValidator.errorList.length != 0 )
                                return false;

                            bFormSubmitAllow[ formId ] = true;

                            if ( $( form ).data( 'ajaxform' ) == 1 ) {

                                setTimeout(function(){
                                    if (oValidator.errorList.length==0)
                                        sendAjaxForm(form);
                                },500);

                            } else {

                                $( form ).submit();
                            }
                        } else {

                            if ($('#captcha').val()) {
                                var hash = $( form ).data('hash');
                                var oldErr = $("#js-error_valid_"+hash).html();
                                var fullErr = oldErr + "<li>"+sResponse+"</li>";
                                $("#js-error_valid_"+hash).html(fullErr);
                                $("#js-error_valid_title_"+hash).show();
                                $("#js-error_valid_"+hash).show();
                            } else
                                $("#js-error_valid_"+hash).html('');

                            // отключаем прелоудер во всплывающем ответе
                            hidePreloader(form);

                            reloadCaptchaByForm( form );
                            $('#captcha').val('');

                            setTimeout(function(){
                                hidePreloader(form);
                                oValidator.showErrors({"captcha": sResponse});
                            },500);

                        }

                        return true;
                    });

                return false;

            } else {

                bFormSubmitAllow[ formId ] = true;

                if ( $( form ).data( 'ajaxform' ) == 1 ) {

                    setTimeout(function(){
                        if (oValidator.errorList.length==0)
                            sendAjaxForm(form);
                    },500);
                    return false;

                } else {

                    $( form ).submit();
                }

            }

            return false;
        }

    }
}

function sendAjaxForm( form ) {

    var moduleName = $(form).data('module_name');
    var cmdForm = $(form).find('input[name="cmd"]');
    var cmd = (cmdForm.val())?cmdForm.val():'send';
    $.ajax({
        url: '/ajax/ajax.php' + '?cmd='+cmd + '&moduleName='+moduleName + '&ajaxForm=1'+ '&language=' + $('#current_language').val() + '&sectionId=' + $("#current_section").val(),
        type: 'POST',
        data: new FormData(form[0]), // Добавить форму с файлами
        cache: false,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function ( mFormResponse ) {

            // передаем признак успешной ajax-отправки формы в GTM
            dataLayer.push({'event': 'send_ajax_form'});

            if ( mFormResponse ) {
                var oResponse = mFormResponse;
                var html = oResponse.html;

                // отключаем прелоудер
                hidePreloader(form);

                if (~html.indexOf('b-form')) {

                    if (form.parent().parent().hasClass('js-callbackForm')
                        || form.parent().find('.form__header')
                    ) {
                        form = form.parent();
                    }
                    $(html).insertAfter(form);
                    $(form).remove();

                    let oRating = new Rating('js-rating-form',false);
                    oRating.initRatings();

                    return false;
                }

                if ( $( form ).data('popup_result_page') == '1' && !isMobile() && $.fancybox.getInstance() === false ){

                    // отключаем прелоудер во всплывающем ответе
                    hidePreloader(form);

                    //Всплывающая результирующая
                    $.fancybox.open(oResponse.html,{
                        dataType : 'html',
                        afterClose: function(){
                            form.get(0).reset();
                            var hash = $( form ).data('hash');
                            $("#js-error_block_"+hash).hide();
                            $("#js-error_block_system_"+hash).hide();
                            // $(".form__icon").removeClass('fa-check');
                            $(".form__icon").hide('fa-check');
                            $('.js-inputfile-label span').html("&nbsp;");
                            $(form).find('input[type="text"]').val('');
                            $(form,'.js-rating-form').find('.ratingstar__item').attr('style','');
                            $(form).find('textarea').text('');
                            const selectedForm = $(form).find('.js-form-select');
                            selectedForm.find('option:selected').removeAttr('selected');
                            selectedForm.select2({
                                val: selectedForm.find('option:first').val()
                            });
                            $(form).find('input[type="checkbox"]').each(function (key, value) {
                                if (value.getAttribute('name') !== 'agreed') {
                                    $(value).removeAttr('checked');
                                }
                            });

                            $(form).find('.form__item--error').each(function (key,value) {
                                $(value).removeClass('form__item--error');
                            });
                        }
                    });

                } else {

                    var fancyboxDiv = $( form ).parents( '.js-form' );

                    // Чистка контейнера формы
                    fancyboxDiv.hide();
                    fancyboxDiv.html( '' );

                    // Выводим текст результирующей
                    fancyboxDiv.html( oResponse.html );
                    fancyboxDiv.show();

                    var ecommerceContainer = $(".js_ecommerce_data_for_buy");

                    if ( ecommerceContainer.length ){
                        // Покупка из формы купить в один клик
                        ecommerce.sendDataPurchaseFastBuy( ecommerceContainer.data("ecommerce_objects"), ecommerceContainer.data("order_id") );
                    }

                }

                setTimeout( function(){
                    $.fancybox.close();
                    reloadCaptchaByForm(form);
                }, 2000);
            }
        },
        beforeSend: function () {

        }
    });
    return false;
}

/**
 * Показываем прелоудер
 */
function showPreloader(form) {

    // отключаем кнопку отправить
    $( form ).find('[input="submit"]').attr('disabled', true);

    var $layout = $('.js-loader');

    if (!$layout.hasClass('b-loader--show'))
        $layout.toggleClass('b-loader--show');

}

/**
 * Скрываем прелоудер
 */
function hidePreloader(form) {

    // включаем кнопку отправить
    $( form ).find('[input="submit"]').attr('disabled', false);

    var $layout = $('.js-loader');

    if ($layout.hasClass('b-loader--show'))
        $layout.toggleClass('b-loader--show');

}

function maskInit(el){
    $('input', el).each(function(){
        switch ($(this).data('mask')){
            case "phone":
                $(this).inputmask("mask", {"mask": "+7 (999) 999-9999", "placeholder": "+7 (___) ___-____"});
                break;
        }
    });
}

function toggleShowPlaceholder(el) {
    $('input, textarea', el).each(function(){

        // обрабатываем только раз
        if ( $(this).data('js_placeholder') )
            return;

        if ($(this).prop('placeholder') && $(this).data('hide-placeholder')) {

            $(this).data('placeholder', $(this).prop('placeholder'));

            this.onmouseenter = function() {
                $(this).prop('placeholder', '');
            };

            this.onfocus = this.onmouseenter;

            this.onmouseleave = function() {
                if ($(this).is(':focus'))
                    return;
                $(this).prop('placeholder', $(this).data('placeholder'));
            };

            this.onblur = function() {
                // синтаксис именно такой для jq > 1.9
                if ($(this).filter(':hover').length)
                    return;
                $(this).prop('placeholder', $(this).data('placeholder'));
            };

        }

        // флаг "обработано"
        $(this).data('js_placeholder', 1);

    });
}

$(function () {
//Для checkbox
    $(document).on('click','.js-checkbox',function (event) {
        $(this).find('.js-handle-checkbox').attr('style','');
        changeSwitchAreaCheck(event);
    });
    $(document).on('click','.js-checkbox-label',function (event) {
        $(this).parent().find('.js-handle-checkbox').attr('style','');
        changeSwitchAreaCheck(event);
    });
    $('.js-checkbox .js-handle-checkbox').draggable({
        axis: 'x',
        containment: 'parent',
        stop: function(event, ui) {
            changeSwitchCheck(event,ui);
        }
    });

//Для radio
    $(document).on('click','.js-radio',function (event) {
        $(this).parent().find('.js-handle-radio').attr('style','');
        changeSwitchAreaCheck(event);
    });
    $(document).on('click','.js-radio-label',function (event) {
        $(this).parent().find('.js-handle-radio').attr('style','');
        changeSwitchAreaCheck(event);
    });
    $('.js-radio .js-handle-radio').draggable({
        axis: 'x',
        containment: 'parent',
        stop: function(event, ui) {
            changeSwitchRadio(event,ui);
        }
    });


    function changeSwitchCheck(event, ui) {
        var idInput = ui.helper.context.getAttribute('for');
        var posLeft = ui.position.left;
        var sNameId = "#"+idInput;
        if (posLeft>0)
            $(sNameId).prop('checked',true);
        else
            $(sNameId).prop('checked',false);
    }
    function changeSwitchAreaCheck(event) {
        var Span = event.currentTarget.getElementsByTagName('span').item(0);
        if (Span)
            Span.setAttribute('style','');
    }


    function changeSwitchRadio(event, ui) {
        var idInput = ui.helper.context.getAttribute('for');
        var aElem = $(ui.helper.context.parentElement.offsetParent);
        aElem.find('.js-handle-radio').attr('style','');
        var posLeft = ui.position.left;
        var sNameId = "#"+idInput;
        if (posLeft>0)
            $(sNameId).prop('checked',true);
        else {
            var aRadio = aElem.find('input[type="radio"]');
            for (var i=0;i<aRadio.length;i++) {
                if (!$(aRadio[i]).is(':checked')) {
                    $(aRadio[i]).prop('checked','true');
                    break;
                }
            }
            $(sNameId).prop('checked',false);
        }
    }


});

$(document).ready(function(){
    maskInit();
});
