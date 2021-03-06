<div class="g-main g-releasetask">
        <h4 class="text-size16 cor-blue2f u-title">我发布的任务</h4>
        <div class="space-12"></div>
        <div class="clearfix g-reletaskhd">
            <div class="pull-left">
                <form action="/user/myTaskAxis" method="get">
                    <span class="cor-gray51 text-size14">搜索任务： </span>
                    <input type="text" placeholder="输入任务关键词" class="input-xlarge inp-size12" name="search" />
                    <button class="g-reletimehd"><i class="fa fa-search text-size16 cor-graybd"></i></button>
                </form>
            </div>
            <div class="pull-right">
                <a class="text-size14 cor-graybd" href="/user/myTasksList"><i class="fa fa-list-ul"></i></a>
                <a class="text-size14 cor-blue2f hidden-md hidden-sm hidden-xs" href="/user/myTaskAxis"><i class="fa fa-list-ul fa-rotate-90"></i></a>
                <a class="text-size14 cor-graybd g-releasechart hidden-md hidden-sm hidden-xs" href="javascript:;"><i class="fa fa-pie-chart"></i>
                    <div class="g-releasehidea"></div>
                    <div class="g-releasehide">
                        <div>
                            <div>饼图统计</div>
                            @if(!$pie_data)
                            <div class="widget-body">
                                <div class="widget-main">
                                    <!-- #section:plugins/charts.flotchart -->
                                    <div class="g-userchartno">您还没有发布过任务 </div>
                                </div><!-- /.widget-main -->
                            </div><!-- /.widget-body -->
                            @else
                            <div class="widget-body">
                                <div class="widget-main">
                                    <!-- #section:plugins/charts.flotchart -->
                                    <div id="piechart-placeholder">

                                    </div>
                                </div><!-- /.widget-main -->
                             </div><!-- /.widget-body -->
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="space-20"></div>
        @if(count($my_tasks['data'])>0)
        <ul class="g-reletimeline" id="task_axis">
            @foreach($my_tasks['data'] as $k=>$v)
            <span style="display:none;">{{ $num +=1 }}</span>
            @if($num!=1)
                <li class="g-reletimeper"><div class="g-reletimebor"><span>{{ date('Y',strtotime($k)) }}</span>{{ date('m',strtotime($k)) }}月</div></li>
            @endif
            @if(!empty($v))
                @foreach($v as $value)
                <li class="row">
                <div class="col-md-10 g-userborbtm"></div>
                <div class="g-reletimeli"><b>{{ date('m-d',strtotime($value['created_at'])) }}</b><span><i></i></span></div>
                <div class="col-md-1"><img src="@if(Theme::get('avatar')) {{CommonClass::getDomain().'/'.Theme::get('avatar')}} @else {!! Theme::asset()->url('images/default_avatar.png') !!} @endif " onerror="onerrorImage('{{ Theme::asset()->url('defauthead.png')}}',$(this))"></div>
                <div class="col-md-11">
                    <div class="col-md-9">
                        <div class="text-size14 cor-gray51"><span class="cor-orange">￥{{ $value['bounty'] }}</span>&nbsp;&nbsp;<a target="_blank" class="cor-blue42" href="">{{ $value['title'] }}</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;@if(isset($value['status_text'])){{ $value['status_text'] }}@endif</div>
                        <div class="space-6"></div>
                        <p class="cor-gray87"><i class="ace-icon fa fa-user bigger-110 cor-grayd2"></i> {{ $value['nickname'] }}&nbsp;&nbsp;&nbsp;<i class="fa fa-eye cor-grayd2"></i> {{ $value['view_count'] }}人浏览/{{ $value['delivery_count'] }}人投稿&nbsp;&nbsp;&nbsp;<i class="fa fa-clock-o cor-grayd2"></i> {{ round((time()-strtotime($value['created_at']))/(3600*24)) }}天前&nbsp;&nbsp;&nbsp;<i class="fa fa-unlock-alt cor-grayd2"></i> {{ ($value['bounty_status']==1)?'已托管赏金':'待托管赏金' }}</p>
                        <div class="space-6"></div>
                        <p class="cor-gray51">{!! str_limit(strip_tags(htmlspecialchars_decode($value['desc'])),120) !!}</p>
                        <div class="space-2"></div>
                        <div class="g-userlabel"><a href="">{{ $value['cate_name'] }}</a>
                            @if($value['region_limit']==1)
                                <a href="">{{ $value['province_name'].$value['city_name'] }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 text-right"><a target="_blank" class="btn-big bg-blue bor-radius2 hov-blue1b" href="/task/{{ $value['id'] }}">查看</a></div>
                </div>
            </li>
                @endforeach
            @endif
            @endforeach
        </ul>
        @if(!is_null($my_tasks['next_page_url']))
        <div class="g-reletimedrop" id="next_page">
            <div role="tooltip" class="popover fade top in" style="display: block;">
                <div class="arrow" style="left: 50%;"></div>
                <h3 class="popover-title" style="display: none;"></h3>
                <div class="popover-content"><a url="/user/myTaskAxisAjax?page={{ $my_tasks['current_page']+1 }}" onclick="ajaxAxis($(this))">查看更多</a></div>
            </div>
        </div>
        @endif
        <div class="space-20"></div>
        @else
        <div class="space-30"></div>
        <div class="space-30"></div>
        <div class="text-center">
            <p class="cor-gray51 text-size20">抱歉！没有找到符合以上条件的任务</p>
            <p class="cor-gray87 text-size14">您可以重新再进行搜索 或</p>
            <div class="space-10"></div>
            <a class="btn-big bg-blue text-size18 bor-radius2" href="/task/create">发布任务</a>
        </div>
        @endif
    </div>
{!! Theme::asset()->container('custom-css')->usepath()->add('usercenter','css/usercenter/usercenter.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('taskaxis','js/doc/taskaxis.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('nopie','js/doc/nopie.js') !!}
@if($pie_data)
{!! Theme::widget('pie',['pie_data'=>$pie_data])->render() !!}
@endif

