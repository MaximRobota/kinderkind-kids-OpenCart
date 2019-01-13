<?php
/*
 *	location: admin/controller
 */

class ControllerExtensionDShopunitySetting extends Controller {

	private $codename = 'd_shopunity';
	private $route = 'extension/d_shopunity/setting';
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

   		$this->load->language('extension/d_shopunity/setting');

   		$data['version'] = $this->model_extension_d_shopunity_mbooth->getVersion($this->codename);
   		//update
        $data['entry_update'] = sprintf($this->language->get('entry_update'), $data['version']);
        $data['button_update'] = $this->language->get('button_update');
        $data['update'] = str_replace('&amp;', '&', $this->url->link($this->route.'/getUpdate', $this->url_token, 'SSL'));
        $data['entry_install_demo_data'] = $this->language->get('entry_install_demo_data');
        $data['button_install_demo_data'] = $this->language->get('button_install_demo_data');
        $data['install_demo_data'] = str_replace('&amp;', '&', $this->url->link($this->route.'/installDemoData', $this->url_token, 'SSL'));
        $data['enabled_ssl_url'] = str_replace('&amp;', '&', $this->url->link($this->route.'/enabledSslUrl', $this->url_token, 'SSL'));


   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');

   		$this->response->setOutput($this->load->view($this->route, $data));
	}

	/*
	*	Ajax: Get the update information on this module. 
	*/
	public function getUpdate(){

		$json = array();

		$this->load->language('extension/d_shopunity/setting');
		$this->load->model('extension/d_shopunity/mbooth');
		$this->load->model('extension/d_shopunity/extension');

		$current_version = $this->model_extension_d_shopunity_mbooth->getVersion($this->codename);
		$extension = $this->model_extension_d_shopunity_extension->getExtension($this->codename);

		if ($extension) {


			if ((string) $extension['version'] == (string) $current_version 
				|| (string) $extension['version'] <= (string) $current_version) 
			{
				$json['success']   = $this->language->get('success_no_update') ;
			} 
			elseif ((string) $extension['version'] > (string) $current_version) 
			{
				$json['warning']   = $this->language->get('warning_new_update');

				foreach($extension['changelog'] as $changelog)
				{
					if((string) $changelog['version'] > (string)$current_version)
					{
						$version = (string)$changelog['version'];
						$json['update'][$version] = (string) $changelog['change'];
					}
				}
			} 
			else 
			{
				$json['error']   = $this->language->get('error_update');
			}
		} 
		else 
		{ 
			$json['error']   =  $this->language->get('error_failed');
		}

		$this->response->setOutput(json_encode($json));
	}
}