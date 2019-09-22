/**
 * 检查是否安装金格插件
 * @returns {boolean}
 * @constructor
 */
function CheckActiveX() {
    var mObject = true;
    try {
        var newAct = new ActiveXObject("Kinggrid.iWebOffice");
        if (newAct == undefined) {
            mObject = false;
        }
    } catch (e) {
        mObject = false;
    }
    newAct = null;
    if (!(window.ActiveXObject || "ActiveXObject" in window)) {
        // activex_install.innerHTML = "多浏览器如果没有正常加载，查看说明"
        return true;
    }

    return mObject;
}

/**
 * 打开office文档
 * @param FileName 文件名称
 * @param FilePath 文件路径
 * @param EditType 编辑类型
 * @param Writing 是否允许批注
 * @param Template 是否允许套红
 * @param Print 是否允许打印
 */
function openfile(FileName, FilePath, EditType, Writing, Template, Print) {
    var mhtmlHeight = window.screen.availHeight;//获得窗口的垂直位置;
    var mhtmlWidth = window.screen.availWidth; //获得窗口的水平位置;
    var iTop = 0; //获得窗口的垂直位置;
    var iLeft = 0; //获得窗口的水平位置;
    window.open(indexScript + "/site/web-office?FileName=" + FileName + '&FilePath=' + FilePath
        + '&EditType=' + EditType + '&Writing=' + Writing + '&Template=' + Template + '&Print=' + Print, '',
        'height=' + mhtmlHeight + ',width=' + mhtmlWidth + ',top=' + iTop + ',left=' + iLeft +
        ',toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,status=no');
}

$('.file-list .view-btn').click(function () {
    if (CheckActiveX()) {
        // console.log('已经安装iWebOffice2015中间件！');
        var filename = $(this).data('filename');
        var src = $(this).data('src');
        var edittype = $(this).data('edittype');
        var writing = $(this).data('writing');
        var template = $(this).data('template');
        var print = $(this).data('print');
        openfile(filename, src, edittype, writing, template, print)
    } else {
        alert('未检测到iWebOffice2015中间件！');
        return false;
    }
});

$(".img-view").click(function() {
    var imgView = $(".img-view");
    var imgItems = [];
    $.each(imgView, function () {
        var imgSrc = $(this).attr("src");
        var name = $(this).data("name");
        imgItems.push({image: imgSrc, caption: name})
    });

    var img = $.photoBrowser({
        items: imgItems,
        initIndex: imgView.index($(this)),
    });

    img.open();
});
