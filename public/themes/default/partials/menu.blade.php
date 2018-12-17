
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
<div class="g-taskbarnav">
    <div class="container col-left">
        <div class="g-nav clearfix">
            <div class="pull-left hidden-xs">
                <div class="g-tasknavdrop" id="nav"><i class="fa fa-reorder"></i>全部任务分类<i class="fa fa-exchange"></i>
                    <div class="parent_box parent_box_m3">
                        <ul class="ul_one">
                            @forelse(Theme::get('task_cate') as $k => $v)
                                @if(isset($v['pid']) && $v['pid'] == 0)
                                    <li>
                                        <div class="u-navitem">
                                            <h4>
                                                <a href="/task?category={!! $v['id'] !!}" class="text-size14 cor-white">{!! $v['name'] !!}</a>
                                            </h4>
                                            @forelse($v['child_task_cate'] as $m => $n)
                                                @if($m < 3)
                                                    <a href="/task?category={!! $n['id'] !!}" class="u-tit">{!! $n['name'] !!}
                                                    </a>

                                                @endif
                                            @empty
                                            @endforelse

                                        </div>
                                        <div class="g-subshow" style="display:none">
                                            <div>{!! $v['name'] !!}</div>
                                            <p>
                                                @forelse($v['child_task_cate'] as $m => $n)
                                                    <a href="/task?category={!! $n['id'] !!}">{!! $n['name'] !!}
                                                    </a>&nbsp;&nbsp;|&nbsp;
                                                @empty
                                                @endforelse
                                            </p>
                                        </div>
                                    </li>
                                @endif
                            @empty
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="g-navList"><div class="g-navList-wrap clearfix">
                        @if(!empty(Theme::get('nav_list')))
                            @if(count(Theme::get('nav_list')) > 6)
                                @for($i=0;$i<6;$i++)
                                    @if(Theme::get('nav_list')[$i]['is_show'] == 1)
                                        <a href="{!! Theme::get('nav_list')[$i]['link_url'] !!}"
                                           @if(Theme::get('nav_list')[$i]['is_new_window'] == 1)target="_blank" @endif @if(Theme::get('nav_list')[$i]['link_url'] == Theme::get('now_menu')) class="z-navHome" @endif>
                                            {!! Theme::get('nav_list')[$i]['title'] !!}
                                        </a>
                                    @endif
                                @endfor

                                <div class="pull-left dropdown-navwrap">
                                    <a href="" class="dropdown-toggle" data-toggle="dropdown">更多   <b class="caret"></b></a>
                                    <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close50 z-navactive">
                                        @for($i=7;$i<count(Theme::get('nav_list'))+1;$i++)
                                            <li @if(Theme::get('nav_list')[$i-1]['link_url'] == $_SERVER['REQUEST_URI']) class="hActive" @endif>
                                                <a class="text-center" href="{!! Theme::get('nav_list')[$i-1]['link_url'] !!}"
                                                   @if(Theme::get('nav_list')[$i-1]['is_new_window'] == 1)target="_blank" @endif >
                                                    {!! Theme::get('nav_list')[$i-1]['title'] !!}
                                                </a>
                                            </li>
                                        @endfor
                                    </ul>
                                </div>
                            @else
                                @for($i=0;$i<count(Theme::get('nav_list'));$i++)
                                    @if(Theme::get('nav_list')[$i]['is_show'] == 1)
                                        <a href="{!! Theme::get('nav_list')[$i]['link_url'] !!}"
                                           @if(Theme::get('nav_list')[$i]['is_new_window'] == 1)target="_blank" @endif @if(Theme::get('nav_list')[$i]['link_url'] == Theme::get('now_menu')) class="z-navHome" @endif>
                                            {!! Theme::get('nav_list')[$i]['title'] !!}
                                        </a>
                                    @endif
                                @endfor
                            @endif
                        @else
                            <a href="{!! CommonClass::homePage() !!}" @if(CommonClass::homePage() == Theme::get('now_menu')) class="z-navHome" @endif>首页</a>
                            <a href="/task" @if('/task' == Theme::get('now_menu')) class="z-navHome" @endif>任务大厅</a>
                            <a href="/bre/service" @if('bre/service' == Theme::get('now_menu')) class="z-navHome" @endif>服务商</a>
                            <a href="/task/successCase" @if('/task/successCase' == Theme::get('now_menu')) class="z-navHome" @endif>成功案例</a>
                            <a href="/article" @if('/article' == Theme::get('now_menu')) class="z-navHome" @endif>资讯中心</a>
                        @endif
                    </div></div>
            </div>
            <!-- <div class="pull-right g-tasknavbtn visible-lg-block visible-md-block">
                <a href="/task/create" class="u-ahref">发布需求</a>
            </div> -->
            <span class="pull-right rel_arrow_cont" id="rel_arrow_btn">
                <a href="/task/create" target="_blank" class="button red">
                    <span>免费发布任务</span>
                    <i class="rel_arrow"></i>
                </a>
                <p class="release_hover">
                    <a href="/task/create" target="_blank">普通自助发布</a>
                    <a id="oneKeyContent">懒人一键发布</a>
                </p>
            </span>
            <nav  class="navbar navbar-default navbar-static hidden-sm hidden-md hidden-lg col-xs-12"  id="navbar-example" role="navigation">
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
                        @if(!empty(Theme::get('nav_list')))
                            @foreach(Theme::get('nav_list') as $m => $n)
                                @if($n['is_show'] == 1)
                                    <li>
                                        <a href="{!! $n['link_url'] !!}" @if($n['is_new_window'] == 1)target="_blank" @endif @if(Theme::get('now_menu') == $n['link_url']) class="z-navHome" @endif>{!! $n['title'] !!}</a>
                                    </li>
                                @endif
                            @endforeach
                        @else
                            <li>
                                <a href="{!! CommonClass::homePage() !!}" @if(CommonClass::homePage() == Theme::get('now_menu')) class="z-navHome" @endif>首页</a>
                            </li>
                            <li>
                                <a href="/task" @if('/task' == Theme::get('now_menu')) class="z-navHome" @endif>任务大厅</a>
                            </li>
                            <li>
                                <a href="/bre/service" @if('bre/service' == Theme::get('now_menu')) class="z-navHome" @endif>服务商</a>
                            </li>
                            <li>
                                <a href="/task/successCase" @if('/task/successCase' == Theme::get('now_menu')) class="z-navHome" @endif>成功案例</a>
                            </li>
                            <li>
                                <a href="/article" @if('/article' == Theme::get('now_menu')) class="z-navHome" @endif>资讯中心</a>
                            </li>
                        @endif
                    </ul>
                </div>
                <div class="collapse navbar-collapse bs-js-navbar-scrollspy1 bg-white">
                    <ul class="nav navbar-nav clearfix">
                        <li class="clearfix">

                            <a href="javascript:;" class="clearfix search-btn">
                                <div class="g-tasksearch clearfix">
                                    <form action="/task" method="get" class="switchSearch" />
                                    <i class="fa fa-search"></i>
                                    <input type="text" placeholder="输入关键词" name="keywords" class="input-boxshaw"/>
                                    <button>搜索</button>
                                    </form>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>








