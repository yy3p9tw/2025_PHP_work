<fieldset>
    <legend>會員註冊</legend>
<form>
    <div class="ct" style="color:red">請設定您要註冊的帳號及密碼(最長12個字元)</div>
    <table>

        <tr>
            <td>Step1:登入帳號</td>
            <td>
                <input type="text" name="acc" id="acc">
            </td>
        </tr>
        <tr>
            <td>Step2:登入密碼  </td>
            <td>
                <input type="password" name="pw" id="pw">
            </td>
        </tr>
        <tr>
            <td>Step3:再次確認密碼</td>
            <td>
                <input type="password" name="pw2" id="pw2">
            </td>
        </tr>
        <tr>
            <td>Step4:信箱(忘記密碼時使用)</td>
            <td><input type="text" name="email" id="email"></td>
        </tr>
        <tr>
            <td>
                <input type="button" value="註冊" onclick="reg()">
                <input type="reset" value="清除">
            </td>
            <td></td>
        </tr>
    </table>
</fieldset>
<script>
function reg(){
    let data={
        acc:$("#acc").val(),
        pw:$("#pw").val(),
        pw2:$("#pw2").val(),
        email:$("#email").val()
    }

    if(data.acc=='' || data.pw=='' || data.pw2=='' || data.email==''){
        alert("不可空白");
    }else if(data.pw != data.pw2){
        alert("密碼錯誤")
    }else{
        $.get("./api/chk_acc.php",data,(res)=>{
            if(parseInt(res)){
                alert("帳號重複")
            }else{
                $.post("./api/reg.php",data,(res)=>{
                    console.log(res);
                    if(parseInt(res)){
                        alert("註冊成功，歡迎加入")
                        location.href="?do=login";
                    }else{
                        alert("註冊失敗，請稍後再試")
                    }
                })
            }
        })
    }

}
</script>