/**
 * Модуль для ресайза изображений
 */
Ext.define('Ext.Adm.PhotoResizer',{

    extend: 'Ext.form.field.Base',

    fieldSubTpl: ['<style>img {max-width: 100%;}</style><div id="js_container" style="width:800px;height:800px;display:none"><img id="image" src=""></div>'],

    getSubmitData: function() {
        var $image = $('#image');
        var data = {
            cropdata:$image.cropper('getData'),
            source:this.crop.file,
            format:this.format,
            imagedata:$image.cropper('getImageData'),
        };
        return data;
    },

    initComponent: function() {
        var me = this;

        this.callParent();
    },
    listeners: {
        afterrender: function( self ) {
            self.execute();
        }
    },

    execute: function(){
        var me = this;
        $(function () {
            var $image = $('#image');

            $image.attr('src',me.crop.file);

        });
        this.initNewWrapper();
    },
    initNewWrapper: function() {

        var me = this;
        $(function () {

            var $image = $('#image');

            var coef = 0.9;
            
            var data = {
                movable: false,
                zoomable: true,
                rotatable: true,
                scalable: false,
               // minCropBoxWidth: me.format.width,
                //minCropBoxHeight: me.format.height,
                autoCrop:true,
                autoCropArea:1
            };

            if (me.format.width!='0' && me.format.height!='0'){
                data.aspectRatio = me.format.width/me.format.height;
            }

            $image.cropper(data);
            setTimeout(function() {

                /*Далее идет приведение данных из рассчетной части и попытка скормить их кропилке.*/
                var zoom = 0;

                var cropBoxData = $image.cropper('getCropBoxData');
                var imageData = $image.cropper('getImageData');

                if (me.format.width>imageData.naturalWidth && me.format.height>imageData.naturalHeight){

                    if (imageData.naturalWidth < imageData.naturalHeight) {
                        var zoom = (800-imageData.naturalHeight)/imageData.naturalHeight;
                    } else {
                        var zoom = (800-imageData.naturalWidth)/imageData.naturalWidth;
                    }
                    zoom = -1 * zoom;

                    $image.cropper('zoom', zoom);

                    var cropbox = {
                        width: (me.crop.calculations.img_width+me.crop.calculations.left_delay*2)*coef,
                        height: (me.crop.calculations.img_height+me.crop.calculations.top_delay*2)*coef,
                        top:(800-me.crop.calculations.img_height)/2-me.crop.calculations.top_delay,
                        left:(800-me.crop.calculations.img_width)/2-me.crop.calculations.left_delay
                    };

                    cropbox = validateCropbox(cropbox);

                    $image.cropper('setCropBoxData', cropbox);

                } else {

                    if (me.format.scale_and_crop == '1') {

                        if (imageData.naturalWidth < imageData.naturalHeight) {
                            var m = 800 - cropBoxData.height;
                            var zoom = m / cropBoxData.height;


                        } else {
                            var m = 800 - cropBoxData.width;
                            var zoom = m / cropBoxData.width;
                        }

                        if (zoom == '0' && (me.crop.calculations.left_delay != '0' || me.crop.calculations.top_delay != '0')) {

                            if (imageData.naturalWidth < imageData.naturalHeight) {

                                var m = 800 - me.crop.calculations.img_height;
                                var zoom = m / me.crop.calculations.img_height;

                            } else {
                                var m = 800 - me.crop.calculations.img_width;
                                var zoom = m / me.crop.calculations.img_width;
                            }

                            var cropbox = {
                                width: (me.crop.calculations.img_width + me.crop.calculations.left_delay * 2)*coef,
                                height: (me.crop.calculations.img_height + me.crop.calculations.top_delay * 2)*coef,
                                top: (800 - me.crop.calculations.img_height) / 2 - me.crop.calculations.top_delay,
                                left: (800 - me.crop.calculations.img_width) / 2 - me.crop.calculations.left_delay
                            };

                            cropbox=validateCropbox(cropbox);

                            $image.cropper('setCropBoxData', cropbox);

                        }

                        zoom = -1 * zoom;
                        $image.cropper('zoom', zoom);
                    } else {

                    }
                }

                var cropboxData = $image.cropper('getCropBoxData', cropbox);

                var cropbox = {
                    width:cropboxData.width*coef,
                    height: cropboxData.height*coef,
                    top: (800-cropboxData.height*coef)/2,
                    left:(800-cropboxData.width*coef)/2
                };

                $image.cropper('setCropBoxData', cropbox);

                if (imageData.naturalHeight>imageData.naturalWidth) {
                    var zoomToCoef = cropbox.height / imageData.naturalHeight;
                } else {
                    var zoomToCoef = cropbox.width / imageData.naturalWidth;
                }

                $image.cropper('zoomTo',zoomToCoef);

                $("#js_container").show();
            },500);

            function validateCropbox(cropbox){

                if (cropbox.width>800 || cropbox.height>800) {
                    if (cropbox.width > cropbox.height) {
                        var coef = 800/cropbox.width;
                        cropbox.width = 800;
                        cropbox.height = cropbox.height*coef;
                    } else {
                        var coef = 800/cropbox.height;
                        cropbox.height = 800;
                        cropbox.width = cropbox.width*coef;
                    }
                }

                return cropbox;
            }
        });

    }
});