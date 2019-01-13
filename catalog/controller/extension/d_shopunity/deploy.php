<?php
class ControllerExtensionDShopunityDeploy extends Controller {

	public function index() {

		//nothing
	}

	/*
	 *	Bitbucket diploy script
	 *
	 *  /index.php?route=d_shopunity/deploy/bitbucket&owner=dreamvention
	 */
	public function bitbucket() {

		$user = '';
		if(isset($this->request->get['user'])){
			$user = $this->request->get['user'];
		}
		$pass = '';
		if(isset($this->request->get['pass'])){
			$pass = $this->request->get['pass'];
		}
		$repo = '';
		if(isset($this->request->get['repo'])){
			$repo = $this->request->get['repo'];
		}
		$branch = '';
		if(isset($this->request->get['branch'])){
			$branch = $this->request->get['branch'];
		}
		$owner = '';
		if(isset($this->request->get['owner'])){
			$owner = $this->request->get['owner'];
		}

		new d_shopunity\Bitbucket($user, $pass, $repo, str_replace("catalog/", "", DIR_APPLICATION), $branch, $owner);
	}

	/*
	 *	Github diploy script
	 *
	 *  /index.php?route=d_shopunity/deploy/github&owner=dreamvention
	 */
	public function github(){

		$user = '';
		if(isset($this->request->get['user'])){
			$user = $this->request->get['user'];
		}
		$repo = '';
		if(isset($this->request->get['repo'])){
			$repo = $this->request->get['repo'];
		}
		$branch = '';
		if(isset($this->request->get['branch'])){
			$branch = $this->request->get['branch'];
		}
		new d_shopunity\GitHub($user, $repo, str_replace("catalog/", "", DIR_APPLICATION), $branch );
	}

}
