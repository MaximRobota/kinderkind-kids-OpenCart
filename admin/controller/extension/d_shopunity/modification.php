<?php
/*
 *  location: admin/controller
 */

class ControllerExtensionDShopunityModification extends Controller {

    private $codename = 'd_shopunity';
    private $route = 'extension/d_shopunity/modification';
    private $extension = array();


    public function index(){
        //nothing;
        return false;
    }

    public function install(){
        if(isset($this->request->get['codename'])){
            $codename = $this->request->get['codename'];
        }else{
            return false;
        }

        $this->load->model('extension/d_opencart_patch/modification');
        $this->model_extension_d_opencart_patch_modification->setModification($codename.'.xml', 0);
        $this->model_extension_d_opencart_patch_modification->setModification($codename.'.xml', 1);
        $this->model_extension_d_opencart_patch_modification->refreshCache();
        return true;
    }

    public function uninstall(){
        if(isset($this->request->get['codename'])){
            $codename = $this->request->get['codename'];
        }else{
            return false;
        }

        $this->load->model('extension/d_opencart_patch/modification');
        $this->model_extension_d_opencart_patch_modification->setModification($codename.'.xml', 0);
        $this->model_extension_d_opencart_patch_modification->refreshCache();
        return true;
    }

}