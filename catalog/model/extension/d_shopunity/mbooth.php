<?php
class ModelExtensionDShopunityMbooth extends Model {
    private $dir_root = '';
    private $subversions = array('lite', 'light', 'free');

    public function __construct($registry){
        parent::__construct($registry);
        $this->dir_root = substr_replace(DIR_SYSTEM, '/', -8);
      
    }

    public function getExtensions(){
        $result = array();

        //old location - depricated
        $files = glob(DIR_SYSTEM . 'mbooth/extension/*.json');

        //new location
        $files =  array_merge($files, glob(DIR_SYSTEM . 'library/d_shopunity/extension/*.json'));


        foreach($files as $file){
            $result[] = $this->_extension(json_decode(file_get_contents($file), true));
        }

        return $result;

    }

    public function getExtension($codename){

        $file = $this->getExtensionJson($codename);
        return $this->_extension($file);
    }

    public function getExtensionJson($codename){
        $result = array();

        //new location
        $file = DIR_SYSTEM . 'library/d_shopunity/extension/'.$codename.'.json';

        if(file_exists($file)){
            return json_decode(file_get_contents($file), true);
        }else{
            foreach ($this->subversions as $subversion){
                $file = DIR_SYSTEM . 'library/d_shopunity/extension/'.$codename.'_'.$subversion.'.json';
                if (file_exists($file)) {
                    return json_decode(file_get_contents($file), true);
                }
            }
        }
        
        //old location - depricated
        $file = DIR_SYSTEM . 'mbooth/extension/'.$codename.'.json';

        if(file_exists($file)){
            return json_decode(file_get_contents($file), true);
        }else{
            foreach ($this->subversions as $subversion){
                $file = DIR_SYSTEM . 'mbooth/extension/'.$codename.'_'.$subversion.'.json';
                if (file_exists($file)) {
                    return json_decode(file_get_contents($file), true);
                }
            }
        }
        return false;
    }

    public function downloadExtensionFromServer($download_link){

        $filename = DIR_SYSTEM . 'library/d_shopunity/download/extension.zip';
        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $ch = curl_init();
        $fp = fopen($filename, "w");
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_URL, htmlspecialchars_decode($download_link));
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $page = curl_exec($ch);
        if (!$page) {
            throw new Exception('Error! downloadExtensionFromServer we could not download '.htmlspecialchars_decode($download_link));
        }
        curl_close($ch);

        return $filename;
    }

     public function extractExtension($filename = false, $location = false) {
        if (!$filename) {
            $filename = DIR_SYSTEM . 'library/d_shopunity/download/extension.zip';
        }
        if (!$location) {
            $location = dirname($filename);
        }

        $result = array();
        $zip = new ZipArchive;
        if (!$zip) {
            $result['error'][] = 'ZipArchive not working.';
        }

        $res = $zip->open($filename, ZipArchive::CHECKCONS);
        if ($res !== TRUE) {
            switch($res) {
                case ZipArchive::ER_NOZIP:
                    $result['error'][] = 'not a zip archive';
                case ZipArchive::ER_INCONS :
                    $result['error'][] = 'consistency check failed';
                case ZipArchive::ER_CRC :
                    $result['error'][] = 'checksum failed';
                default:
                    $result['error'][] = 'error ' . $res;
            }
        }else{
            if ($zip->open($filename) != "true") {
                $result['error'][] = $filename;
            }
            $zip->extractTo($location);
            $zip->close();
        }


        if(isset($result['error'])){
            throw new Exception('Error! extractExtension failed: filename: '. $filename. ', message: '.json_encode($result['error']));
        }

        unlink($filename);

        return $result;
    }

    public function downloadExtension($codename){

        $mbooth = $this->getExtension($codename);
        if($mbooth){
            $temp = tempnam(ini_get('upload_tmp_dir'), 'zip');
            $zip = new ZipArchive();
            $zip->open($temp, ZipArchive::OVERWRITE);

            foreach ($mbooth['files'] as $file) {

                if (file_exists($this->dir_root . $file)) {

                    if (is_file($this->dir_root . $file)) {
                        $zip->addFile($this->dir_root . $file, 'upload/' . $file);

                        $result['success'][] = $file;
                    } else {
                        $result['error'][] = $file;
                    }
                } else {
                    $result['error'][] = $file;
                }
            }

            //add install.xml file for opencart automatic installer.
            if(isset($mbooth['install'])){

                if(isset($mbooth['install']['php'])){
                    if(file_exists($this->dir_root . $mbooth['install']['php'])){
                        $zip->addFile($this->dir_root . $mbooth['install']['php'], 'install.php');
                    }
                }

                if(isset($mbooth['install']['sql'])){
                    if(file_exists($this->dir_root . $mbooth['install']['sql'])){
                        $zip->addFile($this->dir_root . $mbooth['install']['sql'], 'install.sql');
                    }
                }

                if(isset($mbooth['install']['xml'])){
                    if(file_exists($this->dir_root . $mbooth['install']['xml'])){
                        $zip->addFile($this->dir_root . $mbooth['install']['xml'], 'install.xml');
                    }
                }
            }

            if(isset($mbooth['readme'])){
                if(file_exists($this->dir_root . $mbooth['readme'])){
                    $zip->addFile($this->dir_root . $mbooth['readme'], 'readme.md');
                }
            }
         
            $zip->close();

            if (empty($result['error'])) {
                header('Pragma: public');
                header('Expires: 0');
                header('Content-Description: File Transfer');
                header('Content-Type: mbooth/xml');
                header('Content-Disposition: attachment; filename=' . $codename . '.v' . $mbooth['version'] . '.ocmod' . '.zip');
                header('Content-Transfer-Encoding: binary');
                readfile($temp);
                unlink($temp);
            }

            return $result;
        }else{
            return false;
        }

    }

    public function installExtension($result) {
        
        return $this->moveFiles(DIR_SYSTEM . 'library/d_shopunity/download/upload/', substr_replace(DIR_SYSTEM, '/', -8), $result);
    }

    
    // public function activateExtension($codename, $result = array()) {
    //     $extension = $this->getExtension($codename);
    //     if(isset($extension['install'])){
    //         if(isset($extension['install']['url'])){
    //             $parts = explode('&', $extension['install']['url']);
    //             $route = array_shift($parts);
    //         }
    //     }

    //     if(isset($route) && isset($parts)){

    //         try{
    //             if(VERSION < '2.3.0.0'){
    //                 $content = file_get_contents(str_replace('&amp;', '&', $this->url->link($route, implode('&', $parts).'&token='.$this->session->data['token'], 'SSL')));
    //             }else{
    //                 parse_str(implode("&", $parts), $vars);
    //                 $this->request->get['extension'] = $vars['extension'];
    //                 $this->load->controller($route);
    //             }
    //             $result['success'][] = 'Extension activated';
                
    //         }catch(Exception $e){
    //             $result['error'][] = 'Extension not activated message: '. $e->message;
    //         }
    //     }
    //     return $result;
    // }

    // public function deactivateExtension($codename, $result = array()) {

    //     $extension = $this->getExtension($codename);
    //     if(isset($extension['uninstall'])){
    //         if(isset($extension['uninstall']['url'])){
    //             $parts = explode('&', $extension['uninstall']['url']);
    //             $route = array_shift($parts);
    //         }

    //     }

    //     if(isset($route) && isset($parts)){

    //         try{
    //             if(VERSION < '2.3.0.0'){
    //                 $content = file_get_contents(str_replace('&amp;', '&', $this->url->link($route, implode('&', $parts).'&token='.$this->session->data['token'], 'SSL')));
    //             }else{
    //                 parse_str(implode("&", $parts), $vars);
    //                 $this->request->get['extension'] = $vars['extension'];
    //                 $this->load->controller($route);
    //             }
                
    //         }catch(Exception $e){
    //             $result['error'][] = 'Extension not deactivated message: '. $e->message;
    //         }
    //     }
    //     return $result;
    // }


    public function deleteExtension($codename){

        $mbooth = $this->getExtension($codename);
        $this->load->model('extension/d_shopunity/vqmod');
        if($mbooth){
            $result = array('success' => array(), 'error' => array());
            foreach ($mbooth['files'] as $file) {
                if (is_file($this->dir_root . $file)) {

                    //if vqmod
                    if(strpos($file, 'vqmod') !== false && strpos($file, '.xml_') !== false){
                        $this->model_extension_d_shopunity_vqmod->setVqmod(basename($file, '.xml_').'.xml', 0);
                    }elseif(strpos($file, 'vqmod') !== false && strpos($file, '.xml') !== false){
                        $this->model_extension_d_shopunity_vqmod->setVqmod(basename($file), 1);
                    }

                    if (@unlink($this->dir_root . $file)) {
                        $result['success'][] = $file;
                    } else {
                        $result['error'][] = $file;
                    }

                    $dir = dirname($this->base_dir . $file);
                    while (strlen($dir) > strlen($this->base_dir)) {
                        if (is_dir($dir)) {
                            if ($this->isDirEmpty($dir)) {
                                if (@rmdir($dir)) {
                                    $result['success'][] = dirname($dir);
                                    $dir = dirname($dir);
                                } else {
                                    $result['error'][] = dirname($dir);
                                }
                            } else {
                                break;
                            }
                        } else {
                            break;
                        }
                    }
                } else {
                    $result['error'][] = $file;
                }
            }
            @unlink($this->dir_root.'vqmod/mods.cache');
            @unlink($this->dir_root.'vqmod/checked.cache');
            $content = file_get_contents(DIR_CATALOG);
        }else{
            $result = false;
        }
        return $result;
    }

    public function backupExtension($codename){


    }

    public function getFiles($dir, &$arr_files) {

        if (is_dir($dir)) {
            $handle = opendir($dir);
            while ($file = readdir($handle)) {
                if ($file == '.' or $file == '..')
                    continue;
                if (is_file($file))
                    $arr_files[] = "$dir/$file";
                else
                    $this->getFiles("$dir/$file", $arr_files);
            }
            closedir($handle);
        }else {
            $arr_files[] = $dir;
        }
    }

    public function moveFiles($from, $to, $result) {

        if(file_exists($from)){
            $files = scandir($from);

            foreach ($files as $file) {

                if ($file == '.' || $file == '..' || $file == '.DS_Store')
                    continue;

                if (is_dir($from . $file)) {
                    if (!file_exists($to . $file . '/')) {
                        mkdir($to . $file . '/', 0777, true);
                    }
                    $result = $this->moveFiles($from . $file . '/', $to . $file . '/', $result);
                } elseif (rename($from . $file, $to . $file)) {
                    $result['success'][] = str_replace($this->dir_root, '', $to . $file);
                } else {
                    $result['error'][] = str_replace($this->dir_root, '', $to . $file);
                }
            }

            $this->deleteFiles($from);
        }else{
            $result['error'][] = $from;
        }

        return $result;
    }

    public function deleteFiles($path){
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($path . "/" . $object) == "dir")
                        $this->delete_dir($path . "/" . $object);
                    else
                        unlink($path . "/" . $object);
                }
            }
            reset($objects);
            rmdir($path);
        }
    }

    public function isDirEmpty($dir) {
        if (!is_readable($dir))
            return true;

        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }
        return true;
    }

    public function installDependencies($codename, $result = array()){

        foreach($this->getDependencies($codename) as $require){
            if(!empty($require['codename'])){

                $extension = $this->getExtension($require['codename']);
                
                $satisfies = false;
                try{
                    $semver = new Semver;
                    if(!empty($extension['version'])){
                        $satisfies = $semver->expression($require['version'])->satisfiedBy($semver->version($extension['version']));
                    }
                  
                }catch(Exception $e){
                    $result['error'][] = 'Error: version:'.$require['version'].', message: '.$e->getMessage();
                }

                if(empty($extension) || !$satisfies){
                    $this->load->model('extension/d_shopunity/extension');
                    $download = $this->model_extension_d_shopunity_extension->getExtensionDownloadByCodename($require['codename'], $require['version']);

                    if(isset($download['download'])){
                        $extension_zip = $this->downloadExtensionFromServer($download['download']);
                        $extracted = $this->extractExtension($extension_zip); 
                        $result = $this->installExtension($result);
                        $result['success'][] = $require['codename'] . ' installed.';
                        $result['success'][] = '----------------------------------------------------------';
                    }elseif(isset($download['error'])){
                        $result['error'][] = 'Error: we could not install '. $require['codename']. ' message: ' . $download['error'];
                    }else{
                        $result['error'][] = 'Error! We could not install ' .$require['codename'] . ', message: '. json_encode($download);
                    }   
                 
                }else{
                    $result['success'][] = $require['codename'] . ' not installed. Already up to date.';
                    $result['success'][] = '----------------------------------------------------------';
                }
           
            }else{
                $result['error'][] = 'Error: requied parse for '. json_encode( $require);
            }
        }
        return $result;
    }

    public function validateDependencies($codename){

        $extension =  $this->getExtension($codename);
        if(isset($extension['required'])){
            foreach($extension['required'] as $extension_codename => $version){
                if(!file_exists(DIR_SYSTEM.'mbooth/extension/'.$extension_codename.'.json')
                && !file_exists(DIR_SYSTEM.'library/d_shopunity/extension/'.$extension_codename.'.json')){
                    $this->response->redirect($this->url->link('d_shopunity/extension/dependency', 'codename='.$codename.'&token='.$this->session->data['token'], 'SSL'));
                }
            }
        }
        return true;

    }

    public function getDependencies($codename){
        $result = array();

        $extension = $this->getExtension($codename);
        if($extension){
            if(!empty($extension['required'])){
                foreach($extension['required'] as $require => $version){
                    $result[] = array(
                        'codename' => (string)$require,
                        'version' => (string)$version
                    );
                }
            }
        }

        return $result;
    }

    public function getVersion($codename){

        $extension = $this->getExtension($codename);

        if(!empty($extension['version'])){
            return $extension['version'];
        }else{
            return false;
        }
    }


    public function _extension($data){

        $result = array();
        if(!empty($data)){
            $result = $data;
            if(isset($data['index'])){
                if(VERSION < '2.3.0.0' && strpos($result['index'], 'extension/module/') !== false) {
                    $result['index'] = str_replace('extension/module/', "module/", $result['index']);
                }

                if(VERSION >= '2.3.0.0' && strpos($result['index'], 'extension/module/') === false) {
                    $result['index'] = str_replace('module/', 'extension/module/', $result['index']);
                }
            }

            if(isset($result['install']) && isset($result['install']['url'])){
                if(VERSION < '2.3.0.0' && strpos($result['install']['url'], 'extension/extension/') !== false) {
                    $result['install']['url'] = str_replace('extension/extension/', "extension/", $result['install']['url']);
                }

                if(VERSION >= '2.3.0.0' && strpos($result['install']['url'], 'extension/extension/') === false) {
                    $result['install']['url'] = str_replace('extension/', 'extension/extension/', $result['install']['url']);
                }
            }

            if(isset($result['uninstall']) && isset($result['uninstall']['url'])){
                if(VERSION < '2.3.0.0' && strpos($result['uninstall']['url'], 'extension/extension/') !== false) {
                    $result['uninstall']['url'] = str_replace('extension/extension/', "extension/", $result['uninstall']['url']);
                }

                if(VERSION >= '2.3.0.0' && strpos($result['uninstall']['url'], 'extension/extension/') === false) {
                    $result['uninstall']['url'] = str_replace('extension/', 'extension/extension/', $result['uninstall']['url']);
                }
            }

            

            if (!empty($data['dirs'])) {

                $dir_files = array();

                foreach ($data['dirs'] as $dir) {
                    $this->getFiles($this->dir_root . $dir, $dir_files);
                }

                foreach ($dir_files as $file) {
                    $file = str_replace($this->dir_root, "", $file);
                    $result['files'][] = (string) $file;
                }
            }

        }
        return $result;

    }

}