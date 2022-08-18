$(function() {

    $(function() {
        $('.js-inputfile-placeholder').each(function() {
            var str = $(this).html();
            var placeholderLength =  'placeholder="'.length;
            var startPos = str.indexOf('placeholder="') + placeholderLength;
            var endPos = str.indexOf('"', startPos + 1);
            var resultStr =  str.substring(startPos, endPos);

            if (endPos != -1) $(this).html(resultStr).addClass('form__label-file-visible');
        })
    });// ready

    //select
    $(document).find('.js-form-select').each(function (){

        var placeholder = ( $(this).attr('placeholder') ) ? $(this).attr('placeholder') : $(this).data('select-placeholder');

        var theme = 'form';
        var options = {
            minimumResultsForSearch: Infinity,
            placeholder: placeholder,
            theme: theme
        };
        $(this).select2(options);
    });

});// ready