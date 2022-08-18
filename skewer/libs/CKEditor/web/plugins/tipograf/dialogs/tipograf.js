CKEDITOR.dialog.add('tipografDialog', function (editor) {

    var html_block = '<div class="js_tipograf"><textarea style="border: 1px solid #000000" rows="20" cols="90" class="js_text_tipograf"></textarea></div>';
    var _dialog, _element;

    return {
        title: editor.lang.tipograf.title,
        minWidth: 600,
        minHeight: 300,
        contents: [
            {
                id: 'tab-basic',
                label: editor.lang.tipograf.title,

                elements: [
                    {
                        type: 'html',
                        html: html_block,
                        id: 'content_block'

                    }
                ]
            }
        ],

        buttons: [
            CKEDITOR.dialog.okButton, CKEDITOR.dialog.cancelButton
        ],

        onOk: function () {
            var text ='';
            var elements = document.getElementsByClassName('js_text_tipograf');
            for (var i = 0; i < elements.length; i++) {
                if (elements[i].value != '')
                    text = elements[i].value;
            }

            if (text != '') {
                result = loadXMLDoc(text);
                var id_block = document.getElementById('js_cur_block').value;

                if (editor.id == id_block) {
                    editor.insertHtml(result);
                }
            }

        },

        onShow: function () {

            /*Удаляем скрытий инпут хранящий инфу о текущем визивиге*/
            var el = document.getElementById('js_cur_block');
            if (el !== null) {
                el.remove();
            }

            /*Создадим и добавим новый скрытый инпут с инфой о текущем визивиге*/
            var input = document.createElement('input');
            input.type = "hidden";
            input.id = "js_cur_block";
            input.value = editor.id;

            var elements = document.getElementsByClassName('js_tipograf');
            elements[0].appendChild(input);

            var selection = editor.getSelection();
            var element = selection.getStartElement();

            _dialog = this;
            var elements = document.getElementsByClassName('js_text_tipograf');
            for (var i = 0; i < elements.length; i++) {
                elements[i].value = '';
            }
            _element = element;

            // заносим контент из атрибутов в форму
            this.setupContent(_element);
        }

    };

    function loadXMLDoc(text) {

        var xmlhttp = new XMLHttpRequest();
        var result = text;
        text = 'text=' + encodeURIComponent(text);

        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == XMLHttpRequest.DONE) {
                if (xmlhttp.status == 200) {
                    result = xmlhttp.responseText;
                } else {
                    alert('Произошла ошибка!');
                }
            }
        };

        xmlhttp.open("POST", "/tipograf/", false);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(text);

        return result;
    }

});