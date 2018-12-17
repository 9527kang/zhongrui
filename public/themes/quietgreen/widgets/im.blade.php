{!! Theme::asset()->container('im-css')->usepath()->add('im-css','css/im.css') !!}
{!! Theme::asset()->container('im-css')->styles() !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('openim-js', 'js/openim.js') !!}

<div class="im clearfix hidden-xs">
    <div id="imblade">
        <div class="im-side1 pull-left im-info im-ck ">
            <div class="text-right im-side1-colos">
                <a href=""><i class="fa fa-close imClose" data-dismiss="alert"></i></a>
            </div>
            <div class="im-side1-list1 clearfix">
                <div class="pull-left chat-t-head">
                    <img src="" alt="..." class="img-circle" width="44" height="44"/>
                </div>
                <div class="im-side1-title">
                    <h4 class="f-size16 mg-margin0 title-tit cor-gury51 chat-t-name"></h4>

                    <p class="cor-gury9c f-size12 tit-time mg-margin0 chat-t-sign"></p>
                </div>
            </div>

            <div id="J_lightDemoWrap" style="width: 640px;height: 515px;position:fixed;right:260px;bottom:0;"></div>


        </div>
    </div>

    <div class="im-side2 pull-left">
        @if(Auth::check())
            <div class=" collapse in im-info imContact-info ">
                <div class="im-side1-list1 clearfix">
                    <div class="pull-left">
                        <img src="@if(!empty(Theme::get('avatar'))) {!!  url(Theme::get('avatar')) !!} @else {!! Theme::asset()->url('images/defaulthead.png') !!} @endif" alt="..." class="img-circle" width="63" height="63"/>
                    </div>
                    <div class="im-side1-title">
                        <p class=" title-tit">
                            <a class="f-size14 cor-gury51">{!! Theme::get('username') !!}</a>
                        </p>

                        <p class="cor-gury9c f-size12 tit-time mg-margin0">
                            <a href="javascript:;"><i class="fa fa-envelope"></i></a>
                            <a href="{!! url('user/messageList/4') !!}">进入消息中心</a>
                        </p>
                        <a href="" class="im-colose-x"><i class="fa fa-close imClose" data-dismiss="alert"></i></a>
                    </div>
                </div>
                <div class="im-side1-list2 clearfix">
                    <!--联系人列表-->
                    @if(!empty($attention))

                        <ul class="mg-margin0 pd-padding0 result-container" id="urse">
                            @foreach($attention as $item)
                                <li class="im-side-itm clearfix listImg item1" onclick="sendImMessage(this,'{{$item['id']}}','{!! url($item['avatar']) !!}','{{$item['name']}}')" data-uid="{{$item['id']}}" data-avatar="{!! url($item['avatar']) !!}" data-username="{{$item['name']}}">
                                    <div class="pull-left">
                                        <img src="@if(!empty($item['avatar'])){!! url($item['avatar']) !!}@else{!! Theme::asset()->url('images/haed.png') !!} @endif" alt="..." class="img-circle" width="44" height="44"/>
                                    </div>
                                    <div class="im-side1-title">
                                        <h4 class="f-size14 mg-margin0 title-tit cor-gury51" data-toUid="{!! $item['id'] !!}">{!! $item['name'] !!}</h4>

                                        <p class="cor-gury9c f-size12 tit-time mg-margin0">
                                            @if($item['autograph']){!! mb_substr($item['autograph'], 0, 10, 'utf-8') !!}@else 这家伙都懒的签名！ @endif
                                        </p>
                                    </div>
                                </li>
                            @endforeach

                        </ul>
                    @else
                        <ul class="mg-margin0 pd-padding0">
                            <li class="center">
                                暂无联系人
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <div class="im-side1-list3 clearfix imContact">
            <i class="fa fa-paper-plane f-size20 pull-left mg-top12"></i>
            @if(Auth::check())
                <b class="f-size12">{!! count($attention) !!} 位联系人 </b>
                <i class="fa fa-chevron-up pull-right mg-top18 Top"></i>
            @else
                <a href="{!! url('login') !!}">请先登录</a>
            @endif

        </div>

    </div>

</div>

<input type="hidden" name="fromUid" value="@if(isset(Auth::User()->id)){!! Auth::User()->id !!}@endif">
<input type="hidden" name="fromAvatar" value="{!!  url(Theme::get('avatar')) !!}">
<input type="hidden" name="imappkey" value="{!! Theme::get('open_im_appkey') !!}">
<input type="hidden" name="open_im_pass" value="{!! Session::get('open_im_pass') !!}">


<script src="/themes/quietgreen/assets/js/doc/jquery.min.js"></script>
<script src="https://g.alicdn.com/aliww/??h5.imsdk/4.0.1/scripts/yw/wsdk.js,h5.openim.kit/0.5.0/scripts/kit.js" charset="utf-8"></script>

<script>

    function sendImMessage(obj,toUid,toAvAtar,toUsername)
    {
        $('.im-side-itm').removeAttr('onclick');

        //获取所有聊天对象
         var allTouid = [];
         $('.im-side-itm').each(function(){
         var toOneUid = $(this).attr('data-uid');
         allTouid.push(toOneUid);
         });

        var appkey = $('input[name="imappkey"]').val();
        var credential = $('input[name="open_im_pass"]').val();
        var fromAvatar = $('input[name="fromAvatar"]').val();
        var thisObj = $(obj);
        thisObj.find('h4').text(toUsername);
        thisObj.removeClass('shake-constant shake-delay im-shake');
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
            toAvatar: toAvAtar,
            theme: 'orange',
            sendBtn:true,
            onloginsuccess:function(){
                cosole.log('login success');
            },
            onMsgReceived:function(data){
                console.log(data);
                var recviveUid = data.touid;
//                var strlen = recviveUid.length;
//                var toUser = '';
//                $.each(allTouid,function(n,i){
//                    if(recviveUid.lastIndexOf(i) == strlen- i.length){
//                        toUser = i;
//                    }
//                });
//                var formId = $("h4[data-toUid='"+toUser+"']");
//                var name = $(formId).text();
//                $(formId).text(name+'您有新消息收到');
//                var imSideright = $('.im-side2 .im-side1-list2 .result-container>li');
//                $(imSideright).find(formId).parent().parent().addClass('shake-constant shake-delay im-shake');
            }

        });

    }

    $(document).on('click','.im-side-itm',function(){
        var toUid = $(this).attr('data-uid');
        var toAvatar = $(this).attr('data-avatar');
        var toName = $(this).attr('data-username');
        $(this).find('h4').text(toName);
        $(this).removeClass('shake-constant shake-delay im-shake');
        toUid = toUid + "";
        WKIT.switchTouid({
            touid: toUid,
            toAvatar: toAvatar
        });
    });

</script>


