var Rating = function constructor(nameClass, check) {
    this.nameClass = nameClass;
    this.check = check;
};

Rating.prototype.initRatings = function initRatings() {

    const iStarsCount = 5; // Число звёзд рейтинга
    let classRating = "." + this.nameClass;
    let checkParam = this.check;

    var self = this;

    $(classRating).each(function () {
        var oRating = $(this);
        var oStarDiv = oRating.children();
        var iStarHeight = oStarDiv.height();

        // Если данный рейтинг уже инициализирвоан, то пропустить
        if (oStarDiv.length > 1) return;

        // Установить бызовые свойства
        oStarDiv.css({
            float: 'left',
            'background-repeat': 'repeat-y'
        });

        // Разрешить голосование
        if (self.isAllow(oRating, checkParam)) {
            oStarDiv
                .css({cursor: 'pointer'})
                .mouseenter(function () {
                    $(this).parent().children().children().remove(); // Удалить не целые звёзды
                    $(this).prevAll().add(this).css('background-position', '0px ' + iStarHeight + 'px');
                    $(this).nextAll().css('background-position', '0px 0px');
                })
                .on('click', function () {
                    checkParam ? self.submitRating(this) : self.checkRating(this)
                })
            ;
            oRating.mouseleave(self.setRating);
        }

            // Добавить недостающие звёзды
            for (let i = 1; i < iStarsCount; i++) {
                oRating.append(oStarDiv.clone(true));
            }

        self.setRating(oRating);
    });
};


Rating.prototype.isAllow = function isAllow(oRating, checkParam) {
    return (checkParam) ? oRating.data('allowrate') : true;
};


/**
 * Установить текущий рейтинг
 * @param that - указатели на dom-элемент рейтинга
 */
Rating.prototype.setRating = function setRating(that) {
    that = (that && that.originalEvent == undefined) ? that : this;

    var iRating = $(that).data('rating');
    var oStarDiv = $(that).children().first();
    var iStarHeight = oStarDiv.height();
    var oStarHalf = oStarDiv.clone(false).css({
        padding: '0px',
        margin: '0px',
        'background-position': '0px ' + iStarHeight + 'px'
    });

    let i = 0;
    $(that).children().each(function () {
        i++;
        if (i <= iRating) {

            // Добавить целую активнцю звезду
            $(this).css('background-position', '0px ' + iStarHeight + 'px');

        } else {

            // Добавить целую пассивную звезду
            $(this).css('background-position', '0px 0px');

            // Добавить не целую звезду
            if ((i - 1 < iRating) && (iRating - i + 1 > 0)) {

                var iFrac = iRating - i + 1; // Дробная часть рейтинга
                var iSizeAct = Math.round(iFrac * iStarHeight);
                var iSizePas = iStarHeight - iSizeAct;

                // Очистить фон родителя
                $(this).css('background-position', oStarDiv.width() + 'px 0px');

                // Добавить не целую активную часть звёзды
                $(this).append(oStarHalf.clone(true).css('width', iSizeAct + 'px'));

                // Добавить не целую пассивную часть звёзды
                $(this).append(oStarHalf.clone(true).css({
                    'background-position': -iSizeAct + 'px 0px',
                    width: iSizePas + 'px'
                }));
            }
        }
    });
};

Rating.prototype.submitRating = function submitRating(thisObject) {

    var iRating = $(thisObject).prevAll().length + 1;
    var oRatingDiv = $(thisObject).parent();

    $.post('/ajax/ajax.php', {
            moduleName: oRatingDiv.data('modulename'),
            cmd: 'addRating',
            rating: parseInt(iRating),
            objId: parseInt(oRatingDiv.data('objectid')),
            rate_url: window.location.pathname + window.location.search
        },
        function (mResponse) {
            if (!mResponse) return false;
            var oResponse = $.parseJSON(mResponse);
            oResponse = $.parseJSON(oResponse.html);

            // Обновить результаты
            var oRatingDivNew = $('.js-ajax_content', $(oResponse.Rating.html).wrapAll('<div>').parent());
            if (oRatingDivNew.length) {

                // Обновить рейтинги у всех копий объектов на странице
                let oRatingDivs = $('.js-rating[data-objectid = ' + oRatingDiv.data('objectid') + ']');
                oRatingDivs.each(function () {
                    $(thisObject).parents('.js-ajax_content').replaceWith(oRatingDivNew.prop('outerHTML'));
                });

                let oRating = new Rating('js-rating', true);
                oRating.initRatings();

                Rating.prototype.initRatings();

                if (oResponse.Answer)
                    $.fancybox.open(oResponse.Answer, {
                        afterLoad: function (instance, current) {
                            // Закрыть через некоторое время
                            setTimeout(function(){
                                instance.close()
                            }, 3000);
                        }
                    });
            }
        }
    );
    return false;
};

Rating.prototype.checkRating = function checkRating(thisObject) {

        let iRating = $(thisObject).prevAll().length + 1;
        $(thisObject).parent().data('rating',iRating);

        let input = $(thisObject).parent().parent().find('input');
        input.val(iRating);

        let checked = iRating ? 'checked' : '';
        input.attr('checked', checked);

};
