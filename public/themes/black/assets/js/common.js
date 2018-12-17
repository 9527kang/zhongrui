$(function() {

    var title=document.getElementById("title_m1");
    var describe=document.getElementById("describe_m1");
    var phone=document.getElementById("phone_m1");
    var testNumber=document.getElementById("testNumber_m1");
    var testCode="";//存放验证码的变量

    if(title){
        title.onblur=function(){
            mouseBlur(this,/\S/);
        };
    }

    if(describe){
        describe.onblur=function(){
            mouseBlur(this,/\S/);
        };
    }

    if(phone){
        phone.onblur=function(){
            mouseBlur(this,/^1[3|4|5|8|7|9][0-9]\d{4,8}$/);
        };
    }

    if(testNumber){
        testNumber.onblur=function(){
            if(phone.value != ""){
                testNum(this,testCode);
            }else{
                var infoContent = $(this).attr("phoneEmpty");
                $(this).next().next().show().html(infoContent);
            }
        };
    }
    
    //失去焦点的验证
    function mouseBlur(slef,reg){
        if(reg.test($(slef).val())){
           $(slef).next().hide();
           return true;
        }else{
           var infoContent = $(slef).attr("info");
           $(slef).next().show().html(infoContent);
           return false;
        }
    }
    
    //验证码的校验
    function testNum(slef,code){
        if($(slef).val() != "" && $(slef).val() == code){
           $(slef).next().next().hide();
           return true;
        }else{
           var infoContent = $(slef).attr("info");
           $(slef).next().next().show().html(infoContent);
           return false;
        }
    }


    var countdown=60;
    var val=$(this);
    var flag=true;

    //获取验证码
    $(".btn_default_m1").click(function(){
        var thisBtn = $(this);
        if(flag == true){
            if($(phone).val() != ""){
                if(mouseBlur(phone,/^1[3|4|5|8|7|9][0-9]\d{4,8}$/)){
                    flag==true?settime($(this)):"";
                   $.ajax({
                       url:"/sendTaskCode?mobile="+phone.value,//验证码接口
                       type:"get",
                       dataType:"json",
                       success:(res)=>{
                            testCode = res.data;//赋值验证码
                       }
                   })
                }

            }else{
                var infoContent = $(testNumber).attr("phoneEmpty");
                $(testNumber).next().next().show().html(infoContent);
            }
        }
        
        
    })

    function settime(val){
        if (countdown == 0) {
            $(val).removeAttr("disabled");
            $(val).html("获取验证码");
            countdown = 60;
            flag=true;
        } else {
            $(val).attr("disabled", true);
            $(val).html("重新发送" + countdown);
            countdown--;
            flag=false;
            setTimeout(function() {
                settime(val)
            },1000)
        } 
    }

    //点击马上发布
    $(".btn_default_m2").click(function(){
        if(!mouseBlur(title,/\S/)){

        }else if(!mouseBlur(describe,/\S/)){

        }else if(!mouseBlur(phone,/^1[3|4|5|8|7|9][0-9]\d{4,8}$/)){

        }else if(!testNum(testNumber,testCode)){

        }else{
            $.ajax({
                url:"/fastPub",//发布接口
                data:{
                    title:$(title).val(),
                    mobile:$(phone).val(),
                    desc:$(describe).val(),
                    code:$(testNumber).val()
                },
                type:"post",
                dataType:"json",
                success:function(res){
                    if(res.code == 1){
                        $(".one_key_issur_box").hide();//请求成功关闭弹框
                        gritterAdd('发布成功，请等待平台审核');
                        $(title).val('');
                        $(phone).val('');
                        $(describe).val('');
                        $(testNumber).val('');
                    }else{
                        gritterAdd('发布失败');
                    }
                }
            })
        }
    })

    //点击关闭弹框按钮
    $(".close_box_issur").click(function(){
        $(".one_key_issur_box").hide();
    })

    //点击一键发布按钮打开弹框
    $("#oneKeyContent").click(function(){
        $(".one_key_issur_box").show();
    })




    if($("#selectM1")){
        var arr=[];
        //点击开打第一级   发布需求
        $("#selectM1").click(function(){
            var self=$(this);
            if($(this).hasClass("showData")){//关闭弹框
                $(this).parent().find(".parent_box_m1").css("display","none");
                $(this).parent().find(".tow_box_m1").css("display","none");
                $(this).removeClass("showData");
            }else{
                $(this).addClass("showData");
                var randTxt = eval($("#task_cate_create").attr('data-values'));
                var html="";
                for(i=0;i<randTxt.length;i++){
                    html+="<li>";
                    html+="<span name="+randTxt[i].name+" id="+randTxt[i].id+" class='one_content'>"+randTxt[i].name+"</span>";
                    if(randTxt[i].child_task_cate != undefined){
                        html+="<b class='next_one'></b>";
                        html+="<ul class='tow_box_m1'>";
                        for(j=0;j<randTxt[i].child_task_cate.length;j++){
                            html+="<li onclick='chooseCateId("+randTxt[i].child_task_cate[j].id+")'><span name="+randTxt[i].child_task_cate[j].name+" id="+randTxt[i].child_task_cate[j].id+" class='one_content'>"+randTxt[i].child_task_cate[j].name+"</span></li>";
                        }
                        html+="</ul>";
                    }
                    html+="</li>";
                }
                $(".first_box_m1").html(html);
                $(self).parent().find(".parent_box_m1").css("display","block");

            }
        });

        //点击打开第二级
        $(document).on("click",".first_box_m1>li",function(){
            $(this).parent().find(".oneStyle").removeClass("oneStyle");
            $(this).addClass("oneStyle");
            $(this).parent().find(".showBlockS").removeClass("showBlockS");
            $(this).find("ul").addClass("showBlockS");
            var oneText=$(this).children().eq(0).attr("name");
            arr[0]=$(this).children().eq(0).attr("id");
            $(".select_one").html(oneText);
        });

        //点击二级子菜单
        $(document).on("click",".showBlockS>li",function(e){
            e.stopPropagation();
            var oneText=$(this).children().eq(0).attr("name");
            var oneId=$(this).children().eq(0).attr("id");
            var titleContent=$(".select_one").html();
            if(titleContent.indexOf("/") != -1){
                var numIndex=titleContent.indexOf("/");
                titleContent=titleContent.slice(0,numIndex);
            }
            $(".select_one").html(titleContent+"/"+oneText);
            arr[1]=oneId;
            $(this).parents(".first_box_m1").find(".towStyle").removeClass("towStyle");
            $(this).addClass("towStyle");
            $(this).parents(".parent_box_m1").css("display","none");
            $(this).parents(".tow_box_m1").css("display","none");
        });
    }
    //倒计时
    function timer() {
        var delivery_deadline = $('.timer-check').attr('delivery_deadline');

        var timestamp2 = Date.parse(new Date(delivery_deadline));
        var ts = timestamp2 - (new Date());//计算剩余的毫秒数
        var dd = parseInt(ts / 1000 / 60 / 60 / 24, 10);//计算剩余的天数
        var hh = parseInt(ts / 1000 / 60 / 60 % 24, 10);//计算剩余的小时数
        var mm = parseInt(ts / 1000 / 60 % 60, 10);//计算剩余的分钟数
        var ss = parseInt(ts / 1000 % 60, 10);//计算剩余的秒数
        var timer = dd + "天" + hh + "时" + mm + "分" + ss + "秒";
        $('.timer-check').html(timer);
    }

    if ($('.timer-check').length) {
        setInterval(timer, 1000);
    }
    //top
    $(window).on('scroll', function () {
        var st = $(document).scrollTop();
        if (st > 0) {
            if ($('#main-container').length != 0) {
                var w = $(window).width(), mw = $('#main-container').width();
                if ((w - mw) / 2 > 70)
                    $('#go-top').css({'left': (w - mw) / 2 + mw + 20});
                else {
                    $('#go-top').css({'left': 'auto'});
                }
            }
            $('#go-top').fadeIn(function () {
                $(this).removeClass('dn');
            });
        } else {
            $('#go-top').fadeOut(function () {
                $(this).addClass('dn');
            });
        }
    });
    $('#go-top .go').on('click', function () {
        $('html,body').animate({'scrollTop': 0}, 500);
    });
    $("#go-top .uc-2vm ").bind('mouseenter', function () {
        $('#go-top .u-pop').show();
    });
    $("#go-top .uc-2vm ").bind('mouseleave', function () {
        $('#go-top .u-pop').hide();
    });
    $("#go-top .feedback ").bind('mouseenter', function () {
        $('#go-top .dnd').show();
    });
    $("#go-top .feedback ").bind('mouseleave', function () {
        $('#go-top .dnd').hide();
    });


    //top nav
    var divHoverLeft = 0;
    var aWidth = 0;

    $(document).ready(function () {
        var hWidth;
        var hLeft;
        if ($('.div-hover').length > 0) {
            if ($('.header-show').length > 0) {
                $('.header-show').show();
                hWidth = $('.hActive .topborbtm').width();
                hLeft = GetthisLeft($(".hActive")) + 12;
                $('.header-show').hide();
            } else {
                hWidth = $('.hActive .topborbtm').width();
                hLeft = GetthisLeft($(".hActive")) + 12;
            }
            $('.div-hover').css('width', hWidth);
            $('.div-hover').css('left', hLeft);
        }
        $(".topborbtm").on({
            'mouseenter': function () {
                SetDivHoverWidthAndLeft(this);
                $(".div-hover").stop().animate({width: aWidth - 24, left: divHoverLeft + 12}, 150);
            }
        });
        $(".topborbtm").on({
            'mouseleave': function (event) {
                $(".div-hover").stop().animate({width: hWidth, left: hLeft}, 150);
            }
        });
    });
    function SetDivHoverWidthAndLeft(element) {
        divHoverLeft = GetLeft(element);
        aWidth = GetWidth(element);
    }

    function GetWidth(ele) {
        return $(ele).parent().width();
    }

    function GetLeft(element) {
        var menuList = $(element).parent().prevAll();
        var left = 0;
        $.each(menuList, function (index, ele) {
            left += $(ele).width();
        });
        return left;
    }

    function GetthisLeft(element) {
        var menuList = $(element).prevAll();
        var left = 0;
        $.each(menuList, function (index, ele) {
            left += $(ele).width();
        });
        return left;
    }

    $(function () {
        $('.header-dropul').eq(0).show();
        $('.header-droptitle a').on('mouseover', function () {
            $('.header-dropul').hide();
            $('.header-dropul').eq($(this).index()).show();
        });

    });


    setTimeout("hovInputt()", 3000);

    function keyDownVal(elm) {
        $(elm).keydown(function (event) {
            $(this).siblings('.user-position').css({
                'top': '-20px'
            });
            $(this).parent().siblings('.user-position').css({
                'top': '-20px'
            });
            var sky = $('.inputLength').val();
        })
    }

    keyDownVal('.inputLength');

    function loginH(elm) {
        var loginH = $(elm);
        var h = loginH.height();

        loginH.css({
            'margin-top': '-' + h / 2 + 'px'
        })

    }

    loginH('.sign-main-bg .sign-main-content');


    function clickDownList(obj) {
        $(obj).click(function () {
            if ($(this).hasClass('fa-angle-down')) {

                $(this).addClass('fa-angle-up')
                    .removeClass('fa-angle-down');

                $(this).parent().parent().css({
                    'height': 'auto'
                });
            } else {

                $(this).addClass('fa-angle-down')
                    .removeClass('fa-angle-up');
                $(this).parent().parent().css({
                    'height': '70px'
                });
            }
        })
    }

    clickDownList('.show-next');

    function searchFlex(obj, add, input, width, attribute) {
        $(obj).on('click', function () {
            $(this).parent().toggleClass(add);
            var keys = $(this).siblings(input).val();
            if (keys.length > 0) {
                $(this).parent().width(width);
                $(this).css({
                    'background': 'none'
                });
                keys.val('');
                $(this).submit();
            } else {
                if ($(this).parent().hasClass(add)) {
                    $(this).css({
                        'background': 'none'
                    });
                } else {
                    $(this).css({
                        'background': attribute
                    });
                }
                return false;
            }
        })
    }

    searchFlex('.header-right .header-search .fom-search .fa-search', 'search_open', '.inputx', '166px', '#282828');


    function toggleUp(obj) {
        $(obj).on('click', function () {
            if ($(this).find('i').hasClass('glyphicon-arrow-down')) {
                $(this).find('i').addClass('glyphicon-arrow-up').removeClass('glyphicon-arrow-down')
            } else {
                $(this).find('i').addClass('glyphicon-arrow-down').removeClass('glyphicon-arrow-up')
            }
        })
    }

    toggleUp('.mshop-sort a');


    function hov(obj) {
        $(obj).hover(function () {
            $('.foc-ewm').toggle()
        })
    }

    hov('.ifooter-wx');

});

//onerror加载默认图片
function onerrorImage(url, obj) {
    obj.attr('src', url);
}

function hovInputt() {

    var sky = $('.inputLength').val();
    if (sky != '') {
        $('.user-position').css({
            'top': '-20px'
        })
    }

}
function gritterAdd(tips){
    $.gritter.add({
        text:'<div><span class="text-center"><h5>'+tips+'</h5></span></div>',
        time:2000,
        position: 'bottom-center',
        class_name: 'gritter-center gritter-info',
    });
}
function switchSearch(obj) {
    var url = $(obj).attr('url');
    var name = $(obj).text();

    $(obj).closest('form').attr('action', url);
    $(obj).closest('ul').parents().find("a:firstChild").text(name);
}