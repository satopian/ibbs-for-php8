<?php

define('HOME_URL', "https://pbbs.sakura.ne.jp/");

// スクリプト名
define('PHP_SELF', "index.php");

// ログファイル名(権限を606,646,666等にする)
define('LOGFILE', "ibbs.dat");

// 管理パス
define('ADMINPASS', "1234");

/* ---------- スパム対策 ---------- */

//拒絶する文字列
$badstring = array("irc.s16.xrea.com","未承諾広告");
//正規表現を使うことができます
//全角半角スペース改行を考慮する必要はありません
//スペースと改行を除去した文字列をチェックします

//使用できない名前
$badname = array("ブランド","通販","販売","口コミ");

//正規表現を使うことができます
//全角半角スペース改行を考慮する必要はありません
//スペースと改行を除去した文字列をチェックします

//設定しないなら ""で。
// $badname = array("");

//初期設定では「"通販"を含む名前」の投稿を拒絶します
//ここで設定したNGワードが有効になるのは「名前」だけです
//本文に「通販で買いました」と書いて投稿する事はできます

//名前以外の項目も設定する場合は
//こことは別の設定項目
//拒絶する文字列で

//AとBが両方あったら拒絶。
$badstr_A = array("激安","低価","コピー","品質を?重視","大量入荷");
$badstr_B = array("シャネル","シュプリーム","バレンシアガ","ブランド");

//正規表現を使うことができます。
//全角半角スペース改行を考慮する必要はありません
//スペースと改行を除去した文字列をチェックします

//設定しないなら ""で。
//$badstr_A = array("");
//$badstr_B = array("");

//AとBの単語が2つあったら拒絶します。
//初期設定では「ブランド品のコピー」という投稿を拒絶します。
//1つの単語では拒絶されないので「コピー」のみ「ブランド」のみの投稿はできます。

//一つの単語で拒絶する場合は
//こことは別の設定項目
//拒絶する文字列で

//本文に日本語がなければ拒絶
define('USE_JAPANESEFILTER', '1');

//本文へのURLの書き込みを禁止する する:1 しない:0
//管理者は設定にかかわらず許可
define('DENY_COMMENTS_URL', '1');

// 投稿通知メールを送るyes=1 no=0
define('NOTICE', 0);
// 通知メール送信先
$admin_mail = "all@s.to";

// レスがついたら記事を上げる？yes=1 no=0
define('AGE', 1);

// URLを自動リンクする？
define('AUTOLINK', 1);
// 投稿後の飛び先
$jump = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// 投稿制限文字数。上から名前、タイトル、コメント。半角で
define('MAXNAME', 32);
define('MAXSUBJ', 32);
define('MAXCOM', 1000);
// 最小文字数
define('MINCOM', 4);
// 改行数制限
define('MAXBR', 20);

// 親記事最大ログ保持件数
define('MAXLOG', 40);

// ヘッドライン表示件数(↑の数以下で）
define('MAXHEADLINE', 30);

// 色指定がない時の色
define('NOCOL', "#666666");

// アイコンの設定
// アイコン用ディレクトリ
define('I_DIR', "./Icon/");
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


// 過去ログ機能を使う？Yes=1,No=0（使用する場合は保存ﾃﾞｨﾚｸﾄﾘを757,777等にする）
define('PAST', 0);
define('PASTLOG', "ilog.log"); // 過去ログカウントファイル
define('PASTDIR', "./");       // 過去ログ生成ディレクトリ(/で終わる事)
define('PASTSIZE', "100");     // 過去ログ記録数 KB
define('PASTDEF', 20);         // 過去ログモードでの表示件数

// カウンタを使う？
define('COUNTER', 1);
define('COUNTIMG', "");    //カウンタ画像のディレクトリ（テキストの場合は空。/で終わる）
define('COUNTLOG', "icount.dat"); //カウンタファイル(権限を606,646',666等にする)

  define('MAINFILE', 'skin_main.html');
  define('OTHERFILE', 'skin_other.html');
  // 1ページに表示する親記事数
  define('PAGEDEF', 5);
  // 1親記事に表示するレス数
  define('RESDEF', 5);
  // 先頭？件、最新？件表示
  define('RESEVERY', 10);
  // 携帯時は日付を省略
  define('MOBILE', 0);

  //---設定ここまで
?>
