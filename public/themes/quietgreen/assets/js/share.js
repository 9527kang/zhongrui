$(function(){

});

function dofristshare(type,title){
    var link = this.location.href;
    dofristshareFu(type,title,link)
}

function dofristshareFu(type,title,link) {
    title = encodeURIComponent(title);
    //var link = this.location.href;
    link = encodeURIComponent(link);
    //var image = encodeURIComponent('http://www.baidu.com/img/bdlogo.gif');

    if (type == "sina") {
        window.open("http://v.t.sina.com.cn/share/share.php?url=" + link + "&title=" + title + "&content=utf8");
    }

    if (type == "tx") {
        window.open("http://v.t.qq.com/share/share.php?url=" + link + "&title=" + title );
    }

    if (type == "qzone") {
        window.open("http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=" + link + "&title=" + title);
    }

    if (type == "rr") {
        window.open("http://widget.renren.com/dialog/share?resourceUrl=" + link + "&title=" + title );
    }

    if (type == "douban") {
        window.open("http://www.douban.com/recommend/?url=" + link + "&title=" + title);
    }

    return false;

}