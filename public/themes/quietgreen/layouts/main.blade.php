<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! strip_tags(Theme::get('title'),'<img>') !!}</title>
    <meta name="keywords" style="display:none;" content="{!!  Theme::get('keywords') !!}">
    <meta name="description" content="{!! Theme::get('description') !!}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @if(Theme::get('engine_status')==1)
    <meta name="robots" content="noindex,follow">
    @endif
    @if(isset(Theme::get('basis_config')['css_adaptive']) && Theme::get('basis_config')['css_adaptive'] == 1)
        <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
    @else
        <meta name="viewport" content="initial-scale=0.1">
    @endif
    <meta property="qc:admins" content="232452016063535256654" />
    <meta property="wb:webmaster" content="19a842dd7cc33de3" />
    @if(!empty(Theme::get('site_config')['browser_logo']) && is_file(Theme::get('site_config')['browser_logo']))
        <link rel="shortcut icon" href="{{ url(Theme::get('site_config')['browser_logo']) }}" />
    @else
        <link rel="shortcut icon" href="{{ Theme::asset()->url('images/favicon.ico') }}" />
        @endif
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="/themes/quietgreen/assets/plugins/bootstrap/css/bootstrap.min.css">
    {!! Theme::asset()->container('specific-css')->styles() !!}
    <link rel="stylesheet" href="/themes/quietgreen/assets/plugins/ace/css/ace.min.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/index/common.css"/>
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/index/case.css"/>
    {!! Theme::asset()->container('old-css')->styles() !!}
    {{--<link rel="stylesheet" href="/themes/default/assets/css/main.css">--}}
    <link rel="stylesheet" href="/themes/default/assets/css/header.css">
    {{--<link rel="stylesheet" href="/themes/default/assets/css/footer.css">--}}

    {{--<link rel="stylesheet" href="../../assets/plugins/bootstrap/css/bootstrap.min.css"/>--}}
    {{--<link rel="stylesheet" href="../../assets/plugins/ace/css/ace.min.css">--}}
    {{--<link rel="stylesheet" href="../../assets/css/font-awesome.min.css"/>--}}
    {{--<link rel="stylesheet" href="../../assets/css/index/common.css"/>--}}

    <link rel="stylesheet" href="/themes/quietgreen/assets/css/{!! Theme::get('color') !!}/style.css">
    {!! Theme::asset()->container('custom-css')->styles() !!}
    {{--<script src="/themes/default/assets/plugins/ace/js/ace-extra.min.js"></script>--}}
</head>
<body>
{!! Theme::partial('header') !!}

{!! Theme::content() !!}

<footer>
    {!! Theme::partial('footer') !!}
</footer>

<script src="/themes/quietgreen/assets/plugins/jquery/jquery.min.js"></script>
<script src="/themes/default/assets/js/doc/jquery.cookie.js"></script>
<script src="/themes/quietgreen/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
{{--<script src="/themes/default/assets/plugins/ace/js/ace.min.js"></script>--}}
{{--<script src="/themes/default/assets/plugins/ace/js/ace-elements.min.js"></script>--}}
<script src="/themes/quietgreen/assets/js/common.js"></script>
<script src="/themes/quietgreen/assets/js/index.js"></script>

{{--<script src="/themes/default/assets/js/nav.js"></script>--}}

{!! Theme::asset()->container('specific-js')->scripts() !!}

{!! Theme::asset()->container('custom-js')->scripts() !!}
{!! Theme::asset()->container('cistom-css')->scripts() !!}
<div class="one_key_issur_box">
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="pull-left issur_style_m3">一键发布任务</span>
            <span class="pull-right close_box_issur">&times;</span>
        </div>
        <div class="panel-body">
            <form>
                <div class="form-group">
                    <label for="exampleInputEmail1">需求标题:</label>
                    <input type="text" name="title" class="form-control" info="需求标题不能为空" id="title_m1" placeholder="请输入需求标题">
                    <p class="info_content_m4"></p>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">需求描述:</label>
                    <textarea class="form-control" placeholder="请输入需求描述" name="describe" info="需求描述内容不能为空" id="describe_m1" rows="3"></textarea>
                    <p class="info_content_m4"></p>
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword1">手机号码:</label>
                    <input type="number" class="form-control" name="phone" info="号码格式不合法" id="phone_m1" placeholder="请输入您的手机号">
                    <p class="info_content_m4 mobile_check"></p>
                </div>
                <div class="form-group">
                    <div class="title_content_m3">手机验证码:</div>
                    <input type="number" name="testNumber" phoneEmpty="请先输入有效的手机号码"  info="验证码不正确" class="form-control form_control_m1 pull-left" id="testNumber_m1" placeholder="请输入验证码">
                    <a class="btn btn-warning btn_default_m1" token="{{csrf_token()}}">获取验证码</a>
                    <p class="info_content_m4"></p>
                </div>
                <a class="clearfix btn btn-primary btn_default_m2">马上发布</a>
            </form>
        </div>
    </div>
</div>
</body>

