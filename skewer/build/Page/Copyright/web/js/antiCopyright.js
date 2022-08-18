$(function(){

    var bCanCopy = true;

    $(document).on("copy", function(e){

        var hiddenDiv, bInsertInBegin, oSelection, oRange, docFragContent, commonParent;


        // Для элементов форм возвращет скопированный текст
        if ( ['TEXTAREA', 'INPUT'].indexOf(e.target.tagName) != -1)
            return ;

        // Защита от многократного запуска
        if (!bCanCopy)
            return ;

        bInsertInBegin = false;

        if(window.getSelection){ // Все браузеры, кроме IE8-

            oSelection = window.getSelection();

            if (oSelection.rangeCount < 0)
                return ;

            if ($.trim(oSelection.toString()).length < 1)
                return ;

            oRange = oSelection.getRangeAt(0);
            docFragContent = oRange.cloneContents();

            hiddenDiv = document.createElement('div');
            hiddenDiv.innerHTML = $(".js_copyright_templatedText").html();

            if (!bInsertInBegin)
                $(hiddenDiv).prepend(docFragContent);
            else
                $(hiddenDiv).append(docFragContent);

            // Вставляем в общего предка, чтобы сохранилось форматирование выделенного текста
            if (oRange.commonAncestorContainer.nodeType == Node.TEXT_NODE){
                $.each($(oRange.commonAncestorContainer).parents(), function(key, elem){
                    if ( (commonParent == undefined) && (elem.nodeType == Node.ELEMENT_NODE) )
                        commonParent = elem;
                });
            } else
                commonParent = oRange.commonAncestorContainer;

            if (commonParent == undefined)
                return ;

            commonParent.appendChild(hiddenDiv);
            oSelection.selectAllChildren(hiddenDiv);
            bCanCopy = false;
            // Запустится ассинхронно, через ~4ms
            window.setTimeout(function(){
                // Удаляем ранее вставленный элемент
                hiddenDiv.remove();
                // Восстанавливаем исходное выделение
                oSelection.removeAllRanges();
                oSelection.addRange(oRange);
                bCanCopy = true;

            }, 0);

        }

    });

});
