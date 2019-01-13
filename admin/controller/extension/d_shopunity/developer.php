<?php
/*
 *	location: admin/controller
 */

class ControllerExtensionDShopunityDeveloper extends Controller {

	private $codename = 'd_shopunity';
	private $route = 'extension/d_shopunity/developer';
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

		if(empty($account['developer'])){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		$this->document->addScript('view/javascript/d_shopunity/library/list/list.min.js');
		$this->document->addScript('view/javascript/d_shopunity/library/list/list.fuzzysearch.min.js');

		$developer_id = $account['developer']['developer_id'];

   		$this->load->language('extension/d_shopunity/extension');
   		$this->load->language('extension/d_shopunity/tester');
   		$this->load->model('extension/d_shopunity/developer');

		$data['extensions'] = $this->model_extension_d_shopunity_developer->getExtensions($developer_id);

   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');
   		$data = $this->load->controller('extension/d_shopunity/extension/_productThumb',$data);
   		$this->response->setOutput($this->load->view($this->route, $data));
	}

	/**
	 * Update the extension over the whole system.
	 */
	public function update(){
		if(!isset($this->request->get['extension_id'])){
			$this->session->data['error'] = 'Error! extension_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		if(!isset($this->request->get['developer_id'])){
			$this->session->data['error'] = 'Error! developer_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}
		$json = array();
		try{
			$this->load->model('extension/d_shopunity/developer');
			$result = $this->model_extension_d_shopunity_developer->updateExtension($this->request->get['extension_id'], $this->request->get['developer_id']);

			$json['result'] = $result;
			if(empty($result)){
				$json['error'] = 'Error! no updates made.';
			}else{
				$json['success'] = 'We have sent ' .count($result).' requests to update the extension';
			}

		}catch(Exception $e){
			$json['error'] = $e->getMessage();
		}

		$this->response->setOutput(json_encode($json));
	}

	public function profile($developer){
		$this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity.css');
		$data['developer'] = $developer;

		return $this->load->view($this->route.'_profile', $data);
	}

	public function generate_module(){
		$account = $this->config->get('d_shopunity_account');

		if(!empty($account['developer'])){
			$this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity.css');
			$this->load->model('extension/d_shopunity/mbooth');
			$data['extensions'] = $this->model_extension_d_shopunity_mbooth->getExtensions();
			$data['action'] = $this->url->link('extension/d_shopunity/developer/generate', $this->url_token, 'SSL');

			return $this->load->view($this->route.'_generate_module', $data);
		}
		return false;
  }

	public function generate(){
		if(!isset($this->request->post['codename'])){
			$this->session->data['error'] = 'Error! codename missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
		}

		if(!isset($this->request->post['template_codename'])){
			$this->session->data['error'] = 'Error! template_codename missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
		}
		$codename = $this->request->post['codename'];
		$template_codename = $this->request->post['template_codename'];

		$this->load->model('extension/d_shopunity/mbooth');
		$template_extension = $this->model_extension_d_shopunity_mbooth->getExtension($template_codename);
		if(!$template_extension){
			$this->session->data['error'] = 'Error! '. $template_codename .' does not exist';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$extension = $this->model_extension_d_shopunity_mbooth->getExtension($codename);
		if($extension){
			$this->session->data['error'] = 'Error! '. $codename.' already exists';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		if(!$template_extension['files']){
			$this->session->data['error'] = 'Error! '. $template_codename.' has no files';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$files = $template_extension['files'];
		$template_codename_camel = str_replace('_','', ucwords($template_codename, "_"));

		$replace[$template_codename] = $codename;
		$replace[$template_codename_camel] = str_replace('_','', ucwords($codename, "_"));

		$modelgen = new ModuleGenerator($replace, $files);
		$modelgen->generate();

		if($modelgen->success){
			$this->session->data['success'] = 'Success!' .$codename.' created.';
		}

		if($modelgen->error){
			$this->session->data['error'] = 'Error!'.' Sorry, there was an error with these files: ' . json_encode($modelgen->error);
		}

		$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
	}
}
