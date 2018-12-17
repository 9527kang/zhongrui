/**
 * Created by quanke on 2018/02/08.
 */
$(function () {

    

    $('.im .imClose').on('click', function () {

        arr.length = 0;
        $('.im-info').stop().fadeOut();
        $('.im .im-side2 .im-side1-list3').css("border-radius", "5px");
        $('.imContact .Top').removeClass('fa-chevron-down');
        $(".im-side ul").empty();
        return;

    });

    $('.im .imContact').on('click', function () {

        $('.imContact-info').stop().fadeToggle();
        $('.im .im-side2 .im-side1-list3').css("border-radius", "0 0 5px 5px");
        $('.imContact .Top').toggleClass(' fa-chevron-down');
        $('.im-side2').addClass('im-container');
        return;

    });

    $('.search-wrapper').on('click', function () {

        $('.search-btn').fadeOut();
        $('.search-wrapper').css({"padding-left": "10px"});
        $('.search-close').fadeIn();

    });
    $('.search-close').on('click', function () {

        $('.search-btn').fadeIn();
        $('.search-wrapper').css({"padding-left": "40px"});
        $('.search-close').fadeOut();

    });

    //右侧联系人
    var arr = [];
    $(document).on('click','.im-side2 .im-side1-list2 .result-container>li',function(){

        var sImg = $(this).find('img').attr('src');
        var tTxt = $(this).find('h4').html();
        var toUid = $(this).find('h4').attr('data-toUid');

        sign = $(this).find('p').html();

        $(this).css('background-color','#f5f5f5').siblings().css('background','#fff');
        $('.chat-t-name').html(tTxt);
        $('.chat-t-sign').html(sign);
        $('.chat-t-head img').attr('src', sImg);
        $(this).removeClass('shake-constant shake-delay im-shake');
        $('.qq-chat-you i').html($(this).find('.qq-hui-name i').html());
        $('.qq-chat-ner').html($(this).find('.qq-hui-txt').html());
        $('.im-ck').fadeIn();

    });


     //任务ico
     $(document).on('click','.taskmessico,.taskconico,.shop-im',function(){
         var toUid = $(this).attr('data-values');
         var isOpenIm = 0;
         //判断聊天对象是否为空
         var allTouid = [];
         if($('#urse').children().hasClass('im-side-itm')){
             //获取所有聊天对象
             $('.im-side-itm').each(function(){
                 var toOneUid = $(this).attr('data-uid');
                 allTouid.push(toOneUid);
                 if($(this).attr('onclick') != undefined){
                     isOpenIm = isOpenIm + 1;
                 }
             });
         }else{
             isOpenIm = 1;
         }


         $.ajax({
             headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
             },
             type: "POST",
             url: '/im/getImUserInfo',
             data: {
             toUid: toUid
             },
             success: function (data) {
                 if(data.code = 200){
                     var info = JSON.parse(data);
                     if($.inArray(toUid, allTouid) < 0){
                         var html = '<li class="im-side-itm clearfix listImg item1" data-uid="'+toUid+'" data-avatar="'+info.data.avatar+'" data-username="'+info.data.username+'"><div class="pull-left"><img src='+info.data.avatar+'" alt="..." class="img-circle" width="44" height="44"/></div><div class="im-side1-title"><h4 class="f-size14 mg-margin0 title-tit cor-gury51" data-toUid="'+toUid+'">'+info.data.username+'</h4><p class="cor-gury9c f-size12 tit-time mg-margin0">'+info.data.sign+'</p></div></li>';
                         $('#urse').prepend(html);
                         allTouid.push(toUid);
                     }
                     if(isOpenIm > 0){
                         $('.im-side-itm').removeAttr('onclick');
                         var appkey = $('input[name="imappkey"]').val();
                         var credential = $('input[name="open_im_pass"]').val();
                         var fromAvatar = $('input[name="fromAvatar"]').val();
                         var fromUid = $('input[name="fromUid"]').val();
                         fromUid = fromUid + "";
                         toUid = toUid + "";
                         WKIT.init({
                             container: document.getElementById('J_lightDemoWrap'),
                             uid: fromUid,
                             appkey: appkey,
                             credential: credential,//登录密码
                             touid: toUid,
                             avatar: fromAvatar,
                             toAvatar: info.data.avatar,
                             theme: 'orange',
                             sendBtn:true,
                             onMsgReceived:function(data){
                                 console.log(data);
                                 var recviveUid = data.touid;
                                 //var strlen = recviveUid.length;
                                 //var toUser = '';
                                 //$.each(allTouid,function(n,i){
                                 //    if(recviveUid.lastIndexOf(i) == strlen- i.length){
                                 //       toUser = i;
                                 //    }
                                 //});
                                 //var formId = $("h4[data-toUid='"+toUser+"']");
                                 //var name = $(formId).text();
                                 //$(formId).text(name+'您有新消息收到');
                                 //var imSideright = $('.im-side2 .im-side1-list2 .result-container>li');
                                 //$(imSideright).find(formId).parent().parent().addClass('shake-constant shake-delay im-shake');
                             }
                        });
                     }else{
                         toUid = toUid + "";
                         WKIT.switchTouid({
                         touid: toUid,
                         toAvatar: info.data.avatar
                         });
                     }
                     $('.chat-t-name').html(info.data.username);
                     $('.chat-t-sign').html(info.data.sign);
                     $('.chat-t-head img').attr('src', info.data.avatar);
                     $('.im-side1-title').find('h4').attr('data-toUid', toUid);
                     $('.im .im-side1').stop().slideDown();
                 }
             }
         });


         $('.imContact-info').stop().slideDown(function () {
             $('.imContact-info .im-side1-list2').css('margin-bottom','45px')
         });

     });



     //任务ico
     $('.taskmessico,.taskconico,.shop-im').on('click',function(){


     $('.im .im-side1').stop().slideDown();/*
     $('.imContact-info').stop().slideDown(function () {
     $('.imContact-info .im-side1-list2').css('margin-bottom','45px')
     });*/

     });



        
});
