/**
 * Created by kuke on 2016/5/6.
 */


var financeExport = function(){
    var start = $("input[name = 'start']").val();
    var end = $("input[name = 'end']").val();
    var param = 'start=' + start + '&end=' + end;
    var url = document.domain + '/manage/financeListExport?' + param;
    window.open('http://' + url);
};

var userFinanceExport = function(){
    var start = $("input[name = 'start']").val();
    var end = $("input[name = 'end']").val();
    var username = $("input[name = 'username']").val();
    var action = $("#action").val();
    var param = 'start=' + start + '&end=' + end  + '&username=' + username + '&action=' + action;
    var url = document.domain + '/manage/userFinanceListExport?' + param;
    window.open('http://' + url);
};

function dateToTimestamp(date)
{
    return new Date(date).getTime()
}