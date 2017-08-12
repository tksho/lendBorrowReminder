<?php
class LendBorrowController {
	public $lendBorrow = "";
	public $who = "";
	public $money = "";
	public $date = "";	
						
	public function get( $user_id, $index ) {
		$lendBorrow = $this->redis->get("user_id:$user_id:index:$index:lendBorrow");
                $who        = $this->redis->get("user_id:$user_id:index:$index:who");
                $money      = $this->redis->get("user_id:$user_id:index:$index:money");
                $date       = $this->redis->get("user_id:$user_id:index:$index:date");
	}
}
