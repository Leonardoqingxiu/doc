<?php
// +----------------------------------------------------------------------
// | SentCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.tensent.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: molong <molong@tensent.cn> <http://www.tensent.cn>
// +----------------------------------------------------------------------

namespace app\admin\controller;
use app\common\controller\Admin;
use app\common\model\Account;
use think\Exception;

class Index extends Admin {

	public function index() {
        $service = model('Service');
        if(IS_POST){
	        $db=model('AdminOrder');
	        $sids=input('post.services/a');
	        if(empty($sids)){
                return ajaxReturn(0,'请选择购买模块');
            }
            $data=[];
            $data['sids']=implode(',',$sids);
            $fee=0;
            $buysids='';
            foreach($sids as $sid){
                $charge=$service->where(['id'=>$sid])->value('charge');
                if($charge>0){
                    $fee+=$charge;
                    if($buysids){
                        $buysids.=",".$sid;
                    }else{
                        $buysids=$sid;
                    }
                }elseif($charge<0){
                    return ajaxReturn(0,'价格错误');
                    break;
                }
            }
            $data['account_id']=session('account_id');
            $data['pay_money']=$data['order_money']=$fee;
            $data['order_id']="SE".date("YmdHis").rand_string(4,1);

            if($fee==0){
                $data['status']=1;
                $data['remark']="免费模块";
                $data['pay_time']=$data['add_time']=$data['update_time']=time();
                $db->startTrans();
                try{
                    $res=$db->insertGetId($data);
                    $add=[];
                    foreach($sids as $sid){
                        $add[]=['uid'=>$data['account_id'],'s_id'=>$sid,'status'=>1,'add_time'=>time()];
                    }
                    model('UserService')->saveAll($add);
                    $db->commit();
                    return ajaxReturn(2,'购买成功');
                }catch(Exception $e){
                    $db->rollback();
                    return ajaxReturn(0,$e->getMessage());
                }
            }else{
                $data['add_time']=time();
                if(!$res=$db->insertGetId($data)){
                    return ajaxReturn(0,'下单失败');
                };
            }

            $app = model('MeetOrder');
            $result = $app->getPrepayId('','美服网模块购买', $data['order_id'],$fee*100,'https://saastest.meifu123.com/index/notify/index','NATIVE' // 请对应换成你的支付方式对应的值类型
            );
	        return ajaxReturn(1,'成功',['url'=>$result,'id'=>$res]);
        }
        $user_auth = session("user_auth");
        //已拥有的服务
        $use_service = $service->getUseService($user_auth['uid']);
        $ids = formatToArr($use_service,'s_id');
        //未拥有的服务
        $un_use_service = $service->getUnUseService($user_auth['uid'],$ids);

        $this->assign('use_service',$use_service);
        $this->assign('un_use_service',$un_use_service);
//        echo '<pre>';var_dump($use_service,$un_use_service);exit;

		$this->setMeta('后台首页');
		return $this->fetch();
	}

}