<?php

$configs = array();

$key = "group1";

//backlogの設定
$configs[$key]['backlog']['url']  = 'YOUR_BACKLOG_SUBDOMAIN.backlog.jp';
$configs[$key]['backlog']['user'] = 'BACKLOG_USERNAME';
$configs[$key]['backlog']['pass'] = 'BACKLOG_PASSWORD';

//chatworkの設定
$configs[$key]['chatwork']['apikey'] = 'YOUR_APIKEY';
$configs[$key]['chatwork']['roomid'] = 'CHATWORK_ROOMID';


// 複数のアカウントで運用する場合はkeyを別名として複数登録します。
/*
$key = "group2";

//backlogの設定
$configs[$key]['backlog']['url']  = 'YOUR_BACKLOG_SUBDOMAIN.backlog.jp';
$configs[$key]['backlog']['user'] = 'BACKLOG_USERNAME2';
$configs[$key]['backlog']['pass'] = 'BACKLOG_PASSWORD2';

//chatworkの設定
$configs[$key]['chatwork']['apikey'] = 'YOUR_APIKEY';
$configs[$key]['chatwork']['roomid'] = 'CHATWORK_ROOMID';


*/

//---------------------------------------
unset($key);



