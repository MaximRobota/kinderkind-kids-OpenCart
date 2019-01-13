<?php
/*
 *  location: admin/model
 */

class ModelExtensionDShopunityDeveloper extends Model {

	private $store_id = '';
    private $api = '';
    private $dir_root = '';

    public function __construct($registry){
        parent::__construct($registry);
        $this->api = new d_shopunity\API($registry);
        $this->store_id = $this->api->getStoreId();
        $this->dir_root = substr_replace(DIR_SYSTEM, '/', -8);
      
    }

    public function getExtensions($developer_id){

        $data = array(
            'shared' => true
        );

        $json = $this->api->get('developers/'.$developer_id.'/extensions', $data);

        if($json){
            foreach($json as $key => $value){
                $json[$key] = $this->_extension($value);
            }
        }

        return $json;
    }

    public function updateExtension($extension_id, $developer_id){

        $json = $this->api->post('developers/'.$developer_id.'/extensions/'.$extension_id.'/update');

        return $json;
    }


    public function _extension($data){
		$this->load->model('extension/d_shopunity/extension');
        return $this->model_extension_d_shopunity_extension->_extension($data);
    }


}