$(function () {
    var forms = $( '.js-form').find('form');

    $.each(forms,function (index,value) {
        if ($(value).data('checkUseJs')) {
            $(value).append('<input type="hidden" name="useJs" value="1"/>');
        }
    });

    $(document).on('submit', $('.js-form'), function (objectSubmit) {
        if ($(objectSubmit.target).data('checkUseJs')) {
            $(objectSubmit.target).append('<input type="hidden" name="useJs" value="1"/>');
        }
    });
});