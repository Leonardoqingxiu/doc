{extend name="public/base" /}
{block name="style"}
<script src="/public/js/qrcode.min.js"></script>
{/block}
{block name="body"}
<div class="row">
	<form id="form1" action="">
		<h1>已拥有的服务：</h1>
		{volist name="use_service" id="use"}
		<div class="checkbox-inline">
			<label for="{$use.name}" style="font-size: 18px;">{$use.name}</label>
		</div>
		{/volist}

		<h1>未拥有的服务：</h1>
		{volist name="un_use_service" id="un_use"}
		<div class="checkbox-nice checkbox-inline">
			<input type="checkbox" name="services[]" id="{$un_use.name}"  value="{$un_use.id}" />
			<label for="{$un_use.name}" style="font-size: 18px;">{$un_use.name}</label><span style="margin-left:20px;color:red">(价格：{$un_use.charge})</span>
		</div>

		{/volist}
		{if $un_use_service}
		<div>
			<button style="margin:20px" class="btn btn-success submit-btn" target-form="form-horizontal" id="buy" data-flag="0">购 买</button>
		</div>
		{/if}
	</form>

	<!-- 模态框（Modal） -->
	<div class="modal fade" id="facemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" onclick="togglemodal('facemodal')">
						&times;
					</button>
					<h4 class="modal-title" style="text-align: center">微信扫描下方二维码支付</h4>
				</div>
				<div class="modal-body" id="qrcode" style="text-align: center">
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal -->
	</div>
	<script>

        function togglemodal(name) {
            $('#'+name).modal('toggle');
        }

		$("#buy").on('click',function (event) {
            event.preventDefault();
            if($("#form1 :checked").length==0){
                $.showMessage('请选择要购买的模块');
                return false;
			}
            var formData = new FormData($("#form1")[0]);
            var that=$(this);
            var flag=that.attr('data-flag');
            if(flag=='0'){
				that.attr('data-flag','1');
				$.ajax({
					type:'post',
                    data:formData,
                    url:"/admin/index/index",
					dataType:'json',
                    contentType:false,
                    processData: false,
					success:function(data){
                        that.attr('data-flag','0');
                        if(data.code==1){
							$("#qrcode").empty();
                            var qrcode = new QRCode("qrcode", {
                                width: 300,
                                height:300,
								display:'',
                                colorDark : "#000000",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.H
                            });
                            qrcode.makeCode(data.data.url); // 生成二维码
							$("#qrcode img").attr('style','margin:0 auto')
                            $('#facemodal').modal('show');
							checkOrderStatus(data.data.id);
						}else{
                            $.showMessage(data.msg);
                            setTimeout(function(){
                                location.reload();
							},1500);
						}
					},
					error:function(data){
                        that.attr('data-flag','0');
                        $.showMessage('数据传输失败');
					},

				})
			}else{
                $.showMessage('点击过快');
			}
        })

		function checkOrderStatus(id){
            var num=0;
            var clock=setInterval(function(){
                if(num>=100){
                    clearInterval(clock);
				}
                $.post("/admin/index/checkOrderStatus",{id:id},function(data){
					if(data.code==1){
                        $('#facemodal').modal('toggle');
                        $.showMessage('支付成功');
                        setTimeout(function () {
                            location.reload();
                        },1500)
					}
                },'json')
                num++;
            },5000);

		}
	</script>
</div>
<div class="row">
	{:hook('AdminIndex')}
</div>
{/block}