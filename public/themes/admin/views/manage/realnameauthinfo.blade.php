
<div class="tier_box_m1">
    <img src="" alt="bigImg">
    <span class="close_icon">X</span>
</div>
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">实名认证详细信息</h3>
<div class="g-backrealdetails clearfix bor-border">
    <div class="realname-bottom clearfix col-xs-12">
        <p class="col-md-1 text-right">真实姓名：</p>
        <p class="col-md-11">{!! $realname->realname !!}</p>
    </div>

    <div class="realname-bottom clearfix col-xs-12">
        <p class="col-md-1 text-right">身份证号：</p>
        <p class="col-md-11">{!! CommonClass::starReplace($realname->card_number, 4, 10) !!}</p>
    </div>

    <div class="realname-bottom clearfix col-xs-12">
        <p class="col-md-1 text-right">证件正面：</p>
        <p class="col-md-11 click_box_showImg"><img src="{!! url($realname->card_front_side) !!}"></p>
    </div>

    <div class="realname-bottom clearfix col-xs-12">
        <p class="col-md-1 text-right">证件反面：</p>
        <p class="col-md-11 click_box_showImg"><img src="{!! url($realname->card_back_dside) !!}"></p>
    </div>

    <div class="realname-bottom clearfix col-xs-12">
        <p class="col-md-1 text-right">示范照片：</p>
        <p class="col-md-11 click_box_showImg"><img src="{!! url($realname->validation_img) !!}"></p>
    </div>
    {{--<div class="realname-bottom clearfix col-xs-12">
        <label class="col-md-1 text-right">项目时间：</label>
        <p class="col-md-10"><input type="text">～<input type="text"></p>
    </div>--}}
    @if($realname->status == 0)
    <div class="col-xs-12">
    	<div class="clearfix row bg-backf5 padding20 mg-margin12">
    		<div class="col-xs-12">
    			<div class="col-md-1 text-right"></div>
	    		<div class="col-md-10"><a href="{!! url('/manage/realnameAuthHandle/'. $realname->id. '/pass') !!}" class="btn btn-primary btn-sm">审核通过</a></div>
	
    		</div>
    	</div>
    </div>
    @endif

    	
</div>

<script>
    //点击弹出框关闭按钮
    $(document).on("click",".close_icon",function(){
        $(this).parent().css("display","none");
    })
    //双击图片显示大图
    $(document).on("dblclick",".click_box_showImg",function(){
        let srcUrl=$(this).find("img").attr("src");
        $(".tier_box_m1").find("img").attr("src",srcUrl);
        $(".tier_box_m1").css("display","block");
    })
</script>


{!! Theme::asset()->container('custom-css')->usePath()->add('backstage', 'css/backstage/backstage.css') !!}