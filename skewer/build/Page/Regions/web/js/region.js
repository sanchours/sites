$(document).ready(function () {

    if ($('#js-show_fancy').length) {
        showConfirm();
    }

    $(document).on('click', '#js-region', function () {
        showFancyBox();
    });

    function showConfirm() {
        var $confirm = $('#js-region-confirmation');

        // Open customized confirmation dialog window
        $.fancybox.open({
            type: 'html',
            content: $confirm.html(),
            afterClose: function (instance, current, e) {
                var button = e ? e.target || e.currentTarget : null;
                var value = button ? $(button).data('value') : 0;

                if (value === 0) {
                    showFancyBox();
                }
            }
        });
    }

    function showFancyBox() {
        var $fancy = $('#js-region-fancy');

        if ($fancy.length) {
            $.fancybox.open({
                type: 'html',
                content: $fancy.html(),
                padding: 20
            });
        }
    }
});
