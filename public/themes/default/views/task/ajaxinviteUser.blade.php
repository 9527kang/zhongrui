<ul class="invite_list">
    @forelse($inviteUser as $k => $v)
        <li class="pull-left">
            <div class="img_title">
                <div class="pull-left imgContent_01">
                    <a href="@if($v['shop_status'] == 1 && $v['shopId']) {!! url('shop/'.$v['shopId']) !!} @else{!! URL('bre/serviceEvaluateDetail/'.$v['id']) !!}@endif" target="_blank">
                        <img src="@if(is_file($v['avatar'])) {{url($v['avatar'])}} @else /themes/default/assets/images/default_avatar.png @endif" alt="logo" width="75" height="75">
                    </a>
                </div>
                <div class="pull-left title_content">
                    <h4><a href="@if($v['shop_status'] == 1 && $v['shopId']) {!! url('shop/'.$v['shopId']) !!} @else{!! URL('bre/serviceEvaluateDetail/'.$v['id']) !!}@endif" target="_blank">{{$v['name']}}</a></h4>
                    <div class="address_style">{{$v['address']}}</div>
                </div>
            </div>
            <div class="comment_num">
                <div class="pull-left comment_good">
                    <span>好评率: <a class="rata_number">{{$v['percent']}}%</a></span>
                </div>
                <div class="pull-right comment_btn" id="invite_user_desc{{$v['id']}}">
                    @if($v['is_invite'])
                        <div class="but_style btn_style01">已邀约</div>
                    @else
                        <div class="but_style" onclick="inviteUser('{{$v['id']}}','{{$taskId}}')">邀约TA</div>
                    @endif
                </div>
            </div>
        </li>
    @empty
    @endforelse
</ul>
<!-- 分页的地方 -->
<div class="pull-right" id="home6_page">
    {!! $inviteUser->appends($merge)->render() !!}
</div>

<script>
    $(function(){

        $('#home6_page .pagination').find('li').find('a').on('click',function(){
            var url = $(this).attr('href');
            $.get(url,function(data){
                $('#home6_desc').html(data);

            });
            return false;
        })
    });
    function inviteUser(uid,taskId){
        var url = '/task/inviteUser?taskId='+taskId+'&uid='+uid;
        $.get(url, function (data) {
            if (data.code == 1) {
                var element = '#invite_user_desc'+uid;
                $(element).html(' <div class="but_style btn_style01">已邀约</div>');
            } else  {
                alert(data.msg);
            }
        });
    }

</script>