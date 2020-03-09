<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadModule("pages");

function pageSidebar() {
  return "<div id='componentTree' class='componentTree list-group list-group-root'></div>";
}

function pageContentArea() {
  return "<h3 class='text-center'>What do you want me to analyse :-)</h3>";
}

$toolBar = [
// 			["title"=>"Search Store","type"=>"search","align"=>"right"],
            
// 			['type'=>"bar"],
            
        "recreateCache"=>["icon"=>"<i class='fa fa-retweet'></i>","title"=>"Recache"],
		['type'=>"bar"],
		"showReports"=>["icon"=>"<i class='fa fa-table'></i>","title"=>"Analyse Reports"],
		"showForms"=>["icon"=>"<i class='fa fa-wpforms'></i>","title"=>"Analyse Forms"],
		"showInfoViews"=>["icon"=>"<i class='fa fa-bookmark'></i>","title"=>"Analyse InfoViews"],		
// 		"showViews"=>["icon"=>"<i class='fa fa-file-o'></i>","title"=>"Analyse Views"],
// 		"showInfoVisuals"=>["icon"=>"<i class='fa fa-area-chart'></i>","title"=>"Analyse InfoVisuals"],
];

$moduleName = basename(dirname(__FILE__));

echo _css([$moduleName]);
echo _js($moduleName);

printPageComponent(false,[
    "toolbar"=>$toolBar,
    "sidebar"=>false,
    "contentArea"=>"pageContentArea"
  ]);
?>
<style>
.panel {
    /*margin: 20px;*/
    margin-top: 0px;
    border: 0px;
}
.table-responsive{
    height:calc(100% - 122px);
}
.pageComp{
    height:auto;
}
.btn.btn-dark, .alert.alert-dark {
    color: #fff;
    background-color: #CCCCCC;
    border-color: #999;
}
.filter-buttons .btn .fa-check {
    display: none;
}
.filter-buttons .btn.active .fa-check {
    display: inline-block;
}
</style>
<script>
$(function() {
    $("#pgworkspace").delegate("a.searchResults","click", function(e) {
        return openCodeLink(this);
    })
    
});
function loadCommonUI(actionTitle, tableHead) {
    if(tableHead==null) tableHead = `<tr>
        <th>Source Name</th>
        <th>Source Path</th>
        <th>SQL Tables</th>
        <th>SQL Query</th>
      </tr>`;
      //<button type='button' class='btn btn-white' data-toggle='button'>All</button>
    $("#pgworkspace").html(`<div class='panel'> 
        <div class='filter-buttons' style='display: inline-block;width: 350px;float: right;margin-top:-5px'>
        	<div class='btn-group' style='border: 1px solid #DEDEDE;'>
        	  <button type='button' class='btn btn-danger' data-toggle='button' data-ref='alert-danger'><i class='fa fa-check'></i> Red</button>
        	  <button type='button' class='btn btn-warning' data-toggle='button' data-ref='alert-warning'><i class='fa fa-check'></i> Orange</button>
        	  <button type='button' class='btn btn-info' data-toggle='button' data-ref='alert-info'><i class='fa fa-check'></i> Blue</button>
        	  <button type='button' class='btn btn-dark' data-toggle='button' data-ref='alert-dark'><i class='fa fa-check'></i> Grey</button>
        	</div>
        </div>
    <h2>`+actionTitle+`</h2> 
<div class="table-responsive">
 
  <p></p>
  <table class="table table-bordered">
    <thead>
      `+tableHead+`
    </thead>
    <tbody class='reportBody'></tbody>
  </table>
</div>
</div>`);

    $("#pgworkspace .filter-buttons .btn").click(function() {
        setTimeout(function() {
            activateFilter();
        }, 200);
    });
}
function recreateCache() {
    // loadCommonUI("Creating Cache","");
    // $("#pgworkspace tbody").html("<tr><td colspan=20><div class='ajaxloading ajaxloading5'></div></td></tr>");
    showLoader();
    
    processAJAXQuery(_service("dcLists","create_cache"), function(data) {
        hideLoader();
        lgksToast(data.Data.msg);
        // $("#pgworkspace tbody").html("");
        
    },"json");
    
}
function showForms() {
    loadCommonUI("Form Analysis");
    html="";
    $("#pgworkspace tbody").html("<tr><td colspan=20><div class='ajaxloading ajaxloading5'></div></td></tr>");
    processAJAXQuery(_service("datacontrolsAnalyser","fetch_forms"), function(data) {
        data=data.Data;
        if(data.status==true){
            jData=data.msg;
            $.each(jData,function(k,v) {
                no=k+1;
    			html +="<tr>";
    // 			html+="<td name='no'>"+ no +"</td>";
    			html+="<td name='title'>"+v.title+"</td>";
    			html+="<td name='value'><a href='"+v.link+"' target='_blank' class='searchResults'>"+v.path+"</a></td>";
    			html+="<td name='tables'>"+v.tables+"</td>";
    			html+="<td name='sqlquery'>"+v.sqlquery+"</td>";
    			html+="</tr>";
    		});
        }else{
            html=data.msg;
        }
        $("#pgworkspace tbody").html(html);
        
    },"json");
}
function showReports() {
    loadCommonUI("Report Analysis");
    html="";
    $("#pgworkspace tbody").html("<tr><td colspan=20><div class='ajaxloading ajaxloading5'></div></td></tr>");
    processAJAXQuery(_service("datacontrolsAnalyser","fetch_reports"), function(data) {
        data=data.Data;
        if(data.status==true){
            jData=data.msg;
            $.each(jData,function(k,v) {
                no=k+1;
                if(v.className==null) v.className = "";
    			html +="<tr class='"+v.className+"'>";
    // 			html+="<td name='no'>"+ no +"</td>";
    			html+="<td name='title'>"+v.title+"</td>";
    			html+="<td name='value'><a href='"+v.link+"' target='_blank' class='searchResults'>"+v.path+"</a></td>";
    			html+="<td name='tables'>"+v.tables+"</td>";
    			html+="<td name='sqlquery'>"+v.sqlquery+"</td>";
    			html+="</tr>";
    		});
        }else{
            html=data.msg;
        }
        $("#pgworkspace tbody").html(html);
        
    },"json");
}
function showViews() {
    loadCommonUI();
    
    $("#pgworkspace tbody").html("<tr><td colspan=20><div class='ajaxloading ajaxloading5'></div></td></tr>");
}
function showInfoVisuals() {
    loadCommonUI();
    
    $("#pgworkspace tbody").html("<tr><td colspan=20><div class='ajaxloading ajaxloading5'></div></td></tr>");
}
function showInfoViews() {
    loadCommonUI();
    
    $("#pgworkspace tbody").html("<tr><td colspan=20><div class='ajaxloading ajaxloading5'></div></td></tr>");
}

function openCodeLink(src) {
    href=$(src).attr("href");
    if(href.length<3) return false;
    
    txt=$(src).text();
	txt=txt.split("/");
	txt=txt[txt.length-1];
    
    parent.openLinkFrame(txt,href);
    return false;
}
function activateFilter() {
    if($("#pgworkspace .filter-buttons .btn.active").length>0) {
        $("#pgworkspace tbody.reportBody tr").hide();
        $("#pgworkspace .filter-buttons .btn.active").each(function() {
            $("#pgworkspace tbody.reportBody tr."+$(this).data("ref")).show();
        });
    } else {
        $("#pgworkspace tbody.reportBody tr").show();
    }
}
</script>