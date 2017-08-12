<?php

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Event\BeaconDetectionEvent;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\UnknownMessage;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\UnfollowEvent;
use LINE\LINEBot\Event\UnknownEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;

use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;


/**
 * Class title
 *
 * Class Description
 *
 * @author Yugo Kimura <me@example.com>
 * @since 1.0.0
 */

class PushController extends Controller {

	function __construct() {
		parent::__construct();
	}

	function __destruct() {
		parent::__destruct();
	}

        /**
         * TITLE
         *
         * DESCRIPTION
         *
         *
         * @param type text
         * pparam type text
         * @return  type test
         */
	function index($arg){
		
		$bot = new LINEBot(
			new CurlHTTPClient(LINE_CHANNEL_ACCESSTOKEN),
			['channelSecret' => LINE_CHANNEL_SECRET]
		);
		
		// 全ユーザID取得	
		$all_user = $this->redis->lRange("user_id:all", 0, -1);
		
		// 貸し借りがあれば	
		$numOfUser = count($all_user);
		if( $numOfUser != 0 ) {	
			// 全貸し借り取得してメッセージ作成
		        for($i=0; $i<$numOfUser; $i++) {
				$lendBorrow 	= "";
				$who 		= "";
				$money		= "";
				$date		= "";
				$str = "毎週月曜のリマインドだよ！\n\n--------";
				$numOfLendBorrow = $this->redis->get("user_id:$all_user[$i]:index");
				
				for($j=1; $j<=$numOfLendBorrow; $j++) {
					$lendBorrow	= $this->redis->get("user_id:$all_user[$i]:index:$j:lendBorrow");
					$who 		= $this->redis->get("user_id:$all_user[$i]:index:$j:who");
					$money		= $this->redis->get("user_id:$all_user[$i]:index:$j:money");
					$date		= $this->redis->get("user_id:$all_user[$i]:index:$j:date");
									
					if( $lendBorrow == "lend" ) {
						$str	 = $str . "\nNo" . $j . " " . $who . " " . $money . " 貸した(" . $date .")";
					}
					else if( $lendBorrow == "borrow" ) {
						$str	 = $str . "\nNo" . $j . " " . $who . " " . $money . " 借りた(" . $date .")";
					}
				}
				// 全貸し借りを見た結果、貸し借りがあればプッシュ送信	
				if( $str != "毎週月曜のリマインドだよ！\n\n--------" ) {
					// メッセージ送信
					$str = $str . "\n--------\n\n今これだけの貸し借りがあるよ。変更があったら教えてね！";
					$textMessageBuilder = new TextMessageBuilder($str);
					$response = $bot->pushMessage($all_user[$i], $textMessageBuilder);
					// 実行結果ステータス
					echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
				}
			}
		}
	}
}
