#backlog2slack
backlog(http://www.backlog.jp/)の更新情報をchatwork(http://www.chatwork.com/ja/)にpostする連携ツールです。
課題の追加、課題の更新に対応しています。

簡易版なので細かい部分まで配慮して実装していませんので、
必要に応じて適宜改修してお使いいただければと。

##ファイル構成
b2cw.php  
本体のファイルです。

config.php  
各種設定を記載します。

##使い方
###下準備
ログ保存用のフォルダを作成し、httpdから書き込めるよう権限を適宜設定します。
/tmp/backlog2chatwork

###動作確認
ブラウザで、b2cw.phpをhttpリクエストすします。

###実運用
cronなどで定期的にb2cw.phpをhttpリクエストします。

##その他
複数のプロジェクトに対応しています。  
一回の処理で大量の更新がある場合は1プロジェクトあたり3件までpostされます。  
4件目以上は通知処理されません。  
※送信件数を調整する場合は、SEND_MESSAGEを変更してください

###chatwork API
APIの利用には申請が必要となります。  
下記URLからAPIの利用申請を行ってください。  
http://developer.chatwork.com/ja/

