(function () {

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

    CKEDITOR.plugins.add('tipograf_all_text', {
        icons: 'tipograf_all_text',
        lang: ['en', 'ru', 'de'],

        init: function (editor) {
            var cmd = editor.addCommand('tipograf_all_text', {
                exec: function (editor) {
                    var text = editor.getData();
                    if (text != '') {
                        var result = loadXMLDoc(text);
                        editor.setData(result);
                    }
                }
            });

            editor.ui.addButton('tipograf_all_text', {
                label: editor.lang.tipograf_all_text.title,
                command: 'tipograf_all_text',
            });
        },
    });

})();

