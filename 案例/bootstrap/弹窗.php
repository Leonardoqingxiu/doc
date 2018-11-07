{extend name="public/base"/}
{block name="style"}
<link rel="stylesheet" type="text/css" href="__PUBLIC__/css/libs/bootstrap-editable.css">
{/block}
{block name="body"}
<div class="main-box clearfix">
    <header class="main-box-header clearfix">
        <div class="pull-left">
            <h2>{$meta_title}</h2>
        </div>
        <div class="pull-right">
            <button class="btn btn-primary" data-toggle="modal" data-target="#searchmodal">搜索添加</button>
            <button class="btn btn-danger ajax-post confirm" url="{:url('delete')}" target-form="ids">删 除</button>
        </div>
    </header>
    <div class="main-box-body clearfix">
        <div class="table-responsive clearfix">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th width="30"><input class="checkbox check-all" type="checkbox"></th>
                    <th width="60">ID</th>
                    <th>名称</th>
                    <th>二维码</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                {volist name="list" id="item"}
                <tr>
                    <td><input class="ids row-selected" type="checkbox" name="id[]" value="{$item['id']}"></td>
                    <td>{$item['id']}</td>
                    <td>{$item['name']}</td>
                    <td><img src="{$item['qrcode']}" style="width: 128px;height: 88px;" onclick="showQrcode(this)"/></td>
                    <td>
                        {if condition="$item['status'] eq 1"}
                        签到
                        {else /} 未签到
                        {/if}
                    </td>
                    <td>
                        <!--<a href="{:url('rec_edit?id='.$item['id'])}">编辑</a>-->
                        <a href="{:url('rec_delete?id='.$item['id'])}" class="confirm ajax-get">删除</a>
                    </td>
                </tr>
                {/volist}
                </tbody>
            </table>
            {$page}
        </div>
    </div>

    <!-- 模态框（Modal） -->
    <div class="modal fade" id="searchmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="togglemodal('searchmodal')">
                        &times;
                    </button>
                    <h4 class="modal-title">
                        搜索
                    </h4>
                </div>
                <div class="modal-body">
                    <!--input-->
                    <div class="col-lg-6">
                        <div class="input-group">
                            <input type="text" class="form-control" name="keyword">
                            <span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="searchUser()">
							搜索
						</button>
					</span>
                        </div>
                    </div>
                    <!--list-->
                    <form method="post">
                        <table class="table table-hover" id="searchlist">
                            <thead>
                            <tr>
                                <th width="30"><input class="checkbox" type="checkbox" onclick="checkall(this)"></th>
                                <th width="60">ID</th>
                                <th>名称</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" onclick="togglemodal('searchmodal')">关闭
                    </button>
                    <button type="button" class="btn btn-primary" onclick="addrec()">
                        提交更改
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>

    <!-- 模态框（Modal） -->
    <div class="modal fade" id="qrcodemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="togglemodal('qrcodemodal')">
                        &times;
                    </button>
                    <h4 class="modal-title">
                        二维码
                    </h4>
                </div>
                <div class="modal-body" style="text-align: center">
                    <img src="", id="qrcode" width="100%">
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>


    <script type="application/javascript">
        // 搜索
        function searchUser(){
            // console.log($("input[name='keyword']").val());
            $.post("{:url('search_meet_user')}", {keyword: $("input[name='keyword']").val()}, function (res) {
                switch (res.code){
                    case 1:
                        $("#searchlist").children('tbody').html(res.data);
                        break;
                    case 0:
                        alert(res.msg);
                        $("#searchlist ").children('tbody').html('');
                        break;
                }
            })
        }
        // 全选
        function checkall(obj) {
            var e = $("input[name='user_id[]']");
            console.log(e);
            if(e.length >= 50){
                alert('不能选超过50个人');
                return false;
            }
            e.each(function (index, el) {
                el.checked = obj.checked;
            });
        }
        // 提交
        function addrec() {
            var t = $('form').serializeArray();
            var data = $.map(t, function (el, index) {
                return el.value;
            });
            $.post("{:url('add_meet_rec')}", {data:data, meetid: {$meetid}}, function (res) {
                alert(res.msg);
                togglemodal('searchmodal');
                window.location.reload();
            });
        }
        // 关闭
        function togglemodal(name) {
            $('#'+name).modal('toggle');
        }

        $('#searchmodal').on('hide.bs.modal', function () {
            $("#searchlist ").children('tbody').html('');
            $("input[name='keyword']").val('');
        });

        function showQrcode(e) {
            $("#qrcode").attr('src', e.src);
            $('#qrcodemodal').modal('show');
        }

    </script>
{/block}