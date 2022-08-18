$(function () {

    var hash = window.location.hash.substr(1);

    if (hash)
        location.hash = "#" + hash;

    var bDebug = false;

    if (bDebug) {

        console.log('Document rendered: %s', new Date());
    }//is debug

    $('.js_use_resize').each(function (index, el) {
        var thisTitle = $(this).find('img').attr('title');
        $(this).attr('data-caption', thisTitle);
    });

    $('.js_use_resize').fancybox({
        slideShow: false,
        fullScreen: false,
        thumbs: false
    });

    $('.js_use_inline').fancybox();

    $('.js_use_fancybox').fancybox();

    window.skewerConfigs = {
        oFotoramaConfig: {
            width: '100%',
            click: false,
            swipe: true,
            shadows: false,
            arrows: 'always',
            nav: 'thumbs',
            thumbwidth: 80,
            thumbheight: 55,
            loop: true,
            allowfullscreen: true,
            fit: "scaledown"
        }
    };

    $('body').on('click', '.js-callback', function () {
        let section = $(this).data('section');
        let objectId = $(this).data('idobj');
        let objectIdUrl = '';

        if (objectId) {
            objectIdUrl = '&objectId=' + objectId;
        }

        //Получаем ширину браузера и если она меньше чем минимальная, открываем новую страницу
        if (isMobile() && typeof (section) != 'undefined') {

            //если указана ссылка, то переходим по ней (актуально для формы покупки в 1 клик)
            if ($(this).attr('href') != '#') {
                document.location.href = $(this).attr('href');
            } else {
                document.location.href = "/?section_id=" + section + "&content_form=1" + objectIdUrl;
            }
            return true;
        }

        var ajaxForm = $(this).data('ajaxform');
        var formName = $(this).data('formname');
        var label = $(this).data('label');
        var link = $(this);

        var module = $(this).data('module');
        var cmd = $(this).data('cmd');
        var idObj = $(this).data('idobj');
        var count = 1;

        if (!module) module = 'Forms';
        if (!cmd) cmd = 'show';
        if (!idObj) idObj = 0;
        else {

            var context = $(this).closest(".js_goods_container");
            if (!context.length)
                context = '';
            var countInput = $(".js_count[data-id='" + idObj + "']", context);

            if (countInput.length)
                count = parseInt(countInput.val());

        }

        $.ajax('/ajax/ajax.php', {
            method: 'post',
            data: {
                moduleName: module,
                cmd: cmd,
                section: section,
                formName: formName,
                ajaxForm: ajaxForm,
                ajaxShow: 1,
                label: label,
                idObj: idObj,
                objectId: idObj,
                count: count,
                language: $('#current_language').val()
            },
            error: function (jqXHR, statusText, error) {

                var regex = /<a href="([^"]*)">([^<]*)<\/a>/g;
                var subst = '<a href="$1" target="_blank" onclick="setTimeout(function(){ window.location.reload(); }, 1000);">$2</a>';

                // Запрос заблокирован антивирусом
                if (jqXHR.status == 499) {
                    //Добавляем ссылке target="_blank"
                    var result = jqXHR.responseText.replace(regex, subst);
                    $.fancybox.open(result);
                }

            },
            success: function (mResponse) {

                if (!mResponse) return false;

                var oResponse = $.parseJSON(mResponse);

                var formDiv = $('#js-callbackForm');

                if (oResponse.css && oResponse.css.length) {
                    $(oResponse.css).each(function () {
                        if (!$('link [href="' + this.filePath + '"]').length) {
                            var fileRef = document.createElement("link");
                            fileRef.setAttribute("rel", "stylesheet");
                            fileRef.setAttribute("type", "text/css");
                            fileRef.setAttribute("href", this.filePath);
                            document.getElementsByTagName("head")[0].appendChild(fileRef);
                        }
                    });
                }

                if (oResponse.js && oResponse.js.length) {
                    $(oResponse.js).each(function () {
                        if (!$('script [src="' + this.filePath + '"]').length) {
                            var fileRef = document.createElement("script");
                            fileRef.setAttribute("type", "text/javascript");
                            fileRef.setAttribute("charset", "utf-8");
                            fileRef.setAttribute("src", this.filePath);
                            document.getElementsByTagName("head")[0].appendChild(fileRef);
                        }
                    });
                }

                var htmlForm = oResponse.html;
                if (oResponse.html.indexOf('data-check-use-js="1"')) {
                    var subResponse = oResponse.html.indexOf('</form>');
                    htmlForm = htmlForm.substring(0, subResponse - 1) + '<input type="hidden" name="useJs" value="1"/>' + htmlForm.substring(subResponse, htmlForm.length + 1);
                }

                formDiv.html(htmlForm);
                var formId = formDiv.find('form:first').attr('id');
                var jqForm = $('#' + formId);

                if (typeof formId != 'undefined') {

                    maskInit(formDiv);
                    toggleShowPlaceholder(formDiv);

                    updateFromValidator(formId);

                    /* calendar - инициализация календарика */
                    if (typeof $.datepicker !== 'undefined') {
                        $.datepicker.setDefaults({
                            dateFormat: 'dd.mm.yy'
                        });

                        $('.js_init_datepicker', '#' + formId).datepicker({
                            nextText: '',
                            prevText: ''
                        });
                    }
                }

                var max_width = link.data('js_max_width');
                var width_type = link.data('width-type') || 'px';

                if (!max_width) {
                    max_width = 9999;
                }
                else {
                    formDiv.css({ 'max-width': max_width + width_type });
                }

                var max_height = link.data('js_max_height');
                if (!max_height) {
                    max_height = 9999;
                } else {
                    formDiv.css({ 'max-height': max_height });
                }

                if (!idObj || (idObj && (count > 0))) {

                    $.fancybox.open({
                        src: formDiv,
                        type: 'inline',
                        opts: {
                            touch: false,
                            slideShow: false,
                            fullScreen: false,
                            thumbs: false,
                            afterShow: function () {

                                formDiv.find('.js-form-select').each(function () {
                                    var theme = 'default select2-container--fancybox';
                                    var options = {
                                        minimumResultsForSearch: Infinity,
                                        placeholder: $(this).data('placeholder'),
                                        theme: theme
                                    };

                                    $(this).select2(options);

                                });

                                $(".js-callback").blur();
                            },
                            beforeClose: function () {
                                $('.js-form-select').select2('close');
                            },
                            afterClose: function () {
                                reloadCaptchaByForm(jqForm);
                                formDiv.text('');
                            },
                            onInit: function () {
                                formDiv.show();
                            },
                            onClosed: function () {
                                formDiv.hide();
                                formDiv.text('');
                            }
                        }
                    });

                    let oRating = new Rating('js-rating-form', false);
                    oRating.initRatings();

                    $(".js_calendar").datepicker({
                        nextText: '',
                        prevText: ''
                    });

                } else alert('Введено неправильное количество товара');

                return true;
            }
        });

        return false;
    });

    $('.js-gallery_resize').fancybox(
        $.extend(commonFancyBoxConfig, {
            helpers: {
                title: {
                    type: 'inside'
                }
            },
            arrows: true,
            nextClick: true,
            mouseWheel: true,
            infobar: true,
            // transitionEffect:  $('.js-get-data').data('fancyboxTransitionEffect'),
            protect: $('.js-get-data').data('fancyboxProtect')
        })
    );

    if ($('.l-header').length) {
        let firstScreen = $('.l-header').height() - 100;
        $(window).scroll(function () {
            if ($(this).scrollTop() >= (firstScreen)) {
                $('.js-fixed-menu').addClass('b-fixed-menu--on');
            }
            else {
                $('.js-fixed-menu').removeClass('b-fixed-menu--on');
            }
        });
    }

    $(".b-tooltip").each(function (key, value) {
        var content = $(value).html();
        var tooltip_id = $(value).attr("tooltip_id");
        var tooltip_content = $("#js_tooltip_" + tooltip_id).html();

        if (tooltip_content) {
            var new_content = ' <span class="tooltip__text">' + content + '</span><span class="tooltip__wrap">' + tooltip_content + '</span>';
            $(value).html(new_content);
        }

    });

    $.fancybox.defaults.hash = false;
});

// Конфиг общих настроек для fancybox
var commonFancyBoxConfig = {

    beforeShow: function () {
        var imgAlt = $(this.element).find("img").attr("alt");
        var dataAlt = $(this.element).data("alt");
        if (imgAlt) {
            $(".fancybox-image").attr("alt", imgAlt);
        } else if (dataAlt) {
            $(".fancybox-image").attr("alt", dataAlt);
        }
    }
};

function parseGetParams(url) {
    var $_GET = {};
    if (url == undefined)
        url = window.location.search;
    var __GET = url.substring(url.indexOf('?') + 1).split("&");
    for (var i = 0; i < __GET.length; i++) {
        var getVar = __GET[i].split("=");
        if (getVar[1] != undefined)
            $_GET[getVar[0]] = getVar[1];
    }
    return $_GET;
}

function reloadImg(obj) {

    if (!obj) return false;
    obj = $(obj);

    var src;
    var base_src = obj.attr('src');

    if (typeof (base_src) == 'undefined' || !base_src) return false;

    var pos = base_src.indexOf('?');

    if (pos >= 0)
        src = base_src.substr(0, pos);
    else
        src = base_src;

    var hash = '';
    var form = $(obj).parents('form');
    if (form.length && form.attr('id'))
        hash = form.attr('id').substr(5);
    else {
        var params = parseGetParams(base_src);
        if (params['h'] != undefined)
            hash = params['h'];
    }

    var date = new Date();
    obj.attr('src', src + '?h=' + hash + '&v=' + date.getTime());

    return false;
}

/**
 * Обновит каптчу формы
 * @param {HTMLElement|jquery} form
 */
function reloadCaptchaByForm(form) {
    var jqForm = $(form),
        imageCaptcha = $('.img_captcha', jqForm)
        ;

    // Если форма имеет каптчу -> обновим её
    if (imageCaptcha.length) {
        reloadImg(imageCaptcha);
    }


}

/**
 * Если ширина экрана меньше опред.величины, то считаем устройство мобильным
 * @return {Boolean}
 */
function isMobile() {
    return window.innerWidth < $('#js-adaptive-min-form-width').val();
}
