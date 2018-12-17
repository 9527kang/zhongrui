<style type="text/css">
    .ma-t20 {
        margin-top: 20px;
    }
    .ma-l40 {
    margin-left: 40px;
    }
    .ma-t10 {
        margin-top: 10px;
    }
    .ma-l50 {
    margin-left: 50px;
    }
    .ma-t50 {
        margin-top: 50px;
    }
    .pd-t20 {
        padding-top: 20px;
    }
    .pd-l54 {
        padding-left: 54px;
    }
    .kee .bord {
        border-top: 1px solid rgb(204, 204, 204);
    }
    .kee .img {
        width: 50px;
        height: 50px;
    }
    .kee .serial_number span {
        display: block;
        width: 40px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        border: 1px solid rgb(204, 204, 204);
        border-radius: 50%;
        -ms-border-radius: 50%;
        -moz-border-radius: 50%;
        -o-border-radius: 50%;
        -webkit-border-radius: 50%;
        margin-top: 10px;
    }
    .kee .serial_number p {
        width: 40px;
        height: 5px;
        text-align: center;
        color: #777;
    }
</style>
@if($rule == 0)
    <div class="row kee">
        <div class="clearfix ma-t20">
            <div class="col-sm-3 col-md-2 col-xs-4 ma-l40 ma-t10 img">

            </div>
            <div class="col-sm-9  col-md-10  col-xs-8">
                <div class="col-sm-12 col-md-12 col-xs-12">
                    <h3 class="ma-t25 pd-l20">
                        应用介绍
                    </h3>
                </div>
                <div class="col-sm-12 col-md-12 col-xs-12">
                    <p class="col-sm-11 lh-30 pd-l20">第三方项目是客客针对运营工作推出的一项服务，申请该项服务后，会有专业运营人员根据定期从其它第三方平台导入项目至本平台，协助企业前期运营管理工作。</p>
                </div>

            </div>
            <div class="col-sm-10 col-md-8  col-lg-12 ma-l50 ma-t50 pd-t20 pd-l54 bord">
                <div class="col-sm-12 col-md-12 col-xs-12">
                    <h3 class="pd-l20">使用流程</h3>
                </div>
                <div class="col-sm-8 col-md-7 col-xs-10 ma-t10">
                    <div class="col-sm-2 col-md-2 col-lg-1 serial_number pd-l20">
                        <span>1</span>
                        <p>.</p>
                        <p>.</p>
                        <p>.</p>
                    </div>
                    <div class="col-sm-10 text pd-l20">
                        <h5>咨询申请</h5>
                    </div>
                </div>
                <div class="col-sm-8 col-md-7 col-xs-10 ma-t10">
                    <div class="col-sm-2 col-md-2 col-lg-1 serial_number pd-l20">
                        <span>2</span>
                        <p>.</p>
                        <p>.</p>
                        <p>.</p>
                    </div>
                    <div class="col-sm-10 text pd-l20">
                        <h5>审核</h5>
                    </div>
                </div>
                <div class="col-sm-8 col-md-7 col-xs-10 ma-t10">
                    <div class="col-sm-2 col-md-2 col-lg-1 serial_number pd-l20">
                        <span>3</span>
                        <p>.</p>
                        <p>.</p>
                        <p>.</p>
                    </div>
                    <div class="col-sm-10 text pd-l20">
                        <h5>导入项目</h5>
                    </div>
                </div>
                <div class="col-sm-8 col-md-7 col-xs-10 ma-t10">
                    <div class="col-sm-2 col-md-2 col-lg-1 serial_number pd-l20">
                        <span>4</span>
                        <p>.</p>
                        <p>.</p>
                        <p>.</p>
                    </div>
                    <div class="col-sm-10 text pd-l20">
                        <h5>编辑分类</h5>
                    </div>
                </div>
                <div class="col-sm-8 col-md-7 col-xs-10 ma-t10">
                    <div class="col-sm-2 col-md-2 col-lg-1 serial_number pd-l20">
                        <span>5</span>
                    </div>
                    <div class="col-sm-10 text pd-l20">
                        <h5>发布</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-10 col-md-8  col-lg-12 ma-l50 ma-t50 pd-t20 pd-l54 bord">
                <div class="col-sm-12 col-md-12 col-xs-12">
                    <h3 class="ma-t25 pd-l20">联系客客</h3>
                    <p class="col-sm-11 lh-30 pd-l20">公司地址：湖北省武汉市洪山区珞喻路876号华工科技产业大厦9层</p>
                    <p class="col-sm-11 lh-30 pd-l20">咨询热线：027 87733922</p>
                    <p class="col-sm-11 lh-30 pd-l20">客服QQ： 262613148</p>
                    <p class="col-sm-11 lh-30 pd-l20">固定电话:18971533922</p>
                </div>
            </div>

        </div>

    </div>

@else
    <form class="form-inline" method="get">
        <div class="well">

            <div class="form-group search-list ">
                <label for="">项目标题　</label>
                <input type="text" name="name" value="@if(isset($merge['name'])){!! $merge['name'] !!}@endif">
            </div>
            <div class="form-group search-list ">
                <label for="">来源　</label>
                <input type="text" name="from" value="@if(isset($merge['from'])){!! $merge['from'] !!}@endif">
            </div>

            <div class="form-group search-list">
                <label for="">发布时间　</label>
                <div class="input-daterange input-group">
                    <input type="text" name="start" class="input-sm form-control" value="@if(isset($merge['start'])){!! $merge['start'] !!}@endif">
                    <span class="input-group-addon"><i class="fa fa-exchange"></i></span>
                    <input type="text" name="end" class="input-sm form-control" value="@if(isset($merge['end'])){!! $merge['end'] !!}@endif">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-sm btn-primary">搜索</button>
            </div>
        </div>
    </form>

    <div>
        <table id="sample-table-1" class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th class="center">编号</th>
                <th>来源</th>
                <th>项目标题</th>
                <th>状态</th>
                <th>来源分类</th>
                <th>本站分类</th>
                <th>发布时间</th>
            </tr>
            </thead>

            <tbody>
            @if(!empty($list))
                @foreach($list as $item)
                    <tr>
                        <td class="center">
                            <label>
                                <input type="checkbox" name="taskId[]" class="ace" value="{{$item['id']}}"/>
                                <span class="lbl"></span>
                                {{$item['id']}}
                            </label>
                        </td>
                        <td>{{$item['site_name']}}</td>
                        <td>
                            {{$item['title']}}
                        </td>

                        <td>
                            @if($item['status'] == 10)
                                失败
                            @elseif($item['status'] == 0)
                                未发布
                            @else
                                进行中
                            @endif
                        </td>

                        <td>
                            {{$item['tag_pname']}}/{{$item['tag_name']}}
                        </td>
                        <td>
                            {{ !empty($item['cate_id']) && in_array($item['cate_id'],array_keys($category)) ?  $category[$item['cate_id']]['name'] : '--'}}
                        </td>
                        <td>
                            {{$item['created_at']}}
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="dataTables_info" id="sample-table-2_info">
                <label><input type="checkbox" class="ace" id="allcheck"/>
                    <span class="lbl"></span>全选
                </label>
                <a id="edit_cate" type="button" class="btn btn-sm btn-primary " data-toggle="modal" data-target="#myModal" >批量编辑分类</a>
                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">编辑提示：</h4>
                            </div>
                            <div class="modal-body">
                                <p style="text-align: center;font-size: 16px;font-weight: 600;">
                                    <select name="cate_pid" onchange="changeCate(this.value,'#cate_id')">
                                        @foreach($category_all as $v)
                                            <option value="{{$v['id']}}" >
                                                {{$v['name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="cate_id" id="cate_id">
                                        @foreach($category_second as $v)
                                            <option value="{{$v['id']}}" >
                                                {{$v['name']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                <button type="button" class="btn btn-primary" onclick="updateTag()">确定</button>
                            </div>
                        </div>
                    </div>
                </div>
                <a id="pub_task" type="button" class="btn btn-sm btn-primary " data-toggle="modal" data-target="#myModal1" >批量发布</a>
                <div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">发布提示：</h4>
                            </div>
                            <div class="modal-body">
                                <p style="text-align: center;font-size: 16px;font-weight: 600;">发布项目前，请先更新项目分类

                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                <button type="button" class="btn btn-primary" onclick="pubTask(this)">确定</button>
                            </div>
                        </div>
                    </div>
                </div>
                <a type="button" class="btn btn-sm btn-danger " data-toggle="modal" data-target="#myModal2" >批量删除</a>
                <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">删除提示：</h4>
                            </div>
                            <div class="modal-body">
                                <p style="text-align: center;font-size: 16px;font-weight: 600;">确认要删除选择项目吗？

                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                                <button type="button" class="btn btn-primary" onclick="deleteTask(this)">确定</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="space col-xs-12"></div>
        <div class="col-xs-12">
            <div class="dataTables_paginate paging_bootstrap text-right">
                <ul class="pagination">
                    {!! $list->appends($merge)->render() !!}
                </ul>
            </div>
        </div>
    </div>

@endif
{!! Theme::asset()->container('custom-css')->usepath()->add('backstage', 'css/backstage/backstage.css') !!}
{{--时间插件--}}
{!! Theme::widget('datepicker')->render() !!}

<script>
    function gritterAdd(tips){
        $.gritter.add({
            text:'<div><span class="text-center"><h5>'+tips+'</h5></span></div>',
            time:2000,
            position: 'bottom-center',
            class_name: 'gritter-center gritter-info',
        });
    }
    $(document).on('click', 'th input:checkbox' , function(){
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function(){
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');

            });
    });
    //全选
    $('#allcheck').on('click',function(){
        if($(this).is(':checked')){
            $('[type="checkbox"]').prop('checked','true');
        }else{
            $('[type="checkbox"]').prop('checked','');
        }
    });
    function updateTag(){
        var type = 1;
        var cate_id = $('select[name="cate_id"]').val();
        var taskId = '';
        $('input[name="taskId[]"]').each(function(){
            if($(this).is(':checked')){
                if(taskId){
                    taskId = taskId+','+$(this).val();
                }else{
                    taskId = $(this).val();
                }
            }
        });
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/manage/updateTag',
            data: {taskId:taskId,cate_id:cate_id,type:type},
            dataType:'json',
            success: function(data){
                if(data.code){
                    window.location.reload();
                }else{
                    gritterAdd(data.msg);
                }
            }
        });
    }

    function pubTask(obj)
    {
        var type = 2;
        var taskId = '';
        $('input[name="taskId[]"]').each(function(){
            if($(this).is(':checked')){
                if(taskId){
                    taskId = taskId+','+$(this).val();
                }else{
                    taskId = $(this).val();
                }
            }
        });
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/manage/updateTag',
            data: {taskId:taskId,type:type},
            dataType:'json',
            success: function(data){
                if(data.code){
                    window.location.reload();
                }else{
                    gritterAdd(data.msg);
                }
            }
        });
    }

    function deleteTask(obj)
    {
        var type = 3;
        var taskId = '';
        $('input[name="taskId[]"]').each(function(){
            if($(this).is(':checked')){
                if(taskId){
                    taskId = taskId+','+$(this).val();
                }else{
                    taskId = $(this).val();
                }
            }
        });
        $.ajax({
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/manage/updateTag',
            data: {taskId:taskId,type:type},
            dataType:'json',
            success: function(data){
                if(data.code){
                    window.location.reload();
                }else{
                    gritterAdd(data.msg);
                }
            }
        });
    }

    function changeCate(id, element) {
        if (id && element) {
            $.get('/task/getSecondCate?id=' + id, function (res) {
                var html='';
                $.each(res, function(i, item) {
                    html = html+'<option value="'+item.id+'">'+item.name+'</option>';
                });
                $(element).html(html);

            }, 'json');
        } else {
            $("#" + element).html("<option>请选择分类</option>");
        }
    }
</script>