# PHP-I-BOARD for php8

![image](https://user-images.githubusercontent.com/44894014/101713695-7cbde280-3adb-11eb-8a08-b3f862de96d3.png)

## ibbs php5.5-php8.0
PHP7では動作しないPHP4の掲示板、PHP-I-BOARDをphp8.0でも動くようにしたものです。  
### テンプレートエンジンをSkinny.phpに変更
htmltemplate.inc v1.31は、php7では動作しませんでした。POTI-board改のhtmltemplate.incとは互換性がありませんでした。  
そのためテンプレートエンジンを[Skinny.php](http://skinny.sx68.net/)に変更しました。
### POTI-board改二のスパムフィルタ   
POTI-board改二と同じスパムフィルタを追加しています。  
一度作成した掲示板用のスパムフィルタは容易に移植可能な事がわかりました。
### このスクリプトをweb上のサーバに設置しないでください、あくまでも学習のために作成したサンプルです   
php8.0でも動作するように書き直している途中です。予期しないエラーがでる可能性があります。  
実際に運用できる掲示板の作成には至っていません。 
また、phpの学習のために作成しただけですので、サポートもできません。  
テンプレートはHTML4の廃止されたタグだらけです。  
またダブルクオートの抜けがたくさんあります。  
    
2020年12月10日 GitHubに公開。
