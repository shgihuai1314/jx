
$('.site-tree li').on('click', function () {
    var page = $(this).find('em').html();
    $(this).addClass('this').siblings().removeClass('this').parent().siblings('ul').find('li').removeClass('this');
    $('.site-content iframe').attr("src", "page/" + page + ".html");
});

$(function () {
    console.log($('.site-tree li:eq(0)').click());
});
