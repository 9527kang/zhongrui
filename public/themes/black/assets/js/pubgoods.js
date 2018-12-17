/**
 * Created by kuke on 2016/8/30.
 */
function getCate(id, element) {
    console.log(id);
    if (id && element) {
        $.get('/shop/getSecondCate/' + id
            , function (json) {
            if (json.code = 200) {
                $("#" + element).html(json.data);
            }
        }, 'json')
    } else {
        $("#" + element).html("<option>请选择分类</option>");
    }
}


//删除现有文件
function deletefile(obj)
{
    var id = obj.attr('attachment_id');
    obj.parent().remove();
    $('#file-'+id).remove();
}

var demo=$("#shop_info").Validform({
    tiptype:3,
    label:".label",
    showAllError:true,
    /*ajaxPost:false,*/
    dataType:{
        'positive':/^[1-9]\d*$/,
    },
    beforeSubmit:function(){
        var file_id = $('input[name="file_id[]"]').val();
        if(file_id){
            $('.file_check').removeClass('Validform_wrong aaa').addClass('Validform_right').html('').show();
        }else{
            $('.file_check').addClass('Validform_wrong aaa').html('请上传附件').show();
            return false;
        }

    }
});
$('#bounty').on('blur',function(){
    demo.eq(0).config({
        ajaxurl:{
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            data:{
                'param':$('#bounty').val()
            }
        }
    });

});