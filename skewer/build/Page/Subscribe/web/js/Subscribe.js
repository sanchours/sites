$(function(){

    $(".js-formsubscribe").find("#js-btncanc").click(function(){

        var oForm = $(this).parents('form');

        if ( oForm.valid() ){

            var sEmail = oForm.find("input[name='email']").val();

            $.post('/ajax/ajax.php',{ moduleName:'Subscribe', cmd: 'unsubscribe_ajax', email: sEmail, language: $('#current_language').val() }, function(mResponse){

                var oResponse = $.parseJSON( mResponse );

                if ( oResponse.data.out ){
                    alert('Вы успешно отписались от рассылки');
                    window.location.reload();
                }
            });
        }
    })
});
