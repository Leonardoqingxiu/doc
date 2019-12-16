    <div class="main-box-body clearfix">
        <div class="table-responsive clearfix">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>用户编号</th>
                    <th>员工姓名</th>
                    <th>性别</th>
                    <th>分支机构</th>
                    <th>职位</th>
                    <th>联系电话</th>
                    <th>绑定微信</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                {volist name="list" id="item"}
                <tr>
                    <td>{$item['id']}</td>
                    <td>{$item['name']}</td>
                    <td class="neededit discolor sex" name="sex">
                        {if condition="$item['sex'] == 1"}
                            男
                        {elseif condition="$item['sex'] == 2"/}
                            女
                        {else}
                            未选择
                        {/if}
                    </td>
                    <td>{$item['compname']}</td>
                    <td>{$item['job']}</td>
                    <td>{$item['phone']}</td>
                    <td>{$item['nickname']}</td>
                    <td>
                        {if condition="$item['status'] == 1"}
                            审核通过
                        {else/}
                            待审核
                        {/if}
                    </td>
                    <td>
                        <a href="jacascript::void(0)"  onclick="showSetVip('{$item.id}','{$item.status}')">设置vip客户</a>
                        <a href="{:url('perdetail?id='.$item['id'].'&compname='.$item['compname'])}">查看</a>
                        <a href="{:url('perdelete?id='.$item['id'])}" class="confirm ajax-get">移除</a>
                        {if condition="$item['status'] == 2"}
                            <a href="{:url('examine?id='.$item['id'])}" class="confirm ajax-get">审核</a>
                        {/if}
                        <a data-id="{$item.id}"  data-sex="{$item.sex|default=0}" style="cursor:pointer" class="edit">修改</a>
                    </td>
                </tr>
                {/volist}
                </tbody>
            </table>
            {$page_render}
        </div>
    </div>


    <script type="application/javascript">

    $(".edit").click(function (e) {
        e.stopPropagation();
        var parentnode=$(this).parent().parent();
        if($(this).html()=='保存'){
            var Gender=parentnode.find(".Gender").val();
            var id=$(this).data('id');
            if(confirm('确定？')){
                $.ajax({
                    url:'/admin/meeteam/saveOption',
                    type:"POST",
                    data:{sex:Gender,id:id},
                    dataType:"json",
                    success:function(data){
                        $.showMessage(data.msg);
                        if(data.code==1){
                            setTimeout(function () {
                                location.reload()
                            },500)
                        }
                    }
                })
            }
        }else{
            $(this).html('保存');
            var sex=$(this).data('sex');
            var sex_arr = ['请选择','男','女'];
            var str ="<select class='Gender'>";
            for(let index in sex_arr) {
                if(sex == index){
                    str += " <option value="+index+" selected>"+sex_arr[index]+"</option>";
                }else{
                    str += " <option value="+index+">"+sex_arr[index]+"</option>";
                }
            };
            parentnode.find(".sex").empty().prepend(str);
        }
    });

</script>