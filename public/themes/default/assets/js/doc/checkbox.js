$(function(){
    var demo=$("#form").Validform({
        tiptype:3,
        label:".label",
        showAllError:true,
        ajaxPost:false,
        postonce:true,
        dataType:{
            'positive':/^[1-9]\d*$/
        },
        beforeSubmit:function(){
            if($('input[name="price"]').length > 0){
                var price = $('#price').val();
                var min = $('#price').attr('data-min');
                var max = $('#price').attr('data-max');
                if(parseFloat(price) < 0 ||parseFloat(price) < parseFloat(min) || parseFloat(price) > parseFloat(max)){
                    $('.price-check').removeClass('Validform_right').addClass('Validform_wrong').text('请输入正确的任务报价').show();
                    return false;
                }else{
                    $('.price-check').removeClass('Validform_wrong').text('').hide();
                }
            }
        }


    });

    $('#editor1').on('blur',function()
    {
        demo.check(false,'#discription-edit');
    });

    //验证描述是否为空
    ue.addListener('blur',function(editor){
        var content = ue.getContent();
        $('#discription-edit').val(content);
        demo.check(false,'#discription-edit');
    });


});

function checkCash(price,min,max){
    var isNum=/^[0-9]+(.[0-9]{1,2})?$/;
    if(isNum.test(price)){
        if(parseFloat(price) < 0 ||parseFloat(price) < parseFloat(min) || parseFloat(price) > parseFloat(max)){
            $('.price-check').removeClass('Validform_right').addClass('Validform_wrong').text('请输入正确的任务报价').show();
            return false;
        }else{
            $('.price-check').removeClass('Validform_wrong').addClass('Validform_right').text('').show();
        }
    }else{
        $('.price-check').removeClass('Validform_right').addClass('Validform_wrong').text('任务报价最多两位小数').show();
        return false;
    }

}
