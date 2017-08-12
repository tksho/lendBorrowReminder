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

class WebhookController extends Controller {

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

		try {
			
			// LINEからの署名を取得	@をつけてエラーを回避
			$signature = @$_SERVER["HTTP_" . HTTPHeader::LINE_SIGNATURE];

			// LINEから送信されたパラメータ(JSON)を取得
			$body = file_get_contents("php://input");

			// このやりとりのイベント名を取得(テキストなのか、画像なのか、スタンプなのか、、、)
			$events  = $bot->parseEventRequest($body, $signature);

			// eventsは配列形式でくるため、配列をループにかける
			foreach ($events as $event) {
					
				// 返信用のワンタイムトークンを取得
				$reply_token = $event->getReplyToken();
				$user_id     = $event->getUserId(); // user_idを取得
				$response        = $bot->getProfile($user_id); // プロフィール名を取得
				$profile = "";
				if($response->isSucceeded() ) {
					$profile = $response->getJSONDecodedBody();
					$profile = $profile['displayName'];
				}

				$user_id = $event->getUserId();	
				
				// はじめてのユーザならDBにユーザ登録
				$all_user = $this->redis->lRange("user_id:all", 0, -1);
				if( !in_array( $user_id, $all_user ) ) {
					$this->redis->rPush("user_id:all", "$user_id");
				}
				
				// もしテキストデータだった場合
				if ($event instanceof TextMessage) {
					// 送信されてきた文字列を取得
					$body = $event->getText();

                                        // 複数個のテキストを返信できるオブジェクトを作成
                                        $SendMessage = new MultiMessageBuilder();

					// 何の会話をしている最中か（＝トークフラグ）取得
					$talkFlg = $this->redis->get("user_id:$user_id:talkFlg");

					// 貸す人を聞いてる最中なら
					if( $talkFlg == "lend_who" ) {
						$index = $this->redis->get("user_id:$user_id:index");
						
						// 貸した人を保存
                                        	$this->redis->set("user_id:$user_id:index:$index:who", $body);
						// トークフラグ更新
						$this->redis->set("user_id:$user_id:talkFlg", "lend_howmuch");
						
						// いくら貸したかユーザに尋ねる
                		                $message = new TextMessageBuilder("いくら貸したの？");
		                                $SendMessage->add($message);
					
					}
					// いくら貸したかを聞いてる最中なら
					else if( $talkFlg == "lend_howmuch" ) {
						$index = $this->redis->get("user_id:$user_id:index");
						
						// 貸した金額を保存
						$date 	= date("n月j日"); 
						// 貸した金額を保存
                                        	$this->redis->set("user_id:$user_id:index:$index:money", $body);
                                        	$this->redis->set("user_id:$user_id:index:$index:date", $date);
						// トークフラグを更新
						$this->redis->set("user_id:$user_id:talkFlg", "lend_confirm");
						
						// 保存してよいかユーザに尋ねる
						$who	 = $this->redis->get("user_id:$user_id:index:$index:who");
						$str	 = $who . "　" . $body . "　貸した(" . $date .")";
                		                $message = new TextMessageBuilder($str);
		                                $SendMessage->add($message);
                		                $message = new TextMessageBuilder("これで保存していい？");
		                                $SendMessage->add($message);
					}
					// 貸した内容確認をとっている最中なら
					else if( $talkFlg == "lend_confirm" ) {
						$index = $this->redis->get("user_id:$user_id:index");
					        
						if( $body == "うん" || $body == "OK" || $body == "おk" || $body == "はい" || $body == "うす" || $body == "うむ" ){
							// 会話終了
							$this->redis->del("user_id:$user_id:talkFlg");
                		        	        $message = new TextMessageBuilder("オッケー、保存したよ。毎週月曜日にリマインドするね。\n貸し借りの一覧を見たいときは何か話しかけてね！");
		                        	        $SendMessage->add($message);
						}else {
							// これまでの会話を全部リセット
							$this->redis->del("user_id:$user_id:index:$index:who");
							$this->redis->del("user_id:$user_id:index:$index:money");
							$this->redis->del("user_id:$user_id:index:$index:date");
							$this->redis->del("user_id:$user_id:index:$index:lendBorrow");
							$this->redis->del("user_id:$user_id:talkFlg");
							$index = $this->redis->get("user_id:$user_id:index");
							$this->redis->set("user_id:$user_id:index", $index-1);

							// 会話終了
							$this->redis->del("user_id:$user_id:talkFlg");
                		                        $message = new TextMessageBuilder("そっか、やめとくね。、また何か用があったら呼んでね");
		                                        $SendMessage->add($message);
						}
					}
					// 貸す人を聞いてる最中なら
					else if( $talkFlg == "borrow_who" ) {
						$index = $this->redis->get("user_id:$user_id:index");
						
						// 貸した人を保存
                                        	$this->redis->set("user_id:$user_id:index:$index:who", $body);
						// トークフラグ更新
						$this->redis->set("user_id:$user_id:talkFlg", "borrow_howmuch");
						
						// いくら貸したかユーザに尋ねる
                		                $message = new TextMessageBuilder("いくら借りたの？");
		                                $SendMessage->add($message);
					
					}
					// いくら貸したかを聞いてる最中なら
					else if( $talkFlg == "borrow_howmuch" ) {
						$index = $this->redis->get("user_id:$user_id:index");
						
						// 貸した金額を保存
						$date 	= date("n月j日"); 
						// 貸した金額を保存
                                        	$this->redis->set("user_id:$user_id:index:$index:money", $body);
                                        	$this->redis->set("user_id:$user_id:index:$index:date", $date);
						// トークフラグを更新
						$this->redis->set("user_id:$user_id:talkFlg", "borrow_confirm");
						
						// 保存してよいかユーザに尋ねる
						$who	 = $this->redis->get("user_id:$user_id:index:$index:who");
						$str	 = $who . "　" . $body . "　借りた(" . $date .")";
                		                $message = new TextMessageBuilder($str);
		                                $SendMessage->add($message);
                		                $message = new TextMessageBuilder("これで保存していい？");
		                                $SendMessage->add($message);
					}
					// 貸した内容確認をとっている最中なら
					else if( $talkFlg == "borrow_confirm" ) {
						$index = $this->redis->get("user_id:$user_id:index");
					        
						if( $body == "うん" || $body == "OK" || $body == "おk" || $body == "はい" || $body == "うす" || $body == "うむ" ){
							// 会話終了
							$this->redis->del("user_id:$user_id:talkFlg");
                		        	        $message = new TextMessageBuilder("オッケー、保存したよ。毎週月曜日にリマインドするね。\n貸し借りの一覧を見たいときは何か話しかけてね！");
		                        	        $SendMessage->add($message);			
						}else {
							// これまでの会話を全部リセット
							$this->redis->del("user_id:$user_id:index:$index:who");
							$this->redis->del("user_id:$user_id:index:$index:money");
							$this->redis->del("user_id:$user_id:index:$index:date");
							$this->redis->del("user_id:$user_id:index:$index:lendBorrow");
							$this->redis->del("user_id:$user_id:talkFlg");
							$index = $this->redis->get("user_id:$user_id:index");
							$this->redis->set("user_id:$user_id:index", $index-1);

							// 会話終了
							$this->redis->del("user_id:$user_id:talkFlg");
                		                        $message = new TextMessageBuilder("そっか、やめとくね。、また何か用があったら呼んでね");
		                                        $SendMessage->add($message);
						}
					}
					
					else if( $talkFlg == "hensai_no" ) {
						// トークから半角数値のみ取得
						$index 	=  preg_replace('/[^0-9]/', '', $body);
						// 半角数値が含まれていれば
						if( is_numeric($index) ) {
							$this->redis->set("user_id:$user_id:hensai_index:", $index);
							$lendBorrow	= $this->redis->get("user_id:$user_id:index:$index:lendBorrow");
							$who 		= $this->redis->get("user_id:$user_id:index:$index:who");
							$money		= $this->redis->get("user_id:$user_id:index:$index:money");
							$date		= $this->redis->get("user_id:$user_id:index:$index:date");
							if( $lendBorrow == "lend" ) {
								$str	 = "----------\nNo" . $index . " " . $who . " " . $money . " 貸した(" . $date .")\n----------";
							}else {
								$str	 = "----------\nNo" . $index . " " . $who . " " . $money . " 借りた(" . $date .")\n----------";
							}
							// ユーザがID入力ミスした場合
							if( $who == "" ) {
								$str = "番号まちがえてない？もっかい入力してみて";
		       						$message = new TextMessageBuilder($str);
		                                		$SendMessage->add($message);
							}else {
								$str = $str . "\n\nこれだよね、じゃあ消すよ？";
		       						$message = new TextMessageBuilder($str);
		                                		$SendMessage->add($message);
								// トークフラグを更新
								$this->redis->set("user_id:$user_id:talkFlg", "hensai_confirm");
							}
						} else {
		       					$message = new TextMessageBuilder("番号で言ってほしいです。。\nちなみにやめるときは「もういい」って言えばやめるよ。");
		                                	$SendMessage->add($message);	
						}
					}
					else if( $talkFlg == "hensai_confirm" ) {
						if( $body == "うん" || $body == "OK" || $body == "おk" || $body == "はい" || $body == "うす" || $body == "うむ" ){
							$hensai_index = $this->redis->get("user_id:$user_id:hensai_index:");
					
							// 貸し借り削除する
							$this->redis->del("user_id:$user_id:index:$hensai_index:who");
							$this->redis->del("user_id:$user_id:index:$hensai_index:money");
							$this->redis->del("user_id:$user_id:index:$hensai_index:date");
							$this->redis->del("user_id:$user_id:index:$hensai_index:lendBorrow");
							$this->redis->del("user_id:$user_id:talkFlg");
							$index = $this->redis->get("user_id:$user_id:index");
	
							// 会話終了
							$this->redis->del("user_id:$user_id:talkFlg");
                		                       	$message = new TextMessageBuilder("削除したよ。また何か用があったら呼んでね");
		                                       	$SendMessage->add($message);
						}
						else {
							// 会話終了
							$this->redis->del("user_id:$user_id:talkFlg");
							$this->redis->del("user_id:$user_id:hensai_index:");
                		                       	$message = new TextMessageBuilder("じゃやめとくよ。また何か用があったら呼んでね");
		                                       	$SendMessage->add($message);
						}
					}
					else {
						if( $body == "かした" ) {
							// 現在の貸し借り数取得
							$numOfLendBorrow = $this->redis->get("user_id:$user_id:index");
						
							// 空のlendレコード作成
							if( $numOfLendBorrow == "" ) {
								$index = 1;
								$this->redis->set("user_id:$user_id:index", $index);
							}
							else {
								$index = $numOfLendBorrow + 1;
								$this->redis->set("user_id:$user_id:index", $index);
							}
							$this->redis->set("user_id:$user_id:index:$index:lendBorrow", "lend");
					
							// 誰に貸したかユーザに尋ねる
                		                        $message = new TextMessageBuilder("オッケー、貸したんだね。誰に貸したの？");
		                                        $SendMessage->add($message);
							
							// トークフラグを更新
							$this->redis->set("user_id:$user_id:talkFlg", "lend_who");
							
						}

						else if( $body == "かりた") {
						// 現在の貸し借り数取得
							$numOfLendBorrow = $this->redis->get("user_id:$user_id:index");
						
							// 空のlendレコード作成
							if( $numOfLendBorrow == "" ) {
								$index = 1;
								$this->redis->set("user_id:$user_id:index", $index);
							}
							else {
								$index = $numOfLendBorrow + 1;
								$this->redis->set("user_id:$user_id:index", $index);
							}
							$this->redis->set("user_id:$user_id:index:$index:lendBorrow", "borrow");
					
							// 誰に貸したかユーザに尋ねる
                		                        $message = new TextMessageBuilder("オッケー、借りたんだね。誰に借りたの？");
		                                        $SendMessage->add($message);
							
							// トークフラグを更新
							$this->redis->set("user_id:$user_id:talkFlg", "borrow_who");
						}
						else if( $body == "返済した" || $body == "返済された" || $body == "返した" || $body == "返された") {
							$numOfLendBorrow = $this->redis->get("user_id:$user_id:index");
							
							$str = "オッケー。どのナンバーの貸し借りのこと？\n--------";
							// 全貸し借り取得してメッセージ作成
						  	for($i=1; $i<=$numOfLendBorrow; $i++) {
								$lendBorrow	= $this->redis->get("user_id:$user_id:index:$i:lendBorrow");
								$who 		= $this->redis->get("user_id:$user_id:index:$i:who");
								$money		= $this->redis->get("user_id:$user_id:index:$i:money");
								$date		= $this->redis->get("user_id:$user_id:index:$i:date");
								
								if( $lendBorrow == "lend" ) {
									$str	 = $str . "\nNo" . $i . " " . $who . " " . $money . " 貸した(" . $date .")";
								}
								else if( $lendBorrow == "borrow" ) {
									$str	 = $str . "\nNo" . $i . " " . $who . " " . $money . " 借りた(" . $date .")";
								}else {
									//何もしない
								}
							}	
							
							$str = $str . "\n--------";
                		                	
							// 貸し借りがなかった場合
							if( $str == "オッケー。どのナンバーの貸し借りのこと？\n--------\n--------" ) {
								$str = "そもそも貸し借りがないみたいだね。。";	
							}
							else {
								// トークフラグを更新
								$this->redis->set("user_id:$user_id:talkFlg", "hensai_no");
							}
							// メッセージ送信
							$message = new TextMessageBuilder($str);
		                               		$SendMessage->add($message);
						}
						// デフォルトメッセージ
						else {
							$numOfLendBorrow = $this->redis->get("user_id:$user_id:index");
							// 貸し借りがあれば	
							if( $numOfLendBorrow != "" ) {	
								error_log("have lendborrow default message");
								$str = "今の貸し借り状況だよ！\n\n--------";
								// 全貸し借り取得してメッセージ作成
							        for($i=1; $i<=$numOfLendBorrow; $i++) {
									$lendBorrow	= $this->redis->get("user_id:$user_id:index:$i:lendBorrow");
									$who 		= $this->redis->get("user_id:$user_id:index:$i:who");
									$money		= $this->redis->get("user_id:$user_id:index:$i:money");
									$date		= $this->redis->get("user_id:$user_id:index:$i:date");
								
									if( $lendBorrow == "lend" ) {
										$str	 = $str . "\nNo" . $i . " " . $who . " " . $money . " 貸した(" . $date .")";
									}
									else if( $lendBorrow == "borrow" ) {
										$str	 = $str . "\nNo" . $i . " " . $who . " " . $money . " 借りた(" . $date .")";
									}
								}
								// 全貸し借りを見た結果、貸し借りが一つもなかった場合	
								if( $str == "今の貸し借り状況だよ！\n\n--------" ) {
									$str = "何も貸し借りがないよ！何したいの？（かした・かりた、返済した・された）";
								}
								else {
									$str = $str . "\n--------\n\n何したいの？（かした・かりた、返済した・された）";
                		                		}
								// メッセージ送信
								$message = new TextMessageBuilder($str);
		                               			$SendMessage->add($message);
							}
							else {
								// 何も貸し借りがないよ！をメッセージ送信
                		                                $message = new TextMessageBuilder("何も貸し借りがないよ！何したいの？（かした・かりた、返済した・された）");
		                                                $SendMessage->add($message);
							}
						}
					}
					
					// 一度だけ利用できるreply_tokenを利用して相手に返信
					$response = $bot->replyMessage($reply_token, $SendMessage);
				}
				// もしステッカー(すたんぷ)だった場合
				else if ($event instanceof StickerMessage) {
					$columns = [];
					$lists = [1,2,3,4,5];
					foreach ($lists as $list) {
						// カルーセルのリンクボタンを作成
						$action1 = new UriTemplateActionBuilder("クリックしてねA", 'https://bot-6415.chat-ai.tk/' );
						$action2 = new UriTemplateActionBuilder("クリックしてねB", 'https://bot-6415.chat-ai.tk/' ); 
						// カルーセルのアイテムにリンク
						$column = new CarouselColumnTemplateBuilder("タイトル(40文字以内)", "追加文", 'https://bot-6415.chat-ai.tk/img/sample.jpg', [$action1, $action2]);
						$columns[] = $column;
					}
					//カルーセルの作成
					$carousel = new CarouselTemplateBuilder($columns);
					$carousel_message = new TemplateMessageBuilder("メッセージのタイトル", $carousel);
					$response = $bot->replyMessage($reply_token, $carousel_message);
				}
			}

		} catch (Exception $e) {
			print $e->getLine() . "\n";
			print $e->getMessage();
		}
	}

}
