<fieldset>
    <legend>會員登入</legend>

<form>
    <table>
        <tr>
            <td>帳號</td>
            <td><input type="text" name="acc" id="acc"></td>
        </tr>
        <tr>
            <td>密碼</td>
            <td>
                <input type="password" name="pw" id="pw">
            </td>
        </tr>
        <tr>
            <td>
                <input type="button" value="登入" onclick='login()'>
                <input type="reset" value="清除">
            </td>
            <td>
                <a href="?do=forgot">忘記密碼</a>
                    |
                <a href="?do=reg">註冊會員</a>
            </td>
        </tr>
    </table>
</form>

</fieldset>
<script>
    function login(){
        let data={
            acc:$("#acc").val(),
            pw:$("#pw").val()
        }

        $.get("./api/chk_acc.php",data,(res)=>{
            if(parseInt(res)){
                $.get("./api/chk_pw.php",data,(res)=>{
                    if(parseInt(res)){
                        //登入成功
                        if(data.acc=='admin'){
                            location.href="back.php"
                        }else{
                            location.href="index.php";
                        }
                    }else{
                        alert("密碼錯誤");
                        location.href="index.php?do=login";
                    }
                })


            }else{
                alert("查無帳號")
            }
        })


    }
</script>