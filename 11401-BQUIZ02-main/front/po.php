<div class="nav" style="margin-bottom:20px;">
    目前位置：首頁 > 分類網誌 > <span id='NavType'>健康新知</span>
</div>

<fieldset style="width:120px;display:inline-block;vertical-align:top">
    <legend>分類網誌</legend>
    <div><a class='type-link' data-type='1'>健康新知</a></div>
    <div><a class='type-link' data-type='2'>菸害防治</a></div>
    <div><a class='type-link' data-type='3'>癌症防治</a></div>
    <div><a class='type-link' data-type='4'>慢性病防治</a></div>
</fieldset>
<fieldset style="width:590px;display:inline-block">
    <legend>文章列表</legend>
    <div id="TypeList"></div>
    <div id="Post"></div>
    
</fieldset>
<script>
getList(1);

$(".type-link").on("click",function(){
    let type=$(this).text();
    $("#NavType").text(type);
    let typeId=$(this).data("type");
    getList(typeId);
})

function getPost(id){
    $.get("./api/get_post.php",{id},function(post){
            $("#TypeList").html("");
            $("#Post").html(post)
        })
}


$(".post-item").on("click",function(){
    let postId=$(this).data("post");
    getPost(postId);    
})


function getList(type){
    $.get("api/get_type_list.php",{type},function(list){
        $("#Post").html("");
        $("#TypeList").html(list);
        //console.log('list');
        $(".post-item").on("click",function(){
          //  console.log('post-item');
                // 取得文章ID   
          let postId=$(this).data("post");
                
                getPost(postId);    
        })
    })
}
</script>