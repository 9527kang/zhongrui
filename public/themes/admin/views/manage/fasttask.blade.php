<style>
	.chosen-container-multi .chosen-choices{
		height: 34px !important;
		line-height: 34px !important;
	}
	.chosen-container-multi .chosen-choices li.search-field input[type="text"]{
		height: 25px;
	}
</style>
<h3 class="header smaller lighter blue mg-top12 mg-bottom20">发布项目</h3>
<div class="tab-content">
    <div class="g-backrealdetails clearfix bor-border">

        <form class="form-horizontal clearfix transForm" role="form"  action="{!! url('manage/fastTask') !!}" method="post" enctype="multipart/form-data">
            {!! csrf_field() !!}
            <input type="hidden" name="uid" value="@if(isset($task_info['uid'])){{$task_info['uid']}}@endif">
            <input type="hidden" name="id" value="@if(isset($data['id'])){{$data['id']}}@endif">
            <div class="bankAuth-bottom clearfix col-xs-12" style="margin-top:20px;">
                <p class="col-sm-2 control-label no-padding-left" for="form-field-1"> *联系手机：</p>
                <p class="col-sm-6">
                    <input type="text" class="js_title"  value="@if(isset($task_info['mobile'])){{$task_info['mobile']}}@endif" name="mobile" datatype="m" nullmsg="请输入手机号">
                </p>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12" style="margin-top:20px;">
                <label for="inputPassword3" class="col-sm-2 control-label no-padding-right s-safetywrp1 s-labelwrp1">*项目分类&nbsp;</label>
                <div class="space-6 visible-xs-block"></div>
                <p class="col-sm-6">
                    <select class="pull-left" name="cate_pid" onchange="changeCate(this.value,'#cate_id')">
                        @foreach($category_all as $v)
                            <option value="{{$v['id']}}" >
                                {{$v['name']}}
                            </option>
                        @endforeach
                    </select>
                    <select class="pull-left" name="cate_id" id="cate_id">
                        @foreach($category_second as $v)
                            <option value="{{$v['id']}}" >
                                {{$v['name']}}
                            </option>
                        @endforeach
                    </select>
                    <div class="space-12 visible-xs-block"></div>
                </p>

            </div>
            <div class="bankAuth-bottom clearfix col-xs-12" style="margin-top:20px;">
                <label for="inputPassword3" class="col-sm-2 control-label no-padding-right s-safetywrp1 s-labelwrp1">*项目地域&nbsp;</label>
                <div class="space-6 visible-xs-block"></div>
                <p class="col-sm-6">
                    <select class="pull-left" name="region_limit" onchange="arealimit(this)">
                        <option value="0">不限地区</option>
                        <option value="1">指定地区</option>
                    </select>
                    <span id="area_select" style="display: none;">
                        <select name="province" style="margin-left:20px;" onchange="checkprovince(this)">
                            @foreach($province as $v)
                                <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                            @endforeach
                        </select>
                        <select name="city" id="province_check" onchange="checkcity(this)">
                            @foreach($city as $v)
                                <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                            @endforeach
                        </select>
                        <select name="area" id="area_check">
                            @foreach($area as $v)
                                <option value={{ $v['id'] }}>{{ $v['name'] }}</option>
                            @endforeach
                        </select>
                    </span>

                     <div class="space-12 visible-xs-block"></div>
                </p>

            </div>
            <div class="bankAuth-bottom clearfix col-xs-12" style="margin-top:20px;">
                <p class="col-sm-2 control-label no-padding-left"> *项目标题：</p>
                <p class="col-sm-6">
                    <input type="text" class="js_title" maxlength="20" placeholder="一句话概括你要做什么" value="@if(isset($task_info['title'])){{$task_info['title']}}@endif" name="title" datatype="*2-50" nullmsg="请输入项目名称">
                </p>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-sm-2 control-label no-padding-left"> *内容介绍：</p>
                <div class="col-sm-6">
                    <script id="editor"  type="text/plain" style="height:200px;resize:none" >@if(isset($task_info['desc'])){{$task_info['desc']}}@endif</script>
                    <input name="desc" id="discription-edit" hidden value="@if(isset($task_info['desc'])){{$task_info['desc']}}@endif" datatype="*" nullmsg="请填写内容介绍">
                </div>
            </div>

            <div class="bankAuth-bottom clearfix col-xs-12">
                <label class="col-sm-2 control-label no-padding-left" for="form-field-1"> 项目附件： </label>
                <div class="col-sm-6">
                   {{-- <div  class="dropzone clea>fix" id="dropzone" url="{{ URL('manage/fileUploads')}}" deleteurl="{{ URL('manage/fileDelet') }}">
                        <div class="fallback">
                            <input name="file_id" type="file" multiple="" />
                        </div>
                    </div>--}}
                    <div class="row">
                        <div class="col-sm-4">
                            <input type="file" id="file_upload"/>
                            <input type="hidden" id="relation_type" value="task"/>
                        </div>
                        <div class="col-sm-2" style="padding: 0;">
                            <button type="button" class="btn btn-xs btn-info2 btn-block upload-quote-file">
                                <span>上传</span>
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-10 pd-r0">
                            <div class="file-box  hide" id="file-list">
                                <div class="file-list"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div id="file_update"></div>
            </div>

            <div class="bankAuth-bottom clearfix col-xs-12" style="margin-top:20px;">
                <label for="inputPassword3" class="col-sm-2 control-label no-padding-right s-safetywrp1 s-labelwrp1">*项目类型&nbsp;</label>
                <div class="space-6 visible-xs-block"></div>
                <p class="col-sm-6">
                    <select class="pull-left" name="type_alias" onchange="changeTaskType(this.value)">
                        @foreach($rewardModel as $v)
                            <option value="{{$v['alias']}}">
                                {{$v['name']}}
                            </option>
                        @endforeach
                    </select>
                    <div class="space-12 visible-xs-block"></div>
                </p>
            </div>

            <div id="xuanshang" style="display: block;">
                <div class="bankAuth-bottom clearfix col-xs-12">
                    <p class="col-sm-2 control-label no-padding-left" > *项目赏金：</p>
                    <p class="col-sm-6">
                        <input type="number"  name="bounty_xuanshang" placeholder="请填写项目赏金">
                        <span class="unit">RMB</span>
                    </p>
                </div>
                <div class="bankAuth-bottom clearfix col-xs-12">
                    <p class="col-sm-2 control-label no-padding-left" > *需要人数：</p>
                    <p class="col-sm-6">
                        <input min="1" type="number" name="worker_num_xuanshang" placeholder="请填写所需人数" >
                        <span>若项目需要多人参与，则参与项目的服务商平分项目金额</span>
                    </p>
                </div>
                <div class="bankAuth-bottom clearfix col-xs-12">
                    <p class="col-sm-2 control-label no-padding-left" > *投稿时间：</p>
                    <p class="col-sm-6">
                        <input type="text"  class="datepiker-deadline" placeholder="选择投稿截至日期" name="delivery_deadline_xuanshang" >
                        <span class="add-on"></span>
                    </p>
                </div>
            </div>
            <div id="zhaobiao" style="display: none;">
                @if($isKee)
                    <div class="bankAuth-bottom clearfix col-xs-12">
                    <p class="col-sm-2 control-label no-padding-left" > 接入交付台：</p>
                    <div class="col-sm-6">
                        <label>
                            是否接入交付台(使用<a target="_blank" href="http://www.jiaofutai.com/home">交付台</a>管理项目，可提交交付计划
                            创建任务，项目管理更加可控，是否立即启用？)
                        </label>
                        <p>
                            <label>
                                <input type="radio" name="is_to_kee" value="1">&nbsp;
                                立即启用
                            </label>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <label>
                                <input type="radio" name="is_to_kee" value="0">&nbsp;
                                <span>暂时不用</span>
                            </label>
                        </p>

                    </div>
                </div>
                @endif
                <div class="bankAuth-bottom clearfix col-xs-12">
                    <p class="col-sm-2 control-label no-padding-left" > 项目预算：</p>
                    <p class="col-sm-6">
                        <input type="number"  name="bounty_zhaobiao" placeholder="请填写项目预算">
                        <input value="1" type="hidden" name="worker_num_zhaobiao">
                        <span class="unit">RMB</span>
                    </p>
                </div>
                <div class="bankAuth-bottom clearfix col-xs-12">
                    <p class="col-sm-2 control-label no-padding-left"> *投稿时间：</p>
                    <p class="col-sm-6">
                        <input type="text"  class="datepiker-deadline" placeholder="选择投稿截至日期" name="delivery_deadline_zhaobiao" >
                        <span class="add-on"></span>
                    </p>
                </div>
            </div>
            <div class="bankAuth-bottom clearfix col-xs-12">
                <p class="col-sm-2 control-label no-padding-left"> 增值服务：</p>
                <p class="col-sm-6">
                    @foreach($service as $v)
                    <input type="checkbox"  name="product[]" value="{{$v['id']}}" >{{ substr($v['title'],3,3) }}/{{$v['price']}}RMB&nbsp;&nbsp;&nbsp;&nbsp;
                    @endforeach
                    <span class="add-on"></span>
                </p>
            </div>
            <div class="col-xs-12">
                <div class="clearfix row bg-backf5 padding20 mg-margin12">
                    <div class="col-xs-12">
                        <div class="col-md-1 text-right"></div>
                        <div class="col-sm-6">
                            <button class="btn btn-info" type="submit">
                                <i class="fa fa-check bigger-100"></i>提交</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- 删除附件弹窗确认 -->
<div class="modal fade bs-modal-ys" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
     id="fileDelete">
    <div class="modal-dialog modal-sm">
        <div class="modal-content text-center">
            <input type="hidden" id="delete_id" value=""/>
            <div class="space-20"></div>
            <p>确认要删除此文件吗?</p>
            <div class="space-10"></div>
            <button class="btn btn-primary btn-xs" onclick="confirmDel(this)">确认</button>
            <button class="btn btn-default btn-xs" data-dismiss="modal">取消</button>
            <div class="space-20"></div>
        </div>
    </div>
</div>
<script>
    var uploadRule = '{!! CommonClass::attachmentUnserialize() !!}';
</script>

{!! Theme::asset()->container('custom-css')->usePath()->add('back-stage-css', 'css/backstage/backstage.css') !!}
{!! Theme::asset()->container('specific-css')->usePath()->add('validform-css', 'plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('validform-js', 'plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}

{!! Theme::widget('uploadimg')->render() !!}
{!! Theme::widget('datepicker')->render() !!}
{!! Theme::widget('ueditor')->render() !!}
<script>
    function gritterAdd(tips){
        $.gritter.add({
            text:'<div><span class="text-center"><h5>'+tips+'</h5></span></div>',
            time:2000,
            position: 'bottom-center',
            class_name: 'gritter-center gritter-info',
        });
    }
    jQuery(function($){

        //附件上传
        $(".upload-quote-file").click(function(){
            var file = document.getElementById("file_upload").files[0];
            if(!file){
                gritterAdd('未选择要上传的文件');
                return false;
            }
            var relation_type = $("#relation_type").val();
            var _token = $("input[name=_token]").val();
            var myForm = new FormData();
            myForm.append('file',file);
            myForm.append('_token',_token);
            myForm.append('relation_type',relation_type);
            $(this).prop('disabled','disabled');
            $(this).find('span').text('上传中');
            $.ajax({
                url:'/manage/fileUploads',
                type:'POST',
                data:myForm,
                processData: false,// 告诉jQuery不要去处理发送的数据
                contentType: false,// 告诉jQuery不要去设置Content-Type请求头
                success:function(data){
                    $(".file-box").removeClass('hide');
                    $(".file-list").append(data.html);
                    $("#file_upload").val('');
                    $(".bootstrap-filestyle").find("input").val('');
                    gritterAdd('文件上传成功');
                    $(".upload-quote-file").prop('disabled',false);
                    $(".upload-quote-file span").text('上传');
                    return false;
                }
            });
        });

    });
    //获取待删除文件的id
    function delFile(obj){
        var id = $(obj).attr("rel");
        $('#delete_id').val(id);
    }

    //确认 附件删除文件
    function confirmDel(obj){
        var id = $('#delete_id').val();
        var url = '/manage/fileDelete';
        $.get(url,{id:id},function(data){
            if(data.errCode == 1){
                $('#delete_id').val("");
                if($(".file-list").find('p').length == 1){
                    $(".file-box").addClass('hide');
                }
                $(".atta-"+id).remove();
                $("#fileDelete").modal('hide');
            }else{
                $('#delete_id').val("");
                gritterAdd(data);
                $("#fileDelete").modal('hide');
                return false;
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

    function changeTaskType(alias){
        if(alias == 'xuanshang'){
            $('#xuanshang').attr('style','display:block;');
            $('#zhaobiao').attr('style','display:none;');
        }else{
            $('#xuanshang').attr('style','display:none;');
            $('#zhaobiao').attr('style','display:block;');
        }
    }

    /**
     * 省级切换
     * @param obj
     */
    function checkprovince(obj){
        var id = obj.value;
        $.get('/task/ajaxcity',{'id':id},function(data){
            var html = '';
            var area = '';
            for(var i in data.province){
                html+= "<option value=\""+data.province[i].id+"\">"+data.province[i].name+"<\/option>";
            }
            for(var s in data.area){
                area+= "<option value=\""+data.area[s].id+"\">"+data.area[s].name+"<\/option>";
            }
            $('#province_check').html(html);
            $('#area_check').html(area);
            $('#region-limit').attr('value',data.area[0].id);
        });
    }
    /**
     * 市级切换
     * @param obj
     */
    function checkcity(obj){
        var id = obj.value;
        $.get('/task/ajaxarea',{'id':id},function(data){
            var html = '';
            for(var i in data){
                html += "<option value=\""+data[i].id+"\">"+data[i].name+"<\/option>";
            }
            $('#area_check').html(html);
            $('#region-limit').attr('value',data[0].id);
        });
    }

    function arealimit(obj){
        var region_limit = $(obj).val();
        if(region_limit == 1){
            $('#area_select').attr('style','display:block;');
        }else{
            $('#area_select').attr('style','display:none;');
        }
    }

    function getObjectURL(file) {
        var url = null;
        if (window.createObjectURL != undefined) { // basic
            url = window.createObjectURL(file);
        } else if (window.URL != undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file);
        } else if (window.webkitURL != undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file);
        }
        return url;
    }
    $(function(){
        //切换为中文显示
        $.fn.datepicker.dates['zh-CN'] = {
            days: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
            daysShort: ["日", "一", "二", "三", "四", "五", "六", "七"],
            daysMin: ["日", "一", "二", "三", "四", "五", "六", "七"],
            months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            today: "今天",
            clear: "清除"
        };
        $('.datepiker-deadline').datepicker({
            autoclose: true,
            startDate: "2013-02-14 10:00",
            language:'zh-CN',
            format: 'yyyy-mm-dd',     //日期格式
        });
        // 初始化
        $('.datepiker-deadlines').datepicker({
            autoclose: true,
            //startDate: "2013-02-14 10:00",
            language:'zh-CN',
            format: 'yyyy-mm-dd',     //日期格式
        });

        var demo = $(".transForm").Validform({
            tiptype:3,
            label:".label",
            showAllError:true,
            ajaxPost:false,
            beforeSubmit:function(){
                var content = ue.getContent();
                $('#discription-edit').val(content);

                var alias = $('select[name="type_alias"]').val();
                if(alias == 'xuanshang'){
                    var bounty = $('input[name="bounty_xuanshang"]').val();
                    if(!bounty){
                        gritterAdd('请输入项目赏金');
                        return false;
                    }
                }
            }

        });
        ue.addListener('blur',function(editor){
            var content = ue.getContent();
            $('#discription-edit').val(content);
        });

        //jq上传图片在线预览
        $(document).on('change', '#id-input-file', function () {
            var objUrl = getObjectURL(this.files[0]); //获取图片的路径，该路径不是图片在本地的路径
            if (objUrl) {
                $('.middle').attr("src", objUrl); //将图片路径存入src中，显示出图片
            }
        });
    });

</script>
