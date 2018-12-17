<h3 class="header smaller lighter blue mg-bottom20 mg-top12">添加成功案例</h3>

<div class="g-backrealdetails clearfix bor-border">
    <form class="form-horizontal" action="/manage/successCaseUpdate" method="post" enctype="multipart/form-data" id="success-case">
        {{ csrf_field() }}
        @if(isset($success_case))
            <input type="hidden" name="id" value="{{ $success_case['id'] }}">
        @endif
        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>案例名称</strong>  </label>
            <p class="col-sm-11">
                <input type="text" id="form-field-1" name='title' class="col-xs-3 col-sm-3" value="{{ (isset($success_case)?$success_case['title']:'') }}"  datatype="*" nullmsg="请填写案例名称！"  >
            </p>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>案例金额</strong>  </label>
            <p class="col-sm-11">
                <input type="text" name='cash' class="col-xs-3 col-sm-3" value="{{ (isset($success_case)?$success_case['cash']:'') }}"  datatype="decimal" nullmsg="请填写案例金额！" errormsg="金额最多两位小数" >
            </p>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>选择服务商</strong>  </label>
            <p class="col-sm-11">
                <select name="uid" class="chosen-select"  data-placeholder="请选择服务商...">
                    @if($user)
                        @foreach($user as $v)
                            <option value="{{ $v['id'] }}" @if(isset($success_case)){{ $v['id'] == $success_case['uid'] ?'selected':'' }} @endif>{{ $v['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </p>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>上传封面</strong>  </label>
            <div class="col-sm-4">
                <div class="memberdiv pull-left">
                    <div class="position-relative">
                        <input type="file" id="id-input-file-3" name="pic" />
                    </div>
                    @if(isset($success_case) && is_file($success_case['pic']))
                        <img src="{!! url($success_case['pic']) !!}" height="200px" width="200px">
                    @endif
                </div>
            </div>
        </div>

        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>案例分类</strong>  </label>
            <div class="col-sm-11">
                <p class="g-userimgupinp g-userimgupbor-validform">
                    <select name="cate_first" id="cate_first">
                        @if(!empty($cate_first))
                            @foreach($cate_first as $item)
                                <option value="{!! $item['id'] !!}"
                                        @if(isset($success_case->cate_pid) && $success_case->cate_pid == $item['id'])selected="selected" @endif>
                                    {!! $item['name'] !!}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <select name="cate_id" id="cate_id">
                        @if(!empty($cate_second))
                            @foreach($cate_second as $item)
                                <option value="{!! $item['id'] !!}" @if(isset($success_case->cate_id) && $success_case->cate_id == $item['id'])selected="selected" @endif>
                                    {!! $item['name'] !!}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </p>
            </div>
        </div>

        <!-- 案例描述 -->
        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>案例描述</strong>  </label>
            <div class="col-sm-8">
                <script id="editor" name="desc"  type="text/plain" style="width:100%;height:300px;" >{!! isset($success_case)?$success_case['desc']:old('description') !!}</script>
            </div>
            <div class="space-4 col-xs-12"></div>
        </div>
        <div class="bankAuth-bottom clearfix col-xs-12">
            <label class="col-sm-1 control-label no-padding-right" for="form-field-1"><strong>案例链接</strong>  </label>
            <p class="col-sm-11">
                <input type="text"  name="url" class="col-xs-3 col-sm-3"  value="{{ (isset($success_case)?$success_case['url']:'') }}">
            </p>
        </div>
    </form>
    <div class="col-xs-12">
        <div class="clearfix row bg-backf5 padding20 mg-margin12">
            <div class="col-xs-12">
                <div class="col-sm-1 text-right"></div>
                <div class="col-sm-10">
                    <button type="submit" form="success-case" class="btn btn-primary btn-blue bor-radius2 btn-big1 subTask">保存</button>
                    <a href="javascript:history.back()" title="" class=" add-case-concel">返回</a>
                </div>
            </div>
        </div>
    </div>
</div>
{!! Theme::asset()->container('custom-css')->usepath()->add('issuetask','css/taskbar/issuetask.css') !!}
{!! Theme::asset()->container('custom-css')->usePath()->add('backstage', 'css/backstage/backstage.css') !!}
{!! Theme::asset()->container('custom-css')->usepath()->add('style','css/blue/style.css') !!}


{!! Theme::widget('popup')->render() !!}
{{--{!! Theme::widget('editor')->render() !!}--}}
{!! Theme::widget('ueditor')->render() !!}

{!! Theme::asset()->container('custom-css')->usePath()->add('chosen', 'plugins/ace/css/chosen.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('chosen','plugins/ace/js/chosen.jquery.min.js') !!}

{!! Theme::asset()->container('specific-css')->usepath()->add('validform-css','plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('validform-js','plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}

{!! Theme::asset()->container('custom-js')->usePath()->add('backstage', 'js/doc/successcase.js') !!}

