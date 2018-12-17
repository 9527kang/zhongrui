$(function(){
    $('.screen').on('click',function(){
        $('input[name="type"]').val($(this).attr('data-type'));
        $('#screen_form').submit();
    });
});