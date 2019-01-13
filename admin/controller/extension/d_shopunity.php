<?php
/*
 *  location: admin/controller
 */

class ControllerExtensionDShopunity extends Controller {
    

    private $codename = 'd_shopunity';
    private $route = 'extension/d_shopunity';
    private $extension = array();
    private $store_id = 0;
    private $url_token = '';
    //private $config_file = '';

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('extension/d_shopunity/mbooth');
        $this->load->model('extension/d_shopunity/account');
        
        //$this->load->model('extension/d_shopunity/config');

        //extension.json
        $this->extension = $this->model_extension_d_shopunity_mbooth->getExtension($this->codename);

        //Store_id (for multistore)
        if (isset($this->request->get['store_id'])) { 
            $this->store_id = $this->request->get['store_id']; 
        }
        
        //token
        $this->load->model('extension/d_shopunity/setting');
        $this->url_token = $this->model_extension_d_shopunity_setting->getUrlToken();

        //Check if all dependencies are installed
        $this->model_extension_d_shopunity_mbooth->installDependencies($this->codename);
    }

    public function index(){
        if(!$this->model_extension_d_shopunity_account->isLogged()){
            if($this->install230()){
                return true;
            }
            $this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
        }

        $this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
    }

    public function content_top(){

        $this->load->language('extension/module/d_shopunity');

        //documentation http://t4t5.github.io/sweetalert/
        $this->document->addStyle('view/javascript/d_shopunity/library/sweetalert/sweetalert.css');
        $this->document->addScript('view/javascript/d_shopunity/library/sweetalert/sweetalert.min.js');
        $this->document->addStyle('view/javascript/d_shopunity/library/syntaxhighlight/syntaxhighlight.css');
        $this->document->addScript('view/javascript/d_shopunity/library/syntaxhighlight/syntaxhighlight.js');

        $this->document->addStyle('view/stylesheet/shopunity/bootstrap.css');
        $this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity.css');
        $this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity_layout.css');
        $this->document->addScript('view/javascript/d_shopunity/d_shopunity.js');

        // Breadcrumbs
        $data['breadcrumbs'] = array(); 
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', $this->url_token, 'SSL')
            );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('extension/module', $this->url_token, 'SSL')
            );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->route, $this->url_token, 'SSL')
            );

        if(!empty($this->session->data['error'])){
            $data['error'] = $this->session->data['error'];
            unset($this->session->data['error']);
        }

        if(!empty($this->session->data['success'])){
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        if (!extension_loaded('zip')) { 
            $data['error'] = $this->language->get('error_no_zip_extension');
        }

        $this->document->setTitle($this->language->get('heading_title_main'));
        $data['heading_title'] = $this->language->get('heading_title_main');
        $data['version'] = $this->model_extension_d_shopunity_mbooth->getVersion($this->codename);
        $data['route'] = $this->request->get['route'];
        //language
        $data['tab_extension'] =  $this->language->get('tab_extension');
        $data['tab_market'] =  $this->language->get('tab_market');
        $data['tab_billing'] =  $this->language->get('tab_billing');
        $data['tab_backup'] =  $this->language->get('tab_backup');
        $data['tab_setting'] =  $this->language->get('tab_setting');
        $data['tab_tester'] =  $this->language->get('tab_tester');
        $data['tab_developer'] =  $this->language->get('tab_developer');

        $data['href_extension'] =  $this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL');
        $data['href_market'] =  $this->url->link('extension/d_shopunity/market', $this->url_token, 'SSL');
        $data['href_billing'] =  $this->url->link('extension/d_shopunity/order', $this->url_token, 'SSL');
        $data['href_backup'] = $this->url->link('extension/d_shopunity/backup', $this->url_token, 'SSL');
        $data['href_setting'] = $this->url->link('extension/d_shopunity/setting', $this->url_token, 'SSL');
        $data['href_tester'] = $this->url->link('extension/d_shopunity/tester', $this->url_token, 'SSL');
        $data['href_developer'] = $this->url->link('extension/d_shopunity/developer', $this->url_token, 'SSL');

        $data['button_logout'] =  $this->language->get('button_logout');
        $data['logout'] = $this->url->link('extension/d_shopunity/account/logout', $this->url_token, 'SSL');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $this->load->model('extension/d_opencart_patch/url');
        $data['cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionLink('module');
        $account = $this->config->get('d_shopunity_account');

        $data['tester'] = false;
        if(!empty($account['tester'])){
            $data['tester'] = true;
        }
        $data['developer'] = false;
        if(!empty($account['developer'])){
            $data['developer'] = true;
        }
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');

        return $this->load->view('extension/d_shopunity/content_top', $data);
    }

    public function content_bottom(){

        $data['purchase_url'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/purchase', $this->url_token, 'SSL')); 

        $data['footer'] = $this->load->controller('common/footer');
        return $this->load->view('extension/d_shopunity/content_bottom', $data);
    }


    private function validate($permission = 'modify') {

        if (isset($this->request->post['config'])) {
            return false;
        }

        $this->language->load('extension/module/d_shopunity');
        
        if (!$this->user->hasPermission($permission, 'extension/d_shopunity')) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }
        return true;
    }

    public function install230(){
        $this->load->model('extension/d_shopunity/ocmod');
        $compatibility = $this->model_extension_d_shopunity_ocmod->getModificationByName('d_opencart_patch');
        if(VERSION >= '2.3.0.0' && VERSION < '3.0.0.0' && !$compatibility ){
            $this->install();
            return true;
        }
        return false;
    }

    public function install() {

        $this->load->model('extension/d_shopunity/ocmod');
        $this->model_extension_d_shopunity_ocmod->setOcmod('d_shopunity.xml', 0);
        $this->model_extension_d_shopunity_ocmod->setOcmod('d_shopunity.xml', 1);

        $this->load->model('extension/d_shopunity/mbooth');
        $this->model_extension_d_shopunity_mbooth->installDependencies($this->codename);

        $this->model_extension_d_shopunity_ocmod->setOcmod('d_opencart_patch.xml', 0);
        $this->model_extension_d_shopunity_ocmod->setOcmod('d_opencart_patch.xml', 1);

        $this->load->model('extension/d_shopunity/setting');
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/account');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/account');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/developer');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/developer');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/extension');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/extension');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/market');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/market');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/backup');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/backup');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/order');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/order');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/invoice');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/invoice');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/transaction');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/transaction');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/setting');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/setting');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/tester');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/tester');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/dependency');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/dependency');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'access', 'extension/d_shopunity/filemanager');
        $this->model_user_user_group->addPermission($this->model_extension_d_shopunity_setting->getGroupId(), 'modify', 'extension/d_shopunity/filemanager');

        $this->model_extension_d_shopunity_ocmod->refreshCache();
    }

    public function uninstall() {
        $this->load->model('extension/d_shopunity/ocmod');
        $this->model_extension_d_shopunity_ocmod->setOcmod('d_shopunity.xml', 0);
        $this->model_extension_d_shopunity_ocmod->setOcmod('d_opencart_patch.xml', 0);
        //$this->model_extension_d_shopunity_ocmod->refreshCache();
    }
}
?>