<?php
/*
 *	location: admin/controller
 */

class ControllerExtensionDShopunityExtension extends Controller {

	private $codename = 'd_shopunity';
	private $route = 'extension/d_shopunity/extension';
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

		// FB::log('model_extension_d_shopunity_extension->getExtensions:');

		if(!$this->model_extension_d_shopunity_account->isLogged()){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		$this->document->addScript('view/javascript/d_shopunity/library/list/list.min.js');
		$this->document->addScript('view/javascript/d_shopunity/library/list/list.fuzzysearch.min.js');

   		$this->load->model('extension/d_shopunity/extension');

   		$data['extensions'] = false;
   		if(isset($this->session->data['welcome_extensions'])){
   			$filter_data = $this->session->data['welcome_extensions'];
   		
			$data['extensions'] = $this->model_extension_d_shopunity_extension->getExtensions($filter_data);
			unset($this->session->data['welcome_extensions']);
   		}
   		$data['href_market'] =  $this->url->link('extension/d_shopunity/market', $this->url_token, 'SSL');
		
		$data['store_extensions'] = $this->model_extension_d_shopunity_extension->getStoreExtensions();
		$data['local_extensions'] = $this->model_extension_d_shopunity_extension->getLocalExtensions('module');
		$data['unregestered_extensions'] = $this->model_extension_d_shopunity_extension->getUnregisteredExtensions();
        $data['library_extensions_count'] = $this->model_extension_d_shopunity_extension->getLibraryExtensionsCount();

        $data['load_libraries'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/load_libraries', $this->url_token, 'SSL')); 
		$data['developer_generate_module'] = $this->load->controller('extension/d_shopunity/developer/generate_module');
		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');
   		$data = $this->_productThumb($data);
   		$this->response->setOutput($this->load->view('extension/d_shopunity/extension', $data));
	}

	public function item(){


   		//is logged in
		if(!$this->model_extension_d_shopunity_account->isLogged()){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}
		//extension id provided
		if(!isset($this->request->get['extension_id'])){
			$this->session->data['error'] = $this->language->get('error_extension_not_found');
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
		}

		$extension_id = $this->request->get['extension_id'];

		$this->load->model('extension/d_shopunity/store');
   		$this->load->model('extension/d_shopunity/extension');

   		$this->load->language('extension/module/d_shopunity');
   		$this->load->language('extension/d_shopunity/extension');


		$data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension_id);

		if(isset($data['extension']['developer'])){
			$data['developer'] = $this->load->controller('extension/d_shopunity/developer/profile', $data['extension']['developer']);
		}else{
			$data['developer'] = '';
		}

		//$extension_recurring_price_id = (isset($data['extension']['price'])) ? $data['extension']['price']['extension_recurring_price_id'] : 0;

		$data['purchase'] = $this->url->link('extension/d_shopunity/extension/purchase', $this->url_token . '&extension_id=' . $extension_id , 'SSL');
		$data['install'] = $this->url->link('extension/d_shopunity/extension/install', $this->url_token . '&extension_id=' . $extension_id , 'SSL');

   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');
   		$data = $this->_productThumb($data);
   		$this->response->setOutput($this->load->view($this->route.'_item', $data));
	}

    public function thumb($extension_id){
        $this->load->model('extension/d_shopunity/extension');
        //documentation http://t4t5.github.io/sweetalert/
        $this->document->addStyle('view/javascript/d_shopunity/library/sweetalert/sweetalert.css');
        $this->document->addScript('view/javascript/d_shopunity/library/sweetalert/sweetalert.min.js');
        $this->document->addStyle('view/javascript/d_shopunity/library/syntaxhighlight/syntaxhighlight.css');
        $this->document->addScript('view/javascript/d_shopunity/library/syntaxhighlight/syntaxhighlight.js');

        $this->document->addStyle('view/stylesheet/shopunity/bootstrap.css');
        $this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity.css');
        $this->document->addScript('view/javascript/d_shopunity/d_shopunity.js');

        $data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
        return $this->load->view('extension/d_shopunity/extension_thumb', $data);
    }

    public function load_libraries(){
        $data = array();
        $this->load->model('extension/d_shopunity/extension');
        $data['extensions'] = $this->model_extension_d_shopunity_extension->getLocalExtensions('library');
        $data = $this->_productThumb($data);
        $this->response->setOutput($this->load->view('extension/d_shopunity/extension_show_thumb_row', $data));

    }

    public function show_thumb(){
        if(isset($this->request->get['extension_id'])){
            $extension_id = $this->request->get['extension_id'];
            $data['extension'] = $this->thumb($extension_id);
            $data['purchase_url'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/purchase', $this->url_token, 'SSL')); 
            $data['links'] = $this->document->getLinks();
            $data['styles'] = $this->document->getStyles();
            $data['scripts'] = $this->document->getScripts();
            
            $this->response->setOutput($this->load->view('extension/d_shopunity/extension_show_thumb', $data));
        }
    }

    public function show_update(){
        if(isset($this->request->get['extension_id'])){
            $extension_id = $this->request->get['extension_id'];
            $this->load->model('extension/d_shopunity/extension');
            //documentation http://t4t5.github.io/sweetalert/
            $this->document->addStyle('view/javascript/d_shopunity/library/sweetalert/sweetalert.css');
            $this->document->addScript('view/javascript/d_shopunity/library/sweetalert/sweetalert.min.js');
            $this->document->addStyle('view/javascript/d_shopunity/library/syntaxhighlight/syntaxhighlight.css');
            $this->document->addScript('view/javascript/d_shopunity/library/syntaxhighlight/syntaxhighlight.js');

            $this->document->addStyle('view/stylesheet/shopunity/bootstrap.css');
            $this->document->addStyle('view/stylesheet/d_shopunity/d_shopunity.css');
            $this->document->addScript('view/javascript/d_shopunity/d_shopunity.js');

            $data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
            $semver = new Semver;
            $data['extension']['changelog'] = array();
            if(isset($data['extension']['mbooth']['changelog'])){
                foreach($data['extension']['mbooth']['changelog'] as $changelog){
                    if(isset($changelog['version']) && isset($changelog['change']) && $semver->gt($changelog['version'], $data['extension']['current_version'])){
                        $data['extension']['changelog'][] = array(
                            'version' => $changelog['version'],
                            'change' => $changelog['change']
                        );
                    }

                }
            }
            
            $data['purchase_url'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/purchase', $this->url_token, 'SSL')); 
            $data['links'] = $this->document->getLinks();
            $data['styles'] = $this->document->getStyles();
            $data['scripts'] = $this->document->getScripts();
            
            $this->response->setOutput($this->load->view('extension/d_shopunity/extension_show_update', $data));
        }
    }

    

	public function dependency(){

		if(!$this->model_extension_d_shopunity_account->isLogged()){
			$this->response->redirect($this->url->link('extension/d_shopunity/account/login', $this->url_token, 'SSL'));
		}

		if($this->request->get['codename']){
			$codename = $this->request->get['codename'];
		}else{
			$this->session->data['error'] = 'Codename missing. Can not get Dependencies!';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
		}

   		$this->load->model('extension/d_shopunity/extension');

		$required = $this->model_extension_d_shopunity_mbooth->getDependencies($codename);
		$filter_data['codename'] = array();
		foreach($required as $require){
			$filter_data['codename'][$require['codename']] = $require['codename'];
		}

		$data['extensions'] = $this->model_extension_d_shopunity_extension->getExtensions($filter_data);

		foreach($data['extensions'] as $extension){
			unset($filter_data['codename'][$extension['codename']]);
		}

		$data['unregistered_extensions'] = array();
		foreach($filter_data['codename'] as $filter_codename){
			$data['unregistered_extensions'][] = array(
				'codename' => $filter_codename,
				'name' => $filter_codename
				);
		}

		$data['profile'] = $this->load->controller('extension/d_shopunity/account/profile');
   		$data['content_top'] = $this->load->controller('extension/d_shopunity/content_top');
   		$data['content_bottom'] = $this->load->controller('extension/d_shopunity/content_bottom');
   		$data = $this->_productThumb($data);
   		$this->response->setOutput($this->load->view($this->route.'_dependency', $data));
	}

	public function purchase(){
		if(!isset($this->request->get['extension_id'])){
			$this->session->data['error'] = 'Error! extension_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		if(!isset($this->request->get['extension_recurring_price_id'])){
			$this->session->data['error'] = 'Error! extension_recurring_price_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$extension_id = $this->request->get['extension_id'];
		$extension_recurring_price_id = $this->request->get['extension_recurring_price_id'];
		$this->load->model('extension/d_shopunity/extension');

		$purchase = $this->model_extension_d_shopunity_extension->purchaseExtension($extension_id, $extension_recurring_price_id);

		if(!empty($purchase['error'])){
			$this->session->data['error'] = $purchase['error'];

		}elseif(!empty($purchase['success'])){
			$this->session->data['success'] = $purchase['success'];

			//create an invoice
			$this->load->model('extension/d_shopunity/billing');
	   		$result = $this->model_extension_d_shopunity_billing->addInvoice();

			if(!empty($result['error'])){
				$this->session->data['error'] = $result['error'];
			}elseif(!empty($result['invoice_id'])){
				$this->session->data['success'] = $result['success'];
                $invoice_id = $result['invoice_id'];

                if(!empty($this->request->get['voucher_id'])){
                    //add voucher to invoice is avalible.
                    $result = $this->model_extension_d_shopunity_billing->applyVoucher($this->request->get['voucher_id'], $invoice_id);
                }
				//make a purchase
		   		$invoice = $this->model_extension_d_shopunity_billing->payInvoice($invoice_id);

		   		if(!empty($invoice['error'])){
					$this->session->data['error'] = $invoice['error'];
				}elseif(!empty($invoice['success'])){
					$this->session->data['success'] = $invoice['success'];
				}
			}
		}

		$this->response->redirect($this->url->link('extension/d_shopunity/extension/item', $this->url_token . '&extension_id=' . $extension_id , 'SSL'));

	}

    public function popup_purchase(){
        try{
            if(!$this->model_extension_d_shopunity_account->isLogged()){
                throw new Exception('Error! you are not logged in');
            }

            if(!isset($this->request->get['extension_id'])){
                throw new Exception('Error! extension_id missing');
            }

            if(!isset($this->request->get['extension_recurring_price_id'])){
                throw new Exception('Error! extension_recurring_price_id missing');
            }

            $extension_id = $this->request->get['extension_id'];
            $data = array();
            $data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension_id);

            $data['account'] = $this->model_extension_d_shopunity_account->getAccount();

            $data['add_money'] = 'https://shopunity.net/index.php?route=billing/transaction';
            $data['claim_external_order'] =  str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/claim_external_order', $this->url_token . '&extension_id='.$extension_id, 'SSL')); 

            $json['content'] = $this->load->view($this->route.'_popup_purchase', $data);
        }catch(Exception $e){
            $json['error'] = $e->getMessage();
        }

        $this->response->setOutput(json_encode($json));

    }

    public function claim_external_order(){
        $json = array();
        try{
            if(!$this->model_extension_d_shopunity_account->isLogged()){
                throw new Exception('Error! you are not logged in');
            }

            if(!isset($this->request->post['market'])){
                throw new Exception('Error! market missing');
            }

            if(!isset($this->request->post['user_id'])){
                throw new Exception('Error! user_id missing');
            }

            if(!isset($this->request->post['order_id'])){
                throw new Exception('Error! order_id missing');
            }

            $data = array(
                'market' => $this->request->post['market'],
                'user_id' => $this->request->post['user_id'],
                'order_id' => $this->request->post['order_id']
            );
            $this->load->model('extension/d_shopunity/billing');
            $voucher = $this->model_extension_d_shopunity_billing->claimExternalOrder($data);

            if(!empty($voucher['error'])){
                $system_message = (isset($voucher['errors'][0]['system_message'])) ? $voucher['errors'][0]['system_message'] : '';
                throw new Exception('Error! '.$voucher['errors'][0]['message'] .'. '. $system_message);
            }

            if(!empty($voucher['extension_id'])){
                $json['extension'] = $this->model_extension_d_shopunity_extension->getExtension($voucher['extension_id']);
                $json['voucher'] = $voucher;
                $json['text'] = 'You have reclaimed your voucher for '.$json['extension']['name'].' for a period of ' . $voucher['recurring_duration'] .'days.';

                $extension_recurring_price_id = false;
                foreach($json['extension']['prices'] as $price){
                    if($price['recurring_duration'] == $voucher['recurring_duration']){
                        $extension_recurring_price_id = $price['extension_recurring_price_id'];
                    }
                }

                if(!$extension_recurring_price_id){
                    throw new Exception('Error! extension_recurring_price_id for this voucher is not available');
                }else{

                    if(isset($this->request->get['invoice_id'])){
                        $json['apply'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/invoice/pay', $this->url_token . '&extension_id=' . $voucher['extension_id'] . '&extension_recurring_price_id='. $extension_recurring_price_id . '&voucher_id='. $voucher['voucher_id'].'&invoice_id=' . $this->request->get['invoice_id'] , 'SSL'));
                    }else{
                        $json['apply'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/purchase', $this->url_token . '&extension_id=' . $voucher['extension_id'] . '&extension_recurring_price_id='. $extension_recurring_price_id . '&voucher_id='. $voucher['voucher_id'] , 'SSL'));
                    }
                    
                    $json['success'] = true;
                }

                

            }else{
                throw new Exception('Error! extension_id missing');
            }
        }catch(Exception $e){

            $json['error'] = $e->getMessage();
        }

        $this->response->setOutput(json_encode($json));
    }

	public function popup(){
		try{
			$action = 'install';
			if(isset($this->request->get['action'])){
				$action = $this->request->get['action'];
			}

			if(!$this->model_extension_d_shopunity_account->isLogged()){
				throw new Exception('Error! you are not logged in');
			}

			$this->load->language('extension/d_shopunity/extension');
			$this->load->model('extension/d_shopunity/extension');

			if(!isset($this->request->get['extension_id'])){
				throw new Exception('Error! extension_id missing');
			}

			$theme = 'extension_thumb';
			if(isset($this->request->get['theme'])){
				$theme = $this->request->get['theme'];
			}

			$extension_id = $this->request->get['extension_id'];

			if($action == 'test'){
				if(!isset($this->request->get['extension_download_link_id'])){
					throw new Exception('Error! extension_download_link_id missing');
				}

				$extension_download_link_id = $this->request->get['extension_download_link_id'];

				$account = $this->config->get('d_shopunity_account');

				if(empty($account['tester']['tester_id'])){
					throw new Exception('Error! you need to be a tester to test an extension');
				}

				$tester_id = $account['tester']['tester_id'];

				$data['extension'] = $this->model_extension_d_shopunity_extension->getTestableExtension($tester_id, $extension_id, $extension_download_link_id);

			}else{
				$data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
			}

			if(empty($data['extension'])){
				throw new Exception('Error! extension not found');
			}

			$data['action'] = $data['extension'][$action].'&theme='.$theme;
			$data['admin'] = false;
			if(isset($data['extension']['admin'])){
                $data['admin'] = $data['extension']['admin'];
            }

            if(!empty($data['extension']['mbooth']['index'])){
            	$data['admin'] = $this->url->link($data['extension']['mbooth']['index'], $this->url_token , 'SSL');
            }
			$data['mbooth'] = json_encode($data['extension']['mbooth']);

			if(!empty($data['extension']['required'])){
				$filter_data = array();
				foreach($data['extension']['required'] as $codename => $version){
					$filter_data['codename'][] = $codename;
				}
				$data['extension']['required'] = $this->model_extension_d_shopunity_extension->getExtensions($filter_data);
			}

			$json['content'] = $this->load->view($this->route.'_popup', $data);

		}catch(Exception $e){
			$json['error'] = $e->getMessage();
		}

		$this->response->setOutput(json_encode($json));
	}



	public function install(){

		$this->start_sse();
		$json = array();
		$json['installed'] = false;

		try{
			if(!$this->model_extension_d_shopunity_account->isLogged()){
				throw new Exception('You are not logged in');
			}

			if(!isset($this->request->get['extension_id'])){
				throw new Exception('Error! extension_id missing');
			}

			$extension_id = $this->request->get['extension_id'];

			$this->load->model('extension/d_shopunity/extension');
			$this->load->model('extension/d_shopunity/mbooth');

			$extension = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
			$this->_send('Get extension data ');

			if(isset($this->request->get['extension_download_link_id'])){
				$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByDownloadLinkId($extension_id, $this->request->get['extension_download_link_id']);
			}else{
				$download = $this->model_extension_d_shopunity_extension->getExtensionDownload($extension_id);
			}

			$result = $this->_install($download);

			if(!empty($result['success'])) {

				if(!empty($extension['required'])){
					$this->_send('installing Dependencies ...');
					foreach($extension['required'] as $codename => $version){

						if($this->model_extension_d_shopunity_mbooth->needUpdate($codename, $version)){
							$this->_send('installing: ' . $codename . ' ' . $version);
							$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByCodename($codename, $version);
							$this->_install($download);
							$this->_installed($codename);
						}else{
							$this->_send('Extension ' . $codename . ' is up to date (' . $version .')');
						}
					}
					$this->_send('dependencies installed ');
				}

				$this->_installed($extension['codename'], $this->_productThumbView($extension));
				$this->_activate($extension['codename']);
			}

			if(!empty($result['error'])) {
				$this->_send($this->language->get('error_install'));
			}

		}catch(Exception $e){
			$this->_send($e->getMessage());
		}

		$this->stop_sse();
	}

	public function update(){

		$this->start_sse();
		$json = array();
		$json['installed'] = false;

		try{
			if(!$this->model_extension_d_shopunity_account->isLogged()){
				throw new Exception('You are not logged in');
			}

			if(!isset($this->request->get['extension_id'])){
				throw new Exception('Error! extension_id missing');
			}

			$extension_id = $this->request->get['extension_id'];

			$this->load->model('extension/d_shopunity/extension');
			$this->load->model('extension/d_shopunity/mbooth');

			$extension = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
			$this->_send('Get extension data ' . json_decode($extension));

			if(isset($this->request->get['extension_download_link_id'])){
				$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByDownloadLinkId($extension_id, $this->request->get['extension_download_link_id']);
			}else{
				$download = $this->model_extension_d_shopunity_extension->getExtensionDownload($extension_id);
			}

			$result = $this->_install($download);


			if(!empty($result['success'])) {


				if(!empty($extension['required'])){
					$this->_send('installing Dependencies ...');
					foreach($extension['required'] as $codename => $version){

						if($this->model_extension_d_shopunity_mbooth->needUpdate($codename, $version)){
							$this->_send('installing: ' . $codename . ' ' . $version);
							$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByCodename($codename, $version);
							$this->_install($download);
							$this->_installed($codename);
						}else{
							$this->_send('Extension ' . $codename . ' is up to date (' . $version .')');
						}
					}
					$this->_send('dependencies installed ');
				}

				$this->_installed($extension['codename'], $this->_productThumbView($extension));
			}



			if(!empty($result['error'])) {
				$this->_send($this->language->get('error_install'));
			}

		}catch(Exception $e){
			$this->_send($e->getMessage());
		}

		$this->stop_sse();
	}


	public function test(){

		$this->start_sse();
		$json = array();
		$json['installed'] = false;

		try{
			if(!$this->model_extension_d_shopunity_account->isLogged()){
				throw new Exception('You are not logged in');
			}

			if(!isset($this->request->get['extension_download_link_id'])){
				throw new Exception('Error! extension_download_link_id missing');
			}

			if(!isset($this->request->get['extension_id'])){
				throw new Exception('Error! extension_id missing');
			}

			$account = $this->config->get('d_shopunity_account');

			if(empty($account['tester'])){
				throw new Exception('Error! you must be a tester');
			}

			$tester_id = $account['tester']['tester_id'];
			$extension_download_link_id = $this->request->get['extension_download_link_id'];
			$extension_id = $this->request->get['extension_id'];

			$this->load->model('extension/d_shopunity/extension');
			$this->load->model('extension/d_shopunity/mbooth');

			$extension = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
			$this->_send('Get extension data ' . json_encode($extension));

			$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByDownloadLinkIdForTesting($extension_id, $extension_download_link_id);

			$result = $this->_install($download);

			if(!empty($result['success'])) {

				if(!empty($extension['required'])){
					$this->_send('installing Dependencies ...');
					foreach($extension['required'] as $codename => $version){

						if($this->model_extension_d_shopunity_mbooth->needUpdate($codename, $version)){
							$this->_send('installing: ' . $codename . ' ' . $version);
							$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByCodename($codename, $version);
							$this->_install($download);
							$this->_installed($codename);
						}else{
							$this->_send('Extension ' . $codename . ' is up to date (' . $version .')');
						}
					}
					$this->_send('dependencies installed ');
				}

				$this->_installed($extension['codename'], $this->_productThumbView($extension));
				$this->_activate($extension['codename']);

			}

			if(!empty($result['error'])) {
				//$json['error'] = $this->language->get('error_install') . "<br />" . implode("<br />", $result['error']);
				$this->_send($this->language->get('error_install'));
			}

		}catch(Exception $e){
			// $json['error'] = $e->getMessage();
			$this->_send($e->getMessage());
		}

		$this->stop_sse();
		//$this->response->setOutput(json_encode($json));
	}




	public function uninstall(){
		$json = array();
		$json['uninstalled'] = false;

		if(!isset($this->request->get['codename'])){
			$json['error'] = 'Error! codename missing';
			$json['redirect'] =  str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
		}

		//if(empty($json['error'])){
			$codename = $this->request->get['codename'];
			$this->load->model('extension/d_shopunity/mbooth');

			//$result = $this->model_extension_d_shopunity_mbooth->deactivateExtension($codename);
		//}

		if(empty($result['error'])){
			$result = $this->model_extension_d_shopunity_mbooth->deleteExtension($codename);
		}

		if(!empty($result['error'])) {
			$json['error'] = $this->language->get('error_delete') . "<br />" . implode("<br />", $result['error']);
		}

		if(!empty($result['success'])) {
			$json['uninstalled'] = true;
			$json['text'] = "Extension ".$codename." has been successfuly uninstalled";

			if(isset($this->request->get['extension_id'])){
				$this->load->model('extension/d_shopunity/extension');
				$extension_id = $this->request->get['extension_id'];

				$json['codename'] = $codename;
				$json['view'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/item', $this->url_token . '&extension_id=' . $extension_id , 'SSL'));

				$data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($this->request->get['extension_id']);
				$theme = 'extension_thumb';
				if(isset($this->request->get['theme'])){
					$theme = $this->request->get['theme'];
				}
				$data = $this->_productThumb($data);
				$json['extension'] = $this->load->view('extension/d_shopunity/'.$theme.'', $data);
			}

			$json['success'] = 'Extension #' . $codename .' uninstalled';
			$json['success'] .=  "<br />" . implode("<br />", $result['success']);

		}
		$this->response->setOutput(json_encode($json));

	}

	public function download(){
		$this->load->language('extension/d_shopunity/extension');
		if(!isset($this->request->get['codename'])){
			$this->session->data['error'] = 'Error! codename missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$this->load->model('extension/d_shopunity/extension');
		$this->load->model('extension/d_shopunity/mbooth');

		$mbooth = $this->model_extension_d_shopunity_mbooth->getExtension($this->request->get['codename']);

		if(empty($mbooth)){
			$this->session->data['error'] = 'Error! extension with this codename does not exist';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$result = $this->model_extension_d_shopunity_mbooth->downloadExtension($this->request->get['codename']);

		if(!empty($result['error'])) {
			$this->session->data['error'] = $this->language->get('error_download') . "<br />" . implode("<br />", $result['error']);
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
		}

	}

	public function suspend(){
		if(!isset($this->request->get['store_extension_id'])){
			$this->session->data['error'] = 'Error! store_extension_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}

		$this->load->model('extension/d_shopunity/extension');
		$purchase = $this->model_extension_d_shopunity_extension->suspendExtension($this->request->get['store_extension_id']);

		if(!empty($purchase['error'])){
			$this->session->data['error'] = $purchase['error'];
		}elseif(!empty($purchase['success'])){
			$this->session->data['success'] = $purchase['success'];
		}

		$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
	}


	public function submit(){
		if(!isset($this->request->get['extension_id'])){
			$this->session->data['error'] = 'Error! extension_id missing';
			$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
		}
		try{
			$this->load->model('extension/d_shopunity/extension');
			$result = $this->model_extension_d_shopunity_extension->submitExtension($this->request->get['extension_id']);

			if(!empty($result['error'])){
				$this->session->data['error'] = $result['error'];
			}elseif(!empty($result['success'])){
				$this->session->data['success'] = $result['success'];
			}
		}catch(Exception $e){
			$this->session->data['error'] = $e->getMessage();
		}

		$this->response->redirect($this->url->link('extension/d_shopunity/extension', $this->url_token, 'SSL'));
	}

	public function json(){
		$this->load->language('extension/extension/d_shopunity/extension');
		$codename = false;
		if(isset($this->request->get['codename'])){
			$codename = $this->request->get['codename'];
		}
		$extension_id = false;
		if(isset($this->request->get['extension_id'])){
			$extension_id = $this->request->get['extension_id'];
		}

		$this->model_extension_d_shopunity_account->isLogged();
		$tester_id = false;
		$account = $this->config->get('d_shopunity_account');
		if(!empty($account['tester']['tester_id'] )){
			$tester_id = $account['tester']['tester_id'];
		}

		$extension_download_link_id = false;
		if(isset($this->request->get['extension_download_link_id'])){
			$extension_download_link_id = $this->request->get['extension_download_link_id'];
		}

		if($codename || $extension_id){
			try{

				if($codename){
					$this->load->model('extension/d_shopunity/mbooth');
					$json = $this->model_extension_d_shopunity_mbooth->getExtensionJson($codename);
				}

				if(empty($json) && $extension_id){
					$this->load->model('extension/d_shopunity/extension');

					if($tester_id && $extension_download_link_id){
						$extension = $this->model_extension_d_shopunity_extension->getTestableExtension($tester_id, $extension_id, $extension_download_link_id);
					}else{
						$extension = $this->model_extension_d_shopunity_extension->getExtension($extension_id);
					}

					if(!empty($extension['mbooth'])){
						$json = $extension['mbooth'];
					}
				}

				if(empty($json)){
					$json['error'] = 'Error! extension.json not found';
				}
			}catch(Exception $e){
				$json['error'] = $e->getMessage();
			}
		}else{
			$json['error'] = 'Error! codename or extension_id is required';
		}


		$this->response->setOutput(json_encode($json));
	}

    public function install_ocmod(){
        if(isset($this->request->get['codename'])){
            $codename = $this->request->get['codename'];
        }else{
            return false;
        }

        $this->load->model('extension/d_shopunity/ocmod');
        $this->model_extension_d_shopunity_ocmod->setOcmod($codename.'.xml', 1);
        $this->model_extension_d_shopunity_ocmod->refreshCache();
        return true;
    }

    public function uninstall_ocmod(){
        if(isset($this->request->get['codename'])){
            $codename = $this->request->get['codename'];
        }else{
            return false;
        }

        $this->load->model('extension/d_shopunity/ocmod');
        $this->model_extension_d_shopunity_ocmod->setOcmod($codename.'.xml', 0);
        $this->model_extension_d_shopunity_ocmod->refreshCache();
        return true;
    }

	public function _productThumb($data){

		$this->load->language('extension/d_shopunity/extension');
		$data['text_tester_status_1'] = $this->language->get('text_tester_status_1');
   		$data['text_tester_status_2'] = $this->language->get('text_tester_status_2');
   		$data['text_tester_status_3'] = $this->language->get('text_tester_status_3');
   		$data['text_tester_status_4'] = $this->language->get('text_tester_status_4');
   		$data['text_tester_status_5'] = $this->language->get('text_tester_status_5');
   		$data['text_tester_status_6'] = $this->language->get('text_tester_status_6');
   		$data['text_new_version_available'] = $this->language->get('text_new_version_available');

		return $data;
	}

	public function _productThumbView($extension){
		$this->load->model('extension/d_shopunity/extension');
		$data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension['extension_id']);
		$this->load->language('extension/d_shopunity/extension');
		$theme = 'extension_thumb';
		if(isset($this->request->get['theme'])){
			$theme = $this->request->get['theme'];
		}

		$data = $this->_productThumb($data);
		return $this->load->view('extension/d_shopunity/'.$theme.'', $data);
	}

	public function start_sse() {
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
	}

	public function stop_sse() {
		//header('Content-Type: text/text');
		echo "id: " . time() . PHP_EOL;
		echo "event: error" . PHP_EOL;
		echo "data: STOP" . PHP_EOL;
		echo PHP_EOL;
  		flush();
	}

	public function _send($msg) {
		echo "id: ".  time() . PHP_EOL;
		echo "data: {\"message\":\"$msg\"}" . PHP_EOL;
		echo PHP_EOL;
  		flush();
	}

	public function _activate($codename){
		echo "id: ".  time() . PHP_EOL;
		echo "data: {\"activate\":\"$codename\", \"message\":\"Activating $codename\"}" . PHP_EOL;
		echo PHP_EOL;
  		flush();
	}
	public function _installed($codename, $thumb = ''){

		$message = array(
			"installed" => $codename,
			"message" => "Installed $codename",
			"thumb" => $thumb
		);

		echo "id: ".  time() . PHP_EOL;
		echo "data: ". json_encode($message) . PHP_EOL;
		echo PHP_EOL;
  		flush();
	}

	public function _install($download){
		$this->load->model('extension/d_shopunity/extension');
		$this->load->model('extension/d_shopunity/mbooth');
		$result = array();

		if(!empty($download['errors'])){
			throw new Exception('Error! We cound not get the download link: '.json_encode($download));
		}

		if(empty($download['download'])){
			throw new Exception('Error! The download link is empty: '.$download);
		}

		$this->_send('we got extension download url');

		$error_download = json_decode(file_get_contents($download['download']),true);

		if(isset($error_download['errors'])){
			throw new Exception('Error! getExtensionDownload failed: '.json_encode($error_download));
		}

		$this->_send('we are going to install the extension');

		//start testing
		//download the extension to system/mbooth/download
		$extension_zip = $this->model_extension_d_shopunity_mbooth->downloadExtensionFromServer($download['download']);
		if(is_array($extension_zip) && isset($extension_zip['errors'])){
			throw new Exception('Error! downloadExtensionFromServer failed: '.json_encode($extension_zip));
		}

		$this->_send('extension downloaded from server');

		//unzip the downloaded file to system/mbooth/download and remove the zip file
		$extracted = $this->model_extension_d_shopunity_mbooth->extractExtension($extension_zip);
		if(is_array($extracted) && isset($extracted['errors'])){
			throw new Exception('Error! extractExtension failed: ' .json_encode($extracted) . ' download from '.$download['download']);
		}

		$this->_send('extension extrected');


		//BACKUP REFACTOR
		// if(file_exists(DIR_SYSTEM . 'mbooth/xml/'.$this->request->post['mbooth'])){
		// 	$result = $this->model_module_mbooth->backup_files_by_mbooth($this->request->post['mbooth'], 'update');
		// }

		$result = $this->model_extension_d_shopunity_mbooth->installExtension($result);

		$this->_send('extension installed');

		return $result;
	}


	// public function install(){
	// 	$json = array();
	// 	$json['installed'] = false;
	// 	if(!isset($this->request->get['extension_id'])){
	// 		$json['error'] = 'Error! extension_id missing';
	// 		$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
	// 	}

	// 	$extension_id = $this->request->get['extension_id'];
	// 	$this->load->model('extension/d_shopunity/extension');
	// 	$this->load->model('extension/d_shopunity/mbooth');

	// 	try{

	// 		$extension = $this->model_extension_d_shopunity_extension->getExtension($extension_id);

	// 		if(!$extension){
	// 			$json['error'] = 'Error! this extension was not found on shopunity: '.$download['error'];
	// 			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/item', $this->url_token . '&extension_id='.$extension_id , 'SSL'));
	// 		}

	// 		if(isset($this->request->get['extension_download_link_id'])){
	// 			$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByDownloadLinkId($extension_id, $this->request->get['extension_download_link_id']);
	// 		}else{
	// 			$download = $this->model_extension_d_shopunity_extension->getExtensionDownload($extension_id);
	// 		}

	// 		if(!empty($download['error']) || empty($download['download'])){
	// 			$json['error'] = 'Error! We cound not get the download link: '.$download['error'];
	// 			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/item', $this->url_token . '&extension_id='.$extension_id , 'SSL'));
	// 		}

	// 		$error_download = json_decode(file_get_contents($download['download']),true);
	// 		if(isset($error_download['error'])){
	// 			$json['error'] = 'Error! getExtensionDownload failed: '.json_encode($error_download['error']);
	// 			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
	// 		}

	// 		//download the extension to system/mbooth/download
	// 		$extension_zip = $this->model_extension_d_shopunity_mbooth->downloadExtensionFromServer($download['download']);
	// 		if(isset($extension_zip['error'])){
	// 			$json['error'] = 'Error! downloadExtensionFromServer failed: '.json_encode($extension_zip['error']);
	// 			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
	// 		}

	// 		//unzip the downloaded file to system/mbooth/download and remove the zip file
	// 		$extracted = $this->model_extension_d_shopunity_mbooth->extractExtension($extension_zip);
	// 		if(isset($extracted['error'])){
	// 			$json['error'] = 'Error! extractExtension failed: ' .json_encode($extracted['error']) . ' download from '.$download['download'];
	// 			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension', $this->url_token , 'SSL'));
	// 		}

	// 		$result = array();

	// 		//BACKUP REFACTOR
	// 		// if(file_exists(DIR_SYSTEM . 'mbooth/xml/'.$this->request->post['mbooth'])){
	// 		// 	$result = $this->model_module_mbooth->backup_files_by_mbooth($this->request->post['mbooth'], 'update');
	// 		// }

	// 		$result = $this->model_extension_d_shopunity_mbooth->installExtension($result);

	// 		if(!empty($result['error'])) {
	// 			$json['error'] = $this->language->get('error_install') . "<br />" . implode("<br />", $result['error']);
	// 		}

	// 		if(!empty($result['success'])) {

	// 			$result = $this->model_extension_d_shopunity_mbooth->installDependencies($extension['codename'], $result);
	// 			$this->load->model('extension/d_shopunity/vqmod');
	// 			$this->model_extension_d_shopunity_vqmod->refreshCache();

	// 			$json['installed'] = true;
	// 			$json['text'] = "Extension ".$extension['codename']." has been successfuly installed";
	// 			$json['view'] = str_replace('&amp;', '&', $this->url->link('extension/d_shopunity/extension/item', $this->url_token . '&extension_id=' . $extension_id , 'SSL'));

	// 			$json['codename'] = $extension['codename'];
	// 			$data['extension'] = $this->model_extension_d_shopunity_extension->getExtension($extension_id);

	// 			$theme = 'extension_thumb';
	// 			if(isset($this->request->get['theme'])){
	// 				$theme = $this->request->get['theme'];
	// 			}
	// 			$data = $this->_productThumb($data);
	// 			$json['extension'] = $this->load->view('d_shopunity/'.$theme.'', $data);

	// 			$json['success'] = 'Extension #' . $this->request->get['extension_id'].' installed';
	// 			$json['success'] .=  "<br />" . implode("<br />", $result['success']);
	// 		}
	// 	}catch(Exception $e){
	// 		$json['error'] = $e->getMessage();
	// 	}

	// 	$this->response->setOutput(json_encode($json));

	// }
	//
	//
	// public function popupTest(){

	// 	$this->load->language('extension/d_shopunity/extension');
	// 	if(!isset($this->request->get['extension_id'])){
	// 		$this->session->data['error'] = 'Error! extension_id missing';
	// 	}else{
	// 		$extension_id = $this->request->get['extension_id'];
	// 	}

	// 	if(!isset($this->request->get['extension_download_link_id'])){
	// 		$this->session->data['error'] = 'Error! extension_download_link_id missing';
	// 	}else{
	// 		$extension_download_link_id = $this->request->get['extension_download_link_id'];
	// 	}

	// 	if(!$this->model_extension_d_shopunity_account->isLogged()){
	// 		$this->session->data['error'] = 'Error! you are not logged in';
	// 	}

	// 	$account = $this->config->get('d_shopunity_account');

	// 	if(empty($account['tester']['tester_id'])){
	// 		$this->session->data['error'] = 'Error! you need to be a tester to test an extension';
	// 	}else{
	// 		$tester_id = $account['tester']['tester_id'];
	// 	}

	// 	try{
	// 		$this->load->model('extension/d_shopunity/extension');
	// 		$data['extension'] = $this->model_extension_d_shopunity_extension->getTestableExtension($tester_id, $extension_id, $extension_download_link_id);

	// 		if(!empty($data['extension']['required'])){
	// 			$filter_data = array();
	// 			foreach($data['extension']['required'] as $codename => $version){
	// 				$filter_data['codename'][] = $codename;

	// 			}

	// 			$data['extension']['required'] = $this->model_extension_d_shopunity_extension->getExtensions($filter_data);
	// 		}
	// 		if(empty($data['extension'])){
	// 			$json['error'] = 'Error! extension.json not found';
	// 		}else{
	// 			$json['content'] = $this->load->view($this->route.'_popup_test', $data);
	// 		}


	// 	}catch(Exception $e){
	// 		$json['error'] = $e->getMessage();
	// 	}

	// 	$this->response->setOutput(json_encode($json));
	// }


}
