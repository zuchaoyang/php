/**
* 验证未通过原因
* return：boolean(true合法/false不合法)
**/
function validateReason()
{
    var id="reason_txt";
    var len=Share.strProcess.trimLR($("#"+id).val()).length;
    if(len<1||len>200)
    {
        alert("未通过原因字符长度在1-200之间,请重新输入");
        Share.sbf.focus(id);
        return false;
    }
    return true;
}