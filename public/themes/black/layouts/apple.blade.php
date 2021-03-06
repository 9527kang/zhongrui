<!DOCTYPE html>
<html lang="en">
<script src="/themes/black/assets/plugins/jquery/clipboard.min.js"></script>
<script src="/themes/black/assets/plugins/jquery/jquery-1.10.2.min.js"></script>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! Theme::get('title') !!}</title>
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{--<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">--}}
    @if(isset(Theme::get('basis_config')['css_adaptive']) && Theme::get('basis_config')['css_adaptive'] == 1)
        <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
    @else
        <meta name="viewport" content="initial-scale=0.1">
    @endif
    @if(!empty(Theme::get('site_config')['browser_logo']) && is_file(Theme::get('site_config')['browser_logo']))
        <link rel="shortcut icon" href="{{ url(Theme::get('site_config')['browser_logo']) }}" />
    @else
        <link rel="shortcut icon" href="{{ Theme::asset()->url('images/favicon.ico') }}" />
        @endif
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="/themes/black/assets/plugins/bootstrap/css/bootstrap.min.css">
    {!! Theme::asset()->container('specific-css')->styles() !!}
    <link rel="stylesheet" href="/themes/black/assets/plugins/ace/css/ace.min.css">
    <link rel="stylesheet" href="/themes/black/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/themes/black/assets/css/main.css">
    <link rel="stylesheet" href="/themes/black/assets/css/header.css">
    <link rel="stylesheet" href="/themes/black/assets/css/footer.css">
    <link rel="stylesheet" href="/themes/black/assets/css/usercenter/finance/finance-layout.css">
    <link rel="stylesheet" href="/themes/black/assets/css/usercenter/userslidebar.css">
    <link rel="stylesheet" href="/themes/black/assets/css/{!! Theme::get('color') !!}/style.css">
    <link rel="stylesheet" href="/themes/black/assets/css/{!! Theme::get('color') !!}/user.css">
    {!! Theme::asset()->container('custom-css')->styles() !!}
</head>
<body>
<header>
    <div class="header-top">
        <div class="container clearfix col-left">
            {!! Theme::partial('usernav') !!}
        </div>
    </div>
</header>

<section>
    <div class="container">
        <div class="row">
            <div class="visible-sm-block visible-xs-block g-sdb">
                {!! Theme::partial('applesidebarother') !!}
            </div>
            <div class="col-md-3 hidden-sm hidden-xs col-left"><div class="focuside">
                    {!! Theme::partial('applesidebar') !!}
                </div></div>
            <div class="col-md-9 g-side2 col-left">
                {!! Theme::content() !!}
            </div>
        </div>
    </div>
</section>

<footer>
    {!! Theme::partial('footer') !!}
</footer>


<script src="/themes/black/assets/plugins/jquery/jquery.min.js"></script>
<script src="/themes/black/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/themes/black/assets/plugins/ace/js/ace.min.js"></script>
<script src="/themes/black/assets/plugins/ace/js/ace-elements.min.js"></script>
<script src="/themes/black/assets/js/common.js"></script>
{!! Theme::asset()->container('specific-js')->scripts() !!}

{!! Theme::asset()->container('custom-js')->scripts() !!}

</body>
</html>

<script type="text/javascript">
    $(document).ready(function(){
        var clipboard = new Clipboard('.btn');
        clipboard.on('success', function(e) {
            console.log(e);
            alert("推荐码复制成功！")
        });
        clipboard.on('error', function(e) {
            console.log(e);
            alert("推荐码复制失败！请手动复制")
        });
    })
</script>