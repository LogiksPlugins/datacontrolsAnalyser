<?php
if(!defined('ROOT')) exit('No direct script access allowed');

handleActionMethodCalls();

define("DCCACHE_FILE",_dirTemp("dcAnalyser").CMS_SITENAME.".json");

function _service_create_cache() {
    $result=listFiles();
    printServiceMsg($result);
}

function _service_fetch_forms() {
    $output="";
    define("DCCACHE_FILE",_dirTemp("dcAnalyser").CMS_SITENAME.".json");
    $cache_file=DCCACHE_FILE; 
    if(!file_exists($cache_file)){
        $result=listFiles();
        if($result['status']==false){
            printServiceMsg(["status"=>false,"msg"=>$result['msg']]);
            return;
        }
    }else{
        $files_info=json_decode(file_get_contents($cache_file),true);
        // printArray($files_info);exit;
        if(is_array($files_info)){
            if(!isset($files_info['forms']) || count($files_info['forms'])<1){
                if(!isset($files_info['default']) || count($files_info['default'])<1){ printServiceMsg(["status"=>false,"msg"=>"No files found"]);return;}
                else {
                    $files_list=$files_info['default'];
                    $output=getContents($files_list,"from");
                }
                
                
                
            }else{
                $files_list=$files_info['forms'];
                // printArray($files_list);exit;
                if(!isset($files_info['default']) || count($files_info['default'])<1){}
                else {$files_list=array_merge($files_list,$files_info['default']);}
                // printArray($files_list);exit;
                $output=getContents($files_list,"from");
            }
        }
    }
    if(count($output)>0){
        printServiceMsg(["status"=>true,"msg"=>$output]);
    }else{
        printServiceMsg(["status"=>false,"msg"=>"No Data found"]);
    }
}
function _service_fetch_reports() {
    $output="";
    define("DCCACHE_FILE",_dirTemp("dcAnalyser").CMS_SITENAME.".json");
    $cache_file=DCCACHE_FILE; 
    if(!file_exists($cache_file)){
        $result=listFiles();
        if($result['status']==false){
            printServiceMsg(["status"=>false,"msg"=>$result['msg']]);
            return;
        }
    }else{
        $files_info=json_decode(file_get_contents($cache_file),true);
        if(is_array($files_info)){
            if(!isset($files_info['reports']) || count($files_info['reports'])<1){
                if(!isset($files_info['default']) || count($files_info['default'])<1){ printServiceMsg(["status"=>false,"msg"=>"No files found"]);return;}
                else {
                    $files_list=$files_info['default'];
                    $output=getContents($files_list,"report");
                }
            }else{
                $files_list=$files_info['reports'];
                if(!isset($files_info['default']) || count($files_info['default'])<1){}
                else {$files_list=array_merge($files_list,$files_info['default']);}
                $output=getContents($files_list,"report");
            }
        }
    }
    if(count($output)>0){
        printServiceMsg(["status"=>true,"msg"=>$output]);
    }else{
        printServiceMsg(["status"=>false,"msg"=>"No Data found"]);
    }
}

function getContents($filesAry,$filetype="report") {
    $finalResults=[];
    $path=CMS_APPROOT;
    if(count($filesAry)>0){
        foreach($filesAry as $key=>$val){
            
            if(file_exists($val["path"])){
                $content = file_get_contents($val["path"]);
                switch($val['filetype']){
                    case "form":
                        $result=getContentsFromForm($content);
                        if($result){
                            $temp=array_merge(getDefaultItem(),$val);
                            $temp['path']=str_replace($path,"/",$temp['path']);
                            $temp['link']="modules/cmsEditor@&type=edit&src=" .urlencode($temp['path']);///plugins/modules/credsRoles/style.css
                            $temp['sqlquery']=$result['sqlquery'];
                            $temp['tables']=$result['tables'];
                            $finalResults[]=$temp;
                        }
                    break;
                    case "report":
                        $result=getContentsFromReport($content);
                        if($result){
                            $temp=array_merge(getDefaultItem(),$val);
                            $temp['path']=str_replace($path,"/",$temp['path']);
                            $temp['link']="modules/cmsEditor@&type=edit&src=" .urlencode($temp['path']);///plugins/modules/credsRoles/style.css
                            $temp['sqlquery']=$result['sqlquery'];
                            $temp['tables']=$result['tables'];
                            $finalResults[]=$temp;
                        }
                    break;
                    default;
                    break;
                }
            }
        }
    }
    return $finalResults;
}
function getContentsFromForm($content){
    if(strlen($content)>0){
        $configContents=json_decode($content,true);
        if(isset($configContents['source'])){
            $src=$configContents['source'];
            if(isset($src['table'])){
                $dbKey=null;
                if(isset($configContents['dbkey']))$dbKey=$configContents['dbkey'];
	            if($dbKey==null) $dbKey="app";
                $sqlQuery=QueryBuilder::fromArray($src,_db($dbKey))->_sql();
                return ["sqlquery"=>$sqlQuery,"tables"=>$src['table']];
            }
        }
    }
    return false;
    
}
function getContentsFromReport($content){
    if(strlen($content)>0){
        $configContents=json_decode($content,true);
        if(isset($configContents['source'])){
            $src=$configContents['source'];
            if(isset($src['table'])){
                $dbKey=null;
                if(isset($configContents['dbkey']))$dbKey=$configContents['dbkey'];
	            if($dbKey==null) $dbKey="app";
                $sqlQuery=QueryBuilder::fromArray($src,_db($dbKey))->_sql();
                return ["sqlquery"=>$sqlQuery,"tables"=>$src['table']." (".count(explode(",",$src['table'])).")"];
            }
            
        }
       
    }
    return false;
    
}
function getDefaultItem($name="",$filePath="",$urlPath="") {
    return [
            "title"=>$name,
            "extension"=>"",
            "path"=>$filePath,
            "link"=>$urlPath,
            "sqlquery"=>"",
            "tables"=>"",
            "extra"=>""
        ];
}
function listFiles() {
    define("DCCACHE_FILE",_dirTemp("dcAnalyser").CMS_SITENAME.".json");
    $path=APPROOT;
    if(defined("CMS_APPROOT")) {
        $path=CMS_APPROOT;
    }
    $appName = basename($path);
    
    if($lang && $lang!="*") {
        $langs=explode(",",$lang);
    } else {
        $langs=false;
    }
    
    $results=scanDirectory($path,["json"]);
    
    $finalResults=[];
    $finalResults['forms']=[];
    $finalResults['reports']=[];
    $finalResults['default']=[];
    
    foreach($results['files'] as $f) {
        if(!isset($f['filetype']) || strlen($f['filetype'])<1){
            $filetype="default";
        }else{
            $filetype=$f['filetype'];   
        }
        switch($filetype){
            case "form":
                $finalResults['forms'][]=$f;
            break;
            case "report":
                $finalResults['reports'][]=$f;
            break;
            default;case "default":
                $finalResults['default'][]=$f;
            break;
        }
    }
    if(count($finalResults['forms']) <1 && count($finalResults['reports']) <1 && count($finalResults['default']) <1){
        return ["status"=>false,"msg"=>"No files found"];
    }else{
        $contents=json_encode($finalResults, true);
        $cache_file=DCCACHE_FILE; 
        if(!file_exists($cache_file)) {
    		if(!is_dir(dirname($cache_file))) mkdir(dirname($cache_file),0777,true);
    		file_put_contents($cache_file,$contents);
    	} elseif((strtotime($data['edited_on'])-filemtime($cache_file))>0) {
    		file_put_contents($cache_file,$contents);
    	}
    	return ["status"=>true,"msg"=>"Cache recreate successfully"];
    }
}

function scanDirectory($path, $extension=["php","js","css","htm","html","tpl","json","cfg","md"]) {
    if($extension===false) {
        //Automatic All Extension Detection
        $extension=["php","js","css","htm","html","tpl","json","cfg","md"];
    }
    $dir = new DirectoryIterator($path);
    $files = array();
    $totalFiles = 0;
    foreach ($dir as $file) {
        if (!$file->isDot()) {
            $valid=checkValidDir($file->getPathname());
        
            if($valid === true and substr($file->getBasename(),0,1) != ".") {
                if($file->isDir()) {
                    $dirScan = scanDirectory($file->getPathname(),$extension);
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
                            $fileType="form";
                        }
                        if(strpos($pathName,"report") !== false) {
                            $fileType="report";
                        }
                        $files[] = [
                            "title"=>$file->getBasename(),
                            "extension"=>$ext,
                            "path"=>$pathName,
                            "filetype"=>$fileType
                            ];
                            
                    }
                }
            }
        }
    }
    return array('files' => $files, 'totalFiles' => $totalFiles);
}
function checkValidDir($string){
    $validDir=["plugins","reports","report","forms","form","misc","modules","pluginsDev"];
    foreach($validDir as $val){
        if(strpos($string,$val) !== false) {
                return true;
            }
    }
    return false;
}

?>