<div class="widget-header mg-bottom20 mg-top12 widget-well">
    <div class="widget-toolbar no-border pull-left no-padding">
        <ul class="nav nav-tabs">
            <li>
                <a href="/manage/config/appalipay">app支付宝支付配置</a>
            </li>
            <li>
                <a href="/manage/config/appwechat">app微信支付配置</a>
            </li>
            <li class="active">
                <a href="/manage/config/appMessage">聊天配置</a>
            </li>
            <!--<li>
                <a href="/manage/config/wechatpublic">微信端配置</a>
            </li>-->
        </ul>
    </div>
</div>
<div class="g-backrealdetails clearfix bor-border interface">

    <div class="space-8 col-xs-12"></div>
    <!-- PAGE CONTENT BEGINS -->
    <form class="form-horizontal" role="form" enctype="multipart/form-data" method="post" action="/manage/config/appMessage">
       {!! csrf_field() !!}

        <div class="form-group interface-bottom col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"> appkey： </label>

            <div class="col-sm-4">
                <input type="text" id="form-field-1" placeholder="" class="col-xs-10 col-sm-12" name="appkey" @if(isset($message['appkey']))value="{{$message['appkey']}}" @endif/>
            </div>
            <div class="col-sm-5 h5 cor-gray87"><i class="fa fa-exclamation-circle cor-orange text-size18"></i>(阿里百川应用的APPKEY)</div>
        </div>

        <div class="form-group interface-bottom col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"> secretKey： </label>

            <div class="col-sm-4">
                <input type="text" id="form-field-1" placeholder="" class="col-xs-10 col-sm-12" name="secretKey" @if(isset($message['secretKey']))value="{{$message['secretKey']}}" @endif/>
            </div>
            <div class="col-sm-5 h5 cor-gray87"><i class="fa fa-exclamation-circle cor-orange text-size18"></i>(阿里百川应用的APPSecret) </div>
        </div>

        <div class="col-xs-12">
            <div class="clearfix row bg-backf5 padding20 mg-margin12">
                <div class="col-xs-12">
                    <div class="col-sm-1 text-right"></div>
                    <div class="col-sm-10"><button type="submit" class="btn btn-sm btn-primary">提交</button></div>
                </div>
            </div>
        </div>

    </form>

</div><!-- /.col -->


{!! Theme::asset()->container('specific-js')->usepath()->add('datepicker', 'plugins/ace/css/bootstrap-datetimepicker/datepicker.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('jquery.webui-popover', '/plugins/jquery/css/jquery.webui-popover.min.css') !!}
{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}


{!! Theme::asset()->container('custom-js')->usepath()->add('configemail', 'js/doc/configemail.js') !!}
