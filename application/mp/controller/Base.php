<?php
// +----------------------------------------------------------------------
// | [RhaPHP System] Copyright (c) 2017 http://www.rhaphp.com/
// +----------------------------------------------------------------------
// | [RhaPHP] 并不是自由软件,你可免费使用,未经许可不能去掉RhaPHP相关版权
// +----------------------------------------------------------------------
// | Author: Geeson <qimengkeji@vip.qq.com>
// +----------------------------------------------------------------------


namespace app\mp\controller;


use app\common\model\Addons;
use think\Db;
use think\facade\Session;

class Base extends \app\admin\controller\Base
{

    public $mid;
    public $mpInfo;
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        if (input('mid')) {
            $this->mid = input('mid');
            session('mid', $this->mid);
            $this->mpInfo = getMpInfo($this->mid);//缓存公众号信息
            session('mpInfo',$this->mpInfo);
            $mpInfo = Db::name('mp')->where('user_id','=', $this->admin_id)
                ->where('is_use','=','1')
                ->find();
            if ($mpInfo['id'] != $this->mid) {
                Db::name('mp')->where('user_id','=',$this->admin_id)
                    ->where('id' ,'=', $this->mid)
                    ->update(['is_use' => '1']);
                Db::name('mp')->where('user_id','=', $this->admin_id)
                    ->where('id','neq', $this->mid)
                    ->update(['is_use' => '0']);
            }
        } else {//若没有 Mid 取出默认使用公众号
            $mpInfo = Db::name('mp')->where('user_id','=', $this->admin_id)
                ->where('is_use','=', '1')
                ->find();
            if(!empty($mpInfo)){
                $this->mid = $mpInfo['id'];
                session('mid', $this->mid);
                $this->mpInfo = getMpInfo($this->mid);//缓存公众号信息
                session('mpInfo',$this->mpInfo);
            }else{
                $this->redirect('mp/index/mpList');
            }
        }
        $options = array(
            'appid' => $this->mpInfo['appid'],
            'appsecret' => $this->mpInfo['appsecret'],
            'token' => $this->mpInfo['valid_token'],
            'encodingaeskey' => $this->mpInfo['encodingaeskey']
        );
        $this->getAddonForMenu();
       // $this->mpListByMenu();
        $this->assign('mpInfo',session('mpInfo'));
        $this->assign('mid',$this->mid);
        Session::set('mp_options', $options);
        Session::delete('miniapp_options');
    }

    public function getAddonForMenu(){
        $model=new Addons();
        $adList=$model->where(['menu_show'=>1,'status'=>1])->select();
        $this->assign('menu_app',$adList);
        $this->assign('menu_app_title','应用扩展');
    }
}