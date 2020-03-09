<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadModuleLib("datacontrolsAnalyser","api");

handleActionMethodCalls();

function _service_fetch_forms() {
    $files = getDCFileList("forms");
    
    if(count($files)>0){
        $output=getDCContents($files,"forms");
        
        printServiceMsg(["status"=>true,"msg"=>$output]);
    }else{
        printServiceMsg(["status"=>false,"msg"=>"No Data found"]);
    }
}
function _service_fetch_reports() {
    $files = getDCFileList("reports");
    
    if(count($files)>0){
        $output=getDCContents($files,"report");
        
        printServiceMsg(["status"=>true,"msg"=>$output]);
    }else{
        printServiceMsg(["status"=>false,"msg"=>"No Data found"]);
    }
}

function getDCContents($filesAry,$filetype="report") {
    $finalResults=[];
    $path=CMS_APPROOT;
    
    if(count($filesAry)>0) {
        foreach($filesAry as $key=>$val){
            if(file_exists($path.$val["path"])){
                $content = file_get_contents($path.$val["path"]);
                switch($val['filetype']){
                    case "form":case "forms":
                        $result=getContentsFromForm($content);
                        if($result){
                            $temp=array_merge(getDCDefaultItem(),$val);
                            $temp['path']=str_replace($path,"/",$temp['path']);
                            
                            $temp['link']=_link("modules/cmsEditor")."&type=edit&src=" .urlencode($temp['path']);///plugins/modules/credsRoles/style.css
                            $temp['sqlquery']=$result['sqlquery'];
                            $temp['tables']=$result['tables'];
                            $finalResults[]=$temp;
                        }
                    break;
                    case "report":case "reports":
                        $result=getContentsFromReport($content);
                        if($result){
                            $temp=array_merge(getDCDefaultItem(),$val);
                            $temp['path']=str_replace($path,"/",$temp['path']);
                            $temp['link']=_link("modules/cmsEditor")."&type=edit&src=" .urlencode($temp['path']);///plugins/modules/credsRoles/style.css
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
function getDCDefaultItem($name="",$filePath="",$urlPath="") {
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

?>

