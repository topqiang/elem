
//ajax登陆
function ajaxLogin(redirect){
    var a_name = $("input[name='a_name']").val();
    var a_pass = $("input[name='a_pass']").val();
    var code = $("input[name='code']").val();
    if(a_name == ''){
        $('.notification').find('div').html('请输入用户名');
        $('.notification').show();
        $("input[name='a_name']").focus();
        return;
    }if(a_pass == ''){
        $('.notification').find('div').html('请输入密码');
        $('.notification').show();
        $("input[name='a_pass']").focus();
        return;
    }if(code == ''){
        $('.notification').find('div').html('请输入验证码');
        $('.notification').show();
        $("input[name='code']").focus();
        return;
    }else{
        $.ajax({
            url :redirect,
            type:'post',
            data:{a_name:a_name,a_pass:a_pass,code:code},
            dataType:'JSON',
            success:function(data){
                if(data.error != null){
                    $('.notification').find('div').html(data.error);
                    $('.notification').show();
                }else{
                    window.location.href = $('.form').attr('action');
                }
            }
        });
    }
}