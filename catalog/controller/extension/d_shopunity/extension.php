<?php
class ControllerExtensionDShopunityExtension extends Controller {
	public function __construct($registry) {
		parent::__construct($registry);
		// Paths and Files
		$this->base_dir = substr_replace(DIR_SYSTEM, '/', -8);
		$this->mboot_script_dir = DIR_SYSTEM .'mbooth/xml/';

	}

	/**	
	 * Get extension data with index.php?route=d_shopunity/extension&codename=d_shopunity&secret=test
	 */
	public function index() {

		if(isset($this->request->get['codename']) && isset($this->request->get['secret'])){
			//validate secret
			if($this->validateSecret($this->request->get['secret'])){
				$this->load->model('extension/d_shopunity/mbooth');

				$json = $this->model_extension_d_shopunity_mbooth->getExtension($this->request->get['codename']);
				if(empty($json)){
					$json['error'] = "Error! extension not found";
				}
			}else{
				$json['error'] = "Error! Secret is invalid";

			}
		}else{
			$json['error'] = "Error! codename or secret is missing";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	/**	
	 * Dowbload with index.php?route=d_shopunity/extension/download&codename=d_shopunity&secret=test
	 */
	public function download(){
		if(isset($this->request->get['codename']) && isset($this->request->get['secret'])){

			//validate secret
			if($this->validateSecret($this->request->get['secret'])){
				$this->load->model('extension/d_shopunity/mbooth');

				$json = $this->model_extension_d_shopunity_mbooth->downloadExtension($this->request->get['codename']);

			}else{
				$json['error'] = "Error! Secret is invalid";

			}
		}else{
			$json['error'] = "Error! codename or secret is missing";
		}

		if(!empty($json['error'])){
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}

	/**	
	 * Delete with index.php?route=d_shopunity/extension/uninstall&codename=d_shopunity&secret=test
	 */
	public function uninstall(){

		// if(isset($this->request->get['codename']) && isset($this->request->get['secret'])){

		// 	if($this->validateSecret($this->request->get['secret'])){
		// 		$this->load->model('module/d_mbooth');

		// 		$json = $this->model_module_d_mbooth->deleteExtension($this->request->get['codename']);

		// 	}else{
		// 		$json['error'] = "Error! Secret is invalid";

		// 	}
		// }else{
		// 	$json['error'] = "Error! no codename or secret is missing";
		// }
		// if(!empty($json['error'])){
		// 	$this->response->addHeader('Content-Type: application/json');
		// 	$this->response->setOutput(json_encode($json));
		// }
	}

	public function install(){
		$json = array();

		if(isset($this->request->get['extension_id']) && isset($this->request->get['secret']) && isset($this->request->get['access_token'])){


			//validate secret
			if($this->validateSecret($this->request->get['secret'])){
				
				$json['installed'] = false;
				
				$extension_id = $this->request->get['extension_id'];
				$this->load->model('extension/d_shopunity/extension');
				$this->load->model('extension/d_shopunity/mbooth');
				$this->load->model('extension/d_shopunity/account');

				try{

					$this->model_extension_d_shopunity_account->setAccessToken($this->request->get['access_token']);

					$extension = $this->model_extension_d_shopunity_extension->getExtension($extension_id);

					if(isset($this->request->get['extension_download_link_id'])){
						$download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByDownloadLinkId($extension_id, $this->request->get['extension_download_link_id']);
					}else{
						$download = $this->model_extension_d_shopunity_extension->getExtensionDownload($extension_id);
					}

					if(!empty($download['error']) || empty($download['download'])){
						$json['error'] = 'Error! We cound not get the download link: '.$download['error'];
						
					}

					$error_download = json_decode(file_get_contents($download['download']),true);
					if(isset($error_download['error'])){
						$json['error'] = 'Error! getExtensionDownload failed: '.json_encode($error_download['error']);
						
					}

					//download the extension to system/mbooth/download
					$extension_zip = $this->model_extension_d_shopunity_mbooth->downloadExtensionFromServer($download['download']);
					if(isset($extension_zip['error'])){
						$json['error'] = 'Error! downloadExtensionFromServer failed: '.json_encode($extension_zip['error']);
						
					}

					//unzip the downloaded file to system/mbooth/download and remove the zip file
					$extracted = $this->model_extension_d_shopunity_mbooth->extractExtension($extension_zip);
					if(isset($extracted['error'])){
						$json['error'] = 'Error! extractExtension failed: ' .json_encode($extracted['error']) . ' download from '.$download['download'];
						
					}

					$result = array();

					//BACKUP REFACTOR
					// if(file_exists(DIR_SYSTEM . 'mbooth/xml/'.$this->request->post['mbooth'])){
					// 	$result = $this->model_module_mbooth->backup_files_by_mbooth($this->request->post['mbooth'], 'update');
					// }

					$result = $this->model_extension_d_shopunity_mbooth->installExtension($result);

					if(!empty($result['error'])) {
						$json['error'] = $this->language->get('error_install') . "<br />" . implode("<br />", $result['error']);
					}

					if(!empty($result['success'])) {

						$result = $this->model_extension_d_shopunity_mbooth->installDependencies($extension['codename'], $result);
						$this->load->model('extension/d_shopunity/vqmod');
						$this->model_extension_d_shopunity_vqmod->refreshCache();

						$json['installed'] = true;
						
						$json['success'] = 'Extension #' . $this->request->get['extension_id'].' installed';
					}

					
				}catch(Exception $e){
					$json['error'] = $e->getMessage();
				}
			}else{
				$json['error'] = "Error! Secret is invalid";

			}
		}else{
			$json['error'] = "Error! extension_id or secret is missing";
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	
	}


	public function validateSecret($secret){
		return true;
	}
}
