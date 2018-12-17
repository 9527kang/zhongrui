<div class="g-taskbarnav
 @if(empty(\CommonClass::getShopColour())) bg-blue @else shop-{{\CommonClass::getShopColour()['nav_color']}} @endif
        shop-navbg" style="border:none;">
    <div class="container col-left">
        <div class="g-nav clearfix">
            <div class="pull-left hidden-xs">
                <div class="shop-navlist clearfix">
                    <!-- <div class="nav_block_style_m3">
                        <a class="shop-navact shop_navact_m3" @if(Theme::get('userId') == Auth::id()) href="/shop/manage/{!! Theme::get('SHOPID') !!}" @else href="/shop/{!! Theme::get('SHOPID') !!}" @endif>首页</a>
                    </div>
                    <div class="nav_block_style_m3">
                        <a href="/shop/work/{!! Theme::get('SHOPID') !!}" class="shop-navact shop_navact_m3">作品</a>
                    </div>
                    <div class="nav_block_style_m3">
                        <a href="/shop/serviceAll/{!! Theme::get('SHOPID') !!}" class="shop-navact shop_navact_m3">服务</a>
                    </div>
                    <div class="nav_block_style_m3">
                        <a href="/shop/successStory/{!! Theme::get('SHOPID') !!}" class="shop-navact shop_navact_m3">成功案例</a>
                    </div>
                    <div class="nav_block_style_m3">
                        <a href="/shop/rated/{!! Theme::get('SHOPID') !!}" class="shop-navact shop_navact_m3">交易评价</a>
                    </div>
                    <div class="nav_block_style_m3">
                        <a href="/shop/about/{!! Theme::get('SHOPID') !!}" class="shop-navact shop_navact_m3">关于我们</a>
                    </div> -->
                </div>
            </div>
            <nav  class="navbar navbar-default navbar-static hidden-sm hidden-md hidden-lg col-xs-12 @if(!empty(\CommonClass::getShopColour()))  @if(\CommonClass::getShopColour()['nav_color'] == "blue") shop-blue @elseif(\CommonClass::getShopColour()['nav_color'] == "purple") shop-purple @elseif(\CommonClass::getShopColour()['nav_color'] == "red") shop-red @elseif(\CommonClass::getShopColour()['nav_color'] == "green") shop-green @elseif(\CommonClass::getShopColour()['nav_color'] == "yellow") shop-yellow @endif @endif"  id="navbar-example" role="navigation">
                <div class="navbar-header">
                    <button class="navbar-toggle z-activeNavlist" type="button" data-toggle="collapse"
                            data-target=".bs-js-navbar-scrollspy">
                        <span class="sr-only">切换导航</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <button class="navbar-toggle mg-right0" type="button" data-toggle="collapse"
                            data-target=".bs-js-navbar-scrollspy1">
                        <span class="fa fa-search"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse bs-js-navbar-scrollspy">
                    <ul class="nav navbar-nav">
                        {{--<li>
                            <a @if(Theme::get('userId') == Auth::id()) href="/shop/manage/{!! Theme::get('SHOPID') !!}" @else href="/shop/{!! Theme::get('SHOPID') !!}" @endif>首页</a>
                        </li>
                        <li>
                            <a href="/shop/work/{!! Theme::get('SHOPID') !!}" >作品</a>
                        </li>
                        <li>
                            <a href="/shop/serviceAll/{!! Theme::get('SHOPID') !!}" >服务</a>
                        </li>
                        <li>
                            <a href="/shop/successStory/{!! Theme::get('SHOPID') !!}" >成功案例</a>
                        </li>
                        <li>
                            <a href="/shop/rated/{!! Theme::get('SHOPID') !!}">交易评价</a>
                        </li>
                        <li>
                            <a href="/shop/about/{!! Theme::get('SHOPID') !!}">关于我们</a>
                        </li>--}}
                    </ul>
                </div>
                <div class="collapse navbar-collapse bs-js-navbar-scrollspy1 bg-white">
                    <ul class="nav navbar-nav clearfix">
                        <li class="clearfix">
                            <a href="javascript:;" class="clearfix search-btn">
                                <div class="g-tasksearch clearfix">
                                    <i class="fa fa-search"></i>
                                    <input type="text" placeholder="输入关键词" class="input-boxshaw"/>
                                    <button>搜索</button>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>
