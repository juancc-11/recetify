$(document).ready(function () {
    $('.rl-ranking-item').each(function () {
        var $item = $(this);
        var $stars = $item.find('.rl-stars i');

        $item.hover(
            function () {
                $stars.addClass('rl-stars-hover');
            },
            function () {
                $stars.removeClass('rl-stars-hover');
            }
        );
    });

    $('.rl-ranking-btn').on('click', function () {
        var $item = $(this).closest('.rl-ranking-item');
        $item.css('opacity', '0.6');
    });
});