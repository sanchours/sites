/*
 Author	: Michael Janea (https://facebook.com/mbjanea)
 Version	: 1.2
 */

if (document.querySelectorAll('#js_class_keeper').length>0){
	var html_block = '';
} else {
	var html_block = '<input type="hidden" id="js_class_keeper"/><input type="hidden" id="js_color_keeper"/><input type="hidden" id="js_size_keeper"/><input type="hidden" id="js_img_class_keeper"/>';
}

/**
 * Удаление конкретного класса
 * @param className
 */
function removeClass(className){

	document.getElementById('js_class_keeper').value = document.getElementById('js_class_keeper').value.replace(className,'');

}

/**
 * Добавление класса
 * @param className
 */
function addClass(className){

	removeClass(className);

	document.getElementById('js_class_keeper').value = document.getElementById('js_class_keeper').value+' '+className;

}

/**
 * Очистка контейнера классов
 */
function clearClass(){
	document.getElementById('js_class_keeper').value = '';
}

var fontawesome = '';
var fontawesomeIcons = '';

for ( var faClass in window['___FONT_AWESOME___']['styles'] ){

	if ( faClass == 'fa' )
		continue;

    for (var key in window['___FONT_AWESOME___']['styles'][faClass]) {
        var newTitle = '';
        var ctr = 0;
        var title = key;
        title = title.split(' ');
        for (var x = 0; x < title.length; x++) {
            ctr++;
            newTitle += ctr == 3 ? '<br />' : '';
            newTitle += title[x] + ' ';
            ctr = ctr == 3 ? 0 : ctr
        }
        // newTitle

        fontawesomeIcons += '<a href="#" onclick="klik(this);return false;" title="' + faClass + ' fa-' + key + '">' +
            '<span style="font-size:30px;" class="' + faClass + ' fa-' + key + '"></span>' +
            '<div style="white-space: nowrap; text-overflow: ellipsis;overflow: hidden;">' + newTitle + '</div></a>';

    }

}

function hexToRgb(hex) {
	// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
	var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
	hex = hex.replace(shorthandRegex, function(m, r, g, b) {
		return r + r + g + g + b + b;
	});

	var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	return 'rgb('+parseInt(result[1], 16)+','+parseInt(result[2], 16)+','+parseInt(result[3], 16)+')';
}

function klik(el) {
	document.getElementsByClassName('fontawesomeClass')[0].getElementsByTagName('input')[0].value = el.getAttribute('title');

	var elements = document.getElementsByClassName('active_fontawesome_element');
	while(elements.length > 0){
		elements[0].classList.remove('active_fontawesome_element');
	}
	el.className += 'active_fontawesome_element';

	//Удалим предыдущий класс
	removeClass(document.getElementById('js_img_class_keeper').value);

	//Пишем во временный контейнер класс выбранного элемента
	var className = el.childNodes[0].dataset.prefix + ' fa-' + el.childNodes[0].dataset.icon;
	addClass(className);

	//Запишем во временное хранилище
	document.getElementById('js_img_class_keeper').value = className;

};

function searchIcon(val,editor_id) {
	var aydi = document.getElementsByClassName('js_main_field_'+editor_id)[0];
	var klases = aydi.getElementsByTagName('a');
	for (var i = 0, len = klases.length, klas, klasNeym; i < len; i++) {
		klas = klases[i];
		klasNeym = klas.getAttribute('title');
		if (klasNeym && klasNeym.indexOf(val) >= 0) {
			klas.style.display = 'block'
		} else {
			klas.style.display = 'none'
		}
	}
};

/*Сборщик данных цвета*/
function setSpanColor(color,editor_id) {

	document.getElementById('js_color_keeper').value = color;

	document.getElementsByClassName('color_field_'+editor_id)[0].getElementsByTagName('input')[0].value = color;
};

/*Сборщик данных с галочек*/
function setCheckboxes(editor_id) {

	/*Класс вращения*/
	if (document.querySelectorAll('span.spinning_'+editor_id)[0].getElementsByTagName('input')[0].checked){
		addClass('fa-spin');
	} else {
		removeClass('fa-spin');
	}

	/*Класс фиксированной ширины*/
	if (document.querySelectorAll('span.fixedWidth_'+editor_id)[0].getElementsByTagName('input')[0].checked){
		addClass('fa-fw');
	} else {
		removeClass('fa-fw');
	}

	/*Класс рамки*/
	if (document.querySelectorAll('span.bordered_'+editor_id)[0].getElementsByTagName('input')[0].checked){
		addClass('fa-border');
	} else {
		removeClass('fa-border');
	}

	/*Класс ротация*/
	/*Удалим другие классы ротации*/
	removeClass('fa-rotate-90');
	removeClass('fa-rotate-180');
	removeClass('fa-rotate-270');
	removeClass('fa-flip-horizontal');
	removeClass('fa-flip-vertical');

	/*Запишем новый*/
	addClass(document.querySelectorAll('select.flippedRotation_'+editor_id)[0].value);

};

function in_array(needle, haystack) {
	for (var i in haystack) {
		if (haystack[i] == needle) return true
	}
	return false
};
CKEDITOR.dialog.add('fontawesomeDialog', function(editor) {
	return {
		title: 'Insert Font Awesome',
		minWidth: 600,
		minHeight: 400,
		resizable: false,
		contents: [{
			id: 'insertFontawesome',
			label: 'insertFontawesome',
			elements: [{
				type: 'hbox',
				widths: ['50%', '50%'],
				children: [{
					type: 'hbox',
					widths: ['75%', '25%'],
					children: [{
						type: 'text',
						id: 'colorChooser',
						className: 'colorChooser color_field_'+editor.id,
						label: 'Color',
						editor_id: editor.id,
						setup: function(widget) {
                            var val = '',
                            	color = widget.data.color.replace('#','');

                            if ( color ){
                                val = '#' . color;
							}

							this.setValue(val);
							document.getElementById('js_color_keeper').value = val;
						},
						commit: function(widget) {
							widget.setData('color', document.getElementById('js_color_keeper').value)
						}
					}, {
						type: 'button',
						label: 'Select',
						style: 'margin-top:1.35em',
						editor_id: editor.id,
						onClick: function() {
							editor.getColorFromDialog(function(color) {
								document.getElementsByClassName('colorChooser')[0].getElementsByTagName('input')[0].value = color;
								setSpanColor(color,editor.id)
							}, this)
						}
					}]
				}, {
					type: 'text',
					id: 'size',
					className: 'size size_'+editor.id,
					label: 'Size',
					editor_id: editor.id,
					setup: function(widget) {
						this.setValue(widget.data.size || '');
					},
					onChange: function() {
						document.getElementById('js_size_keeper').value = document.getElementsByClassName('size_'+editor.id)[0].getElementsByTagName('input')[0].value;
					},
					commit: function(widget) {
						widget.setData('size', document.getElementById('js_size_keeper').value)
					}
				}]
			}, {
				type: 'hbox',
				widths: ['25%', '25%', '25%', '25%'],
				children: [{
					type: 'checkbox',
					id: 'spinning',
					className: 'spinning cke_dialog_ui_checkbox_input spinning_'+editor.id,
					label: 'Spinning',
					value: 'true',
					onClick: function() {
						setCheckboxes(editor.id)
					}
				}, {
					type: 'checkbox',
					id: 'fixedWidth',
					className: 'fixedWidth cke_dialog_ui_checkbox_input fixedWidth_'+editor.id,
					label: 'Fixed Width',
					value: 'true',
					onClick: function() {
						setCheckboxes(editor.id)
					}
				}, {
					type: 'checkbox',
					id: 'bordered',
					className: 'bordered cke_dialog_ui_checkbox_input bordered_'+editor.id,
					label: 'Bordered',
					value: 'true',
					onClick: function() {
						setCheckboxes(editor.id)
					}
				}, {
					type: 'select',
					id: 'flippedRotation',
					className: 'flippedRotation cke_dialog_ui_checkbox_input flippedRotation_'+editor.id,
					label: 'Flipping and Rotating',
					items: [
						['Normal', ''],
						['Rotate 90', 'fa-rotate-90'],
						['Rotate 180', 'fa-rotate-180'],
						['Rotate 270', 'fa-rotate-270'],
						['Flip Horizontal', 'fa-flip-horizontal'],
						['Flip Vertical', 'fa-flip-vertical']
					],
					onClick: function() {
						setCheckboxes(editor.id)
					}
				}]
			}, {
				type: 'text',
				id: 'fontawesomeSearch',
				className: 'fontawesomeSearch cke_dialog_ui_input_text',
				label: 'Search',
				editor_id: editor.id,
				onKeyUp: function(e) {
					searchIcon(e.sender.$.value,editor.id)
				}
			}, {
				type: 'text',
				id: 'fontawesomeClass',
				className: 'fontawesomeClass',
				style: 'display:none',
				editor_id: editor.id,
				setup: function(widget) {
					var klases = '';

					/*Установка служебных классов при открытии диалога с уже добавленного элемента*/
					if (widget.data.class != '') {
						klases = widget.data.class;
						klases = klases.split(' ');

						/*Очищаем иконке выбранность*/
						var elements = document.getElementsByClassName('active_fontawesome_element');
						while(elements.length > 0){
							elements[0].classList.remove('active_fontawesome_element');
						}


                        var iconClassName,prefix,fullClassName;

                        iconClassName = getIconNameByClassName(widget.data.class);

                        if ( iconClassName ){
                            prefix = extractPrefix(widget.data.class);
							fullClassName = prefix + ' ' + iconClassName;
                            addClass(fullClassName);
                            document.getElementById('js_img_class_keeper').value = fullClassName;
                            document.getElementsByClassName('js_main_field_'+editor.id)[0].querySelectorAll('[title="' + fullClassName+'"]')[0].className += 'active_fontawesome_element';
						}

						if (in_array('fa-border', klases)){
							addClass('fa-border');
							document.querySelectorAll('span.bordered_'+editor.id)[0].getElementsByTagName('input')[0].checked = true;
						}
						if (in_array('fa-fw', klases)){
							addClass('fa-fw');
							document.querySelectorAll('span.fixedWidth_'+editor.id)[0].getElementsByTagName('input')[0].checked = true;
						}
						if (in_array('fa-spin', klases)){
							addClass('fa-spin');
							document.querySelectorAll('span.spinning_'+editor.id)[0].getElementsByTagName('input')[0].checked = true;
						}
						if (in_array('fa-rotate-90', klases)){
							addClass('fa-rotate-90');
							document.querySelectorAll('select.flippedRotation_'+editor.id)[0].value='fa-rotate-90';
						}
						if (in_array('fa-rotate-180', klases)){
							addClass('fa-rotate-180');
							document.querySelectorAll('select.flippedRotation_'+editor.id)[0].value='fa-rotate-180';
						}
						if (in_array('fa-rotate-270', klases)){
							addClass('fa-rotate-270');
							document.querySelectorAll('select.flippedRotation_'+editor.id)[0].value='fa-rotate-270';
						}

						if (in_array('fa-flip-horizontal', klases)){
							addClass('fa-flip-horizontal');
							document.querySelectorAll('select.flippedRotation_'+editor.id)[0].value='fa-flip-horizontal';
						}
						if (in_array('fa-flip-vertical', klases)){
							addClass('fa-flip-vertical');
							document.querySelectorAll('select.flippedRotation_'+editor.id)[0].value='fa-flip-vertical';
						}
					}

				},
				commit: function(widget) {

					var out = document.getElementById('js_class_keeper').value.replace(/\s{2,}/g, ' ');

					widget.setData('class', out);

				}
			}, {
				type: 'html',
				html: '<div id="fontawesome" class="js_main_field_'+editor.id+'">' + fontawesomeIcons + '</div>'
			}, {
				type: 'html',
				html: html_block,
				id: 'html_block'
			}, {
				/*Псевдо элемент для валидации при нажатии ОК*/
				type: 'html',
				html: '',
				id: 'pseudo_validator',
				validate: function(){
					if (!document.getElementById('js_img_class_keeper').value) {
						alert("Выбери иконку!");
						return false;
					} else {
						return true;
					}
				}
			}
			]

		}],
		onShow: function(){
			clearClass();

			if (!document.getElementById('js_color_keeper').value)
				document.getElementById('js_color_keeper').value = '#000';

			document.getElementById('js_size_keeper').value = '20px';
		},
		onOk: function() {

			// glyphs = document.getElementById('fontawesome');
			// glyphs = glyphs.getElementsByTagName('a');
			// for (i = 0; i < glyphs.length; i++) {
			// 	glyphs[i].firstChild.className = glyphs[i].getAttribute('title');
			// 	glyphs[i].className = '';
			// 	glyphs[i].style.display = '';
			// 	glyphs[i].getElementsByTagName('span')[0].style.color = ''
			// }

			/*Очищаем контейнер с иконкой*/
			document.getElementById('js_img_class_keeper').value = '';

		}
	}
});