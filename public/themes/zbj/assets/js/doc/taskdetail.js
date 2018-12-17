//$('.evaluateshow').on('click',function(){
//    $(this).parent().next().slideToggle();
//});
$(function(){
    if(parseInt($('.js_moreDetail .text').css('height')) < 50){
        $('.js_more').hide();
    }
    $('.js_more').on('click',function(){
        if($('.js_moreDetail').css('height') == '55px'){
            $('.js_moreDetail').css('height','auto');
            $('.js_more i').attr('class','fa fa-angle-double-up');
            $('.js_more .text').text('收起');
            return;
        }else{
            $('.js_moreDetail').css('height','55px');
            $('.js_more i').attr('class','fa fa-angle-double-down');
            $('.js_more .text').text('查看更多');
            return;
        }
    });
    $('#home6_page .pagination').find('li').find('a').on('click',function(){
        var url = $(this).attr('href');
        $.get(url,function(data){
            $('#home6_desc').html(data);

        });
        return false;
    });
    if($("#selectM2")){
        var arr1 = [];
        //点击开打第一级  邀约高手
        $("#selectM2").click(function(){
            arr1 = [];
            var self=$(this);
            if($(this).hasClass("showData")){//关闭弹框
                $(this).parent().find(".parent_box_m2").css("display","none");
                $(this).parent().find(".tow_box_m2").css("display","none");
                $(this).removeClass("showData");
            }else{
                $(this).addClass("showData");
                var randTxt = eval($("#task_cate").attr('data-values'));
                var html="<li><span name='全部' id='0' class='one_content'>全部</span></li>";
                for(i=0;i<randTxt.length;i++){
                    html+="<li>";
                    html+="<span name="+randTxt[i].name+" id="+randTxt[i].id+" class='one_content'>"+randTxt[i].name+"</span>";
                    if(randTxt[i].child_task_cate != undefined){
                        html+="<b class='next_one'></b>";
                        html+="<ul class='tow_box_m2'>";
                        for(j=0;j<randTxt[i].child_task_cate.length;j++){
                            html+="<li><span name="+randTxt[i].child_task_cate[j].name+" id="+randTxt[i].child_task_cate[j].id+" class='one_content'>"+randTxt[i].child_task_cate[j].name+"</span></li>";
                        }
                        html+="</ul>";
                    }
                    html+="</li>";
                }
                $(".first_box_m2").html(html);
                $(self).parent().find(".parent_box_m2").css("display","block");

            }
        });

        //点击打开第二级
        $(document).on("click",".first_box_m2>li",function(){
            $(this).parent().find(".oneStyle").removeClass("oneStyle");
            $(this).addClass("oneStyle");
            $(this).parent().find(".showBlock").removeClass("showBlock");
            $(this).find("ul").addClass("showBlock");
            var oneText=$(this).children().eq(0).attr("name");
            arr1[0]=$(this).children().eq(0).attr("id");
            $(".select_one01").html(oneText);
            console.log(arr1);
        });

        // 点击二级子菜单  **showBlock**
        $(document).on("click",".showBlock>li",function(e){
            e.stopPropagation();
            var oneText=$(this).children().eq(0).attr("name");
            var oneId=$(this).children().eq(0).attr("id");
            var titleContent=$(".select_one01").html();
            if(titleContent.indexOf("/") != -1){
                var numIndex=titleContent.indexOf("/");
                titleContent=titleContent.slice(0,numIndex);
            }
            $(".select_one01").html(titleContent+"/"+oneText);
            arr1[1]=oneId;
            $(this).parents(".first_box_m2").find(".towStyle").removeClass("towStyle");
            $(this).addClass("towStyle");
            $(this).parents(".parent_box_m2").css("display","none");
            $(this).parents(".tow_box_m2").css("display","none");
        });
    }

    if($("#selectM3")){
        var arr2=[];
        /*点击选择框，弹出选择内容*/ //邀约高手
        //点击开打第一级
        $("#selectM3").click(function(){
            arr2 = [];
            var slef=$(this);
            //关闭所有弹框
            if($(this).hasClass("showData01")){
                $(this).parent().find(".parent_box_m6").css("display","none");
                $(this).parent().find(".tow_box_m3").css("display","none");
                $(this).removeClass("showData01");
            }else{/*显示一级框*/
                $(this).addClass("showData01");
                $(this).parent().find(".parent_box_m6").css("display","block");
                $(this).parent().find(".tow_box_m3").css("display","none");
                var randTxt1 = eval($("#district_second").attr('data-values'));
                var html="<li><span name='全部' id='0' class='one_content'>全部</span></li>";
                for(i=0;i<randTxt1.length;i++){
                    html+="<li>";
                    html+="<span name="+randTxt1[i].name+" id="+randTxt1[i].id+" class='one_content'>"+randTxt1[i].name+"</span>";
                    if(randTxt1[i].child != undefined){
                        html+="<b class='next_one'></b>";
                        html+="<ul class='tow_box_m3'>";
                        for(j=0;j<randTxt1[i].child.length;j++){
                            html+="<li><span name="+randTxt1[i].child[j].name+" id="+randTxt1[i].child[j].id+" class='one_content'>"+randTxt1[i].child[j].name+"</span></li>";
                        }
                        html+="</ul>";
                    }
                    html+="</li>";
                }
                $(".first_box_m3").html(html);
            }
        });

        //点击打开第二级
        $(document).on("click",".first_box_m3>li",function(){
            $(this).parent().find(".oneStyle").removeClass("oneStyle");
            $(this).addClass("oneStyle");
            $(this).parent().find(".showBlockF").removeClass("showBlockF");
            $(this).find("ul").addClass("showBlockF");
            var oneText=$(this).children().eq(0).attr("name");
            arr2[0]=$(this).children().eq(0).attr("id");
            $(".select_one02").html(oneText);
        });

        //点击二级子菜单
        $(document).on("click",".showBlockF>li",function(e){
            e.stopPropagation();
            var oneText=$(this).children().eq(0).attr("name");
            var oneId=$(this).children().eq(0).attr("id");
            var titleContent=$(".select_one02").html();
            if(titleContent.indexOf("/") != -1){
                var numIndex=titleContent.indexOf("/");
                titleContent=titleContent.slice(0,numIndex);
            }
            $(".select_one02").html(titleContent+"/"+oneText);
            arr2[1]=oneId;
            $(this).parents(".first_box_m3").find(".towStyle").removeClass("towStyle");
            $(this).addClass("towStyle");
            $(this).parents(".parent_box_m6").css("display","none");
            $(this).parents(".tow_box_m3").css("display","none");
        });
    }


    // 点击预约高手 搜索按钮
    $("#searchBtn").click(function(){
        $.get('/task/ajaxInviteUser?task_id='+$("#invite_task_id").val()+'&classifyArr='+arr1.join(',')+'&areaArr='+arr2.join(',')+'&Keyword='+$("#exampleInputName2").val(),function(data){
            $('#home6_desc').html(data);

        });
        return false;
    });
});

function inviteUser(uid,taskId){
    var url = '/task/inviteUser?taskId='+taskId+'&uid='+uid;
    $.get(url, function (data) {
        if (data.code == 1) {
            var element = '#invite_user_desc'+uid;
            $(element).html('<div class="but_style">已邀约</div>');
        } else  {
            alert(data.msg);
        }
    });
}


function evaluateshow(obj)
{
    obj.parent().next().slideToggle();
    var work_id = obj.attr('work_id');
    var url = obj.attr('url')+'/'+work_id;
    var num = obj.attr('num');
    if(num==0) {
        $.get(url, function (data) {
            if (data.errCode == 1) {
                var html = '';
                for (var i in data.comment) {
                    if (data.comment[i].parent_user != undefined) {
                        html += "<div class=\"itemdiv dialogdiv\"><div class=\"user\"><img alt=\"Alexa's Avatar\" src=\"" + data.comment[i].avatar_md5 + "\" onerror=\"onerrorImage('" + data.onerror_img + "',$(this))\"><\/div><div class=\"body\"><div class=\"time\"><i class=\"ace-icon fa fa-clock-o\"><\/i><span class=\"green\">回复于" + data.comment[i].created_at + "<\/span><\/div><div class=\"name\"><a href=\"#\">" + data.comment[i].nickname + " 回复 " + data.comment[i].parent_user + "<\/a><\/div><div class=\"text\">" + data.comment[i].comment + "<\/div><div class=\"tools\"><a href=\"javascript:void(0);\" class=\"btn btn-minier btn-info allbtn \" onclick='workAnswer($(this))' woker_nam='"+data.comment[i].nickname+"' work_id='" + data.comment[i].id + "' task_id='" + data.comment[i].work_id + "'><i class=\"icon-only ace-icon fa fa-share \"><\/i> 回复<\/a><\/div><\/div><\/div>";
                    } else {
                        html += "<div class=\"itemdiv dialogdiv\"><div class=\"user\"><img alt=\"Alexa's Avatar\" src=\"" + data.comment[i].avatar_md5 + "\" onerror=\"onerrorImage('" + data.onerror_img + "',$(this))\"><\/div><div class=\"body\"><div class=\"time\"><i class=\"ace-icon fa fa-clock-o\"><\/i><span class=\"green\">回复于" + data.comment[i].created_at + "<\/span><\/div><div class=\"name\"><a href=\"#\">" + data.comment[i].nickname + "<\/a><\/div><div class=\"text\">" + data.comment[i].comment + "<\/div><div class=\"tools\"><a href=\"javascript:void(0);\" class=\"btn btn-minier btn-info allbtn\" onclick='workAnswer($(this))' woker_nam='"+data.comment[i].nickname+"' work_id='" + data.comment[i].id + "' task_id='" + data.comment[i].work_id + "'><i class=\"icon-only ace-icon fa fa-share \"><\/i> 回复<\/a><\/div><\/div><\/div>";
                    }
                }
                $('#work-comment-' + work_id).html(html);
                $('#work-comment-' + work_id).attr('class','dialogs scroll-content');
                comment_init();//初始化回复
                obj.attr('num',num+1);
            } else if (data.errCode == 0) {
                alert(data.errMsg);
            }
        });
    }
}
function loginremaind(obj)
{
    $.gritter.add({
        //            title: '消息提示：',
        text: '<div><span class="text-center"><h5>' + '登陆后才能回复' + '</h5></span></div>',
        class_name: 'gritter-info gritter-center'
    });
}
//回复初始化
$(function(){

    deliver();
    lostDeliver();
    //获取评论数据
    //$('.get-comment').one('click',function(data){
    //    var work_id = $(this).attr('work_id');
    //    var url = $(this).attr('url')+'/'+work_id;
    //    $.get(url,function(data){
    //        if(data.errCode==1){
    //            var html = '';
    //            for(var i in data.comment)
    //            {
    //                if(data.comment[i].parent_user!=undefined )
    //                {
    //                    html += "<div class=\"itemdiv dialogdiv\"><div class=\"user\"><img alt=\"Alexa's Avatar\" src=\""+data.comment[i].avatar_md5+"\" onerror=\"onerrorImage('"+data.onerror_img+"',$(this))\"><\/div><div class=\"body\"><div class=\"time\"><i class=\"ace-icon fa fa-clock-o\"><\/i><span class=\"green\">回复于"+data.comment[i].created_at+"<\/span><\/div><div class=\"name\"><a href=\"#\">"+data.comment[i].nickname+" 回复 "+data.comment[i].parent_user+"<\/a><\/div><div class=\"text\">"+data.comment[i].comment+"<\/div><div class=\"tools\"><a href=\"javascript:void(0);\" class=\"btn btn-minier btn-info allbtn \" onclick='workAnswer($(this))' work_id='"+data.comment[i].id+"' task_id='"+data.comment[i].work_id+"'><i class=\"icon-only ace-icon fa fa-share \"><\/i> 回复<\/a><\/div><\/div><\/div>";
    //                }else{
    //                    html += "<div class=\"itemdiv dialogdiv\"><div class=\"user\"><img alt=\"Alexa's Avatar\" src=\""+data.comment[i].avatar_md5+"\" onerror=\"onerrorImage('"+data.onerror_img+"',$(this))\"><\/div><div class=\"body\"><div class=\"time\"><i class=\"ace-icon fa fa-clock-o\"><\/i><span class=\"green\">回复于"+data.comment[i].created_at+"<\/span><\/div><div class=\"name\"><a href=\"#\">"+data.comment[i].nickname+"<\/a><\/div><div class=\"text\">"+data.comment[i].comment+"<\/div><div class=\"tools\"><a href=\"javascript:void(0);\" class=\"btn btn-minier btn-info allbtn\" onclick='workAnswer($(this))' work_id='"+data.comment[i].id+"' task_id='"+data.comment[i].work_id+"'><i class=\"icon-only ace-icon fa fa-share \"><\/i> 回复<\/a><\/div><\/div><\/div>";
    //                }
    //            }
    //            comment_init();//初始化回复
    //            $('#work-comment-'+work_id).children("div[class='scroll-content']").html(html);
    //        }else if(data.errCode==0){
    //            alert(data.errMsg);
    //        }
    //    });
    //})
    //文件上传初始化
    $('#id-input-file-3').ace_file_input({
        style:'well',
        btn_choose:'最多可添加3个附件，每个大小不超过10MB',
        btn_change:null,
        no_icon:'ace-icon fa fa-cloud-upload',
        droppable:true,
        thumbnail:'small'//large | fit
        //,icon_remove:null//set null, to hide remove/reset button
        /**,before_change:function(files, dropped) {
						//Check an example below
						//or examples/file-upload.html
						return true;
					}*/
        /**,before_remove : function() {
						return true;
					}*/
        ,
        preview_error : function( error_code) {
            //name of the file that failed
            //error_code values
            //1 = 'FILE_LOAD_FAILED',
            //2 = 'IMAGE_LOAD_FAILED',
            //3 = 'THUMBNAIL_FAILED'
            //alert(error_code);
        }

    }).on('change', function(){
        //console.log($(this).data('ace_input_files'));
        //console.log($(this).data('ace_input_method'));
    });
    $.cookie("table_index", 1);
});

/**
 * 悬赏模式中标
 * @param obj
 */
function winBid(obj)
{
    var work_id = obj.attr('work_id');
    var task_id = obj.attr('task_id');
    var url = '/task/winBid/'+work_id+'/'+task_id;
    window.location.href = url;
}

/**
 * 招标模式中标
 * @param obj
 */
function bidWinBid(obj)
{
    var work_id = obj.attr('work_id');
    var task_id = obj.attr('task_id');
    var url = '/task/bidWinBid/'+work_id+'/'+task_id;
    window.location.href = url;
}



//验收成功
function deliver(){
    $('.deliver-success').on('click',function(){
        var url = $(this).attr('url');
        var work_id = $(this).attr('work_id');
        var task_id = $(this).attr('task_id');
        $.get(url,{'task_id':task_id,'work_id':work_id},function(data){
            if(data.errCode==1){
                $('#select-attachment-'+data.id+'').html('');
                $('#selected-'+data.id+'').attr('style','display:none');
                $('#cutyellow-'+data.id).attr('style','');

            }else if(data.errCode==0){
                alert(data.errMsg);
            }
        });
    });
}
//验收失败
function lostDeliver()
{
    $('.deliver-lost').on('click',function(){
        var url = $(this).attr('url');
        var work_id = $(this).attr('work_id');
        var task_id = $(this).attr('task_id');
        $.get(url,{'task_id':task_id,'work_id':work_id},function(data){
            if(data.errCode==1){
                $('#select-attachment-'+data.id+'').html('');
                $('#selected-'+data.id+'').attr('style','display:none');
                $('#weedout-'+data.id).attr('style','');
            }else if(data.errCode==0){
                alert(data.errMsg);
            }
        });
    });
}
//回复评价
function workAnswer(obj){
    var comment_id = obj.attr('work_id');
    var task_id = obj.attr('task_id');
    var name = obj.attr('woker_nam');
    $('#work-comment-pid-'+task_id+'').val(comment_id);
    $('#work-comment-answer-'+task_id+'').attr('placeholder','回复'+name);
    $('#work-comment-answer-'+task_id+'').focus();
}
//ajax维权举报
function commentInit(){
    $('#work-comment-pid').val(0);
}
//评论或回复
function ajaxComment(obj)
{
    var work_id = obj.attr('work_id');
    var task_id = obj.attr('task_id');
    var token = obj.attr('token');
    var pid = $('#work-comment-pid-'+work_id+'').val();

    var comment = $('#work-comment-answer-'+work_id+'').val();
    var url = obj.attr('url');
    $.post(url,{'_token':token,'task_id':task_id,'work_id':work_id,'pid':pid,'comment':comment},function(data){
        if(data.errCode==1){
            var html = '';
            if(data.parent_user!=undefined)
            {
                html += "<div class=\"itemdiv dialogdiv\"><div class=\"user\"><img alt=\"Alexa's Avatar\" src=\""+data.avatar_md5+"\"  onerror=\"onerrorImage('" + data.onerror_img + "',$(this))\"><\/div><div class=\"body\"><div class=\"time\"><i class=\"ace-icon fa fa-clock-o\"><\/i><span class=\"green\">回复于"+data.created_at+"<\/span><\/div><div class=\"name\"><a href=\"#\">"+data.nickname+" 回复 "+data.parent_user+"<\/a><\/div><div class=\"text\">"+data.comment+"<\/div><div class=\"tools\"><a href=\"javascript:void(0);\" class=\"btn btn-minier btn-info allbtn \" onclick='workAnswer($(this))' woker_nam='"+data.nickname+"' work_id='"+data.id+"' task_id = '"+data.work_id+"'><i class=\"icon-only ace-icon fa fa-share \"><\/i> 回复<\/a><\/div><\/div><\/div>";
            }else{
                html += "<div class=\"itemdiv dialogdiv\"><div class=\"user\"><img alt=\"Alexa's Avatar\" src=\""+data.avatar_md5+"\" onerror=\"onerrorImage('" + data.onerror_img + "',$(this))\"><\/div><div class=\"body\"><div class=\"time\"><i class=\"ace-icon fa fa-clock-o\"><\/i><span class=\"green\">回复于"+data.created_at+"<\/span><\/div><div class=\"name\"><a href=\"#\">"+data.nickname+"<\/a><\/div><div class=\"text\">"+data.comment+"<\/div><div class=\"tools\"><a href=\"javascript:void(0);\" class=\"btn btn-minier btn-info allbtn\" onclick='workAnswer($(this))' woker_nam='"+data.nickname+"' work_id='"+data.id+"' task_id = '"+data.work_id+"'><i class=\"icon-only ace-icon fa fa-share \"><\/i> 回复<\/a><\/div><\/div><\/div>";
            }
            $('#work-comment-'+work_id).children("div[class='scroll-content']").append(html);
			//回复加一
			var replyNum=$('.evaluateshow').children('span').text();
			$('.evaluateshow').children('span').text(parseInt(replyNum) + parseInt(1));
        }else if(data.errCode==0){
            $.gritter.add({
                text: '<div><span class="text-center"><h5>' + data.errMsg + '</h5></span></div>',
                class_name: 'gritter-info gritter-center'
            });
        }
    });
    $('.hfchat-text').val('');
}
//验收通过
function delivery(obj)
{
    var id = obj.attr('work_id');
    $.get('/task/check',{'work_id':id},function(data)
    {
        if(data.errCode==1){
            var html = "<a href='/task/evaluate?id="+data.id+"&work_id="+data.work_id+"' target='_blank' class=\"btn btn-primary btn-sm btn-blue btn-big1 bor-radius2 \">去评价</a>";
            $('#check-'+id).html(html);
        }else if(data.errCode==2)
        {
            alert('验收失败！');
        }
    });
}
//ajax提交维权
function ajaxRights(obj)
{
    var work_id = obj.attr('work_id');
    var form_data = $('#right-form-'+work_id).serialize();
    $.post('/task/ajaxRights',form_data,function(data){
        if(data.errCode==0){
            alert(data.errMsg);
        }else{
            $('#check-'+data['id']).remove();//移除评价或验收按钮
        }
    });

}
//维权举报弹窗
function report(obj)
{
    var work_id = obj.attr('work_id');
    $('#report-work-id').val(work_id);
}
//ajax举报维权
function ajaxReport()
{
    var form_data = $('#report-form').serialize();
    $.post('/task/report',form_data,function(data){
        if(data.errCode==0){
            $.gritter.add({
                title: '消息提示：',
                text: data.errMsg,
                class_name: 'gritter-info gritter-center'
            });
        }else if(data.errCode==1){
            $.gritter.add({
                title: '消息提示：',
                text: data.errMsg,
                class_name: 'gritter-info gritter-center'
            });
        }
    });
    $('.jbchat-text').val('');
}
//ajax投稿分页
function ajaxPageWorks(obj)
{
    var url = obj.attr('url');
    $.get(url,function(data){
        $('#home2').html(data);
    });
    $('.evaluateshow').on('click',function(){
        $(this).parent().next().slideToggle();
    });z
}
function ajaxPageDelivery(obj)
{
    var url = obj.attr('url');
    $.get(url,function(data){
        $('#home3').html(data);
    });
}
function ajaxPageComment(obj)
{
    var url = obj.attr('url');
    $.get(url,function(data){
        $('#home4').html(data);
    });
}
function ajaxPageRights(obj)
{
    var url = obj.attr('url');
    $.get(url,function(data){
        $('#home5').html(data);
    });
}

//回复初始化
function comment_init()
{
    $('.easy-pie-chart.percentage').each(function(){
        var $box = $(this).closest('.infobox');
        var barColor = $(this).data('color') || (!$box.hasClass('infobox-dark') ? $box.css('color') : 'rgba(255,255,255,0.95)');
        var trackColor = barColor == 'rgba(255,255,255,0.95)' ? 'rgba(255,255,255,0.25)' : '#E2E2E2';
        var size = parseInt($(this).data('size')) || 50;
        $(this).easyPieChart({
            barColor: barColor,
            trackColor: trackColor,
            scaleColor: false,
            lineCap: 'butt',
            lineWidth: parseInt(size/10),
            animate: /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase()) ? false : 1000,
            size: size
        });
    })

    $('.sparkline').each(function(){
        var $box = $(this).closest('.infobox');
        var barColor = !$box.hasClass('infobox-dark') ? $box.css('color') : '#FFF';
        $(this).sparkline('html',
            {
                tagValuesAttribute:'data-values',
                type: 'bar',
                barColor: barColor ,
                chartRangeMin:$(this).data('min') || 0
            });
    });


    //flot chart resize plugin, somehow manipulates default browser resize event to optimize it!
    //but sometimes it brings up errors with normal resize event handlers
   /* $.resize.throttleWindow = false;*/

    var placeholder = $('#piechart-placeholder').css({'width':'90%' , 'min-height':'150px'});
    var data = [
        { label: "social networks",  data: 38.7, color: "#68BC31"},
        { label: "search engines",  data: 24.5, color: "#2091CF"},
        { label: "ad campaigns",  data: 8.2, color: "#AF4E96"},
        { label: "direct traffic",  data: 18.6, color: "#DA5430"},
        { label: "other",  data: 10, color: "#FEE074"}
    ]
    /**
     we saved the drawing function and the data to redraw with different position later when switching to RTL mode dynamically
     so that's not needed actually.
     */
    placeholder.data('chart', data);
//        placeholder.data('draw', drawPieChart);


    //pie chart tooltip example
    var $tooltip = $("<div class='tooltip top in'><div class='tooltip-inner'></div></div>").hide().appendTo('body');
    var previousPoint = null;

    placeholder.on('plothover', function (event, pos, item) {
        if(item) {
            if (previousPoint != item.seriesIndex) {
                previousPoint = item.seriesIndex;
                var tip = item.series['label'] + " : " + item.series['percent']+'%';
                $tooltip.show().children(0).text(tip);
            }
            $tooltip.css({top:pos.pageY + 10, left:pos.pageX + 10});
        } else {
            $tooltip.hide();
            previousPoint = null;
        }

    });






    var d1 = [];
    for (var i = 0; i < Math.PI * 2; i += 0.5) {
        d1.push([i, Math.sin(i)]);
    }

    var d2 = [];
    for (var i = 0; i < Math.PI * 2; i += 0.5) {
        d2.push([i, Math.cos(i)]);
    }

    var d3 = [];
    for (var i = 0; i < Math.PI * 2; i += 0.2) {
        d3.push([i, Math.tan(i)]);
    }



    $('#recent-box [data-rel="tooltip"]').tooltip({placement: tooltip_placement});
    function tooltip_placement(context, source) {
        var $source = $(source);
        var $parent = $source.closest('.tab-content')
        var off1 = $parent.offset();
        var w1 = $parent.width();

        var off2 = $source.offset();
        //var w2 = $source.width();

        if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
        return 'left';
    }


    $('.dialogs,.comments').ace_scroll({
        size: 300
    });


    //Android's default browser somehow is confused when tapping on label which will lead to dragging the task
    //so disable dragging when clicking on label
    var agent = navigator.userAgent.toLowerCase();
    if("ontouchstart" in document && /applewebkit/.test(agent) && /android/.test(agent))
        $('#tasks').on('touchstart', function(e){
            var li = $(e.target).closest('#tasks li');
            if(li.length == 0)return;
            var label = li.find('label.inline').get(0);
            if(label == e.target || $.contains(label, e.target)) e.stopImmediatePropagation() ;
        });


    //show the dropdowns on top or bottom depending on window height and menu position
    $('#task-tab .dropdown-hover').on('mouseenter', function(e) {
        var offset = $(this).offset();

        var $w = $(window)
        if (offset.top > $w.scrollTop() + $w.innerHeight() - 100)
            $(this).addClass('dropup');
        else $(this).removeClass('dropup');
    });


}
//联系雇主
$('#contactMe').on('click',function(){
    var title = $('.title').val();
    var content = $('.content').val();
    var js_id = $('#contactMeId').val();
    $.ajax({
        type: 'post',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '/bre/contactMe',
        data: {title:title,content:content,js_id:js_id},
        dataType:'json',
        success: function(data){
            if(data.code == 1){
                location.reload();
            }else{
                $.gritter.add({
                    //            title: '消息提示：',
                    text: '<div><span class="text-center"><h5>' + data.msg + '</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }
        }
    });
});

//联系投稿人
$('.contactHe').on('click',function(){
    var js_id = $(this).attr('data-values');
    $('#contactHeId').val(js_id);
});
$('#contactHe').on('click',function(){
    var title = $('.titleHe').val();
    var content = $('.contentHe').val();
    console.log(title);
    var js_id = $('#contactHeId').val();
    $.ajax({
        type: 'post',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: '/bre/contactMe',
        data: {title:title,content:content,js_id:js_id},
        dataType:'json',
        success: function(data){
            if(data.code == 1){
                location.reload();
            }else{
                $.gritter.add({
                    //            title: '消息提示：',
                    text: '<div><span class="text-center"><h5>' + data.msg + '</h5></span></div>',
                    class_name: 'gritter-info gritter-center'
                });
            }
        }
    });
});

function rememberTable(obj)
{
    var index = obj.attr('index');
    $.get('/task/rememberTable',{'index':index},function(){});
}


