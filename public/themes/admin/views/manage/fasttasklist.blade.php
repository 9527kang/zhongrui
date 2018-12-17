
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">懒人发布项目</h3>
<div class="row">
    <div class="col-xs-12">
        <div class="clearfix  well">
            <div class="form-inline search-group">
                <form  role="form" action="" method="get">
                    <div class="form-group">
                        <div class="form-group search-list">
                            <label for="name">用户名　</label>
                            <input type="text" class="form-control" name="name" placeholder="请输入用户名" @if(isset($merge['name']))value="{!! $merge['name'] !!}"@endif>
                        </div>
                        <div class="form-group search-list">
                            <label for="name">手机号　</label>
                            <input type="text" class="form-control" name="mobile" placeholder="请输入手机号" @if(isset($merge['mobile']))value="{!! $merge['mobile'] !!}"@endif>
                        </div>
                        <div class="form-group search-list">
                            <label for="name">邮箱　</label>
                            <input type="text" class="form-control" name="email" placeholder="请输入邮箱" @if(isset($merge['email']))value="{!! $merge['email'] !!}"@endif>
                        </div>
                    </div>

                    <div class="space"></div>
                    <div class="form-group">
                        <div class="form-group search-list width285">
                            <label class=""> 需求状态　</label>
                            <select name="status">
                                <option value="">全部</option>
                                <option value="0" @if(isset($merge['status']) && $merge['status'] == '0')selected="selected"@endif>待完善</option>
                                <option value="1" @if(isset($merge['status']) && $merge['status'] == '1')selected="selected"@endif>已完善</option>

                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-sm">搜索</button>
                        </div>
                    </div>


                </form>
            </div>
        </div>
        <div>
            <table id="sample-table" class="table table-striped table-bordered table-hover">
                <thead>
                <tr>

                    <th>序号</th>
                    <th>需求标题</th>
                    <th>需求描述</th>
                    <th>用户名</th>

                    <th>手机号</th>
                    <th>邮箱</th>
                    <th>需求状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                    {!! csrf_field() !!}
                    <tbody>
                    @foreach($list as $key=>$item)
                        <tr>
                            <td>{{($page-1)*$paginate+$key+1}}</td>
                            <td>
                                {{$item->title}}
                            </td>
                            <td class="hidden-480">
                              {{$item->desc}}
                            </td>
                            <td>{!! $item->name !!}</td>
                            <td>
                                {{$item->mobile}}
                            </td>
                            <td>
                                {{$item->email}}
                            </td>

                            <td class="hidden-480">
                                @if($item->status == 0)
                                    <span class="label label-sm label-warning">待完善</span>
                                @elseif( $item->status == 1)
                                    <span class="label label-sm label-success">已完善</span>
                                @endif
                            </td>


                            <td>
                                <div class="hidden-sm hidden-xs btn-group">
                                    @if($item->status == 0)
                                        <a class="btn btn-xs btn-success" href="/manage/pubTask?id={{$item['id']}}">
                                            <i class="ace-icon fa fa-check bigger-120">去完善</i>
                                        </a>

                                        <a class="btn btn-xs btn-danger"  data-toggle="modal" data-target="#myModal{{$item->id}}">
                                            <i class="ace-icon fa fa-minus-circle bigger-120"> 删除</i>
                                        </a>
                                        <div class="modal fade" id="myModal{{$item->id}}" tabindex="-1" role="dialog"
                                             aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                                                        </button>
                                                        <h4 class="modal-title" id="myModalLabel">确认删除</h4>
                                                    </div>

                                                    <div class="modal-body">
                                                        <p>确认删除{{$item['desc']}}吗？</p>

                                                    </div>
                                                    <div class="modal-footer">

                                                        <a class="btn btn-primary" type="button" href="/manage/deleteFast/{{$item['id']}}">确定</a>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <a href="/task/{{ $item->task_id }}" class="btn btn-xs btn-info" target="_blank">
                                            <i class="ace-icon fa fa-edit bigger-120">详情</i>
                                        </a>

                                    @endif

                                </div>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
            </table>
        </div>
        <div class="row">
            <div class="space-10 col-xs-12"></div>
            <div class="col-xs-12">
                <div class="dataTables_paginate paging_simple_numbers row" id="dynamic-table_paginate">
                    {!! $list->appends($merge)->render() !!}
                </div>
            </div>
        </div>
    </div>
</div><!-- /.row -->

{!! Theme::widget('popup')->render() !!}
{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}
