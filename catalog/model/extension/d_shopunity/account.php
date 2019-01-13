<?php
/*
 *	location: admin/model
 */

class ModelExtensionDShopunityAccount extends Model {

	private $store_id = '';
	private $api = '';
	private $dir_root = '';

	public function __construct($registry){
		parent::__construct($registry);
		$this->api = new d_shopunity\API($registry);
		$this->store_id = $this->api->getStoreId();
		$this->dir_root = substr_replace(DIR_SYSTEM, '/', -8);
	}

	public function isLogged(){
		//validate if settings is avalible.
		if($this->config->get('d_shopunity_oauth')){

			$json = $this->config->get('d_shopunity_account');
			if(!$json){
				$json = $this->getAccount();
			}
			

			//validate is json returned.
			if ($json) 
			{
				//validate if expired_token
				if(isset($json['error']['error']) && 
					(
						$json['error']['error'] === 'expired_token' ||  
						$json['error']['error'] === 'invalid_token'
					)
				){
					$d_shopunity_oauth = $this->config->get('d_shopunity_oauth');
					
					if(isset($d_shopunity_oauth['access_token'])){
						$json = $this->refreshToken($d_shopunity_oauth['refresh_token']);
					}else{
						$json = false;
					}

					//get new access token
					if($json)
					{

						//validate is access_token returned.
						if(!empty($json['access_token']))
						{
							$this->login($json);

							$this->config->set('d_shopunity_account', $json);
							return $json;
						}else{
							//access_token is not retunred
							$this->logout();
							return false;
						}
					}
				}else{
					
					//OK. account returned. 
					$this->config->set('d_shopunity_account', $json);
					return $json;
				}
			}else{
				//json not returned. logout. 
				$this->logout();
				return false;
			}
		}else{
			//no settings set.
			return false;
		}
	}

	public function login($json){
		$data = array();
		$data['d_shopunity_oauth'] = $json;
		$this->config->set('d_shopunity_oauth', $json);
		
		//set access_token
		$this->api->set('access_token', $json['access_token']);

		//set store_info
		$json = $this->api->get('stores', array('url' => HTTP_SERVER));
		$data['d_shopunity_store_info'] = $json[0];
		$this->config->set('d_shopunity_store_info', $json[0]);

		//save oauth and store_info to database
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('d_shopunity', $data);

		return $data;
	}

	public function setAccessToken($access_token){
		$this->api->set('access_token', $access_token);
		$json = $this->api->get('stores', array('url' => HTTP_SERVER));
		if(isset($json['error'])){
			throw new Exception('Error! access not granted '. json_encode($json), 500);
		}else{
			$this->config->set('d_shopunity_store_info', $json[0]);
		}
		
	}

//NEEDS REFACTOR
	// public function getCurrentStore(){
	// 	if($this->config->get('d_shopunity_store_info')){
	// 		return $this->config->get('d_shopunity_store_info');
	// 	}else{

	// 		$json = $this->api->get('stores', array('url' => HTTP_CATALOG));

	// 		if ($json && !isset($json['error'])) {
	// 			$this->load->model('setting/setting');
	// 			$data = array('d_shopunity_store_info' => $json[0]);
	// 			$data += $this->model_setting_setting->getSetting('d_shopunity');
	// 			$this->model_setting_setting->editSetting('d_shopunity', $data);
	// 			$this->config->set('d_shopunity_store_info', $json[0]);
	// 			return $json[0];
	// 		}else{
	// 			return false;
	// 		}
	// 	}
	// }

	public function getAccount(){
		$json = $this->api->get('account');
		return $json;
	}


	public function logout(){
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('d_shopunity');
	}


	//TODO
	public function getAuthorizeUrl($callback_route){
		return $this->api->getAuthorizeUrl($this->url->link($callback_route, 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function getToken($callback_route){
		$json = array();
		if(isset($this->request->get['code'])){
			$resource = array( 
		    	'grant_type' => 'authorization_code',
		    	'client_id' => $this->api->getClientId(),
				'code' => $this->request->get['code'],
		        'state' => $this->request->get['state'],
		        'redirect_uri' => urlencode($this->url->link($callback_route, 'token=' . $this->session->data['token'], 'SSL'))
			);

			$json = $this->api->post('oauth/token', $resource);
		}

		if(isset($this->request->get['error'])){
			$json['error'] = $this->request->get['error'];
		}

		return $json;
	}

	public function refreshToken($refresh_token){
		$resource = array( 
	    	'grant_type' => 'refresh_token',
	    	'client_id' => $this->client_id,
			'refresh_token' => $refresh_token,
	    );

	    $json = $this->api->post('oauth/token', $resource);

	    return $json;
	}
}