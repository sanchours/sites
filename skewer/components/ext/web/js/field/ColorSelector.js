/**
 * Поле выбора цвета
 */

Ext.define('Ext.sk.field.ColorSelector', {
    extend:'Ext.form.field.Picker',
    alias: 'widget.colorfield',
    colors: [
        "800000", "993300", "2F4F4F", "003300", "003333", "000080", "333399", "000000",
        "CD0000", "FF4500", "548B54", "008000", "008080", "0000FF", "666699", "333333",
        "FF0000", "FF7F00", "4EEE94", "66CD00", "00CED1", "3366FF", "800080", "808080",
        "FF7256", "FFA500", "FFD700", "76EE00", "87CEEB", "00CCFF", "D15FEE", "C0C0C0",
        "FFCCCC", "FFCC99", "FFFF99", "CCFFCC", "CCFFFF", "99CCFF", "FFFFFF", "TRANSPARENT"
    ],

    blankText: 'Поле должно иметь восьмеричный формат #ABCDEF и содержать 3 или 6 восьмеричных знаков, либо текст "transparent, либо цвет в нотации rgb/rgba".',

    regex: /^(#?[0-9a-f]{3}|[0-9a-f]{6})|TRANSPARENT|(rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\))|(rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[\d.]+\s*\))$/i,
    darkRegex: /[0-4]/g,
    selectOnFocus: true,

    saveType: 'default',

    picker: null,

    validateValue : function(value){
        if(!this.getEl()) {
            return true;
        }
        if((value.length < 1 && !this.allowBlank) || !this.regex.test(value)) {
            this.markInvalid(Ext.String.format(this.blankText, value));
            return false;
        }

        this.markInvalid();
        this.setColor(value);
        return true;
    },

    markInvalid : function( msg ) {
        Ext.sk.field.ColorSelector.superclass.markInvalid.call(this, msg);
        this.inputEl.setStyle({
            'background-image': 'url(/skewer/build/libs/ExtJS/img/grid/invalid_line.gif)'
        });
    },

    setValue : function(hex){

        if ( !hex ) return false;
        hex = hex.toLowerCase();

        if ( hex === 'transparent' ){
            this.setColor(hex);
            Ext.sk.field.ColorSelector.superclass.setValue.call(this, hex);
            return hex;
        }

        if ( !hex.match(/^rgb/i)  ) {

            if ( this.saveType == 'rgba' ){
                hex = this.hexToRGBA(hex);
                Ext.sk.field.ColorSelector.superclass.setValue.call(this, hex);
                return hex;
            }

            if (this.regex.test(hex) && hex.indexOf('#') !== 0)
                hex = '#' + hex;
        }
        Ext.sk.field.ColorSelector.superclass.setValue.call(this, hex);
        this.setColor(hex);
        return hex;
    },

    setColor : function(hex) {
        if ( !hex ) return false;

        if (hex === 'transparent'){
            Ext.sk.field.ColorSelector.superclass.setFieldStyle.call(this, {
                'background-color': '#fff',
                'color': '#000',
                'background-image': 'none'
            });
            return hex;
        }

        // число "темных" символов
        var darkCnt = hex.match(this.darkRegex);
        // половина длины строки
        var halfLen = Math.floor(hex.length/2);
        // флаг "темный", если более половины символов "темные"
        var dark = darkCnt && darkCnt.length>=halfLen;

        if ( hex.match(/^rgb/i) ) {
            hex = '#fff';
            dark = false;
        }

        Ext.sk.field.ColorSelector.superclass.setFieldStyle.call(this, {
            'background-color': hex,
            'color': dark?'#fff':'#000',
            'background-image': 'none'
        });
        return hex;
    },

    pickerListeners : {
        select: function(m, d){
            this.setValue(d);
            this.triggerBlur();
        },
        show : function(){
            this.onFocus();
        },
        hide : function(){
            this.focus();
            var ml = this.pickerListeners;
            this.picker.un("select", ml.select,  this);
            this.picker.un("show", ml.show,  this);
            this.picker.un("hide", ml.hide,  this);
        }
    },

    onTriggerClick : function(){

        var me = this;

        if(this.disabled){
            return;
        }

        me.picker = Ext.create('Ext.menu.ColorPicker',{
            colorRe: /(?:^|\s)color-(.{6}|TRANSPARENT)(?:\s|$)/,
            colors: me.colors,
            shadow: true,
            autoShow : true
        });


        this.picker.on(Ext.apply({}, this.pickerListeners, {
            scope:this
        }));

        me.picker.alignTo(this.inputEl, 'tl-bl?');
        me.picker.doLayout();

        me.picker.show(this.inputEl);
    },

    beforeBlur : function(){

        var me = this,
            v = me.getRawValue();

        if (v) {
            me.setValue(v);
        }

    },

    hexToRGBA: function( hex ){

        var match, RGB, rgba, r, g, b, a;

        match = hex.match(/#?([0-9a-f]{6}|[0-9a-f]{3})/i);

        if ( !match )
            return false;

        hex = String(match[1]);

        var double = (hex.length === 3);

        RGB = hex.match(/([0-9a-f]{1,2})([0-9a-f]{1,2})([0-9a-f]{1,2})/i);

        if ( double ){
            RGB.forEach(function(value, index, arr){
                value = String(value);
                arr[index] = value + value;
            });
        }

        r = parseInt(RGB[1], 16);
        g = parseInt(RGB[2], 16);
        b = parseInt(RGB[3], 16);
        a = 1;

        rgba = 'rgba(' + r + ',' + g + ',' + b + ',' + a + ')';

        return rgba;

    }

});
