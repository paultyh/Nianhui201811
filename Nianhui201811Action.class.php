<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2013 http://www.wileho.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Supser_Yang QQ:9627279 请勿擅自更改
// +----------------------------------------------------------------------
class Nianhui201811Action extends CommonAction {
	public function index(){
       $uinfo = $this->getuserinfo();
	   if($uinfo and $uinfo['get']){
		   $memdb = M('t201811nianhui_mem','acti_','DB_ACTI');
		   $memin['wuid'] = AESmcryptdecrypt($uinfo['wuid'])?AESmcryptdecrypt($uinfo['wuid']):AESmcryptdecrypt(urldecode($uinfo['wuid']));
		   if($memin['wuid']){
				$uinfo['myteam'] = $memdb->where($memin)->find();
		   }
	   }
       $this->assign("uinfo",$uinfo);   
       $this->display();
	}

	//创建战队
	//参数 teamname 要创建的战队名称 返回json中teamid为创建成功后的teamid
	public function createteam(){
		$uinfo = $this->getuserinfo();
		$teamname =  I('post.teamname');
		$wuid = AESmcryptdecrypt($uinfo['wuid'])?AESmcryptdecrypt($uinfo['wuid']):AESmcryptdecrypt(urldecode($uinfo['wuid']));
		$memdb = M('t201811nianhui_mem','acti_','DB_ACTI');
		$teamdb = M('t201811nianhui_team','acti_','DB_ACTI');
		if($teamname and $wuid){
			$hasteamin['teamname'] = $teamname;
			$hasteam = $teamdb->where($hasteamin)->select();
			if($hasteam){
				$arr['code'] = 1;
				$arr['msg'] = '创建失败，该战队已经存在';
			}else{
				$memdb->startTrans();
				$memin['wuid'] = array('neq',$wuid);
				$memin['teamname'] = array('neq',$teamname);
				$memsave['teamname'] = $teamname;
				$memsave['wuid'] = $wuid;
				$memsave['wname'] = $uinfo['userinfo']['wname'];
				$memsave['headimg'] = $uinfo['userinfo']['wimg'];
				$okmem = $memdb->where($memin)->add($memsave);
				if($okmem){
					$teamin['swuid'] =  array('neq',$wuid);
					$teamin['teamname'] = array('neq',$teamname);
					$teamsave['teamname'] = $teamname;
					$teamsave['swuid'] = $wuid;
					$teamsave['cdatetime'] = date('Y-m-d H:i:s');
					$okteam = $teamdb->where($teamin)->add($teamsave);
				}
				if($okteam and $okmem){
					$memdb->commit();
					$arr['code'] = 0;
					$arr['msg'] = '成功创建';
					$arr['teamid'] = $okteam;
				}else{
					$memdb->rollback();
					$arr['code'] = 1;
					$arr['msg'] = '创建失败，你是否已经创建或加入过了战队？';
				}
			}
		}else{
			$arr['code'] = 2;
			$arr['msg'] = '提交的信息有误';
		}
		echo enjson($arr);
	}
	//加入邀请
	//参数teamid teamname 分享过来要加入的战队id和战队名称
	public function joinvtation(){
		$uinfo = $this->getuserinfo();
		$teamid =  IntVal(I('post.teamid'));
		$teamname =  I('post.teamname');
		$wuid = AESmcryptdecrypt($uinfo['wuid'])?AESmcryptdecrypt($uinfo['wuid']):AESmcryptdecrypt(urldecode($uinfo['wuid']));
		$memdb = M('t201811nianhui_mem','acti_','DB_ACTI');
		$teamdb = M('t201811nianhui_team','acti_','DB_ACTI');
		if($teamid and $teamname and $wuid){
			$memdb->startTrans();
			$memin['wuid'] = array('neq',$wuid);
			$memsave['teamid'] = $teamid;
			$memsave['teamname'] = $teamname;
			$memsave['wuid'] = $wuid;
			$memsave['wname'] = $uinfo['userinfo']['wname'];
			$memsave['headimg'] = $uinfo['userinfo']['wimg'];
			$okmem = $memdb->where($memin)->add($memsave);
			if($okmem){
				$okteam = $teamdb->setInc('status');
			}
			if($okteam and $okmem){
				$memdb->commit();
				$arr['code'] = 0;
				$arr['msg'] = '成功加入';
			}else{
				$memdb->rollback();
				$arr['code'] = 1;
				$arr['msg'] = '加入失败，你是否已经创建或加入过了战队？';
			}
		}else{
			$arr['code'] = 2;
			$arr['msg'] = '提交的信息有误';
		}
		echo enjson($arr);
	}

	//投票提升助力值
	//参数teamid wuid分享者的战队id和微信id
	public function toupiao(){
		$uinfo = $this->getuserinfo();
		$teamid =  IntVal(I('post.teamid'));
		$wuid =  I('post.wuid');
		$uid = AESmcryptdecrypt($uinfo['wuid'])?AESmcryptdecrypt($uinfo['wuid']):AESmcryptdecrypt(urldecode($uinfo['wuid']));
		$memdb = M('t201811nianhui_mem','acti_','DB_ACTI');
		$teamdb = M('t201811nianhui_team','acti_','DB_ACTI');
		$mxdb = M('t201811nianhui_mx','acti_','DB_ACTI');
		if($teamid and $uid and $wuid){
			$mxdb->startTrans();
			$mxin['udatetime'] = array(array('egt',date('Y-m-d').' 00:00:00'),array('lt',date('Y-m-d').' 24:00:00'));
			$mxin['teamid'] = $teamid;
			$mxin['uid'] = array('neq',$uid);
			$mxsave['uid'] = $uid;
			$mxsave['teamid'] = $teamid;
			$mxsave['wuid'] = $wuid;
			$mxsave['udatetime'] = date('Y-m-d H:i:s');
			$okmx =  $mxdb->where($mxin)->add($mxsave);
			if($okmx){
				$memin['wuid'] = $wuid;
				$okmem = $memdb->where($memin)->setInc('wclick');
				if($okmem){
					$teamin['teamid'] = $teamid;
					$okteam = $teamdb->where($teamin)->setInc('allclick');
				}
			}else{
				$arr['msg'] = '你今天是否已经给该战队投过票了？';
			}
			if($okmx and $okmem and $okteam){
				$mxdb->commit();
				$arr['code'] = 0;
				$arr['msg'] = '投票成功';
			}else{
				$mxdb->rollback();
				$arr['code'] = 1;
				$arr['msg'] = '投票失败。'.$arr['msg'];
			}
		}else{
			$arr['code'] = 2;
			$arr['msg'] = '提交的信息有误';
		}
		echo enjson($arr);
	}


	//日冠军和日排名
	//type参数0日排 1总排
	// 返回gj：日冠军；pm：日排名  返回gj：总冠军；pm：总排名
	public function getdaylist(){

		$udatetimeG = I('post.udatetime')?I('post.udatetime'): date("Y-m-d",strtotime("-1 day"));
		$udatetimeP = I('post.udatetime')?I('post.udatetime'):date("Y-m-d");

		$querydb = M('','acti_','DB_ACTI');

		$timeGA = $udatetimeG.' 00:00:00';
		$timeGB = $udatetimeG.' 23:59:59';

		$timePA = $udatetimeP.' 00:00:00';
		$timePB = $udatetimeP.' 23:59:59';

		
		$type = I('post.type')?I('post.type'):0;
		
		//日冠军
		$sql_gj="select * from acti_t201811nianhui_team aaa right join (select teamid,max(udatetime),count(teamid) from acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timeGA."' and '".$timeGB."' GROUP BY teamid HAVING teamid in (select teamid from (SELECT count(teamid) c,teamid from acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timeGA."' and '".$timeGB."' GROUP BY teamid HAVING c in (select * from (SELECT count(teamid) FROM acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timeGA."' and '".$timeGB."' group by teamid ORDER BY count(teamid) desc,teamid limit 10) d)) dd) ORDER BY count(teamid) desc,max(udatetime) limit 0,1) ddd on aaa.teamid=ddd.teamid";

		//日排名
		$sql_pm="select * from acti_t201811nianhui_team aaa right join (select teamid,max(udatetime),count(teamid) from acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timePA."' and '".$timePB."' GROUP BY teamid HAVING teamid in (select teamid from (SELECT count(teamid) c,teamid from acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timePA."' and '".$timePB."' GROUP BY teamid HAVING c in (select * from (SELECT count(teamid) FROM acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timePA."' and '".$timePB."' group by teamid ORDER BY count(teamid) desc,teamid limit 10) d)) dd) ORDER BY count(teamid) desc,max(udatetime) limit 0,10) ddd on aaa.teamid=ddd.teamid";

		//总排名前三
		$sql_zgj="select * from acti_t201811nianhui_team aaa right join (select teamid,max(udatetime),count(teamid) from acti_t201811nianhui_mx GROUP BY teamid HAVING teamid in (select teamid from (SELECT count(teamid) c,teamid from acti_t201811nianhui_mx where udatetime GROUP BY teamid HAVING c in (select * from (SELECT count(teamid) FROM acti_t201811nianhui_mx where udatetime group by teamid ORDER BY count(teamid) desc,teamid limit 10) d)) dd) ORDER BY count(teamid) desc,max(udatetime) limit 0,3) ddd on aaa.teamid=ddd.teamid";
		//总排名后七
		$sql_zpm="select * from acti_t201811nianhui_team aaa right join (select teamid,max(udatetime),count(teamid) from acti_t201811nianhui_mx GROUP BY teamid HAVING teamid in (select teamid from (SELECT count(teamid) c,teamid from acti_t201811nianhui_mx where udatetime GROUP BY teamid HAVING c in (select * from (SELECT count(teamid) FROM acti_t201811nianhui_mx where udatetime group by teamid ORDER BY count(teamid) desc,teamid limit 10) d)) dd) ORDER BY count(teamid) desc,max(udatetime) limit 3,7) ddd on aaa.teamid=ddd.teamid";

		if($type){
			$arr['gj'] = $querydb->query($sql_gj);
			$arr['pm'] = $querydb->query($sql_pm);
		}else{
			$arr['gj'] = $querydb->query($sql_zgj);
			$arr['pm'] = $querydb->query($sql_zpm);
		}
		echo enjson($arr);
	}


       // --------------------------- 获取微信用户信息 START
      // ---------------------------------------------------------  获取用户信息
       /**
       * 获取用户信息
       * @return userinfo
       */
        public function getuserinfo(){
            $userinfo = $this->getusercode();       
            return $userinfo;
        }
           
        private function noCode(){
            $jumpurl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);
            //微信返回url
            $jumpstrurl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx80cd81ef4bfbe8e8&redirect_uri='.$jumpurl.'&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect';
            redirect($jumpstrurl);
        }

        private function getCode(){
            $openurl = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
            $code = $_GET['code'];
            $dourl = $openurl.'appid=wx80cd81ef4bfbe8e8&secret=966aada64c9d2368e2607f79db5ca142&code='.$code.'&grant_type=authorization_code';
            $result = $this->httppost($dourl);
            $result = json_decode($result,true);

            return $result;
        }

        private function setusercooike($data,$type){

            if($type==0){
                setcookie('protrail_userwuid9',$data,time() + 24*60*60);
            }
            if($type==1){
                setcookie('protrail_userwuid9','',time() - 24*60*60);
            }     
        }
       
        public function getusercode(){
            $userdb = M('wenjuanuser','weixin_','DB_WEIX');
            // $this->setusercooike('',1);  //清cookie 测试用
            $protrail_userwuid = $_COOKIE['protrail_userwuid9'];
            $wuidin['wuid'] = AESmcryptdecrypt(urldecode($protrail_userwuid));
           
            $userfind = $userdb->where($wuidin)->find();
            //如果存在cookie并且用户数据存在
            if(isset($protrail_userwuid) && $userfind && $userfind!=null){
                $userinfos['wname'] = $userfind['wname'];
                $userinfos['name'] = $userfind['name'];
                $userinfos['phone'] = $userfind['phone'];
                $userinfos['address'] = $userfind['address'];
                $userinfos['wimg'] = $userfind['wimg'];

                return array(
                        "get"=>true,
                        "wuid"=>$protrail_userwuid,
                        "id"=>urlencode(AESmcryptencrypt($userfind['id'])),
                        "msg"=>"读取成功",
                        "userinfo"=>$userinfos
                    );
            }

            if (!isset($_GET['code']) || $_GET['code'] == '0714lQOj2FqmeF0971Oj24oJOj24lQOf'){
                $this->noCode();
            }else{
                $result = $this->getCode();
                if(!isset($result)){
                    $msg = "登记失败,请关闭页面重试";  
                    return array(
                            "get"=>false,
                            "msg"=>$msg
                        );
                }

                $wuid = $result['openid'];
                $atoken = $result['access_token'];
                $rtoken = $result['refresh_token'];
                $userinfo = $this->httppost("https://api.weixin.qq.com/sns/userinfo?access_token=".$atoken."&refresh_token=".$rtoken."&openid=".$wuid."&lang=zh_CN");
                $userinfo = json_decode($userinfo,true);
                $userin['wuid'] = $userinfo['openid'];

                if(!$userin['wuid'] || $userin['wuid'] == null){
                    $msg = "授权失败,请退出页面重试";
                    return array(
                            "get"=>false,
                            "msg"=>$msg
                        );

                }

                $userdo = $userdb->where($userin)->find();

                //如果数据库存在此信息
                if($userdo){
                    $userdo['wuid'] = urlencode(AESmcryptencrypt($userdo['wuid']));
                   
                    $this->setusercooike($userdo['wuid'],0);

                    $userinfos['wname'] = $userdo['wname'];
                    $userinfos['wimg'] = $userdo['wimg'];
                    $userinfos['name'] = $userdo['name'];
                   $userinfos['phone'] = $userdo['phone'];
                   $userinfos['address'] = $userdo['address'];
                   
                    return array(
                            // "条件"=>$userin['wuid'],
                            // "查找出来的wuid"=>AESmcryptdecrypt(urldecode($userdo['wuid'])),
                            // "云端返回的用户信息"=>$userinfo,
                            // "查找到的用户信息"=>$userdo,

                            "get"=>true,
                            "wuid"=>$userdo['wuid'],
                            "id"=>urlencode(AESmcryptencrypt($userdo['id'])),
                            "msg"=>"数据库已有此人",
                            "userinfo"=>$userinfos,

                        );
                }else{
                    //否则
                    $useradd['wuid'] = $userinfo['openid'];
                    $useradd['wname'] = $userinfo['nickname'];
                    $useradd['wimg'] = $userinfo['headimgurl'];
                    $useradd['createtime'] = date('Y-m-d H:i:s');
                   
                    $useraddo = $userdb->add($useradd,array(),true);
                    if($useraddo){
                        $useradd['wuid'] = urlencode(AESmcryptencrypt($useradd['wuid']));
                       
                        $this->setusercooike($useradd['wuid'],0);
                        $userinfos['wname'] = $useradd['wname'];
                        $userinfos['wimg'] = $useradd['wimg'];
                        $userinfos['name'] = $useradd['name'];
                      $userinfos['phone'] = $useradd['phone'];
                      $userinfos['address'] = $useradd['address'];
                        return array(
                                "get"=>true,
                                "wuid"=>$useradd['wuid'],
                                "id"=>urlencode(AESmcryptencrypt($useraddo)),
                                "msg"=>"新增成功",
                                // "datas"=>AESmcryptdecrypt(urldecode($protrail_userwuid)),
                                // "datas"=>$protrail_userwuid,
                                "userinfo"=>$userinfos
                            );
                    }else{
                        $this->setusercooike('',1);
                        $msg = "<center>微信验证未能通过，请重试</center>";

                        return array(
                            "get"=>false,
                            "msg"=>$msg
                        );
                    }
                }
            }   
        }

       private function httppost($url,$param){
          $oCurl = curl_init();
          if(stripos($url,"https://")!==FALSE){
             curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
             curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
          }
          if (is_string($param)) {
             $strPOST = $param;
          } else {
             $aPOST = array();
             foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
             }
             $strPOST =  join("&", $aPOST);
          }
          curl_setopt($oCurl, CURLOPT_URL, $url);
          curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
          curl_setopt($oCurl, CURLOPT_POST,true);
          curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
          curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
          $sContent = curl_exec($oCurl);
          $aStatus = curl_getinfo($oCurl);
         
          curl_close($oCurl);
          if(intval($aStatus["http_code"])==200){
             return $sContent;
          }else{
             return false;
          }
       }
       // ------------------获取微信用户信息 END



	   //日冠军自动或手动结算
	   //每天执行一次即可，不能漏结
	   public function jiesuan(){
		$teamdb = M('t201811nianhui_team','acti_','DB_ACTI');
		$querydb = M('','acti_','DB_ACTI');
		
		$udatetimeG = date("Y-m-d",strtotime("-1 day"));
		
		$maxprize = $teamdb->field('max(prize) as cc')->select();
		if(!$maxprize[0]['cc']){
			$udatetimeG = date("Y-m-d",strtotime("2018-11-07"));
		}else{
			if($maxprize[0]['cc']>=$udatetimeG){
				//不执行
				exit;
			}else{
				$udatetimeG = date('Y-m-d',strtotime($maxprize[0]['cc']."+1 day"));
			}
		}
		$timeGA = $udatetimeG.' 00:00:00';
		$timeGB = $udatetimeG.' 23:59:59';
		
		//日冠军
		$sql_gj="update acti_t201811nianhui_team set prize='".$udatetimeG."' where teamid=(select teamid from (select teamid,max(udatetime),count(teamid) from acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timeGA."' and '".$timeGB."' GROUP BY teamid HAVING teamid in (select teamid from (SELECT count(teamid) c,teamid from acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timeGA."' and '".$timeGB."' GROUP BY teamid HAVING c in (select * from (SELECT count(teamid) FROM acti_t201811nianhui_mx where teamid not in (select teamid from acti_t201811nianhui_team where prize<>'') and udatetime between '".$timeGA."' and '".$timeGB."' group by teamid ORDER BY count(teamid) desc,teamid limit 10) d)) dd) ORDER BY count(teamid) desc,max(udatetime) limit 0,1) ddd)";
		$mm = $querydb->query($sql_gj);
		echo enjson($mm);
	   }

}

