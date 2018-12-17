{{--<div class="page-header">
    <h1>
        用户流水
    </h1>
</div><!-- /.page-header -->--}}
<h3 class="header smaller lighter blue mg-bottom20 mg-top12">用户流水</h3>
<div class="well">
    <form class="form-inline search-group" role="form" action="{!! url('manage/userFinance') !!}" method="get">
        <div class="form-group search-list ">
            <label for="namee" class="">用户名　　</label>
            <input type="text" name="username" value="@if(isset($username)){!! $username !!}@endif" />
        </div>
        <div class="form-group search-list ">
            <label for="namee" class="">财务类型　</label>
            <select name="action" id="action">
                <option value="">全部</option>
                @foreach($action_arr as $k => $v)
                <option value="{{$k}}" @if(isset($action) && $action == $k)selected="selected"@endif>{{$v}}</option>
                @endforeach

            </select>
        </div>

        <div class="space"></div>
        <div class="form-inline search-group " >
            <div class="form-group search-list ">
                <label class="">时间　</label>
                <div class="input-daterange input-group">
                    <input type="text" name="start" class="input-sm form-control" value="@if(isset($start)){!! $start !!}@endif">
                    <span class="input-group-addon"><i class="fa fa-exchange"></i></span>
                    <input type="text" name="end" class="input-sm form-control" value="@if(isset($end)){!! $end !!}@endif">
                </div>
            </div>
            <div class="form-group"><button type="submit" class="btn btn-primary btn-sm">搜索</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="javascript:;" onclick="userFinanceExport()">导出Excel</a>
            </div>
        </div>
    </form>
</div>
<div class="table-responsive">
    <table id="sample-table-1" class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th>
                <label>
                    <span class="lbl"></span>
                    编号
                </label>
            </th>
            <th>财务类型</th>
            <th>用户</th>

            <th>
                金额
            </th>
            <th>用户余额</th>
            <th>时间</th>
        </tr>
        </thead>

        <tbody>
        @if(!empty($list))
        @foreach($list as $item)
        <tr>
            <td>
                <label>
                    <span class="lbl"></span>
                    {!! $item->id !!}
                </label>
            </td>

            <td>
                @if(in_array($item->action,array_keys($action_arr))){{$action_arr[$item->action]}}@endif

            </td>
            <td >{!! $item->name !!}</td>
            <td>￥{!! $item->cash !!}元</td>

            <td>
                ￥{!! $item->balance !!}元
            </td>

            <td>
                {!! $item->created_at !!}
            </td>
        </tr>
        @endforeach
        @endif
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="dataTables_paginate paging_bootstrap row">
            {!! $list->appends($search)->render() !!}
        </div>
    </div>
</div>
{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}

{!! Theme::asset()->container('custom-js')->usePath()->add('userfinance-js', 'js/userfinance.js') !!}
{{--时间插件--}}
{!! Theme::widget('datepicker')->render() !!}