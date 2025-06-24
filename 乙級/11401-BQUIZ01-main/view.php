<?php
    switch ($_GET['do'] ?? 'title') {
        case 'title':
        ?>
        <h3 style="text-align:center;">新增標題區圖片</h3>
        <form action="">
            <div><label for="img">標題區圖片：</label>
            <input type="file" id="img" name="img"></div>
            <div><label for="alt">標題區替代文字：</label>
            <input type="text" id="alt" name="alt"></div>
            <div><input type="submit" value="新增">
                <input type="reset" value="重置"></div>
        </form>
<?php
    break;
        case 'ad':
        ?>
        
    <?php break;
            default:
                # code...
            break;
    }?>