<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! Theme::get('title') !!}</title>
    <meta name="keywords" content="{!! Theme::get('keywords') !!}">
    <meta name="description" content="{!! Theme::get('description') !!}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{--<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">--}}
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
    <link rel="stylesheet" href="/themes/default/assets/plugins/bootstrap/css/bootstrap.min.css">
    {!! Theme::asset()->container('specific-css')->styles() !!}
    <link rel="stylesheet" href="/themes/default/assets/plugins/ace/css/ace.min.css">
    <link rel="stylesheet" href="/themes/default/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/themes/default/assets/css/main.css">
    <link rel="stylesheet" href="/themes/default/assets/css/header.css">
    <link rel="stylesheet" href="/themes/default/assets/css/footer.css">
    <link rel="stylesheet" href="/themes/default/assets/css/{!! Theme::get('color') !!}/style.css">
    {!! Theme::asset()->container('custom-css')->styles() !!}
    <script src="/themes/default/assets/plugins/ace/js/ace-extra.min.js"></script>
</head>
<body>

<header>
    @if(Module::exists('substation') && Theme::get('is_substation') == 1)
    {!! Theme::partial('substationheader') !!}
    @else
    {!! Theme::partial('homeheader') !!}
    @endif
</header>

    {!! Theme::partial('homemenu') !!}


<section>
    <div class="clearfix u-grayico hidden-md hidden-sm hidden-xs">
        <div class="container">
            <div class="col-md-3 s-ico clearfix">
            <div class="pull-right">
                <h4 class="text-size16 cor-gray51">有需求？</h4>
                <p class="cor-gray97">万千威客为您出谋划策</p>
            </div>
            </div>
            <div class="col-md-3 s-ico s-ico2">
                <div class="pull-right">
                    <h4 class="text-size16 cor-gray51">找任务</h4>
                    <p class="cor-gray97">海量需求任你来挑</p>
                </div>
            </div>
            <div class="col-md-3 s-ico s-ico3">
                <div class="pull-right">
                    <h4 class="text-size16 cor-gray51">快速交易</h4>
                    <p class="cor-gray97">轻松交易快速解决</p>
                </div>
            </div>
            <div class="col-md-3 s-ico s-ico4">
                <div class="pull-right">
                    <h4 class="text-size16 cor-gray51">畅无忧</h4>
                    <p class="cor-gray97">快速接单畅通无阻</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            {!! Theme::content() !!}
        </div>
    </div>
</section>
<footer>
    {!! Theme::partial('footer') !!}
</footer>

<script src="/themes/default/assets/plugins/jquery/jquery.min.js"></script>
<script src="/themes/default/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/themes/default/assets/js/nav.js"></script>
<script src="/themes/default/assets/js/common.js"></script>

{!! Theme::asset()->container('specific-js')->scripts() !!}

{!! Theme::asset()->container('custom-js')->scripts() !!}
</body>