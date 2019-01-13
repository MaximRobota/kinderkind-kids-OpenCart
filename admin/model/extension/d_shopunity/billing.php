<?php
/*
 *	location: admin/model
 *
 * manage orders, invoices and transactions
 */

class ModelExtensionDShopunityBilling extends Model {

	private $store_id = '';
	private $api = '';
	private $dir_root = '';

	public function __construct($registry){
		parent::__construct($registry);
		$this->api = new d_shopunity\API($registry);
		$this->store_id = $this->api->getStoreId();
		$this->dir_root = substr_replace(DIR_SYSTEM, '/', -8);
        $this->load->model('extension/d_shopunity/setting');
        $this->url_token = $this->model_extension_d_shopunity_setting->getUrlToken();
	}

	public function getOrders($filter_data = array()){
		$result = array();
		$filter_data['limit'] = 12;
		$orders = $this->api->get('account/orders', $filter_data);
		if(is_array($orders)){
			foreach($orders as $order){
				$result[] = $this->_order($order);
			}
		}
		
		return $result;

	}

	public function getOrdersOverdue(){
		$result = array();
		$orders = $this->api->get('account/orders/overdue');
		if(is_array($orders)){
			foreach($orders['orders'] as $order){
				$result['orders'][] = $this->_order($order);
			}
		}
		$result['total'] = $orders['total'];
		$result['total_format'] = $orders['total_format'];
		$result['count'] = $orders['count'];
		$result['date_next_invoice'] = date($this->language->get('date_format_short'), strtotime($orders['date_next_invoice']));

		return $result;

	}

	public function getOrder($order_id){

		$order = $this->api->get('account/orders/'.$order_id);
		return $this->_order($order);
		
	}

	private function _order($data){
		$result = array();
		if(!empty($data)){
			$result = $data;
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($data['date_added']));
			$result['date_next_invoice'] = date($this->language->get('date_format_short'), strtotime($data['date_next_invoice']));
			// $result['suspend'] = $this->url->link('d_shopunity/order/suspend', $this->url_token.'&order_id='.$data['order_id']);
			// $result['activate'] = $this->url->link('d_shopunity/order/activate', $this->url_token.'&order_id='.$data['order_id']);
			$result['url'] = $this->url->link('extension/d_shopunity/order/item', $this->url_token.'&order_id='.$data['order_id']);
			if($result['store_extension']){
				$this->load->model('extension/d_shopunity/extension');
				$result['store_extension'] = $this->model_extension_d_shopunity_extension->_extension($result['store_extension']);
                //in case a module was paid and became free.
                if(isset($result['store_extension']['price'])){
                    $result['activate'] = $result['store_extension']['purchase'] . '&extension_recurring_price_id='.$result['store_extension']['price']['extension_recurring_price_id'];
                }else{
                    $result['activate'] = false;
                }
				$result['suspend'] = $result['store_extension']['suspend'];
			}

		}
		return $result;
	}

/**
 * Invoices
 */
	public function getInvoices($filter_data = array()){
		$result = array();
		$filter_data['limit'] = 12;
		$invoices = $this->api->get('account/invoices', $filter_data);
		if(is_array($invoices)){
			foreach($invoices as $invoice){
				$result[] = $this->_invoice($invoice);
			}
		}
		
		return $result;

	}

	public function getInvoice($invoice_id){

		$invoice = $this->api->get('account/invoices/'.$invoice_id);
		return $this->_invoice($invoice);
		
	}

	public function addInvoice(){

		$result = $this->api->post('account/invoices');
		return $result;
	}


	public function payInvoice($invoice_id){

		$result = $this->api->post('account/invoices/'.$invoice_id.'/pay');
		return $result;
		
	}

	public function refundInvoice($invoice_id){

		$result = $this->api->post('account/invoices/'.$invoice_id.'/refund');
		return $result;
		
	}

	public function cancelInvoice($invoice_id){

		$result = $this->api->post('account/invoices/'.$invoice_id.'/cancel');
		return $result;
		
	}

    public function claimExternalOrder($data){

    
        $send = array(
            'market' => (isset($data['market'])) ? $data['market']: '',
            'user_id' => (isset($data['user_id'])) ? $data['user_id']: '',
            'order_id' => (isset($data['order_id'])) ? $data['order_id']: '',
            );
        $result = $this->api->post('external/claim', $send);
        return $result;
        
    }

    public function applyVoucher($voucher_id, $invoice_id){
        $send = array(
            'voucher_id' => $voucher_id,
            'invoice_id' => $invoice_id
        );
        $result = $this->api->post('account/invoices/'.$invoice_id.'/voucher', $send);
        return $result;
    }
	

	private function _invoice($data){
		$result = array();
		if(!empty($data)){
			$result = $data;
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($data['date_added']));
			$result['url'] = $this->url->link('extension/d_shopunity/invoice/item', $this->url_token.'&invoice_id='.$data['invoice_id']);
			$result['pay'] = $this->url->link('extension/d_shopunity/invoice/pay', $this->url_token.'&invoice_id='.$data['invoice_id']);
            $result['popup_pay_invoice'] = $this->url->link('extension/d_shopunity/invoice/popup_pay_invoice', $this->url_token.'&invoice_id='.$data['invoice_id']);
			$result['refund'] = $this->url->link('extension/d_shopunity/invoice/refund', $this->url_token.'&invoice_id='.$data['invoice_id']);
			$result['cancel'] = $this->url->link('extension/d_shopunity/invoice/cancel', $this->url_token.'&invoice_id='.$data['invoice_id']);

		}	
		return $result;
	}

/**
 * Transactions
 */
	public function getTransactions($filter_data = array()){
		$result = array();
		$filter_data['limit'] = 12;
		$transactions = $this->api->get('account/transactions', $filter_data);
		if(is_array($transactions)){
			foreach($transactions as $transaction){
				$result[] = $this->_transaction($transaction);
			}
		}
		
		return $result;

	}

	public function getTransaction($transaction_id){

		$transaction = $this->api->get('account/transactions/'.$transaction_id);
		return $this->_transaction($transaction);
		
	}

	private function _transaction($data){
		$result = array();
		if(!empty($data)){
			$result = $data;
			$result['date_added'] = date($this->language->get('date_format_short'), strtotime($data['date_added']));
			
		}
		return $result;
	}
	

}