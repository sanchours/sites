/*
 Author	: Michael Janea (https://facebook.com/mbjanea)
 Version	: 1.2
 */
function RGBToHex(color)
{
	color = color.replace(/\s/g,"");
	var aRGB = color.match(/^rgb\((\d{1,3}[%]?),(\d{1,3}[%]?),(\d{1,3}[%]?)\)$/i);
	if(aRGB) {
		color = '';
		for (var i=1;  i<=3; i++) color += Math.round((aRGB[i][aRGB[i].length-1]=="%"?2.55:1)*parseInt(aRGB[i])).toString(16).replace(/^(.)$/,'0$1');
	} else color = color.replace(/^#?([\da-f])([\da-f])([\da-f])$/i, '$1$1$2$2$3$3');
	return '#'+color;
}

function buildStyle(color, fontSize){

    var style = '';

    color = color.replace("#", '');

    if ( color ){
        style += 'color:' + color + ';';
	}

	if ( fontSize ){
    	style += 'font-size:' + parseInt(fontSize) + 'px;';
	}

	return style;

}

function extractPrefix(str){

	var prefix;

    if ( str.indexOf('fab') != -1 ){
        prefix = 'fab';
    } else if ( str.indexOf('far') != -1 ){
        prefix = 'far';
    } else if ( str.indexOf('fas') != -1 ){
        prefix = 'fas';
    } else {
        prefix = 'fa';
    }

    return prefix;
}

function extractParamFromClass(className){

	var regex = /fa-rotate-(\d*)/gm;

    var m,
		match = [];

    while ((m = regex.exec(className)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regex.lastIndex) {
            regex.lastIndex++;
        }

        match.push(m);
    }

    return match[0]?match[0][1]:'';

}

function getIconNameByClassName(className){

    var classes = className.split(' ');

    classes = classes.map( function(val){
        return val.trim();
    } );

    return classes.find(isIconClass);

}

function isIconClass(class_name){

    var bRes = false;

    class_name = class_name.replace('fa-', '');

    for ( var prefix in window['___FONT_AWESOME___']['styles'] ){
        if ( window['___FONT_AWESOME___']['styles'][prefix][class_name] ){
            bRes = true;
            break;
        }
    }


    return bRes;

}

CKEDITOR.plugins.add('fontawesome', {
	requires: 'widget',
	icons: 'fontawesome',
    onLoad: function( editor ) {
        CKEDITOR.addCss( 'svg:not(:root).svg-inline--fa {\n' +
            '  overflow: visible; }\n' +
            '\n' +
            '.svg-inline--fa {\n' +
            '  display: inline-block;\n' +
            '  font-size: inherit;\n' +
            '  height: 1em;\n' +
            '  overflow: visible;\n' +
            '  vertical-align: -.125em; }\n' +
            '  .svg-inline--fa.fa-lg {\n' +
            '    vertical-align: -.225em; }\n' +
            '  .svg-inline--fa.fa-w-1 {\n' +
            '    width: 0.0625em; }\n' +
            '  .svg-inline--fa.fa-w-2 {\n' +
            '    width: 0.125em; }\n' +
            '  .svg-inline--fa.fa-w-3 {\n' +
            '    width: 0.1875em; }\n' +
            '  .svg-inline--fa.fa-w-4 {\n' +
            '    width: 0.25em; }\n' +
            '  .svg-inline--fa.fa-w-5 {\n' +
            '    width: 0.3125em; }\n' +
            '  .svg-inline--fa.fa-w-6 {\n' +
            '    width: 0.375em; }\n' +
            '  .svg-inline--fa.fa-w-7 {\n' +
            '    width: 0.4375em; }\n' +
            '  .svg-inline--fa.fa-w-8 {\n' +
            '    width: 0.5em; }\n' +
            '  .svg-inline--fa.fa-w-9 {\n' +
            '    width: 0.5625em; }\n' +
            '  .svg-inline--fa.fa-w-10 {\n' +
            '    width: 0.625em; }\n' +
            '  .svg-inline--fa.fa-w-11 {\n' +
            '    width: 0.6875em; }\n' +
            '  .svg-inline--fa.fa-w-12 {\n' +
            '    width: 0.75em; }\n' +
            '  .svg-inline--fa.fa-w-13 {\n' +
            '    width: 0.8125em; }\n' +
            '  .svg-inline--fa.fa-w-14 {\n' +
            '    width: 0.875em; }\n' +
            '  .svg-inline--fa.fa-w-15 {\n' +
            '    width: 0.9375em; }\n' +
            '  .svg-inline--fa.fa-w-16 {\n' +
            '    width: 1em; }\n' +
            '  .svg-inline--fa.fa-w-17 {\n' +
            '    width: 1.0625em; }\n' +
            '  .svg-inline--fa.fa-w-18 {\n' +
            '    width: 1.125em; }\n' +
            '  .svg-inline--fa.fa-w-19 {\n' +
            '    width: 1.1875em; }\n' +
            '  .svg-inline--fa.fa-w-20 {\n' +
            '    width: 1.25em; }\n' +
            '  .svg-inline--fa.fa-pull-left {\n' +
            '    margin-right: .3em;\n' +
            '    width: auto; }\n' +
            '  .svg-inline--fa.fa-pull-right {\n' +
            '    margin-left: .3em;\n' +
            '    width: auto; }\n' +
            '  .svg-inline--fa.fa-border {\n' +
            '    height: 1.5em; }\n' +
            '  .svg-inline--fa.fa-li {\n' +
            '    width: 2em; }\n' +
            '  .svg-inline--fa.fa-fw {\n' +
            '    width: 1.25em; }\n' +
            '\n' +
            '.fa-layers svg.svg-inline--fa {\n' +
            '  bottom: 0;\n' +
            '  left: 0;\n' +
            '  margin: auto;\n' +
            '  position: absolute;\n' +
            '  right: 0;\n' +
            '  top: 0; }\n' +
            '\n' +
            '.fa-layers {\n' +
            '  display: inline-block;\n' +
            '  height: 1em;\n' +
            '  position: relative;\n' +
            '  text-align: center;\n' +
            '  vertical-align: -.125em;\n' +
            '  width: 1em; }\n' +
            '  .fa-layers svg.svg-inline--fa {\n' +
            '    -webkit-transform-origin: center center;\n' +
            '            transform-origin: center center; }\n' +
            '\n' +
            '.fa-layers-text, .fa-layers-counter {\n' +
            '  display: inline-block;\n' +
            '  position: absolute;\n' +
            '  text-align: center; }\n' +
            '\n' +
            '.fa-layers-text {\n' +
            '  left: 50%;\n' +
            '  top: 50%;\n' +
            '  -webkit-transform: translate(-50%, -50%);\n' +
            '          transform: translate(-50%, -50%);\n' +
            '  -webkit-transform-origin: center center;\n' +
            '          transform-origin: center center; }\n' +
            '\n' +
            '.fa-layers-counter {\n' +
            '  background-color: #ff253a;\n' +
            '  border-radius: 1em;\n' +
            '  color: #fff;\n' +
            '  height: 1.5em;\n' +
            '  line-height: 1;\n' +
            '  max-width: 5em;\n' +
            '  min-width: 1.5em;\n' +
            '  overflow: hidden;\n' +
            '  padding: .25em;\n' +
            '  right: 0;\n' +
            '  text-overflow: ellipsis;\n' +
            '  top: 0;\n' +
            '  -webkit-transform: scale(0.25);\n' +
            '          transform: scale(0.25);\n' +
            '  -webkit-transform-origin: top right;\n' +
            '          transform-origin: top right; }\n' +
            '\n' +
            '.fa-layers-bottom-right {\n' +
            '  bottom: 0;\n' +
            '  right: 0;\n' +
            '  top: auto;\n' +
            '  -webkit-transform: scale(0.25);\n' +
            '          transform: scale(0.25);\n' +
            '  -webkit-transform-origin: bottom right;\n' +
            '          transform-origin: bottom right; }\n' +
            '\n' +
            '.fa-layers-bottom-left {\n' +
            '  bottom: 0;\n' +
            '  left: 0;\n' +
            '  right: auto;\n' +
            '  top: auto;\n' +
            '  -webkit-transform: scale(0.25);\n' +
            '          transform: scale(0.25);\n' +
            '  -webkit-transform-origin: bottom left;\n' +
            '          transform-origin: bottom left; }\n' +
            '\n' +
            '.fa-layers-top-right {\n' +
            '  right: 0;\n' +
            '  top: 0;\n' +
            '  -webkit-transform: scale(0.25);\n' +
            '          transform: scale(0.25);\n' +
            '  -webkit-transform-origin: top right;\n' +
            '          transform-origin: top right; }\n' +
            '\n' +
            '.fa-layers-top-left {\n' +
            '  left: 0;\n' +
            '  right: auto;\n' +
            '  top: 0;\n' +
            '  -webkit-transform: scale(0.25);\n' +
            '          transform: scale(0.25);\n' +
            '  -webkit-transform-origin: top left;\n' +
            '          transform-origin: top left; }\n' +
            '\n' +
            '.fa-lg {\n' +
            '  font-size: 1.33333em;\n' +
            '  line-height: 0.75em;\n' +
            '  vertical-align: -.0667em; }\n' +
            '\n' +
            '.fa-xs {\n' +
            '  font-size: .75em; }\n' +
            '\n' +
            '.fa-sm {\n' +
            '  font-size: .875em; }\n' +
            '\n' +
            '.fa-1x {\n' +
            '  font-size: 1em; }\n' +
            '\n' +
            '.fa-2x {\n' +
            '  font-size: 2em; }\n' +
            '\n' +
            '.fa-3x {\n' +
            '  font-size: 3em; }\n' +
            '\n' +
            '.fa-4x {\n' +
            '  font-size: 4em; }\n' +
            '\n' +
            '.fa-5x {\n' +
            '  font-size: 5em; }\n' +
            '\n' +
            '.fa-6x {\n' +
            '  font-size: 6em; }\n' +
            '\n' +
            '.fa-7x {\n' +
            '  font-size: 7em; }\n' +
            '\n' +
            '.fa-8x {\n' +
            '  font-size: 8em; }\n' +
            '\n' +
            '.fa-9x {\n' +
            '  font-size: 9em; }\n' +
            '\n' +
            '.fa-10x {\n' +
            '  font-size: 10em; }\n' +
            '\n' +
            '.fa-fw {\n' +
            '  text-align: center;\n' +
            '  width: 1.25em; }\n' +
            '\n' +
            '.fa-ul {\n' +
            '  list-style-type: none;\n' +
            '  margin-left: 2.5em;\n' +
            '  padding-left: 0; }\n' +
            '  .fa-ul > li {\n' +
            '    position: relative; }\n' +
            '\n' +
            '.fa-li {\n' +
            '  left: -2em;\n' +
            '  position: absolute;\n' +
            '  text-align: center;\n' +
            '  width: 2em;\n' +
            '  line-height: inherit; }\n' +
            '\n' +
            '.fa-border {\n' +
            '  border: solid 0.08em #eee;\n' +
            '  border-radius: .1em;\n' +
            '  padding: .2em .25em .15em; }\n' +
            '\n' +
            '.fa-pull-left {\n' +
            '  float: left; }\n' +
            '\n' +
            '.fa-pull-right {\n' +
            '  float: right; }\n' +
            '\n' +
            '.fa.fa-pull-left,\n' +
            '.fas.fa-pull-left,\n' +
            '.far.fa-pull-left,\n' +
            '.fal.fa-pull-left,\n' +
            '.fab.fa-pull-left {\n' +
            '  margin-right: .3em; }\n' +
            '\n' +
            '.fa.fa-pull-right,\n' +
            '.fas.fa-pull-right,\n' +
            '.far.fa-pull-right,\n' +
            '.fal.fa-pull-right,\n' +
            '.fab.fa-pull-right {\n' +
            '  margin-left: .3em; }\n' +
            '\n' +
            '.fa-spin {\n' +
            '  -webkit-animation: fa-spin 2s infinite linear;\n' +
            '          animation: fa-spin 2s infinite linear; }\n' +
            '\n' +
            '.fa-pulse {\n' +
            '  -webkit-animation: fa-spin 1s infinite steps(8);\n' +
            '          animation: fa-spin 1s infinite steps(8); }\n' +
            '\n' +
            '@-webkit-keyframes fa-spin {\n' +
            '  0% {\n' +
            '    -webkit-transform: rotate(0deg);\n' +
            '            transform: rotate(0deg); }\n' +
            '  100% {\n' +
            '    -webkit-transform: rotate(360deg);\n' +
            '            transform: rotate(360deg); } }\n' +
            '\n' +
            '@keyframes fa-spin {\n' +
            '  0% {\n' +
            '    -webkit-transform: rotate(0deg);\n' +
            '            transform: rotate(0deg); }\n' +
            '  100% {\n' +
            '    -webkit-transform: rotate(360deg);\n' +
            '            transform: rotate(360deg); } }\n' +
            '\n' +
            '.fa-rotate-90 {\n' +
            '  -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=1)";\n' +
            '  -webkit-transform: rotate(90deg);\n' +
            '          transform: rotate(90deg); }\n' +
            '\n' +
            '.fa-rotate-180 {\n' +
            '  -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=2)";\n' +
            '  -webkit-transform: rotate(180deg);\n' +
            '          transform: rotate(180deg); }\n' +
            '\n' +
            '.fa-rotate-270 {\n' +
            '  -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=3)";\n' +
            '  -webkit-transform: rotate(270deg);\n' +
            '          transform: rotate(270deg); }\n' +
            '\n' +
            '.fa-flip-horizontal {\n' +
            '  -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=0, mirror=1)";\n' +
            '  -webkit-transform: scale(-1, 1);\n' +
            '          transform: scale(-1, 1); }\n' +
            '\n' +
            '.fa-flip-vertical {\n' +
            '  -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=2, mirror=1)";\n' +
            '  -webkit-transform: scale(1, -1);\n' +
            '          transform: scale(1, -1); }\n' +
            '\n' +
            '.fa-flip-horizontal.fa-flip-vertical {\n' +
            '  -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=2, mirror=1)";\n' +
            '  -webkit-transform: scale(-1, -1);\n' +
            '          transform: scale(-1, -1); }\n' +
            '\n' +
            ':root .fa-rotate-90,\n' +
            ':root .fa-rotate-180,\n' +
            ':root .fa-rotate-270,\n' +
            ':root .fa-flip-horizontal,\n' +
            ':root .fa-flip-vertical {\n' +
            '  -webkit-filter: none;\n' +
            '          filter: none; }\n' +
            '\n' +
            '.fa-stack {\n' +
            '  display: inline-block;\n' +
            '  height: 2em;\n' +
            '  position: relative;\n' +
            '  width: 2em; }\n' +
            '\n' +
            '.fa-stack-1x,\n' +
            '.fa-stack-2x {\n' +
            '  bottom: 0;\n' +
            '  left: 0;\n' +
            '  margin: auto;\n' +
            '  position: absolute;\n' +
            '  right: 0;\n' +
            '  top: 0; }\n' +
            '\n' +
            '.svg-inline--fa.fa-stack-1x {\n' +
            '  height: 1em;\n' +
            '  width: 1em; }\n' +
            '\n' +
            '.svg-inline--fa.fa-stack-2x {\n' +
            '  height: 2em;\n' +
            '  width: 2em; }\n' +
            '\n' +
            '.fa-inverse {\n' +
            '  color: #fff; }\n' +
            '\n' +
            '.sr-only {\n' +
            '  border: 0;\n' +
            '  clip: rect(0, 0, 0, 0);\n' +
            '  height: 1px;\n' +
            '  margin: -1px;\n' +
            '  overflow: hidden;\n' +
            '  padding: 0;\n' +
            '  position: absolute;\n' +
            '  width: 1px; }\n' +
            '\n' +
            '.sr-only-focusable:active, .sr-only-focusable:focus {\n' +
            '  clip: auto;\n' +
            '  height: auto;\n' +
            '  margin: 0;\n' +
            '  overflow: visible;\n' +
            '  position: static;\n' +
            '  width: auto; }\n' );
    },
	init: function(editor) {
		editor.widgets.add('FontAwesome', {
            button: 'Insert Font Awesome',
			template: '<span class="sk-fontawesome"><svg><g><g><path></path></g></g></svg></span>',
			dialog: 'fontawesomeDialog',
			// allowedContent: 'span(!sk-fontawesome)',
			upcast: function(element) {
				// Проверка заменять ли элемент
				return element.name == 'span' && element.hasClass('sk-fontawesome');
			},

            downcast: function(el){

				var style = buildStyle(this.data.color, this.data.size),
                    className = this.data.class;

                if (className.indexOf('sk-fontawesome') == -1){
                    className += ' sk-fontawesome';
                }

				var span = new CKEDITOR.htmlParser.element( 'span', {
                    'class': className,
                    'style': style,
                } );

                // Добавляем пробел т.к. ckeditor удаляет пустые теги
                span.setHtml('&nbsp;');

                el.replaceWith(span);

                return span;
			},

			init: function() {

				//Инициализация виджета
				var color = this.element.getStyle('color') ? this.element.getStyle('color'): '#000',
					size = this.element.getStyle('font-size') ? this.element.getStyle('font-size'): '20px',
					className = this.element.getAttribute('class');

                this.setData('color', RGBToHex(this.element.getStyle('color')));
                this.setData('size', this.element.getStyle('font-size'));
                className = className.replace('cke_widget_element', '');
                this.setData('class', className);

                // Удаляем лишние классы, оставляем только 'cke_widget_element'
                this.element.setAttribute('class', 'cke_widget_element');

			},

			data: function() {

				// Первая инициализация виджета
				if ( this.data.class.indexOf('fa') == -1 )
					return ;

				var color = this.data.color.replace("#", ''),
                    istayl = '';
				istayl += color ? 'color: #' + color + ';' : '';
				istayl += this.data.size != '' ? 'font-size:' + parseInt(this.data.size) + 'px;' : '';
				istayl != '' ? this.element.setAttribute('style', istayl) : '';
				istayl == '' ? this.element.removeAttribute('style') : '';


				var iconClassName = getIconNameByClassName(this.data.class);

				if ( iconClassName == undefined ){
				    return ;
                }

                var iconName = iconClassName.substr(3),
					prefix = extractPrefix(this.data.class),
					rotate = extractParamFromClass(this.data.class) || 0;

				var svg = window.FontAwesome.icon({prefix: prefix, iconName: iconName }, { transform: {
                        // size: parseInt(this.data.size)? parseInt(this.data.size) : 30,
                        rotate: rotate,
                        flipX: (this.data.class.indexOf('fa-flip-horizontal') != -1),
                        flipY: (this.data.class.indexOf('fa-flip-vertical') != -1),
                    }}).node[0];

				if ( istayl ){
                    svg.setAttribute('style',istayl);
                }

                if ( (this.data.class.indexOf('fa-border') != -1) ){
                    svg.className.baseVal += ' fa-border';
                }

                if ( (this.data.class.indexOf('fa-fw') != -1) ){
                    svg.className.baseVal += ' fa-fw';
                }

                //Spin добавляем враперу(иначе неверно работает рамка при наведении)
                if ( (this.data.class.indexOf('fa-spin') != -1) ){
                    this.wrapper.setAttribute('class', this.wrapper.getAttribute('class') + ' fa-spin');
                }

                this.element.setAttribute('style', '');

                this.element.setHtml(svg.outerHTML);


			}
		});

		CKEDITOR.dialog.add('fontawesomeDialog', this.path + 'dialogs/fontawesome.js');
		CKEDITOR.document.appendStyleSheet(CKEDITOR.plugins.getPath('fontawesome') + 'font-awesome/css/tile.css');


	}
});