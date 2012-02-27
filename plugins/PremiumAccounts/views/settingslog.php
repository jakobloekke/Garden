<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data['Title'];?></h1>
<style>
	table,tr,td,th,tbody,thead{
		padding:0!important;
		margin:0!important;
	}
	
	table td div{
		white-space:normal;
		word-wrap: break-word!important;
		height:200px;
		overflow:auto;
	}
	
	.flexigrid div.nBtn,.flexigrid div.nBtn *{
		display:none!important;
	}
</style>
<div class="Info">
   <?php echo T($this->Data['Description']); ?>
</div>
<div>
<table id="gridContainer" height="100%" width="100%"></table> 

<script> 
jQuery(document).ready(function($) {

var cols =[];
var rows 
$.getJSON(gdn.url('/settings/premiumlog.json'),{page:1,rc:1}, function(log) {
	$.each(log.rows[0].cell,function(name,value){
		$('gridContainer').html(cols.length)
		if($.inArray(name, ['Name','Email','Photo','Inform'])==-1)
			cols.push({'name': name,   display: name=='UserID'?'User':name, searchable: true,  sortable: true,width : name=='Log'?250:185});
	});
	cols.push({id:'blank',name: 'blank',   display: '',   sortable: false,width : 100});
	cols[0].isdefault= true;
	
	loadGrid();
});

function loadGrid(){
	$('#gridContainer').flexigrid({ 
		dataType: 'json',
		method: 'GET',
		url: gdn.url('/settings/premiumlog.json'),
		sortname:'Date',
		sortorder: 'asc',
		useRp: true,
		rp:20,
		usepager: true,
		colModel:cols,
		searchitems:cols,
		showTableToggleBtn: false,
		height: 200,
		width:'auto',
		height: $(" #flexigridDiv").innerHeight(),
		resizable:false,
		preProcess:function(log){
			
			$.each(log.rows,function(index,row){
				log.rows[index].cell['blank']='';
				if(!row.cell.Log) return;
				log.rows[index].cell.Log=row.cell.Log.replace(/[|]/g,'<br />').replace(/[:]/,':<br />').replace(/<br \/>([a-z_]+)(->)/g,'<br /><b>$1&nbsp;</b>');
				log.rows[index].cell.blank='';
				log.rows[index].cell.UserID='<a href="'+gdn.url('/user/'+row.cell.Name)+'" target="_blank">'+row.cell.Name+'</a>';
				colour='';
				switch(row.cell.Status){
					case 'force_complete':
					case 'payment_complete':
						colour='green';
						break;
					case 'payment_pending':
						colour='blue';
						break;
					case 'payment_incomplete':
						colour='orange';
						break;
					case 'payment_invalid':
						colour='red';
						break;
					case 'account_expired':
						colour='grey';
						break;
				}
				if(colour)
					log.rows[index].cell.Status='<span style="color:'+colour+';">'+row.cell.Status+'</span>';
					
				log.rows[index].cell.TransactionID='<a href="'+gdn.definition('PayPalUrl')+'?cmd=_view-a-trans&id='+row.cell.TransactionID+'" target="_blank">'+row.cell.TransactionID+'</a>';
				
			});
		
		return log;
		},
		
	    onSubmit : function(){
		$('#gridContainer').flexOptions({params: [{name:'history', value:$('#history:checked').length?1:0}]});
		return true;
	    } 
	});
	
	$('.flexigrid .pDiv .pDiv2').append($('<div class="btnseparator"></div>'+
		'<div class="pGroup"><span class="pcontrol"><input id="history" type="checkbox" />'+
		'Show historical / superseded transaction statuses</span></div>'));
	$('#history').click(function(){$('#gridContainer').flexReload();});

}
 });
</script>
<noscript>JavaScript Required</noscript>
</div>