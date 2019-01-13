<?php
/*
 *	location: admin/controller
 */

class ControllerExtensionDShopunityTransaction extends Controller {

	private $codename = 'd_shopunity';
	private $route = 'extension/d_shopunity/transaction';
	private $extension = array();

	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('extension/d_shopunity/mbooth');
		$this->load->model('extension/d_shopunity/account');
        $this->load->model('extension/d_shopunity/setting');
        $this->url_token = $this->model_extension_d_shopunity_setting->getUrlToken();

		$this->extension = $this->model_extension_d_shopunity_mbooth->getExtension($this->codename);
	}


	public function index(){
		if(!$this->model_extension_d_shopunity_account->isLogged()){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

   		$this->load->language('extension/d_shopunity/billing');
   		$this->load->model('extension/d_shopunity/billing');

   		
		$data['tab_order'] =  $this->language->get('tab_order');
		$data['tab_invoice'] =  $this->language->get('tab_invoice');
		$data['tab_transaction'] =  $this->language->get('tab_transaction');

		$data['href_order'] =  $this->url->link('extension/d_shopunity/order', $this->url_token, 'SSL');
		$data['href_invoice'] = $this->url->link('extension/d_shopunity/invoice', $this->url_token, 'SSL');
		$data['href_transaction'] = $this->url->link('extension/d_shopunity/transaction', $this->url_token, 'SSL');

		$filter_data = array();
		$data['page'] = 1;
		if(isset($this->request->get['page'])){
			$filter_data['page'] = $this->request->get['page'];
			$data['page'] = $this->request->get['page'];
		}

		$data['transactions'] = $this->model_extension_d_shopunity_billing->getTransactions($filter_data);
		$data['profile'] = $this->load->controller('extension/d_shopunity/account/profile');
		
		$data['prev'] = $this->url->link('extension/d_shopunity/transaction', $this->url_token.'&page='.($data['page']-1), 'SSL');
		$data['next'] = $this->url->link('extension/d_shopunity/transaction', $this->url_token.'&page='.($data['page']+1), 'SSL');

   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');

   		$this->response->setOutput($this->load->view($this->route, $data));
	}


}