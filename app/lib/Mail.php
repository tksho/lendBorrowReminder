<?php

/**
 * @exmample

// メール処理開始
$mail = new Mail();

// Fromの設定
$mail->setFrom( array( 'name' => 'FROM', 'email' => 'from@example.com') );

// Toの設定
$mail->setTos( array('name' => 'TO1', 'email' => 'to1@example.com') );
$mail->setTos( array('name' => 'TO2', 'email' => 'to2@example.com') );
$mail->setTos( array('name' => 'TO3', 'email' => 'to3@example.com') );

// Ccの設定
$mail->setCcs( array('name' => 'CC1', 'email' => 'cc1@example.com'));
$mail->setCcs( array('name' => 'CC2', 'email' => 'cc2@example.com'));
$mail->setCcs( array('name' => 'CC3', 'email' => 'cc3@example.com') );

// Bccの設定
$mail->setBccs( array('name' => 'BCC1', 'email' => 'bcc1@example.com'));
$mail->setBccs( array('name' => 'BCC2', 'email' => 'bcc2@example.com'));
$mail->setBccs( array('name' => 'BCC3', 'email' => 'bcc3@example.com'));

// 件名設定
$mail->setSubject('これは件名です。');

// 本文設定
$body = <<<EOL
メール配信テスト

テストです。
EOL;
$mail->setBody( $body );

// 送信
$mail->send();
**/

class Mail {

        protected $from;
        protected $tos;
        protected $ccs;
        protected $bccs;
        protected $subject;
        protected $body;


        function __construct() {

                $this->from = array();
                $this->tos = array();
                $this->ccs = array();
                $this->bccs = array();
                $subject = "";
                $body = "";

        }

        function __destruct() {

        }

        public function setFrom( $from ) {
                $this->from = $from;
        }

        public function setTo( $to ) {
                $this->tos = array();
                $this->tos[] = $to;
        }
        public function setTos( $to ) {
                $this->tos[] = $to;
        }

        public function setCc( $cc ) {
                $this->ccs = array();
                $this->ccs[] = $cc;
        }
        public function setCcs( $cc ) {
                $this->ccs[] = $cc;
        }

        public function setBcc( $bcc ) {
                $ths->bccs = array();
                $this->bccs[] = $bcc;
        }
        public function setBccs( $bcc ) {
                $this->bccs[] = $bcc;
        }
        public function setSubject( $subject ) {
                $this->subject = $subject;
        }

        public function setBody( $body ) {
                $this->body = $body;
        }


        public function send() {
                //言語設定、内部エンコーディングを指定する
                mb_language("japanese");
                mb_internal_encoding("UTF-8");

                //日本語メール送信
                $from = '';
                $ccs = array();
                $bccs = array();

                if( isset( $this->from['name'] ) and isset( $this->from['email'] ) ) {
                        $from = mb_encode_mimeheader( mb_convert_encoding( $this->from['name'], 'JIS', 'UTF-8') ) . '<' . $this->from['email']  . '>';
                }

                foreach ( $this->ccs as $cc ) {
                        if( isset( $cc['name'] ) and isset( $cc['email'] ) ) {
                                $ccs[] = mb_encode_mimeheader( mb_convert_encoding( $cc['name'], 'JIS', 'UTF-8') ) . '<' . $cc['email']  . '>';
                        }
                }

                foreach( $this->bccs as $bcc ) {
                        if( isset( $bcc['name'] ) and isset( $bcc['email'] ) ) {
                                $bccs[] = mb_encode_mimeheader( mb_convert_encoding( $bcc['name'], 'JIS', 'UTF-8') ) . '<' . $bcc['email']  . '>';
                        }
                }
                $headers = '';
                if( $from )  {
                        $headers .= "From:" . $from . "\n";
                        if ( $ccs )
                                $headers .= "CC:" . implode(',', $ccs) . "\n";

                        if( $bccs )
                                $headers .= "BCC:" . implode(',', $bccs) . "\n";

                        if( $this->tos ) {
                                foreach ( $this->tos as $to ) {
                                        if( isset( $to['name'] ) and isset( $to['email'] ) ) {
                                                $to = mb_encode_mimeheader( mb_convert_encoding( $to['name'], 'JIS', 'UTF-8') ) . '<' . $to['email']  . '>';
                                                if (! @mb_send_mail($to, $this->subject, $this->body, $headers) ) {
                                                        print "error";
                                                }
                                        }
                                }
                        }
                }
        }

}

?>
