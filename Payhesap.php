<?php

/**
 *
 * Payhesap Iframe Pos Basic PHP Class
 *
 * PHP versions 5 and 7
 *
 * @author  Mirarus <aliguclutr@gmail.com>
 * @version 1.0
 * @link https://github.com/mirarus/Payhesap
 *
 */

class Payhesap
{

	private 
	$config = [],
	$buyer = [],
	$order_id,
	$price,
	$installment = 1,
	$currency_codes = ['TRY', 'USD', 'EUR'],
	$currency_code = 'TRY',
	$error;

	public function set_config($data=[])
	{
		if ($data['hash'] == null || $data['callback_url'] == null || $data['success_url'] == null || $data['fail_url'] == null) {
			$this->error = "Missing api, url information.";
		} else {
			$this->config = [
				'hash'         => $data['hash'],
				'callback_url' => $data['callback_url'],
				'success_url'  => $data['success_url'],
				'fail_url'     => $data['fail_url']
			];
		}
	}

	public function set_buyer($data=[])
	{
		if ($data['name'] == null || $data['email'] == null || $data['phone'] == null || $data['city'] == null || $data['state'] == null || $data['address'] == null) {
			$this->error = "Missing Buyer information.";
		} else {
			$this->buyer = [
				'name'    => $data['name'],
				'email'   => $data['email'],
				'phone'   => $data['phone'],
				'city'    => $data['city'],
				'state'   => $data['state'],
				'address' => $data['address']
			];
		}
	}

	public function set_order_id($order_id)
	{
		if ($order_id != null) {
			$this->order_id = (time() . 'PH' . $order_id);
		}
	}

	public function set_price($price)
	{
		if ($price != null) {
			$this->price = number_format($price, 2, '.', '');
		}
	}

	public function set_installment($installment)
	{
		if ($installment != null) {
			if ($installment <= 12) {
				$this->installment = $installment;
			} else {
				$this->error = "Max ınstallment Count 12";
			}
		}
	}

	public function set_currency($currency)
	{
		if ($currency != null) {
			if (in_array($currency, $this->currency_codes)) {
				$this->currency_code = $currency;
			} else {
				$this->error = "Invalid Currency Code";
			}
		}
	}

	public function get_error()
	{
		if ($this->error != null) {
			return $this->error;
		}
	}

	public function init()
	{
		if ($this->config == null || $this->buyer == null || $this->order_id == null || $this->price == null) {
			$this->error = "Insufficient Data";
		} else {

			$post = [
				'hash'         => $this->config['hash'],
				'callback_url' => $this->config['callback_url'],
				'success_url'  => $this->config['success_url'],
				'fail_url'     => $this->config['fail_url'],

				'order_id'     => $this->order_id,
				'amount'       => $this->price,
				'installment'  => $this->installment,
				'currency'     => $this->currency_code,

				'name'         => $this->buyer['name'],
				'email'        => $this->buyer['email'],
				'phone'        => $this->buyer['phone'],
				'city'         => $this->buyer['city'],
				'state'        => $this->buyer['state'],
				'address'      => $this->buyer['address'],
				'ip'           => $this->GetIP()
			];


			$encode = json_encode($post, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			
			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => "https://www.payhesap.com/api/iframe/pay",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $encode,
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json',
					'Content-Length: ' . strlen($encode)
				]
			]);
			$response = @curl_exec($ch);

			if (curl_errno($ch)) {
				$this->error = curl_error($ch);
			} else {

				$result = json_decode($response, true);
				if ($result['status'] == true) {
					return 'https://payhesap.com/api/iframe/' . $result['data']['token'];
				} else {
					$this->error = $result['errors'];
				}
			}
			curl_close($ch);
		}
	}

	public function callback()
	{
		$status   = $this->post('STATUS');
		$order_id = $this->post('ORDER_REF_NUMBER');
		$hash     = $this->post('HASH');

		if ($status == true) {

			$ch = curl_init();
			curl_setopt_array($ch, [
				CURLOPT_URL => "https://www.payhesap.com/api/pay/checkOrder",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_FRESH_CONNECT => true,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => ['hash' => $hash]
			]);
			$response = @curl_exec($ch);

			if (curl_errno($ch)) {
				$this->error = curl_error($ch);
			} else {

				$result = json_decode($response, true);

				if ($result['statusID'] == true) {
					return [
						'order_id' => explode('PH', $order_id)[1],
						'hash'     => $hash
					];
				} else {
					$this->error = "Payment Failed";
				}
			}
			curl_close($ch);
		}
	}

	private function GetIP()
	{
		if (getenv("HTTP_CLIENT_IP")) {
			$ip = getenv("HTTP_CLIENT_IP");
		} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
			if (strstr($ip, ',')) {
				$tmp = explode (',', $ip);
				$ip = trim($tmp[0]);
			}
		} else{
			$ip = getenv("REMOTE_ADDR");
		}
		return $ip;
	}

	private function post($par, $empty=true) {
		if ($empty == true) {
			return (isset($_POST[$par]) && !empty($_POST[$par])) ? $_POST[$par] : null;
		} else {
			return (isset($_POST[$par])) ? $_POST[$par] : null;
		}
	}
}