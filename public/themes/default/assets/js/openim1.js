
var fromuid = $('input[name="imformuid"]').val();
var fromAvarar = $('input[name="imformavatar"]').val();
var toUid = $('input[name="imtouid"]').val();
var toAvatar = $('input[name="imtoavatar"]').val();
window.onload = function(){
    WKIT.init({
        container: document.getElementById('J_lightDemo'),
        uid: fromUid,
        appkey: 23580081,
        credential: '123456',//登录密码
        touid: toUid,
        avatar: fromAvarar,
        toAvatar: toAvAtar,
        theme: 'orange',
        sendBtn:true,
        onMsgReceived:function(){
            var formId = $("h4[data-toUid='"+toUid+"']");
            var imSideright = $('.im-side2 .im-side1-list2 .result-container>li');
            $(imSideright).find(formId).text(toUsername+'您有新消息收到');
            $(imSideright).find(formId).parent().parent().addClass('shake-constant shake-delay im-shake');
        }
    });
};
