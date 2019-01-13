<?php
/*
 *	location: admin/controller
 */

class ControllerExtensionDShopunityMarket extends Controller {
	
	private $codename = 'd_shopunity';
	private $route = 'extension/d_shopunity/market';
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

		//documentation http://t4t5.github.io/sweetalert/
		$this->document->addStyle('view/javascript/d_shopunity/library/sweetalert/sweetalert.css');
		$this->document->addScript('view/javascript/d_shopunity/library/sweetalert/sweetalert.min.js');

		$this->document->addStyle('view/stylesheet/shopunity/bootstrap.css');
		$this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity.css');
		$this->document->addScript('view/javascript/d_shopunity/d_shopunity.js');
		
   		$this->load->language('extension/d_shopunity/extension');
   		$this->load->model('extension/d_shopunity/extension');

   		$url = array();
		//REFACTOR
		$filter_data = array();

		$data['search'] = '';
		if(isset($this->request->get['search'])){
			$filter_data['search'] = $this->request->get['search'];
			$data['search'] = $this->request->get['search'];
			$url['search'] = $this->request->get['search'];
		}
		$data['page'] = 1;
		if(isset($this->request->get['page'])){
			$filter_data['page'] = $this->request->get['page'];
			$data['page'] = $this->request->get['page'];
		}
		$data['category_id'] = '';
		if(isset($this->request->get['category_id'])){
			$filter_data['category_id'] = $this->request->get['category_id'];
			$url['category_id'] =  $this->request->get['category_id'];
		}

		if(isset($this->request->get['commercial'])){
			$filter_data['commercial'] = $this->request->get['commercial'];
			$url['commercial'] =  $this->request->get['commercial'];
		}
		
		$filter_data['limit'] = 12;
		$filter_data['status'] = 1;
		$filter_data['published'] = 1;
		$filter_data['store_version'] = VERSION;
		
		$data['extensions'] = $this->model_extension_d_shopunity_extension->getExtensions($filter_data);
		$data['categories'] = $this->load->controller('extension/d_shopunity/market/categories'); 
		$data['search_href'] = $this->url->link('extension/d_shopunity/market', $this->url_token, 'SSL');

		$data['all'] = $this->url->link('extension/d_shopunity/market', $this->url_token, 'SSL');
		$data['commercial'] = $this->url->link('extension/d_shopunity/market', $this->url_token.'&commercial=1', 'SSL');
		$data['free'] = $this->url->link('extension/d_shopunity/market', $this->url_token.'&commercial=0', 'SSL');

		$data['prev'] = $this->url->link('extension/d_shopunity/market', $this->url_token.'&'.http_build_query($url).'&page='.($data['page']-1), 'SSL');
		$data['next'] = $this->url->link('extension/d_shopunity/market', $this->url_token.'&'.http_build_query($url).'&page='.($data['page']+1), 'SSL');

   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');

   		$this->response->setOutput($this->load->view($this->route, $data));
	}

	public function categories(){
		$this->load->model('extension/d_shopunity/category');

		$data['categories'] = $this->model_extension_d_shopunity_category->getCategories();
		foreach($data['categories'] as $key => $category){
			$data['categories'][$key]['href'] = $this->url->link('extension/d_shopunity/market', $this->url_token.'&category_id='. $category['category_id'], 'SSL');
		}

		return $this->load->view($this->route.'_categories', $data);

	}
}



