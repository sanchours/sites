$(function(){

    $('body').on('click', '.js-poll', function(){

        var oText = $(this).closest(".js-formopros__wrap");
        var iPoll = oText.find("#js-poll").val();
        var iAnswer = oText.find("input.js-answer:checked").val();

        if ( !iAnswer ) {
            alert('Не выбран вариант ответа');
            return false;
        }

        $.post('/ajax/ajax.php',{ moduleName:'Poll', cmd: 'vote_ajax', poll: iPoll, answer: iAnswer, language: $('#current_language').val() }, function(mResponse){

            var oResponse = $.parseJSON( mResponse );
            oText.parent().html(oResponse.data.out);
        });

        return true;

    });

    $('body').on('click', '.js-poll-results', function(){

        var oText = $(this).closest(".js-formopros__wrap");
        var iPoll = oText.find("#js-poll").val();

        $.post('/ajax/ajax.php',{ moduleName:'Poll', cmd: 'vote_ajax', poll: iPoll, language: $('#current_language').val() }, function(mResponse){

            var oResponse = $.parseJSON( mResponse );
            oText.parent().html(oResponse.data.out);
        });

        return false;
    });
});
