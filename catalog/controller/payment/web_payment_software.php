<?php
class ControllerPaymentWebPaymentSoftware extends Controller {
	protected function index() {
		$this->language->load('payment/web_payment_software');

		$this->data['text_credit_card'] = $this->language->get('text_credit_card');
		$this->data['text_wait'] = $this->language->get('text_wait');

		$this->data['entry_cc_owner'] = $this->language->get('entry_cc_owner');
		$this->data['entry_cc_number'] = $this->language->get('entry_cc_number');
		$this->data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
		$this->data['entry_cc_cvv2'] = $this->language->get('entry_cc_cvv2');

		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');

		$this->data['months'] = array();

		for ($i = 1; $i <= 12; $i++) {
			$this->data['months'][] = array(
				'text'  => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)), 
				'value' => sprintf('%02d', $i)
			);
		}

		$today = getdate();

		$this->data['year_expire'] = array();

		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$this->data['year_expire'][] = array(
				'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
				'value' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)) 
			);
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/web_payment_software.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/web_payment_software.tpl';
		} else {
			$this->template = 'default/template/payment/web_payment_software.tpl';
		}	

		$this->render();		
	}

	public function send() {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$request  = 'MERCHANT_ID=' . urlencode($this->config->get('web_payment_software_merchant_name'));
		$request .= '&MERCHANT_KEY=' . urlencode($this->config->get('web_payment_software_merchant_key'));
		$request .= '&TRANS_TYPE=' . urlencode($this->config->get('web_payment_software_method') == 'capture' ? 'AuthCapture' : 'AuthOnly');
		$request .= '&AMOUNT=' . urlencode($this->currency->format($order_info['total'], $order_info['currency_code'], 1.00000, false));
		$request .= '&CC_NUMBER=' . urlencode(str_replace(' ', '', $this->request->post['cc_number']));
		$request .= '&CC_EXP=' . urlencode($this->request->post['cc_expire_date_month'] . substr($this->request->post['cc_expire_date_year'], 2));
		$request .= '&CC_CVV=' . urlencode($this->request->post['cc_cvv2']);
		$request .= '&CC_NAME=' . urlencode($order_info['payment_firstname'] . ' ' . $order_info['payment_lastname']);
		$request .= '&CC_COMPANY=' . urlencode($order_info['payment_company']);
		$request .= '&CC_ADDRESS=' . urlencode($order_info['payment_address_1']);
		$request .= '&CC_CITY=' . urlencode($order_info['payment_city']);
		$request .= '&CC_STATE=' . urlencode($order_info['payment_iso_code_2'] != 'US' ? $order_info['payment_zone'] : $order_info['payment_zone_code']);
		$request .= '&CC_ZIP=' . urlencode($order_info['payment_postcode']);
		$request .= '&CC_COUNTRY=' . urlencode($order_info['payment_country']);
		$request .= '&CC_PHONE=' . urlencode($order_info['telephone']);
		$request .= '&CC_EMAIL=' . urlencode($order_info['email']);
		$request .= '&INVOICE_NUM=' . urlencode($this->session->data['order_id']);

		if ($this->config->get('web_payment_software_mode') == 'test') {
			$request .= '&TEST_MODE=1';
		}

		$curl = curl_init('https://secure.web-payment-software.com/gateway');

		curl_setopt($curl, CURLOPT_PORT, 443);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

		$response = curl_exec($curl);

		curl_close($curl);

		//If in test mode strip results to only contain xml data
		if($this->config->get('web_payment_software_mode') == 'test'){
			$end_index = strpos($response, '</WebPaymentSoftwareResponse>');
			$debug = substr($response, $end_index + 30);
			$response = substr($response, 0, $end_index)  .'</WebPaymentSoftwareResponse>';
		}

		//get response xml
		$xml = simplexml_load_string($response);

		//create object to use as json
		$json = array();

		//If successful log transaction in your Onlinestore system
		if ('00' === (string)$xml->response_code) {
			$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('config_order_status_id'));

			$message = '';

			$message .= 'Response Code: ';

			if (isset($xml->response_code)) {
				$message .= (string)$xml->response_code . "\n";
			}

			$message .= 'Approval Code: ';

			if (isset($xml->approval_code)) {
				$message .= (string)$xml->approval_code . "\n";
			}

			$message .= 'AVS Result Code: ';

			if (isset($xml->avs_result_code)) {
				$message .= (string)$xml->avs_result_code . "\n";
			}

			$message .= 'Transaction ID (web payment software order id): ';

			if (isset($xml->order_id)) {
				$message .= (string)$xml->order_id . "\n";
			}

			$message .= 'CVV Result Code: ';

			if (isset($xml->cvv_result_code)) {
				$message .= (string)$xml->cvv_result_code . "\n";
			}

			$message .= 'Response Text: ';

			if (isset($xml->response_text)) {
				$message .= (string)$xml->response_text . "\n";
			}

			$this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('web_payment_software_order_status_id'), $message, false);				

			$json['success'] = $this->url->link('checkout/success', '', 'SSL');
		} else {
			$json['error'] = (string)$xml->response_text;
		}

		$this->response->setOutput(json_encode($json));
	}
}
?>