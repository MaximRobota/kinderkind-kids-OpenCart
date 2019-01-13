<?php
/*
 *  location: admin/model
 */

class ModelExtensionDShopunityCategory extends Model {

    private $store_id = '';
    private $api = '';
    private $dir_root = '';

    public function __construct($registry){
        parent::__construct($registry);
        $this->api = new d_shopunity\API($registry);
        $this->store_id = $this->api->getStoreId();
        $this->dir_root = substr_replace(DIR_SYSTEM, '/', -8);
      
    }

    public function getCategories($filter_data = array()){
        $json = $this->cache->get('d_shopunity.category.getCategories');

        if(!$json){
            $json = $this->api->get('categories', $filter_data);

            if($json){
                foreach($json as $key => $value){
                    $json[$key] = $this->_category($value);
                }  
            }
            $this->cache->set('d_shopunity.category.getCategories', $json);
        }

        return $json;
    }

    private function _category($data){
        $result = array();

        if(!empty($data)){
            $result = $data;
        }

        return $result;
    }
}