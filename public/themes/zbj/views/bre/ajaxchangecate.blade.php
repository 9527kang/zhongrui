@forelse($user_Arr as $k => $v)
    @if($k < 6)
        <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="pull-left">
                    <h6>
                        <a target="_blank" href="/bre/serviceEvaluateDetail/{{$v['uid']}}">
                            {{$v['name']}}
                        </a>
                    </h6>
                    <p class="p-space">{{$v['introduce']}}</p>
                    <ul class="clearfix">
                        @if(isset($v['skill']) && is_array($v['skill']))
                            @forelse($v['skill'] as $key => $val)
                                @if($key < 3)
                                    <li class="pull-left">{{$val}}</li>
                                @endif
                            @empty
                            @endforelse
                        @endif
                    </ul>
                </div>
                <div class="pull-right">
                    <a target="_blank" href="/bre/serviceEvaluateDetail/{{$v['uid']}}" class="img">
                        @if($v['avatar'] && is_file($v['avatar']))
                            <img src="{!! url($v['avatar']) !!}">
                        @else
                            <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                        @endif

                    </a>
                    <a class="free_information" target="_blank" href="/bre/serviceEvaluateDetail/{{$v['uid']}}">免费咨询</a>
                </div>
            </div>
        </li>
    @endif
@empty

    <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="pull-left">
                <h6>
                    <a target="_blank" href="#">接单达人2</a>
                </h6>
                <p class="p-space">全面打造以接单为目的的服务宗旨</p>
                <ul class="clearfix">
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                </ul>
            </div>
            <div class="pull-right">
                <a target="_blank" href="#" class="img">
                    <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                </a>
                <a class="free_information" target="_blank" href="#">免费咨询</a>
            </div>
        </div>
    </li>
    <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="pull-left">
                <h6>
                    <a target="_blank" href="#">接单达人2</a>
                </h6>
                <p class="p-space">全面打造以接单为目的的服务宗旨</p>
                <ul class="clearfix">
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                </ul>
            </div>
            <div class="pull-right">
                <a target="_blank" href="#" class="img">
                    <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                </a>
                <a class="free_information" target="_blank" href="#">免费咨询</a>
            </div>
        </div>
    </li>
    <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="pull-left">
                <h6>
                    <a target="_blank" href="#">接单达人2</a>
                </h6>
                <p class="p-space">全面打造以接单为目的的服务宗旨</p>
                <ul class="clearfix">
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                </ul>
            </div>
            <div class="pull-right">
                <a target="_blank" href="#" class="img">
                    <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                </a>
                <a class="free_information" target="_blank" href="#">免费咨询</a>
            </div>
        </div>
    </li>
    <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="pull-left">
                <h6>
                    <a target="_blank" href="#">接单达人2</a>
                </h6>
                <p class="p-space">全面打造以接单为目的的服务宗旨</p>
                <ul class="clearfix">
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                </ul>
            </div>
            <div class="pull-right">
                <a target="_blank" href="#" class="img">
                    <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                </a>
                <a class="free_information" target="_blank" href="#">免费咨询</a>
            </div>
        </div>
    </li>
    <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="pull-left">
                <h6>
                    <a target="_blank" href="#">接单达人2</a>
                </h6>
                <p class="p-space">全面打造以接单为目的的服务宗旨</p>
                <ul class="clearfix">
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                </ul>
            </div>
            <div class="pull-right">
                <a target="_blank" href="#" class="img">
                    <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                </a>
                <a class="free_information" target="_blank" href="#">免费咨询</a>
            </div>
        </div>
    </li>
    <li class="row clearfix col-lg-4 col-md-6 col-sm-12 col-xs-12">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="pull-left">
                <h6>
                    <a target="_blank" href="#">接单达人2</a>
                </h6>
                <p class="p-space">全面打造以接单为目的的服务宗旨</p>
                <ul class="clearfix">
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                    <li class="pull-left">微信推送</li>
                </ul>
            </div>
            <div class="pull-right">
                <a target="_blank" href="#" class="img">
                    <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                </a>
                <a class="free_information" target="_blank" href="#">免费咨询</a>
            </div>
        </div>
    </li>

@endforelse
<div class="col-xs-12">
    <p class="text-center see_more">
        <a href="/bre/service">查看更多顾问 ></a>
    </p>
</div>