<?php
/*
 *	location: admin/controller
 */

class ControllerExtensionDShopunityTester extends Controller {

	private $codename = 'd_shopunity';
	private $route = 'extension/d_shopunity/tester';
	private $extension = array();

	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->model('extension/d_shopunity/mbooth');
		$this->load->model('extension/d_shopunity/account');
		$this->load->model('extension/d_shopunity/extension');
        $this->load->model('extension/d_shopunity/setting');
        $this->url_token = $this->model_extension_d_shopunity_setting->getUrlToken();

		$this->extension = $this->model_extension_d_shopunity_mbooth->getExtension($this->codename);
	}

	public function index(){

		if(!$this->model_extension_d_shopunity_account->isLogged()){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		$account = $this->config->get('d_shopunity_account');

		if(empty($account['tester'])){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		$tester_id = $account['tester']['tester_id'];

		$this->document->addScript('view/javascript/d_shopunity/library/list/list.min.js');
		$this->document->addScript('view/javascript/d_shopunity/library/list/list.fuzzysearch.min.js');

   		$this->load->language('extension/d_shopunity/extension');
   		$this->load->language('extension/d_shopunity/tester');
   		$this->load->model('extension/d_shopunity/extension');
   		
		$data['extensions'] = $this->model_extension_d_shopunity_extension->getTastableExtensions($tester_id);

   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');
   		$data = $this->load->controller('extension/d_shopunity/extension/_productThumb',$data);
   		$this->response->setOutput($this->load->view($this->route, $data));
	}

	public function approve(){

		if(!$this->model_extension_d_shopunity_account->isLogged()){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		$account = $this->config->get('d_shopunity_account');

		if(empty($account['tester'])){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		if(!isset($this->request->get['extension_download_link_id'])){
			$this->session->data['error'] = 'Error! extension_download_link_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		if(!isset($this->request->get['extension_id'])){
			$this->session->data['error'] = 'Error! extension_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		if(!isset($this->request->get['status'])){
			$this->session->data['error'] = 'Error! status missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$tester_id = $account['tester']['tester_id'];
		$extension_id = $this->request->get['extension_id'];
		$data['extension_download_link_id'] = $this->request->get['extension_download_link_id'];
		$data['status'] = $this->request->get['status'];
		$data['tester_comment'] = '';
		if(!empty($this->request->post['tester_comment'])){
			$data['tester_comment'] = $this->request->post['tester_comment'];
		}

   		$this->load->language('extension/d_shopunity/tester');
   		$this->load->model('extension/d_shopunity/extension');

		$response = $this->model_extension_d_shopunity_extension->approveExtension($tester_id, $extension_id, $data);

   		if(!empty($response['error'])){
			$this->session->data['error'] = $response['error'];
		}elseif(!empty($response['success'])){
			$this->session->data['success'] = $response['success'];
		}

		//refactor
		if($data['status']){
			$this->response->redirect($this->url->link('extension/d_shopunity/tester', $this->url_token, 'SSL'));
		}
		
	}

	
}