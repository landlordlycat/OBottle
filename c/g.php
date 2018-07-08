<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set("Asia/Shanghai");
session_start();
header('Content-type:text/json;charset=utf-8');
require_once './f.php';
require_once './md.php';
$type = $_GET['type'];
$page = $_POST['p'];
$chsload = $_POST['load'];
$mode = $_POST['mode'];
if (empty($chsload)) {
    $chsload = 0;
}
$result['result'] = 'ok';
function turndate($v) {
    return substr($v, 0, 4) . "-" . substr($v, 4, 2) . "-" . substr($v, 6, 2);
}
if ($type == 'getpage') {
    if ($mode == 'normal') { /*普通加载t目录内的页面*/
        if (file_exists('./../t/' . $page . '.php')||stripos($page,'tag')!==false) {
			$request=explode('/',$page);
            $c = file_get_contents('./../t/' . $request[0] . '.php');
            $c = preg_replace("/\t|\[name\]/", name(), $c); /*替换小站名*/
            $c = preg_replace("/\t|\[intro\]/", intro(), $c); /*替换小站描述*/
            $c = preg_replace("/\t|\[year\]/", date('Y'), $c); /*替换小站年份*/
            $c = preg_replace("/\t|\[avatar\]/", avatar(), $c); /*替换小站头像*/
            $c = preg_replace("/\t|\[host\]/", host() . '#m', $c); /*替换小站链接*/
            preg_match('/\[css:(.*?)\]/i', $c, $match);
            $c = preg_replace('/\[css:(.*?)\]/i', './t/' . $match[1], $c); /*替换CSS路径*/
            preg_match('/\[js:(.*?)\]/i', $c, $match2);
            $c = preg_replace('/\[js:(.*?)\]/i', './t/' . $match2[1], $c); /*替换JS路径*/
            if ($page == 'm') { /*生成文章列表*/
                $poststr = '';
                if (file_exists('./../p/index.php')) {
                    require './../p/index.php';
                    $recentin = $in;
                    $lastid = 0;
                    $clip = array_chunk($in, frontnum(), true); /*分段文章*/
                    $clipnum = count($clip);
                    $in = $clip[$chsload];
                    /*先获取置顶文章列表*/
                    $tops = explode(',', $tp);
                    foreach ($tops as $val) {
                        if ($val !== '') {
                            $k = file_get_contents('./../t/posts.html');
                            require './../p/' . $val . '.php';
                            $k = preg_replace("/\t|\[index\]/", '>', $k);
                            $k = preg_replace("/\t|\[title\]/", $ptitle, $k);
                            $k = preg_replace("/\t|\[date\]/", '[置顶]', $k);
                            $k = preg_replace("/\t|\[link\]/", '#!' . $val, $k);
                            $poststr = $poststr . $k;
                        }
                    }
                    $recentid = 0; /*计算文章排列ID*/
                    foreach ($clip as $key => $val) {
                        if ($key < $chsload) {
                            $recentid+= count($val);
                            foreach ($val as $k => $i) {
                                if (in_array($k, $tops)) {
                                    $recentid-= 1;
                                }
                            }
                        }
                    }
                    $recentid+= 1;
                    if (!empty($in)) {
                        $ids = 1;
                        if (!empty($recentid)) {
                            $ids = $recentid;
                        }
                        foreach ($in as $key => $val) {
                            if (!in_array($key, $tops,true)) { /*排除置顶文章*/
                                $tp = file_get_contents('./../t/posts.html');
                                require './../p/' . $key . '.php';
                                $tp = preg_replace("/\t|\[index\]/", $ids . '.', $tp);
                                $tp = preg_replace("/\t|\[title\]/", $ptitle, $tp);
                                if ($ptype == 'post') {
                                    $tp = preg_replace("/\t|\[date\]/", turndate($val), $tp);
                                } else if ($ptype == 'page') {
                                    $tp = preg_replace("/\t|\[date\]/", '[页面]', $tp);
                                }
                                $tp = preg_replace("/\t|\[link\]/", '#!' . $key, $tp);
                                $poststr = $poststr . $tp;
                                $ids+= 1;
                            }
                        }
						if(empty($poststr)){
							$poststr='<center><h3 style=\'color:#AAA;\'>这里没有东西哦~</h3></center>'	;
						}
                        $c = preg_replace("/\t|\[posts\]/", $poststr, $c); /*替换文章html*/
                    } else {
                        $result['result'] = 'notok';
                        $result['msg'] = '这里没有任何文章呢O_o.';
                    }
                    $result['allp'] = $clipnum;
                } else {
                    $result['result'] = 'notok';
                    $result['msg'] = '你还没有任何文章呢.';
                }
            } else if (stripos($page,'tag')!==false) { /*生成标签列表*/
			if (file_exists('./../p/index.php')) {
                    require './../p/index.php';
					$hand=explode('/',$page);
					if(empty($hand[1])){/*如果没有标签索引*/
					$tags=array();
					foreach($tagi as $key=>$val){
						$ps=explode(',',$val);
						foreach($ps as $v){
							if(!empty($v)){
								if(!isset($tags[$v])||empty($tags[$v])){
									$tags[$v]=1;
								}else{
									$tags[$v]+=1;
								}
							}
						}
					}
					$str='';
					$ids=1;
					foreach($tags as $k=>$t){
						$ia= file_get_contents('./../t/tags.html');
						$ia= preg_replace("/\t|\[index\]/",$ids.'.', $ia);
						$ia= preg_replace("/\t|\[tag\]/",$k, $ia);
						$ia= preg_replace("/\t|\[num\]/",$t.'篇文章', $ia);
						$ia= preg_replace("/\t|\[link\]/",'#tag/'.$k, $ia);
						$str=$str.$ia;
						$ids+=1;
					}
					$c = preg_replace("/\t|\[tags\]/", $str, $c); /*替换标签页面html*/
					}else{/*如果有标签索引*/
						$rt=$hand[1];
						$ids=1;
						$poststr='';
						foreach($tagi as $key=>$val){
							$ps=explode(',',$val);
							$found=false;
							foreach($ps as $v){
								if($v==$rt&&!$found){
									$found=true;
									$tp = file_get_contents('./../t/posts.html');
                                require './../p/' . $key . '.php';
                                $tp = preg_replace("/\t|\[index\]/", $ids . '.', $tp);
                                $tp = preg_replace("/\t|\[title\]/", $ptitle, $tp);
                                if ($ptype == 'post') {
                                    $tp = preg_replace("/\t|\[date\]/", turndate($pdat), $tp);
                                } else if ($ptype == 'page') {
                                    $tp = preg_replace("/\t|\[date\]/", '[页面]', $tp);
                                }
                                $tp = preg_replace("/\t|\[link\]/", '#!' . $key, $tp);
                                $poststr = $poststr . $tp;
                                $ids+= 1;
								}
							}
						}
						if(empty($poststr)){
						$poststr='<center><h4 style=\'color:#AAA;\'>箱子里翻不出来这个标签诶</h4></center>'	;
						}
						$c = preg_replace("/\t|\[tags\]/", $poststr, $c); /*替换标签页面html*/
						$c = preg_replace("/\t|\标签页/", '标签：'.$rt, $c); /*替换标签头html*/
					}
			}else {
                $result['result'] = 'notok';
                $result['msg'] = '标签被吃了OAO';
            }
            }
            if (!empty($c)) {
                $result['r'] = $c;
            } else {
                $result['result'] = 'notok';
                $result['msg'] = '404 QAQ';
            }
        } else {
            $result['result'] = 'notok';
            $result['msg'] = '404 QAQ';
        }
    } else if ($mode == 'popage') { /*加载文章之类的页面*/
        if (file_exists('./../p/index.php')) {
            require './../p/index.php';
            $found = false;
            $c = '';
			$ids=1;		
			if(is_numeric($page)){/*防止PHP:0判断true的BUG*/
				$page=intval($page);
			}
            foreach ($in as $key => $val) { /*先寻找是否有适配的PID*/
                if ($key===$page) {
                    $found = true;
                    require './../p/' . $page . '.php';
                    $c = file_get_contents('./../t/p.php');
                    $c = preg_replace("/\t|\[title\]/", $ptitle . '.', $c);
                    $c = preg_replace("/\t|\[date\]/", $pdat, $c);
					$c = preg_replace("/\t|\[commentid\]/", $page, $c);
					if($_SESSION['log']=='yes'){
					$edith='<div><a href="/a/edit.php?e='.$page.'" class="button button-rounded button-tiny"  target="_blank">编辑</a>&nbsp;<a href="/a/edit.php?e='.$page.'&t=del" target="_self" class="button button-rounded button-tiny">删除</a></div>';
					$c = preg_replace("/\t|\[editbar\]/", $edith, $c);
					}else{
						$c = preg_replace("/\t|\[editbar\]/", '', $c);
					}
                    $html = Markdown(htmlspecialchars_decode($pcontent));
                    $c = preg_replace("/\t|\[content\]/", $html, $c);
					$kb=explode('[!page]',$c);
					if($ptype=='page'){
						$c=$kb[0];
					}else{
						$c = preg_replace("/\t|\[!page\]/",'', $c);
					}
                }
            }
            if (!$found) { /*如果没有适配的pid,寻找页面链接*/
                foreach ($in as $key => $val) {
                    require './../p/' . $key . '.php';
                    if ($pdat == $page) {
                        $found = true;
                        $c = file_get_contents('./../t/p.php');
                        $c = preg_replace("/\t|\[title\]/", $ptitle . '.', $c);
                        $c = preg_replace("/\t|\[date\]/", $pdat, $c);
						$c = preg_replace("/\t|\[commentid\]/", $key, $c);
						if($_SESSION['log']=='yes'){
					$edith='<div><a href="/a/edit.php?e='.$key.'" class="button button-rounded button-tiny"  target="_blank">编辑</a>&nbsp;<a href="/a/edit.php?e='.$key.'&t=del" target="_self" class="button button-rounded button-tiny">删除</a></div>';
					$c = preg_replace("/\t|\[editbar\]/", $edith, $c);
					}else{
						$c = preg_replace("/\t|\[editbar\]/", '', $c);
					}
                        $html = Markdown(htmlspecialchars_decode($pcontent));
                        $c = preg_replace("/\t|\[content\]/", $html, $c);
						$kb=explode('[!page]',$c);
					if($ptype=='page'){
						$c=$kb[0];
					}else{
						$c = preg_replace("/\t|\[!page\]/",'', $c);
					}
                    }
                }
            }
			if(!empty($c)){
            $result['r'] = $c;
			}else{
				 $result['result'] = 'notok';
            $result['msg'] = '这篇文章被吃了哦>A<~';
			}
        } else {
            $result['result'] = 'notok';
            $result['msg'] = '这篇文章被吃了哦>A<~';
        }
    } else if ($mode == 'search') {/*搜索页面*/
	if (file_exists('./../p/index.php')) {
                    require './../p/index.php';
        $hand=explode('?',$page);
		$s=$hand[1];
		$poststr='';
		$ids=1;
		if(!empty($s)){
			$found=false;
			foreach($in as $key=>$val){/*搜索日期*/
				if($s==$val){
					$found=true;
					$tp = file_get_contents('./../t/posts.html');
                                require './../p/' . $key . '.php';
                                $tp = preg_replace("/\t|\[index\]/", $ids . '.', $tp);
                                $tp = preg_replace("/\t|\[title\]/", $ptitle, $tp);
                                if ($ptype == 'post') {
                                    $tp = preg_replace("/\t|\[date\]/", turndate($pdat), $tp);
                                } else if ($ptype == 'page') {
                                    $tp = preg_replace("/\t|\[date\]/", '[页面]', $tp);
                                }
                                $tp = preg_replace("/\t|\[link\]/", '#!' . $key, $tp);
                                $poststr = $poststr . $tp;
                                $ids+= 1;
				}
			}
			if(!$found){
				foreach($in as $key=>$val){
					require './../p/' . $key . '.php';
					if(stripos($ptitle,$s)!==false||stripos($pcontent,$s)!==false){
						$found=true;
						$tp = file_get_contents('./../t/posts.html');
                                require './../p/' . $key . '.php';
                                $tp = preg_replace("/\t|\[index\]/", $ids . '.', $tp);
                                $tp = preg_replace("/\t|\[title\]/", $ptitle, $tp);
                                if ($ptype == 'post') {
                                    $tp = preg_replace("/\t|\[date\]/", turndate($pdat), $tp);
                                } else if ($ptype == 'page') {
                                    $tp = preg_replace("/\t|\[date\]/", '[页面]', $tp);
                                }
                                $tp = preg_replace("/\t|\[link\]/", '#!' . $key, $tp);
                                $poststr = $poststr . $tp;
                                $ids+= 1;
					}
				}
			}
			$c=file_get_contents('./../t/search.php');
			$c = preg_replace("/\t|\搜索/", '搜索:'.$s, $c);
			if(empty($poststr)){
				$poststr='<center><h3 style=\'color:#AAA;\'>箱子里空空如也..</h3></center>';
			}
			$c = preg_replace("/\t|\[searchs\]/", $poststr, $c);
			if(!empty($c)){
            $result['r'] = $c;
			}else{
				 $result['result'] = 'notok';
            $result['msg'] = '服务器故障了OAO';
			}
		}else {
        $result['result'] = 'notok';
        $result['msg'] = '你要搜索什么啊(#`O′)~';
    }
	}else {
        $result['result'] = 'notok';
        $result['msg'] = '请求错误.O_o';
    }
	}	else {
        $result['result'] = 'notok';
        $result['msg'] = '请求错误.O_o';
    }
} else if ($type == 'getmore') { /*加载首页文章页面*/
    $c = file_get_contents('./../t/m.php');
    if (file_exists('./../p/index.php')) {
        require './../p/index.php';
        $clip = array_chunk($in, frontnum(), true); /*分段文章*/
        $clipnum = count($clip);
        $in = $clip[$chsload];
        /*先获取置顶文章列表*/
        $tops = explode(',', $tp);
        $recentid = 0; /*计算文章排列ID*/
        foreach ($clip as $key => $val) {
            if ($key < $chsload) {
                $recentid+= count($val);
                foreach ($val as $k => $i) {
                    //echo $k.' ';
                    if (in_array($k, $tops)) {
                        $recentid-= 1;
                    }
                }
            }
        }
        $recentid+= 1;
        if (!empty($in)) {
            $ids = 1;
            if (!empty($recentid)) {
                $ids = $recentid;
            }
            foreach ($in as $key => $val) {
                if (!in_array($key, $tops,true)) { /*排除置顶文章*/
                    $tp = file_get_contents('./../t/posts.html');
                    require './../p/' . $key . '.php';
                    $tp = preg_replace("/\t|\[index\]/", $ids . '.', $tp);
                    $tp = preg_replace("/\t|\[title\]/", $ptitle, $tp);
                    if ($ptype == 'post') {
                        $tp = preg_replace("/\t|\[date\]/", turndate($val), $tp);
                    } else if ($ptype == 'page') {
                        $tp = preg_replace("/\t|\[date\]/", '[页面]', $tp);
                    }
                    $tp = preg_replace("/\t|\[link\]/", '#!' . $key, $tp);
                    $poststr = $poststr . $tp;
                    $ids+= 1;
                }
            }
            $c = preg_replace("/\t|\[posts\]/", $poststr, $c); /*替换文章html*/
        } else {
            $result['result'] = 'notok';
            $result['msg'] = '没有更多了呢~.';
        }
        $result['allp'] = $clipnum;
    } else {
        $result['result'] = 'notok';
        $result['msg'] = '你还没有任何文章呢.';
    }
    if (!empty($c)) {
        $result['r'] = $c;
    } else {
        $result['result'] = 'notok';
        $result['msg'] = '没有更多了呢~.';
    }
} else {
    $result['result'] = 'notok';
    $result['msg'] = '';
}
session_write_close();
echo json_encode($result, true);
?>