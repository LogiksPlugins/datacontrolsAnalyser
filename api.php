<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getDCFileList")) {
    
    define("DCCACHE_FILE",_dirTemp("datacontrols_analyser").CMS_SITENAME.".json");
    
    function getDCFileList($dcType) {
        if(!file_exists(DCCACHE_FILE)) {
            $result=createDCFileCache();
        }
        
        if(!file_exists(DCCACHE_FILE)) {
            return [];
        }
        
        $fileList = json_decode(file_get_contents(DCCACHE_FILE),true);
        
        if(isset($fileList[$dcType])) {
            $fileList = $fileList[$dcType];
            
            foreach($fileList as $a=>$b) {
                
            }
            
            
            return $fileList;
        }
        else return [];
    }
    
    function createDCFileCache() {
        $path=APPROOT;
        
        if(defined("CMS_APPROOT")) {
            $path=CMS_APPROOT;
        }
        define("DC_BASEPATH",$path);
        
        $appName = basename($path);
        
        if($lang && $lang!="*") {
            $langs=explode(",",$lang);
        } else {
            $langs=false;
        }
        
        $results=scanDCDirectory($path,["json"]);
        
        $finalResults=[];
        
        foreach($results['files'] as $f) {
            if(!isset($f['filetype']) || strlen($f['filetype'])<1){
                $filetype="default";
            }else{
                $filetype=$f['filetype'];   
            }
            
            if(!isset($finalResults[$filetype])) $finalResults[$filetype] = [];
            
            $finalResults[$filetype][] = $f;
        }
        
        if(count($finalResults['forms']) <1 && count($finalResults['reports']) <1 && count($finalResults['default']) <1){
            return ["status"=>false,"msg"=>"No files found"];
        }else{
            $contents=json_encode($finalResults, true);
            
            $cache_file=DCCACHE_FILE; 
            if(!is_dir(dirname($cache_file))) mkdir(dirname($cache_file),0777,true);
    		file_put_contents($cache_file,$contents);
        		
        	return ["status"=>true,"msg"=>"Cache recreate successfully"];
        }
    }
    
    function scanDCDirectory($path, $extension=["php","js","css","htm","html","tpl","json","cfg","md"]) {
        if($extension===false) {
            //Automatic All Extension Detection
            $extension=["php","js","css","htm","html","tpl","json","cfg","md"];
        }
        
        $dir = new DirectoryIterator($path);
        $files = array();
        $totalFiles = 0;
        foreach ($dir as $file) {
            if (!$file->isDot()) {
                $valid=checkDCValidDir($file->getPathname());
            
                if($valid === true and substr($file->getBasename(),0,1) != ".") {
                    if($file->isDir()) {
                        $dirScan = scanDCDirectory($file->getPathname(),$extension);
                        $files = array_merge($files,$dirScan['files']);
                        $totalFiles+=$dirScan['totalFiles'];
                    } else {
                        $totalFiles++;
                        $fname=$file->getBasename();
                        $pathName=$file->getPathname();
                        $ext=$file->getExtension();
                        
                        $fileType="default";
                        
                        if(in_array($ext,$extension)) {
                            
                            if(strpos($pathName,"form") !== false) {
                                $fileType="forms";
                            } elseif(strpos($pathName,"report") !== false) {
                                $fileType="reports";
                            } elseif(strpos($pathName,"infoviews") !== false) {
                                $fileType="infoviews";
                            } elseif(strpos($pathName,"views") !== false) {
                                $fileType="views";
                            } elseif(strpos($pathName,"search") !== false) {
                                $fileType="search";
                            } elseif(strpos($pathName,"dashboard") !== false) {
                                $fileType="dashboards";
                            }
                            
                            $bPath = str_replace(DC_BASEPATH,"",$pathName);
                            $srcType = current(explode("/",$bPath));
                            $moduleName = "";
                            $editable = true;
                            
                            switch($srcType) {
                                case "plugins":case "plugin":
                                    $editable = false;
                                case "pluginsDev":
                                    $arr = explode("/",$bPath);
                                    $moduleName = $arr[2];
                                    break;
                                case "misc":
                                    $moduleName = basename(dirname($bPath));
                                    break;
                            }
                            if(in_array($moduleName,[
                                    "forms",
                                    "reports",
                                    "infoviews",
                                    "views",
                                    "search",
                                    "dashboards",
                                ])) {
                                    $moduleName = "";
                                }
                            
                            $files[] = [
                                "title"=>"{$moduleName}/{$fname}",
                                "extension"=>$ext,
                                "path"=>$bPath,
                                "filetype"=>$fileType,
                                "module"=>$moduleName,
                                "src"=>$srcType,
                                "editable"=>$editable,
                                ];
                                
                        }
                    }
                }
            }
        }
        
        return array('files' => $files, 'totalFiles' => $totalFiles);
    }
    function checkDCValidDir($string){
        $validDir=["plugins","reports","report","forms","form","infoviews","misc","modules","pluginsDev"];
        foreach($validDir as $val){
            if(strpos($string,$val) !== false) {
                    return true;
                }
        }
        return false;
    }
}
?>