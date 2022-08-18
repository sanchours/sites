CKEDITOR.dialog.add( 'contenteditorDialog', function( editor ) {

	var html_block = '<div class="js_content_blocks"></div>';
	html_block = html_block+'<div block_name="none" style="display:none" class="js_keeper_block">none</div>';

    window.content_generator = {
    	kepper: ""
	};

	loadXMLDoc(editor.langCode);

	return {

		title: editor.lang.contenteditor.content_editor,
		minWidth: 700,
		minHeight: 500,

		contents: [
			{
				id: 'tab-basic',
				label: 'Basic Settings',

				// The tab content.
				elements: [
					{
						type: 'html',
						html: html_block,
						id: 'content_block'
					}
				]
			}
		],

		onShow: function() {

			/*Удаляем скрытий инпут хранящий инфу о текущем визивиге*/
			var el = document.getElementById('js_cur_block');
			if (el!==null){
				el.remove();
			}

			/*Создадим и добавим новый скрытый инпут с инфой о текущем визивиге*/
			var elements = document.getElementsByClassName('js_content_blocks');
			var i;

			var body = document.getElementsByTagName('body');

			var input = document.createElement('input');
			input.type = "hidden";
			input.id = "js_cur_block";
			input.value = editor.id;

			body[0].appendChild(input);

			var selection = editor.getSelection();

			var element = selection.getStartElement();

			if ( element )
				element = element.getAscendant( 'contenteditor', true );

			this.element = element;

			var elements = document.getElementsByClassName('tab_title');
			var i;
			for (i = 0; i < elements.length; i++) {
				elements[i].style.backgroundColor = "white";
			}

			var elements = document.getElementsByClassName('imgbox__item');
			var i;
			for (i = 0; i < elements.length; i++) {
				elements[i].style.backgroundColor = "white";
			}

			var elements = document.getElementsByClassName('js_one_block');
			var i;
			for (i = 0; i < elements.length; i++) {
				elements[i].style.display = "none";
			}

			// оцищаем выбранное на предыдущем шаге
            window.content_generator.kepper = '';
			clearElementHighLight();

			operateGroup('caption');

			if ( !this.insertMode )
				this.setupContent( this.element );
		},
		onOk: function() {
			var id_block = document.getElementById('js_cur_block').value;

			/*Достаем из скрытого инпута ID визивига который обрабатывется и кладем в него контент*/
			if (editor.id==id_block){
				editor.insertHtml(window.content_generator.kepper);
			}

		}
	};
});

/*подтягивает шаблон по имени*/
function checkDiv(name,lang){
	loadXMLDocOne(name,false,lang);
}

function insertDiv(name,lang){
	loadXMLDocOne(name,true,lang);
}

function clearElementHighLight() {

	var elements = document.getElementsByClassName('imgbox__item');
	var i;
	for (i = 0; i < elements.length; i++) {
		elements[i].style.border = "1px solid #f2f2f2";
	}

}

function loadXMLDoc(lang) {
	var xmlhttp = new XMLHttpRequest();

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
			if (xmlhttp.status == 200) {
				var elements = document.getElementsByClassName('js_content_blocks');
				var i;
				for (i = 0; i < elements.length; i++) {
					elements[i].innerHTML = xmlhttp.responseText;
				}
			}
			else if (xmlhttp.status == 400) {
				alert(editor.lang.contenteditor.error_400);
			}
			else {
				alert(editor.lang.contenteditor.not_200_error);
			}
		}
	};

	xmlhttp.open("GET", "/contentgenerator/?lang="+lang, true);
	xmlhttp.send();
}

function loadXMLDocOne(name,bInsert,lang) {
	var xmlhttp = new XMLHttpRequest();

	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
			if (xmlhttp.status == 200) {

				clearElementHighLight();

				var elements = document.querySelectorAll("[name='"+name+"']");
				var i;
				for (i = 0; i < elements.length; i++) {
					elements[i].style.border = "1px solid #b6c3cf";
				}

                window.content_generator.kepper = xmlhttp.responseText;

				if (bInsert===true){

					elements = document.getElementsByClassName('cke_dialog_ui_button_ok');
					for (i = 0; i < elements.length; i++) {
						if (isHidden(elements[i])!==true)
							elements[i].click();
					}
				}
			}
			else if (xmlhttp.status == 400) {
				alert(editor.lang.contenteditor.error_400);
			}
			else {
				alert(editor.lang.contenteditor.not_200_error);
			}
		}
	};

	xmlhttp.open("GET", "/contentgenerator/name/?name="+name+"&lang="+lang, true);
	xmlhttp.send();
}

function operateGroup(group_name){

	var elements = document.getElementsByClassName('tab_title');
	var i;
	for (i = 0; i < elements.length; i++) {
		elements[i].style.backgroundColor = "white";
	}

	var elements = document.querySelectorAll("[group_name='"+group_name+"']");
	var i;
	for (i = 0; i < elements.length; i++) {
		elements[i].style.background = "#f8f8f8";
	}
	/*Скроем все шаблоны*/
	var elements = document.getElementsByClassName('js_one_block');
	var i;
	for (i = 0; i < elements.length; i++) {
		elements[i].style.display = "none";
	}

	var elements = document.querySelectorAll("[group='"+group_name+"']");
	var i;
	for (i = 0; i < elements.length; i++) {
		elements[i].style.display = "block";
	}
}

function isHidden(el) {
	return (el.offsetParent === null)
}