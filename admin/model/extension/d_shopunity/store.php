<?php
/*
 *	location: admin/model
 */

class ModelExtensionDShopunityStore extends Model {

	private $store_id = '';
	private $api = '';
	private $dir_root = '';

	public function __construct($registry){
        parent::__construct($registry);
        $this->api = new d_shopunity\API($registry);
        $this->store_id = $this->api->getStoreId();
        $this->dir_root = substr_replace(DIR_SYSTEM, '/', -8);
    }

	// public function getStore($store_id = 'current'){

	// 	$result = file_get_contents($this->api."stores/". $store_id . "?access_token=".$this->config->get('d_shopunity_oauth')['access_token']).'&url='.urlencode(HTTP_CATALOG);

	// 	$json = json_decode($result,true);

	// 	if (json_last_error() === JSON_ERROR_NONE) {
	// 		return $json;
	// 	}else{
	// 		return false;
	// 	}
	// }

	public function getCurrentStore(){
		if($this->config->get('d_shopunity_store_info')){
			return $this->config->get('d_shopunity_store_info');
		}else{
			// $data = array();
			// $json = $this->api->get('stores', array('url' => HTTP_CATALOG));
			// if(isset($json[0]))
			// {
			// 	$data['d_shopunity_store_info'] = $json[0];
			// 	$this->config->set('d_shopunity_store_info', $json[0]);

			// 	//save oauth and store_info to database
			// 	$this->load->model('setting/setting');
			// 	$this->model_setting_setting->editSetting('d_shopunity', $data);
			// 	return $json[0];
			// }else{
			// 	return false;
			// }		
		}
	}

}