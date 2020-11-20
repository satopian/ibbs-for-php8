<?php
if (phpversion()<"4.1.0") {
  $_GET = $HTTP_GET_VARS;
  $_POST = $HTTP_POST_VARS;
  $_SERVER = $HTTP_SERVER_VARS;
  $_COOKIE = $HTTP_COOKIE_VARS;
}
/***********************************
  * PHP-I-BOARD
  *               by ToR http://php.s3.to/
  *
  * original by http://www.cj-c.com/
  ***********************************/
// 2003/02/14 v1.0
// 2003/02/22 v1.1 ヘルプ、改行制限
// 2003/02/28 v1.2 携帯対応
// 2003/03/04 v1.2b 管理モード修正
// 2003/03/08 v1.2d DoCoMo用スキン、レス数追加
// 2003/03/11 v1.2e urlをurlencode,skin_other更新（管理パス
// 2003/03/14 v1.3 管理者アイコンのバグ修正、クッキーアイコン
// 2003/03/17 v1.3b 管理者アイコンクッキー
// 2003/03/20 v1.3c 色指定無し時
// 2003/03/30 v1.4 禁止ホスト、禁止ワード、特殊文字追加
// 2003/04/03 v1.45 画像カウンタバグ修正
// 2003/04/06 v1.5 ヘッドライン数、レス時親記事ホスト
// 2003/04/08 v1.56 レス時親アイコン{$oyaicon}。URLクッキー{$curl}
// 2003/07/26 v1.6 過去ログオフ時でファイル無い時バグ。570:if (PAST && is_array($kako))
// 2004/01/08 v1.65 EzWEBスキン判定ミス
// 2009/06/22 v1.7 XSS、ﾃﾞｨﾚｸﾄﾘﾄﾗﾊﾞｰｻﾙ脆弱性を修正
// 2010/03/25 v1.8 メール通知機能追加
/*
  ■使用方法　
　　・ibbs.dat,icount.dat,ilog.logの属性を666か646にする。
  　・過去ログ使用の場合は生成ﾃﾞｨﾚｸﾄﾘ（./ならpublic_html等)の属性777か757にする
*/
require_once("htmltemplate.inc"); 

// スクリプト名
define(PHP_SELF, "ibbs.php");

// ログファイル名(権限を606,646,666等にする)
define(LOGFILE, "ibbs.dat");

// 管理パス
define(ADMINPASS, "1234");

// 投稿通知メールを送るyes=1 no=0
define(NOTICE, 0);
// 通知メール送信先
$admin_mail = "all@s.to";

// レスがついたら記事を上げる？yes=1 no=0
define(AGE, 1);

// URLを自動リンクする？
define(AUTOLINK, 1);
// 投稿後の飛び先
$jump = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// 投稿制限文字数。上から名前、タイトル、コメント。半角で
define(MAXNAME, 32);
define(MAXSUBJ, 32);
define(MAXCOM, 1000);
// 最小文字数
define(MINCOM, 4);
// 改行数制限
define(MAXBR, 20);

// 親記事最大ログ保持件数
define(MAXLOG, 40);

// ヘッドライン表示件数(↑の数以下で）
define(MAXHEADLINE, 30);

// 色指定がない時の色
define(NOCOL, "#666666");

// アイコンの設定
// アイコン用ディレクトリ
define(I_DIR, "./Icon/");
// HTML表示用アイコン一覧 'ファイル名'=>'アイコン名'をペアで
$html_icon = array('randam'=>'ランダム','cat1.gif'=>'しろねこ','dog1.gif'=>'いぬ',
                   'rob1.gif'=>'くるくるロボ','pen1.gif'=>'ぺんぎん','td1.gif'=>'くま',
                   'rabi1.gif'=>'うさぎ','ball1.gif'=>'ぼーるやろう','tel1.gif'=>'てるてるお嬢','master'=>'管理者用');
// ランダムの画像候補
$rand_icon = array('cat1.gif','dog1.gif','rob1.gif','pen1.gif','td1.gif','rabi1.gif','ball1.gif','tel1.gif');

// 管理者用アイコン
$mas_i= array('master.gif','master2.gif','master3.gif');
// 管理者アイコンパスワード 削除キーに入れる 使い分けることによって複数の管理者アイコンが使用可能
$mas_p= array('7777','8888','9999');
$Ico_h= 5; // アイコン一覧で改行をする数

// 文字色
$font = array('#585858','#C043E0','#3947C6','#F25353','#EF8816','#67AC22','#34A086','#7191FF','#FF819B');
// 枠線色
$hr   = array('#FAAFAB','#FBB85E','#C785E0','#9FC1FB','#EDE94E','#70D179','#969696','#C8CCFF','#E0D0B0');

// 閲覧禁止ホスト
$no_host[] = 'kantei.go.jp';
$no_host[] = 'anonymizer.com';

// 使用禁止ワード
$no_word[] = '死ね';
$no_word[] = '<img';
$no_word[] = '<script';
$no_word[] = 'http:';

// 過去ログ機能を使う？Yes=1,No=0（使用する場合は保存ﾃﾞｨﾚｸﾄﾘを757,777等にする）
define(PAST, 0);
define(PASTLOG, "ilog.log"); // 過去ログカウントファイル
define(PASTDIR, "./");       // 過去ログ生成ディレクトリ(/で終わる事)
define(PASTSIZE, "100");     // 過去ログ記録数 KB
define(PASTDEF, 20);         // 過去ログモードでの表示件数

// カウンタを使う？
define(COUNTER, 1);
define(COUNTIMG, "");    //カウンタ画像のディレクトリ（テキストの場合は空。/で終わる）
define(COUNTLOG, "icount.dat"); //カウンタファイル(権限を606,646,666等にする)

// 機種判別
//$ua = explode("/", getenv('HTTP_USER_AGENT'));
//ibbs.php?ua=DoCoMoとか
if ($_GET['ua']) $ua[0] = $_GET['ua'];
if(preg_match("/^KDDI/",$ua[0])){
  //WAP2.0の場合
  define(MAINFILE, "i_skin_main.html");
  define(OTHERFILE, "i_skin_other.html");
  define(PAGEDEF, 5);
  define(RESDEF, 3);
  define(RESEVERY, 5);
  define(MOBILE, 1);
}
switch( $ua[0] ){
case "PDXGW" :
  //H"
case "UP.Browser" :
  //HDMLの場合
case "J-PHONE" :
  //J-PHONEの場合
case "DoCoMo" :
  // デザインファイル携帯
  define(MAINFILE, "i_skin_main.html");
  define(OTHERFILE, "i_skin_other.html");
  define(PAGEDEF, 5);//親記事表示数
  define(RESDEF, 3);//レス表示数
  define(RESEVERY, 5);//レス？件ずつ
  define(MOBILE, 1);//携帯モードは日付表示省略
  break;
case 'line'://一行レス
  // デザインファイルPC
  define(MAINFILE, "skin_main_line.html");
  define(OTHERFILE, "skin_other.html");
  // 1ページに表示する親記事数
  define(PAGEDEF, 5);
  // 1親記事に表示するレス数
  define(RESDEF, 5);
  // 先頭？件、最新？件表示
  define(RESEVERY, 10);
  break;
default :
  // デザインファイルPC
  define(MAINFILE, "skin_main.html");
  define(OTHERFILE, "skin_other.html");
  // 1ページに表示する親記事数
  define(PAGEDEF, 5);
  // 1親記事に表示するレス数
  define(RESDEF, 5);
  // 先頭？件、最新？件表示
  define(RESEVERY, 10);
  // 携帯時は日付を省略
  define(MOBILE, 0);
  break;
}
//---設定ここまで

// 禁止ホスト
if (is_array($no_host)) {
  $host = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
  foreach ($no_host as $user) {
    if(eregi($user, $host)){
      header("Status: 204\n\n");//空白ページ
      exit;
    }
  }
}
/*-- カウンタ --*/
if (COUNTER) {
  // ｸｯｷｰをセット。リロード防止用
  setcookie("ibbs[count]", 1, time()+14*86400);
  // ｸｯｷｰがなければ初訪問。でカウントアップ
  if (!isset($_COOKIE['ibbs']['count'])) {
    $fp = fopen(COUNTLOG, "r+");
    $c = fgets($fp, 10);
    $c++;
    rewind($fp);
    set_file_buffer($fp, 0);
    flock($fp, LOCK_EX);
    fputs($fp, $c);
    fclose($fp);
  }
  $cc = file(COUNTLOG);
  $c = $cc[0];
  // 画像を使う場合
  if (COUNTIMG) {
    // altを得る
    $size = @getimagesize(COUNTIMG."0.gif");
    // 桁数分ループ
    for ($i = 0; $i < strlen($c); $i++) {
      $n = substr($c, $i, 1);
      $count.="<img src=\"".COUNTIMG.$n.".gif\" alt=".$n." ".$size[3].">";
    }
    $c = $count;
  }
}
/*-- 色HTML作成 --*/
function radio_list($name, $select="") {
  global $font,$hr;
  // ｸｯｷｰが無い場合は0番目にセット
  if (!isset($_COOKIE['ibbs'][$name])) $select = ${$name}[0];
  foreach ($$name as $l=>$col) {
    if ($_COOKIE['ibbs'][$name] == $col || $select == $col) $arg[$l]['chk'] = " checked";
    $arg[$l]['color'] = $col;
  }
  return $arg;
}
/*-- アイコンHTML作成 --*/
function option_list($select="") {
  global $html_icon,$mas_i;
  $l = 0;
  if (in_array($_COOKIE['ibbs']['ico'], $mas_i)) $select = "master";
  foreach ($html_icon as $file=>$name) {
    if ($_COOKIE['ibbs']['ico'] == $file || $select == $file) $arg[$l]['sel'] = " selected";
    $arg[$l]['file'] = $file;
    $arg[$l]['name'] = $name;
    $l++;
  }
  return $arg;
}
/*-- 全記事表示 --*/
function all_view($page,$mode="") {
  global $html_icon,$font,$hr,$c;

  if ($mode == "admin") {
    $pass = ($_GET['pass']) ? $_GET['pass'] : $_POST['pass'];
    if ($pass != ADMINPASS) error("パスワードが違います!");
  }
  // ログを配列に格納
  $lines = file(LOGFILE);
  // 最初はページ0
  if (!$page) $page = 0;
  $p = 1;
  $o = 0;
  // 最終更新日
  list(,,,,$up) = explode("<>", $lines[0]);
  $arg['update'] = gmdate("Y/m/d(D) H:i:s",time()+9*60*60);
  // ヘッドライン
  for ($h = 1; $h < count($lines); $h++) {
    list($num,,,,$subj,,,,,$type,)  = explode("<>", $lines[$h]);
    // レスの場合
    if ($type) {
      if (!is_array($res[$type])) $res[$type] = array();
      array_unshift($res[$type], $lines[$h]);
    }
    // 親記事の場合。親配列作成
    else {
      $oya[] = $lines[$h];
      $res_num = count($res[$num]);
      $o_num++;
      $ptop = PAGEDEF * $p;

      if ($ptop < $o_num) {
        $url = "?page=$ptop";
        $p++;
      }
      if ($mode != "admin") {
        $arg['headline'][] = array('url'=>"{$_SERVER['PHP_SELF']}$url#$num", 'subj'=>$subj, 'cnt'=>$res_num);
      }
    }
  }
  if (count($arg['headline']) > MAXHEADLINE) {
    array_splice($arg['headline'], 0, $page);
    array_splice($arg['headline'], MAXHEADLINE);
  }
  // 親記事展開
  for ($i = $page; $i < $page+PAGEDEF; $i++) {
    if (!trim($oya[$i])) continue;
    list($num,$date,$name,$email,$subj,$com,$url,$col,$icon,$type,,$host) = explode("<>", $oya[$i]);
    list($color,$b_color) = explode(";", $col);
    if ($color == "") $color = NOCOL;
    if ($b_color == "") $b_color = NOCOL;
    if ($url) $url = "http://".$url;
    if ($icon) $icon = I_DIR.$icon;
    if ($mode!="admin" && AUTOLINK) $com = autolink($com);
    if (MOBILE) $date = substr($date, 5, 5) . substr($date, 15, 6);
    // 管理モード時本文省略
    if ($mode == "admin") {
      $com = str_replace("<br>", " ", $com);
      $com = substr($com, 0, 60) . "..";
    }
    $cnt = $i+1;
    $res_cnt = count($res[$num]);
    // 親記事格納
    $arg['oya'][$o] = compact('cnt','res_cnt','num','date','name','email','subj','com','b_color','color','icon','url','host','page');
    // レス数オーバー？
    $rst = $res_cnt-RESDEF;
    if ($rst <= 0) {
      $rst = 0;
      $arg['oya'][$o]['over'] = false;
    }
    else {
      $arg['oya'][$o]['over'] = true;
    }
    // 管理モード時は全レス表示
    if ($mode == "admin") {
      $rst = 0;
      $arg['pass'] = $pass;
      $arg['size'] = filesize(LOGFILE);
    }
    // レス展開
    for ($j=$rst; $j<count($res[$num]); $j++) {
      list($rnum,$rdate,$rname,$remail,$rsubj,$rcom,$rurl,$rcol,$ricon,,,$host) = explode("<>", $res[$num][$j]);
      list($rcolor,$rb_color) = explode(";", $rcol);
      if ($rcolor == "") $rcolor = NOCOL;
      if ($rb_color == "") $rb_color = NOCOL;
      if ($rurl) $rurl = "http://".$rurl;
      if ($ricon) $ricon = I_DIR.$ricon;
      if ($mode!="admin" && AUTOLINK) $rcom = autolink($rcom);
      if ($mode == "admin") {
        $rcom = str_replace("<br>", " ", $rcom);
        $rcom = substr($rcom, 0, 60) . "..";
      }
      // レス記事格納
      $rres[$o][] = array('cnt'=>$j+1,'num'=>$rnum,'date'=>$rdate,'name'=>$rname,'email'=>$remail,'subj'=>$rsubj,
                          'com'=>$rcom,'b_color'=>$rb_color,'color'=>$rcolor,'icon'=>$ricon,'url'=>$rurl,'host'=>$host
                          );
    }
    // 親記事格納
    $arg['oya'][$o]['res'] = $rres[$o];
    $o++;
  }

  if ($mode == "admin") $qry = "&mode=admin&pass=".$arg['pass'];
  // ページ前/次
  $prev = $page - PAGEDEF;
  $next = $i;
  if ($prev >= 0)          $arg['prev'] = "{$_SERVER['PHP_SELF']}?page=$prev$qry";
  if ($next < count($oya)) $arg['next'] = "{$_SERVER['PHP_SELF']}?page=$next$qry";
  // ページ直接移動
  $tpage = (int)count($oya) / PAGEDEF;
  for ($a = 0; $a < $tpage; $a++) {
    if ($a == $page/PAGEDEF) $arg['paging'].= "[<b>$a</b>] ";
    else $arg['paging'].= "[<a href=\"{$_SERVER['PHP_SELF']}?page=$pp$qry\"><b>$a</b></a>] ";
    $pp += PAGEDEF;
  }

  $arg['count'] = $c;
  $arg['page_def'] = PAGEDEF;
  $arg['res_def'] = RESEVERY;
  $arg['total'] = count($lines) - 1;
  $arg['oyakiji'] = count($oya);
  $arg['reskiji'] = $arg['total'] - $arg['oyakiji'];
  $arg['maxcom'] = MAXCOM;
  if (PAST) $arg['kako'] = true;

  // クッキー
  $arg['cname'] = $_COOKIE['ibbs']['name'];
  $arg['cemail'] = $_COOKIE['ibbs']['email'];
  $arg['cpass'] = $_COOKIE['ibbs']['pass'];
  $arg['curl'] = $_COOKIE['ibbs']['url'];

  if ($mode == "admin") {
    $arg['admin'] = true;
    $arg['title'] = "管理モード";
    $arg['self'] = PHP_SELF;
    HtmlTemplate::t_include(OTHERFILE,$arg);
  }
  else {
    $arg['font'] = radio_list("font");
    $arg['hr']   = radio_list("hr");
    $arg['icon'] = option_list();
    $arg['self'] = PHP_SELF;
    HtmlTemplate::t_include(MAINFILE,$arg);
  }
}

/*-- 個別表示 --*/
function res_view($num) {
  global $html_icon;

  $res = array();

  $fd = fopen (LOGFILE, "r");
  fgets($fd, 4096);
  while (!feof ($fd)) {
    $buf = fgets($fd, 4096);
    $line = explode("<>", $buf);
    // 親記事
    if ($line[9]=="0") {
      // 該当記事なら終了
      if ($num == $line[0]) break;
      // 違うなら一から
      $res = array();
    }
    else{
      array_unshift($res, $buf);// レスを貯める
    }
  }
  fclose ($fd);

  // old-最初から？件、new-最新？件、all-全レス表示、通常-最新X件
  switch ($_GET['res']) {
    case 'old': $st = 0; $to = RESEVERY; break;
    case 'new': $st = count($res)-RESEVERY; $to = count($res); break;
    case 'all': $st = 0; $to = count($res); break;
    default:    $st = count($res)-RESDEF; $to = count($res); break;
  }
  if ($st < 0) $st = 0;

  // レス展開
  for ($i = $st; $i < $to; $i++) {
    if ($res[$i] == "") continue;
    list($rnum,$rdate,$rname,$remail,$rsubj,$rcom,$rurl,$rcol,$ricon,,,$rhost) = explode("<>", $res[$i]);
    list($rcolor,$rb_color) = explode(";", $rcol);
    if ($rcolor == "") $rcolor = NOCOL;
    if ($rb_color == "") $rb_color = NOCOL;
    if ($rurl) $rurl = "http://".$rurl;
    // 引用
    if (isset($_GET['q']) && $_GET['q'] == $rnum) {
      $q_com = "&gt;$rcom";
      $rrcom = str_replace("<br>","\r&gt;",$q_com);
    }
    else {
      if (AUTOLINK) $rcom = autolink($rcom);
    }
    // レス記事格納
    $rres[] = array('cnt'=>$i+1,'num'=>$rnum,'date'=>$rdate,'name'=>$rname,'email'=>$remail,'subj'=>$rsubj,
                    'com'=>$rcom,'b_color'=>$rb_color,'color'=>$rcolor,'icon'=>I_DIR.$ricon,'url'=>$rurl,'host'=>$rhost
                    );
  }
  // 親記事
  list($num,$date,$name,$email,$subj,$com,$url,$col,$icon,$type,,$host) = explode("<>", $buf);
  list($color,$b_color) = explode(";", $col);
  if ($color == "") $color = NOCOL;
  if ($b_color == "") $b_color = NOCOL;
  if ($url) $url = "http://".$url;
  // 引用
  if (isset($_GET['q']) && $_GET['q'] == $num) {
    $q_com = "&gt;$com";
    $rrcom = str_replace("<br>","\r&gt;",$q_com);
  }
  else {
    if (AUTOLINK) $com = autolink($com);
  }
  // 親記事格納
  $arg = array('res'=>$rres,'num'=>$num,'date'=>$date,'name'=>$name,'email'=>$email,
                'subj'=>$subj,'com'=>$com,'b_color'=>$b_color,'color'=>$color,'oyaicon'=>I_DIR.$icon,'url'=>$url,
                'page'=>$_GET['page'],'rsubj'=>"Re: $subj", 'rcom'=>$rrcom,'host'=>$host
                );
  $arg['res_def'] = RESEVERY;
  $arg['res_mode'] = true;
  $arg['font'] = radio_list("font");
  $arg['hr']   = radio_list("hr");
  $arg['icon'] = option_list();
  $arg['title'] = "記事No.$num 返信フォーム [通常/引用表示]";
  $arg['maxcom'] = MAXCOM;
  $arg['self'] = PHP_SELF;
  // クッキー
  $arg['cname'] = $_COOKIE['ibbs']['name'];
  $arg['cemail'] = $_COOKIE['ibbs']['email'];
  $arg['cpass'] = $_COOKIE['ibbs']['pass'];
  $arg['curl'] = $_COOKIE['ibbs']['url'];

  HtmlTemplate::t_include(OTHERFILE,$arg);
}
/*-- 書込み前処理 --*/
function check() {
  global $rand_icon,$mas_i,$mas_p,$no_word;
  //No<>Y/m/d(D) h:i:s<>name<>email<>subj<>com<>url<>#ffffff;#back<>icon.gif<>oyaNo<>crypt<>ip<><>

  if (trim($_POST['name'])=="")   error("名前が入力されてません");
  if (ereg("^( |　|\t|\r|\n)*$",$_POST['comment'])) error("コメントが入力されてません");
  if (strlen($_POST['pass']) > 8) error("削除キーは8文字以上でお願いします");
  if (strlen($_POST['name']) > MAXNAME) error("名前は長すぎますっ！");
  if (strlen($_POST['subject']) > MAXSUBJ)  error("タイトルが長すぎますっ！");
  if (strlen($_POST['comment']) > MAXCOM)  error("本文が長すぎますっ！");
  if (strlen($_POST['comment']) < MINCOM)  error("本文が短すぎますっ！");
  if ($_POST['email'] && !ereg("(.*)@(.*)\.(.*)", $_POST['email']))
    error("E-メールの入力内容が不正です!");

  // 禁止ワード
  if (is_array($no_word)) {
    foreach ($no_word as $fuck) {
      if (ereg($fuck, $_POST['comment'])) error("使用できない文字が含まれています！");
      if (ereg($fuck, $_POST['subject'])) error("使用できない文字が含まれています！");
      if (ereg($fuck, $_POST['name'])) error("使用できない文字が含まれています！");
    }
  }
  if (preg_match("/(<a\b[^>]*?>|\[url(?:\s?=|\]))|href=/i", $_POST['comment'])) error("エラー！！");
  // 副題
  if ($_POST['sex']) $_POST['subject'] = $_POST['sex']."/".$_POST['subject'];

  // ランダムアイコン
  if ($_POST['ico']=="randam") {
    mt_srand((double)microtime()*1000000);
    $randval = mt_rand(0, (count($rand_icon)-1));
    $ico = $rand_icon[$randval];
  }
  // 管理者アイコン
  elseif ($_POST['ico']=="master") {
    $find = false;
    foreach ($mas_p as $l=>$mpass) {
      if ($_POST['delkey'] == $mpass) {
        $ico = $mas_i[$l];
        $find = true;
      }
    }
    if (!$find) error("管理者用アイコンは使用できません!");
  }
  else{
    $ico = $_POST['ico'];
  }
  // 全$_POSTに適用
  $post = array_map("htmlspecialchars",$_POST);
  if (get_magic_quotes_gpc())
    $post = array_map("stripslashes", $post);
  // 無題
  if (trim($post['subject'])=="") $post['subject'] = "(無題)";
  // 改行処理
  $comment = str_replace("\r\n", "\r", $post['comment']);
  $comment = str_replace("\r", "\n", $comment);//改行文字統一
  $comment = preg_replace("/\n{2,}/", "\n\n", $comment);//2行以上の改行を2行に
  if (substr_count($comment, "\n") > MAXBR) error("改行が多すぎます!");
  $comment = eregi_replace("&amp;([0-9a-z#]+);", "&\\1;", $comment);
  $post['comment'] = str_replace("\n", "<br>", $comment);//\nをbrに

  // 時間、IP、削除キー、色
  $post['now'] = gmdate("Y/m/d(D) H:i:s",time()+9*60*60);
  $post['url'] = eregi_replace("^http://", "", $post['url']);
  $post['url'] = str_replace(" ", "", $post['url']);
  $post['ico'] = $ico;
  $post['ip'] = gethostbyaddr(getenv("REMOTE_ADDR"));

  return $post;
}
/*-- ログ書込み処理 --*/
function log_write($post) {
  global $admin_mail;
  // 新NO.
  $fp = fopen(LOGFILE, "r");
  $fline = fgets($fp, 2048);
  fclose($fp);
  // 重複カキ子チェック
  list($num,$rname,$rcom,$rip,)  = explode("<>", $fline);
  if ($rname == $post['name'] && $rcom == $post['comment']) error("同じ内容は送信できません");
  // 新No.
  $newnum = $num+1;
  $font = $post['font'].";".$post['hr'];
  $post['pass'] = crypt($post['delkey'], my_crypt($post['delkey']));
  // 先頭用データ、記事データ生成
  $newfline = "$newnum<>{$post['name']}<>{$post['comment']}<>{$post['ip']}<>".time()."\n";
  $newline = "$newnum<>{$post['now']}<>{$post['name']}<>{$post['email']}<>{$post['subject']}<>{$post['comment']}<>{$post['url']}<>$font<>{$post['ico']}<>{$post['type']}<>{$post['pass']}<>{$post['ip']}<><>\n";
  // クッキーセット、2週間有効
  setcookie("ibbs[name]", $post['name'], time()+14*86400);
  setcookie("ibbs[email]", $post['email'], time()+14*86400);
  setcookie("ibbs[ico]", $post['ico'], time()+14*86400);
  setcookie("ibbs[font]", $post['font'], time()+14*86400);
  setcookie("ibbs[hr]", $post['hr'], time()+14*86400);
  setcookie("ibbs[pass]", $post['delkey'], time()+14*86400);
  setcookie("ibbs[url]", $post['url'], time()+14*86400);

  if (NOTICE) {
    $mail_body = <<<EOL
掲示板に投稿がありました。
名前     : {$post['name']}
タイトル : {$post['subject']}
ＵＲＬ　 : {$post['url']}
記事No.  : {$newnum}
コメント : 
{$post['comment']}
--------------------------------
{$post['now']}
{$post['ip']}
EOL;

    $mail_body  = str_replace("<br>",   "\n", $mail_body);
    $mail_sub = "投稿通知 ".$_SERVER['REQUEST_URI'];
    if (ereg("^[0-9A-Za-z._-]+@[0-9A-Za-z.-]+$", $post['email'])) {
    $from = " <".$post['email'].">";
    } else {
      $from = " <nomail@xxxx.xxx>";
    }
    $head = "From: ".$from;
    //送信
    mb_language('japanese');
    mb_internal_encoding('SJIS');
    @mb_send_mail($admin_mail, $mail_sub, $mail_body, $head);
  }
	
  $lines = file(LOGFILE);
  array_shift($lines);//一行目流す

  // 親記事の場合。先頭に追加
  if ($post['type'] == 0) {
    array_unshift($lines, $newline);
    // 過去ログ
    $kako = array();
    $over = false;
    for ($i = 0; $i < count($lines); $i++) {
      list($num,,,,,,,,,$type,)  = explode("<>", $lines[$i]);
      if ($over) {
        if (PAST) array_push($kako, $lines[$i]);
        $lines[$i] = "";
      }
      if ($type == 0) $oya++;
      if ($oya >= MAXLOG) $over = true;
    }
    if (PAST && is_array($kako)) past_write($kako);
  }
  // レスの場合。該当記事検索
  else{
    $find = false;
    $res = array();
    for ($i = 0; $i < count($lines); $i++) {
      list($num,,,,,,,,,$type,)  = explode("<>", $lines[$i]);
      if ($post['type'] == $type) {
        if (!$find) $st = $i;
        $find = true;
        array_push($res, $lines[$i]);
      }
      elseif ($type == 0 && $post['type'] == $num) {
        if (!isset($st)) $st = $i;
        $find = true;
        array_push($res, $lines[$i]);
        array_unshift($res, $newline);
        break;
      }
    }
    if (!$find) error("該当記事が見つかりませんでした");
    // アゲの場合、該当スレ削除して、新スレと結合
    if (AGE) {
      array_splice($lines, $st, count($res)-1);
      $newlines = array_merge($res, $lines);
      $lines = $newlines;
    }
    // サゲの場合、新スレに置換
    else{
      array_splice($lines, $st, count($res)-1, $res);
    }
  }
  // 先頭用データ追加
  array_unshift($lines, $newfline);
  // ログ更新
  update($lines);
}
/*-- 個別記事削除 --*/
function del() {
  if (trim($_POST['del']) == "") error("記事No.が入力されてません!");
  if (trim($_POST['delkey']) == "") error("パスワードが入力されてません!");

  $lines = file(LOGFILE);
  $find = false;
  for ($i = 1; $i < count($lines); $i++) {
    list($num,,,,$subj,,,,,$type,$cpass,)  = explode("<>", $lines[$i]);
    if ($num == $_POST['del'] || @in_array($num, $_POST['del'])) {
      if (ADMINPASS != $_POST['delkey']) {
        if ($cpass == "") error("この記事には削除キーが存在しません!");
        if ($cpass != crypt($_POST['delkey'], $cpass)) error("パスワードが違います!");
      }
      $lines[$i] = ($type != "0") ? "" : "$num<><><><><><><><><>$num<><><>\n";
      $find = true;
    }
  }
  if (!$find) error("該当記事が見つかりません!");

  update($lines);
}
/*-- 個別記事編集表示 --*/
function edit() {
  global $html_icon;

  $del = ($_GET['del']) ? $_GET['del'] : $_POST['del'];
  $delkey = ($_GET['delkey']) ? $_GET['delkey'] : $_POST['delkey'];
  if (trim($_REQUEST['del']) == "") error("記事No.が入力されてません!");
  if (trim($_REQUEST['delkey']) == "") error("パスワードが入力されてません!");

  $lines = file(LOGFILE);
  $find = false;
  for ($i = 1; $i < count($lines); $i++) {
    list($num,$date,$name,$email,$subj,$com,$url,$col,$icon,$ty,$cpass,) = explode("<>", $lines[$i]);
    if ($num == $del) {
      if (ADMINPASS != $delkey) {
        if ($cpass == "") error("この記事には削除キーが存在しません!");
        if ($cpass != crypt($_POST['delkey'], $cpass)) error("パスワードが違います!");
      }
      $find = true;
      break;
    }
  }
  if (!$find) error("該当記事が見つかりません!");

  list($color,$b_color) = explode(";", $col);
  $pass = $delkey;
  $com = str_replace("<br>","\n",$com);
  $arg = compact('num','name','email','subj','com','url','b_color','color','icon','pass');
  $arg['edit_mode'] = true;
  $arg['font'] = radio_list("font", $color);
  $arg['hr']   = radio_list("hr", $b_color);
  $arg['icon'] = option_list($icon);
  $arg['title'] = "記事No.$num の編集";
  $arg['self'] = PHP_SELF;
  HtmlTemplate::t_include(OTHERFILE,$arg);
}
/*-- 編集書き直し --*/
function rewrite($post, $target) {
  $lines = file(LOGFILE);
  $find = false;

  for ($i = 1; $i < count($lines); $i++) {
    list($num,$now,,,,,,,,$type,$cpass,) = explode("<>", $lines[$i]);
    if ($num == $target && ($cpass == crypt($post['delkey'], $cpass) || $post['delkey'] == ADMINPASS)) {
      $find = true;
      $font = $post['font'].";".$post['hr'];
      $lines[$i] = "$num<>$now<>{$post['name']}<>{$post['email']}<>{$post['subject']}<>{$post['comment']}<>{$post['url']}<>$font<>{$post['ico']}<>$type<>$cpass<>{$post['ip']}<><>\n";
      break;
    }
  }
  if (!$find) error("編集に失敗しました!");

  update($lines);
}
/*-- 検索 --*/
function search() {
  if (trim($_GET['w']) != "") {
    // スペース区切りを配列に
    $word = htmlspecialchars($_GET['w']);
    $words = preg_split("/(　| )+/", $word);
    // ログ決定
    if ($_GET['logs'] == 0) {
      $lines = file(LOGFILE);
      array_shift($lines);
    }
    elseif (file_exists(PASTDIR.$_GET['logs'].".txt")) {
      $lines = file(PASTDIR.$_GET['logs'].".txt");
    }
    else {
      return false;
    }
    $result = array();
    foreach ($lines as $line) {	//ログを走査
      $find = FALSE;			//フラグ
      foreach ($words as $w) {
        if ($w == "") continue;	//空文字はパス
        if (stristr($line, $w)) {	//マッチ
          $find = TRUE;
          if ($_GET['kyo']) $line = str_replace($w, "<b style='color:green;background-color:#ffff66'>$w</b>", $line);
        }
        elseif ($_GET['andor'] == "and") {	//ANDの場合マッチしないなら次のログへ
          $find = FALSE;
          break;
        }
      }
      if($find) array_push($result, $line);	//マッチしたログを配列に
    }
    $arg['total'] = count($result);
    if (get_magic_quotes_gpc()) $word = stripslashes($word);
    $arg['word'] = $word;

    if (count($result) > 0) {
      $page_def = ($_GET['pp']) ? (int)$_GET['pp'] : PASTDEF;
      $page = ($_GET['page']) ? (int)$_GET['page'] : 0;
      // 記事表示
      for ($i = $page; $i < $page+$page_def; $i++) {
        $oya = $res = "";
        if (!trim($result[$i])) break;
        list($num,$date,$name,$email,$subj,$com,$url,
             $col,$icon,$type,,$host) = explode("<>", $result[$i]);
        list($color,$b_color) = explode(";", $col);
        if ($url != "") $url = "http://".$url;
        if ($icon != "") $icon = I_DIR.$icon;
        if ($type == 0) $oya = true;
        else $res = $type;
        // 親記事格納
        $arg['out'][] = compact('num','date','name','email','subj','com','b_color','color','icon','host','oya','res','over','page');
      }
      $arg['page_def'] = $page_def;
      $arg['st'] = $page + 1;
      $arg['to'] = $i;
      // ページ前/次
      $prev = $page - $page_def;
      $next = $i;
      if ($prev >= 0)          $arg['prev'] = "{$_SERVER['PHP_SELF']}?mode=s&w=$word&andor=$andor&log=$log&pp=$page_def&page=$prev";
      if ($next < count($result)) $arg['next'] = "{$_SERVER['PHP_SELF']}?mode=s&w=$word&andor=$andor&log=$log&pp=$page_def&page=$next";
      // ページ直接移動
      $tpage = ceil(count($result) / $page_def);
      for ($a = 0; $a < $tpage; $a++) {
        if ($a == $page/$page_def) $arg['paging'].= "[<b>$a</b>] ";
        else $arg['paging'].= "[<a href=\"{$_SERVER['PHP_SELF']}?mode=s&w=$word&andor=$andor&log=$log&pp=$page_def&page=$pp\"><b>$a</b></a>] ";
        $pp += $page_def;
      }
      if ($_GET['all'] == 1)       $arg['logname'] = "No.{$word} の関連記事表示";
      elseif ($_GET['logs'] == 0)  $arg['logname'] = "現在のログを検索";
      elseif ($_GET['logs'])       $arg['logname'] = "過去ログ $logs を検索";
    }
  }
  $pastno = file(PASTLOG);
  for ($i = $pastno[0]; $i > 0; $i--) {
    $sel = ($_GET['logs'] == $i) ? " selected" : "";
    $arg['past'][] = array('no'=>$i,'sel'=>$sel);
  }
  $arg['search_mode'] = true;
  $arg['title'] = "ログ内検索";
  $arg['self'] = PHP_SELF;
  HtmlTemplate::t_include(OTHERFILE,$arg);
}
/*-- 過去ログ表示 --*/
function past_view($logs, $page) {
  if ($logs == "0") $logs = 1;
  if (file_exists(PASTDIR.$logs.".txt")) {
    $lines = file(PASTDIR.$logs.".txt");
    if (!$page) $page = 0;
    // 記事表示
    for ($i = $page; $i < $page+PASTDEF; $i++) {
      if (!trim($lines[$i])) break;
      list($num,$date,$name,$email,$subj,$com,$url,
           $col,$icon,$type,,$host) = explode("<>", $lines[$i]);
      list($color,$b_color) = explode(";", $col);
      if ($url != "") $url = "http://".$url;
      if ($icon != "") $icon = I_DIR.$icon;
      if ($type == 0) $oya = true;
      else $res = $type;
      // 親記事格納
      $arg['out'][] = compact('num','date','name','email','subj','com','b_color','color','icon','host','oya','res','over','page');
    }
    $arg['page_def'] = PASTDEF;
    $arg['st'] = $page + 1;
    $arg['to'] = $i;
    // ページ前/次
    $prev = $page - PASTDEF;
    $next = $i;
    if ($prev >= 0)          $arg['prev'] = "{$_SERVER['PHP_SELF']}?mode=log&logs=$logs&page=$prev";
    if ($next < count($lines)) $arg['next'] = "{$_SERVER['PHP_SELF']}?mode=log&logs=$logs&page=$next";
    // ページ直接移動
    $tpage = (int)count($lines) / PASTDEF;
    for ($a = 0; $a < $tpage; $a++) {
      if ($a == $page/PASTDEF) $arg['paging'].= "[<b>$a</b>] ";
      else $arg['paging'].= "[<a href=\"{$_SERVER['PHP_SELF']}?mode=log&logs=$logs&page=$pp\"><b>$a</b></a>] ";
      $pp += PASTDEF;
    }
    $arg['logname'] = "過去ログ $logs を表示";
    $arg['total'] = count($lines);
  }
  else {
    $arg['logname'] = "過去ログ が見つかりません";
  }
  $pastno = file(PASTLOG);
  for ($i=$pastno[0],$j=0; $i>0,$j<$pastno[0]; $i--,$j++) {
    $arg['past'][$j]['no'] = $i;
    $arg['past'][$j]['link'] = ($logs == $i) ? "" : true;
    if (($j % 4)==3) $arg['past'][$j]['br'] = "<br>";
  }
  $arg['logs'] = $logs;
  $arg['past_mode'] = true;
  $arg['title'] = "過去ログ表示";
  $arg['self'] = PHP_SELF;
  HtmlTemplate::t_include(OTHERFILE,$arg);
}
/*-- 過去ログ書込み --*/
function past_write($lines) {
  // 過去ログNo読み込み
  $fp = fopen(PASTLOG, "r");
  $cnt = fgets($fp, 10);
  fclose($fp);
  $pfile = PASTDIR . $cnt . ".txt";
  if (file_exists($pfile)) {
    // 過去ログサイズオーバーなら過去ログNoアップ
    if (filesize($pfile) > PASTSIZE*1024) {
      $cnt++;
      $fp = fopen(PASTLOG, "w");
      set_file_buffer($fp, 0);
      flock($fp, LOCK_EX);
      fputs($fp, $cnt);
      fclose($fp);
      $pfile = PAST_DIR . $cnt . ".txt";
    }
  }
  // 過去ログに書込み
  $fp = fopen($pfile, "a");
  set_file_buffer($fp, 0);
  flock($fp, LOCK_EX);
  fputs($fp, implode('', $lines));
  fclose($fp);
}
/*-- ログ更新 --*/
function update($lines) {
  $fp = fopen(LOGFILE, "w");
  set_file_buffer($fp, 0);
  flock($fp, LOCK_EX);
  fputs($fp, implode('', $lines));
  fclose($fp);
}
/*-- 自動リンク --*/
function autolink($str) {
    return ereg_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)","<a href=\"\\1\\2\" target=_top>\\1\\2</a>",$str);
}
/*-- 暗号化関数 --*/
function my_crypt($str) {
  $time = time();
  list($p1, $p2) = unpack("C2", $time);
  $wk = $time / (7*86400) + $p1 + $p2 - 8;
  $saltset = array_merge(range('a', 'z'),range('A', 'Z'),range('0', '9'),array('/'));
  return $saltset[$wk % 64] . $saltset[$time % 64];
}
/*-- エラー表示 --*/
function error($str) {
  $arg['error'] = $str;
  $arg['err_mode'] = true;
  $arg['title'] = "エラー！！";
  $arg['self'] = PHP_SELF;
  HtmlTemplate::t_include(OTHERFILE,$arg);
  exit;
}
/*-- デバグ --*/
function _dbg($str) {
  echo "<pre>";
  var_export($str);
  echo "</pre>";
}
// スタート！
$page = intval($_GET['page']);
$mode = ($_GET['mode']) ? $_GET['mode'] : $_POST['mode'];

switch ($mode) {
  // 書込み
  case 'write':
    $data = check();
    log_write($data);
    header("Location: $jump");
  //echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".PHP_SELF."?\">";
    break;
  // 削除
  case 'del':
    del();
    header("Location: $jump");
    //echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".PHP_SELF."?\">";
    break;
  // 編集
  case 'edit':
    edit();
    break;
  // 編集書込み
  case 'rewrite':
    $data = check();
    rewrite($data, intval($_POST['num']));
    echo "<META HTTP-EQUIV=\"refresh\" content=\"0;URL=".PHP_SELF."?\">";
    break;
  // 管理
  case 'admin':
    all_view($page, "admin");
    break;
  // レス表示
  case 'res':
    res_view(intval($_GET['num']));
    break;
  // 検索
  case 's':
    search();
    break;
  // 過去ログ表示
  case 'log':
    past_view(intval($_GET['logs']), $page);
    break;
  // アイコン一覧
  case 'img':
    $l=1;
    foreach ($html_icon as $key=>$val) {
      if ($key == "randam") continue;
      if ($key == "master") $key = $mas_i[0];
      $arg['icon'][] = array('file' => I_DIR.$key, 'name' => $val);
      if (($l % $Ico_h)==0) $arg['icon'][$l-1]['tr'] = "</tr><tr>";
      $l++;
    }
    $arg['img_mode'] = true;
    $arg['title'] = "アイコン画像一覧";
    $arg['self'] = PHP_SELF;
    HtmlTemplate::t_include(OTHERFILE,$arg);
    break;
  // ヘルプ表示
  case 'man':
    $arg['man_mode'] = true;
    $arg['title'] = "掲示板の使い方";
    $arg['maxlog'] = MAXLOG;
    $arg['self'] = PHP_SELF;
    HtmlTemplate::t_include(OTHERFILE,$arg);
    break;
  // 新規投稿別画面
  case 'post':
    $arg['post_mode'] = true;
    $arg['title'] = "新規投稿";
    $arg['font'] = radio_list("font");
    $arg['hr']   = radio_list("hr");
    $arg['icon'] = option_list();
    $arg['maxcom'] = MAXCOM;
    $arg['self'] = PHP_SELF;
    HtmlTemplate::t_include(OTHERFILE,$arg);
    break;
  // 通常表示
  default:
    all_view($page);
}
?>
