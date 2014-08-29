<?php

set_time_limit(300);

print "backlog2chatwork<br />";

//-----------------------------
ini_set( 'display_errors', 1 );

define("TMP_DIR","/tmp/backlog2chatwork");

define("BASE_DIR",dirname(__FILE__));

define('CW_ENDPOINT_URL',"https://api.chatwork.com/v1/rooms/%%ROOM_ID%%/messages");

define("SEND_MESSAGE",3);

//------------------------------

$path = get_include_path();

$path = $path.":".dirname(__FILE__)."/Services_Backlog/Backlog";
set_include_path($path);

//------------------------------

require_once(BASE_DIR."/config.php");
require_once(BASE_DIR."/Services/Backlog.php");
require_once(BASE_DIR."/lib/xml2.class.php5");

//------------------------------

if(!is_array($configs)){
	print "configs not found.";
	exit;
}

foreach($configs as $project=>$config){

	$blConfig = $config['backlog'];

	$backlog = new Services_Backlog($blConfig['url'],$blConfig['user'],$blConfig['pass']);

	# Apiの呼び出し
	$result_xml = $backlog->getTimeline();
	//print_r($result_xml);

	$xmlObj = new xml2;

	$dataAry = $xmlObj->xml2array($result_xml);

	$dataAry = dataFormat($dataAry);

	//var_dump($dataAry);

	print "<hr />";
	print "実行中：{$project}";

	chatworkApiSendMessage($project,$config,$dataAry);

}

print "<hr />";
print "Finish.";

exit;

//--------------------------------------------------------------------
/**
*
*/
function dataFormat($dataAry){

	if(!is_array($dataAry)){
		return false;
	}

	$dataAry = $dataAry['params']['param']['value']['array']['data']['value'];


	$aryTimeLineTmp = array();
	foreach($dataAry as $data){

		$data = $data['struct']['member'];

		$message = $data[0]['value']['string'];

		$title = $data[1]['value']['struct']['member'][0]['value']['string'];

		if(isset($data[1]['value']['struct']['member'][4]['name']) && $data[1]['value']['struct']['member'][4]['name'] == 'key'){
			$pKey = $data[1]['value']['struct']['member'][4]['value']['string'];
		}
		elseif(isset($data[1]['value']['struct']['member'][5]['name']) && $data[1]['value']['struct']['member'][5]['name'] == 'key'){
			$pKey = $data[1]['value']['struct']['member'][5]['value']['string'];
		}
		else{
			continue;
		}

		$user = $data[4]['value']['struct']['member'][1]['value']['string'];

		$update = $data[2]['value']['string'];

		if($message && $pKey && $user){
			$tmp = array();
			$tmp['key']     = $pKey;
			$tmp['title']   = $title;
			$tmp['message'] = $message;
			$tmp['user']    = $user;
			$tmp['update']  = $update;

			$aryTimeLineTmp[] = $tmp;
		}

	}

	$aryTimeLine = array();

	foreach($aryTimeLineTmp as $v){

		$key = $v['key'];

		if(!isset($aryTimeLine[$key])){
			$aryTimeLine[$key] = $v;
		}
	}

	return $aryTimeLine;

}

/**
*
*/
function getHistry($project){

	$historyFile = TMP_DIR."/".$project.".history";

	print "load:{$historyFile}<br />";

	if(!file_exists($historyFile)){
		return array();
	}

	$fileData = file_get_contents($historyFile);

	$aryData = unserialize($fileData);

	return $aryData;

}

/**
*
*/
function saveHistry($project,$aryData){

	$historyFile = TMP_DIR."/".$project.".history";

	print "save:{$historyFile}<br />";

	$fileData = serialize($aryData);

	file_put_contents($historyFile,$fileData);

	return true;

}

/**
* chatworkAPI連携
* メッセージの送信
*/
function chatworkApiSendMessage($projectKey,$config,$dataAry){

	$notSendFlg = false;

	$histryAry = getHistry($projectKey);

	if(!is_array($histryAry)){
		$histryAry = array();
		$notSendFlg = true;
	}

	// 送信対象をチェック
	$sendTargetAry = array();
	foreach($dataAry as $aryVal){

		$key    = $aryVal['key'];
		$update = $aryVal['update'];

		if(!isset($histryAry[$key])){
		// データなし
			$sendTargetAry[] = $aryVal;

		} else{

			// 更新されているかチェック
			if($update > $histryAry[$key]){
				$sendTargetAry[] = $aryVal;
			}
		}

		$histryAry[$key] = $update;

	}

	//var_dump($sendTargetAry);
	//var_dump($histryAry);

	// データを保存
	$histryAry = saveHistry($projectKey,$histryAry);
	print "データを保存しました。<br />";


	if($notSendFlg){
		print "データを更新しました。APIへの送信は中止しました。<br />";
		return true;
	}

	if(empty($sendTargetAry)){
		print "送信対象のデータがありません。<br />";
		return true;
	}

	$backlogUrlBase = "https://".$config['url']."/view/";

	$cwApikey = $config['chatwork']['apikey'];
	$cwRoomId = $config['chatwork']['roomid'];

	$count = 0;
	foreach($sendTargetAry as $data){

		$count ++;

		$key     = $data['key'];
		$title   = $data['title'];
		$message = $data['message'];
		$user    = $data['user'];

		// メールによる課題追加の対応処理
		$pos = strpos($message,"----------------------------------------------------------------------");
		if($pos){
			$message = substr($message,0,$pos);
		}

		$message = mb_strimwidth($message,0,200,"...",'UTF-8');

		$url = $backlogUrlBase.$key;

		$message = "{$title}\n{$message}";

		$endpointURL = str_replace('%%ROOM_ID%%', $cwRoomId, CW_ENDPOINT_URL);

		//var_dump($message);

		$aryData = array('body'=>$message);

		$data = http_build_query($aryData);

		$opts = array(
		    'http'=>array(
		        'method'=>"POST",
		        'header'=>"X-ChatWorkToken: {$cwApikey}\r\n",
		        "content" => $data
		)
		);

		$context = stream_context_create($opts);

		// chatworkにメッセージ送信
		$resutl = file_get_contents($endpointURL, false, $context);

		print "REQUESTED.";

		if($resutl){
		    print "OK:".$resutl;
		} else{
		    print "NG:".$http_response_header[0];
		}

		print "<br />";
		print "send message:{$key}<br />";
		print_r($result);

		if($count >= SEND_MESSAGE){
			print "メッセージ送信自主制限:".SEND_MESSAGE."<br />";
			break;
		}

	}

	return true;
}

