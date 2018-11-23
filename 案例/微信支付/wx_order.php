<?php
// +----------------------------------------------------------------------
// | SentCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.tensent.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: molong <molong@tensent.cn> <http://www.tensent.cn>
// +----------------------------------------------------------------------

namespace app\common\model;

/**
* 用户会议头像模型
*/
class MeetOrder extends Base{
    private $order;
    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->order = load_wechat('Pay');
    }

    /**
     * 获取预支付ID 同一下单接口
     * @param string $openid 用户openid，JSAPI必填
     * @param string $body 商品标题
     * @param string $out_trade_no 第三方订单号
     * @param int $total_fee 订单总价
     * @param string $notify_url 支付成功回调地址
     * @param string $trade_type 支付类型JSAPI|NATIVE|APP
     * @param string $goods_tag 商品标记，代金券或立减优惠功能的参数
     * @param string $fee_type 交易币种
     * @return bool|string
     */
    public function getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type = "JSAPI", $goods_tag = null, $fee_type = 'CNY'){
        return $this->order->getPrepayId($openid, $body, $out_trade_no, $total_fee, $notify_url, $trade_type, $goods_tag, $fee_type);
    }

    /**
     * 生成jsapi 页面调用支付参数
     * @param $prepay_id
     */
    public function createMchPay($prepay_id){
        return $this->order->createMchPay($prepay_id);
    }


    /**
     * 订单退款接口
     * @param string $out_trade_no 商户订单号
     * @param string $transaction_id 微信订单号，与 out_refund_no 二选一（不选时传0或false）
     * @param string $out_refund_no 商户退款订单号，与 transaction_id 二选一（不选时传0或false）
     * @param int $total_fee 商户订单总金额
     * @param int $refund_fee 退款金额，不可大于订单总金额
     * @param int|null $op_user_id 操作员ID，默认商户ID
     * @param string $refund_account 退款资金来源
     *        仅针对老资金流商户使用
     *        REFUND_SOURCE_UNSETTLED_FUNDS --- 未结算资金退款（默认使用未结算资金退款）
     *        REFUND_SOURCE_RECHARGE_FUNDS  --- 可用余额退款
     * @param string $refund_desc 退款原因
     * @param string $refund_fee_type 退款货币种类
     * @return bool
     */
    public function  refund($out_trade_no, $transaction_id, $out_refund_no, $total_fee, $refund_fee, $op_user_id = null, $refund_account = '', $refund_desc = '', $refund_fee_type = 'CNY')
    {
       return $this->order-> refund($out_trade_no, $transaction_id, $out_refund_no, $total_fee, $refund_fee, $op_user_id , $refund_account , $refund_desc , $refund_fee_type );

    }

    /**
     * @param $meet_id 会议id
     * @param $nums
     * @param int $status
     */
    public function createOrder($meet,$nums,$share_oid){

        $data=array(
            'order_id'=>$this->getOrderId(),
            'account_id'=>$meet['account_id'],
            'meet_id'=>$meet['id'],
            'share_openid'=>$share_oid,
            'nums'=>$nums,
            'add_time'=>time(),
            'update_time'=>time(),
        );

        //判断是否免费
        if ($meet['charge']==0){
            $data['pay_time']=time();
            $data['order_money']=0;
            $data['pay_openid']='free';
            $data['wx_order_id']='free';
            $data['status']=1;
            //创建订单
            $order_id=$this->insertGetId($data);
            if (!$order_id){
                return false;
            }
            //创建签到二维码
            $d=$this->createOrderQr($nums,$meet['account_id'],$meet['id'],$order_id);
            if (!$d) return false;
        }else{
            $om=$meet['charge']*$nums;
            $data['pay_time']=0;
            $data['order_money']=$om;
            $data['pay_openid']='free';
            $data['wx_order_id']='free';
            $data['status']=0;
            //创建订单
            $order_id=$this->insertGetId($data);
            if (empty($order_id)) return false;
         }
        return $data['order_id'];
    }

    public function getOrderId(){
        return time()+rand(100000,999999);
    }

    /**
     *
     * 创建订单对应的电子票
     * 流程 先创建电子票记录,后生产二维码,在不上电子票的二维码地址
     * @param $nums
     * @param $account_id
     * @param $meet_id
     * @param $order_id
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function createOrderQr($nums,$account_id,$meet_id,$order_id){
        if ($nums>0){
            $this->startTrans();
            $ids=array();
            for ($i=0;$i<$nums;$i++){
                //生成票据id,后补上二维码链接,二维码需要带上对应id
                $id=model('MeetOrderQrCode')->insertGetId(array(
                    'account_id'=>$account_id,
                    'meet_id'=>$meet_id,
                    'order_id'=>$order_id,
                    'status'=>2,
                    'add_time'=>time(),
                    'update_time'=>time()
                ));
                if ($id>0){
                    $ids[]=$id;
                }else{
                    $this->rollback();
                    return false;
                }
            }

            //真正生成二维码
            if (!empty($ids)){
                foreach ($ids as $qid) {
                    $url=model('QrCode')->getSignUrl($qid);
                    $res=model('MeetOrderQrCode')->where(array('id'=>$qid))->update(array('qrcode'=>$url));
                    if (!$res){
                        $this->rollback();
                        return false;
                    }
                }
            }

        }
        $this ->commit();
        return true;
    }


}