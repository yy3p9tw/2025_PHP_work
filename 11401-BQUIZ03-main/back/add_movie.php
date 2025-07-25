<div>
    <h2 class="ct">新增電影</h2>
    <hr>
    <form action="./api/add_movie.php" method="post" enctype="multipart/form-data">

    <div style="display:flex">
        <div>影片資料</div>
        <div>
         <table>
            <tr class="ct">
                <td class="ct">片名</td>
                <td><input type="text" name="name" id="name"></td>
            </tr>
            <tr>
                <td class="ct">分級</td>
                <td><select name="level" id="level">
                    <option value="1">普遍級</option>
                    <option value="2">保護級</option>
                    <option value="3">輔導級</option>
                    <option value="4">限制級</option>
                </select></td>
            </tr>
            <tr class="ct">
                <td>片長</td>
                <td><input type="text" name="length" id="length"></td>
            </tr>
            <tr class="ct">
                <td>上映日期</td>
                <td>
                    <select name="year" id="year">
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>年
                    <select name="month" id="month">
                        <?php
                        for($i=1;$i<=12;$i++){
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>月
                    <select name="day" id="day">
                        <?php
                        for($i=1;$i<=31;$i++){
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>日

                </td>
            </tr>
            <tr class="ct">
                <td>發行商</td>
                <td><input type="text" name="publish" id="publish"></td>
            </tr>
            <tr class="ct">
                <td>導演</td>
                <td><input type="text" name="director" id="director"></td>
            </tr>
            <tr class="ct">
                <td>預告影片</td>
                <td><input type="file" name="trailer" id="trailer"></td>
            </tr>
            <tr class="ct">
                <td>電影海報</td>
                <td><input type="file" name="poster" id="poster"></td>
            </tr>
         </table>
        </div>
    </div>
    <div style="display:flex">
        <div>劇情簡介</div>
        <div>
            <textarea name="intro" id="intro"></textarea>
        </div>
    </div>
    <div class="ct"><input type="submit" value="新增"><input type="reset" value="重置"></div>
    </form>
</div>