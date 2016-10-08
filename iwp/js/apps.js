/************************************************************
* InfiniteWP Admin panel									*
* Copyright (c) 2012 Revmakx								*
* www.revmakx.com											*
*															*
************************************************************/
var totalWindowHeight='';
var totalWindowWidth='';
var bottomFullBar=0;
var reloadStatsControl=1;
var dpConsumerKey = '';
var dpConsumerSecret = '';
var scheduleAddonFlag='';
var repositoryAddonFlag='';
var installCloneAddonFlag='';
var forceBackup=0;
var manage = {};
var activeItem='plugins';
var g1 = 'w1,w2,w3,w4'; // the groups with site id seperated.
var timeOut='';
var selectedGroup = {}; // Array for grouping sites
var groupEditFlag = 0;
var incrementRand=0;
var siteSelectorVar='';
var bottomToolbarVar='';
var currentPage='';
var ajaxCallPath='ajax.php';
var groupCounter=0;
var groupCreateArray={};
var groupChangeArray={};
var groupNameArray={};
var groupDeleteArray={};
var usernameTemp, passwordTemp;
var currentPage='updates';
var state;
var currentUpdatePage;
var parentFlag=0;
var updateCheckArray={};
var viewHiddenFlag=0;
var toobarAddsite='';
var toobarHiddenUpdates='';
var historyRefreshCheckFlag = 0;
var stopAllAction=false;
var notNowUpdate=false;
var clientUpdateSites=false;
var updateAvailable;
var siteSelectorRestrictVar;
var formArrayVar={};
var runOffBrowser=0;
var clientPluginBrandingSettings;
var tempConfirmObject;
var recentLength=0;
var historyRefreshInterval,historyRefreshIntervalFlag=0;
var bottom_count = 0;
var groupOperationFlag = 1;
var currentGroupID = 0;
var tempTimeOut = 1;
var wpRepositoryFlag = 0;
var notInstalledSiteID = 0;
var test_count = 0;
var check_fsock = '';
var pauseCallRefreshCount = 0;
var showBrowserCloseWarning = 0;
var manageEasyCronActive = false;
var systemCronTimeSettings = false;
var loadUpdateNotificationPopupShown = 0;
var total_url_parts = 0;
//var this_download_backup_var = 1;
var myVar;
var timeZones={};

 
var restrictUpdatePageFlag = '';
var restrictAddEditDeleteSiteFlag = '';
var restrictToggleOpenAdminFlag = '';
var restrictToggleCreateBackupFlag = '';
var restrictTogglerestoreDeleteDownloadBackupFlag = '';
var numberOfSitesForUser = 0;
var scheduleBackupNowRestrictToggleFlag = '';
var permissions = '';
var lastBottomLineContent = ''; //currentPage
var lastBottomLinePage = '';

function echeck(str) {

	var at="@";
	var dot=".";
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
	if (str.indexOf(at)==-1){
		return false
	}

	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		return false
	}

	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		
		return false
	}

	if (str.indexOf(at,(lat+1))!=-1){
		return false
	}

	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		return false
	}

	if (str.indexOf(dot,(lat+2))==-1){
		return false
	}
	
	if (str.indexOf(" ")!=-1){
		return false
	}

	return true					
}
Date.prototype.addDays = function(days) {
	this.setDate( this.getDate()  + days);
	return this;
};
/* 
* To Title Case 2.0.1 – http://individed.com/code/to-title-case/
* Copyright © 2008–2012 David Gouch. Licensed under the MIT License. 
*/
String.prototype.Trim = function()
{
	return this.replace(/(^\s*)|(\s*$)/g, "");
}

String.prototype.toTitleCase = function () {
	var smallWords = /^(a|an|and|as|at|but|by|en|for|if|in|of|on|or|the|to|vs?\.?|via)$/i;

	return this.replace(/([^\W_]+[^\s-]*) */g, function (match, p1, index, title) {
		if (index > 0 && index + p1.length !== title.length &&
				p1.search(smallWords) > -1 && title.charAt(index - 2) !== ":" && 
				title.charAt(index - 1).search(/[^\s-]/) < 0) {
			return match.toLowerCase();
		}

		if (p1.substr(1).search(/[A-Z]|\../) > -1) {
			return match;
		}

		return match.charAt(0).toUpperCase() + match.substr(1);
	});
}
function isNumber(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}
function getPropertyCount(obj) {
	var count = 0,
	key;

	for (key in obj) {
		if (obj.hasOwnProperty(key)) {
			count++;
		}
	}

	return count;
}
function addNumber(object, id, numToAdd) 
{
	if(typeof numToAdd == 'undefined')
	{
		numToAdd = 1;
	}
	if($(".dialog_cont #backupTotal").val()=="" || $(".dialog_cont #backupTotal").val()=="0")
	$(".dialog_cont #backupTotal").val("1");
	if($(object).hasClass('incr'))
	type=1;
	else if($(object).hasClass('decr'))
	type=0;
	val = $("#"+id).val();
	if(type==0 && val!=1 && val!=0)
	{
		$("#"+id).val(parseInt(val)-numToAdd);
	}
	else if(type==1)
	$("#"+id).val(parseInt(val)+numToAdd);
}

function centerDialog()
{
	$('#modalDiv').dialog({width:'auto',modal:true,position: 'center',resizable: false});
}
function bottomToolBarShow()
{
	dynamicResize();
	
}
function bottomToolBarHide()
{
	$("#bottom_sites_cont").hide();
	$(".showFooterSelector").removeClass('pressed');
	$("#dynamic_resize").css("margin-left","0");
}
function dynamicResize(forceAction)
{
	if(forceAction==undefined)
	forceAction=0;
	if($('[role="dialog"]').is(":visible") && forceAction==0)
	return false;
	
	var totalWindowHeight=$(window).height();

	var totalWindowWidth=$(window).width();
	var totalWindowHalfWidth = Math.round(totalWindowWidth/2);

	if(totalWindowWidth<1400)
	{

		$(".social_love").hide();
	}
	else
	$(".social_love").show();

	if(totalWindowWidth>=1250)
	{
		bottomFullBar=1;
		$("#dynamic_resize").css({"margin-left":"260px"});
		$("#bottom_toolbar #bottom_sites_cont").height(totalWindowHeight-32).css("margin-top","-"+(totalWindowHeight-35)+"px").show();
		$("#bottom_toolbar #bottom_sites_cont #bottom_left,#bottom_toolbar #bottom_sites_cont #bottom_right").height(totalWindowHeight-34);	
		$("#bottom_toolbar #bottom_sites_cont .list_cont").height(totalWindowHeight-112);	
		$("#bottomToolBarSelector .bg_yellow").removeClass('bg_yellow');
		$(".showFooterSelector").addClass('pressed');
		var newLoadingWidth = parseInt(totalWindowHalfWidth)+parseInt(130);
		$("#loadingDiv").css('left',newLoadingWidth);
	}
	else {
		bottomFullBar=0;
		$("#dynamic_resize").css({"margin-left":"0"});
		$("#bottom_toolbar #bottom_sites_cont").height(401).css("margin-top","-400px").show();
		$("#bottom_toolbar #bottom_sites_cont #bottom_left,#bottom_toolbar #bottom_sites_cont #bottom_right").height(401);	
		$("#bottom_toolbar #bottom_sites_cont .list_cont").height(324);	
		$("#loadingDiv").css('left',totalWindowHalfWidth);
		
	}
}
function closeDialogs(type,object) // type 1 for update center 2 for settings dialog
{
	if($('#ui-tooltip-manageGroupsQtip').length>0)
	$(".toggle_manage_groups").qtip('destroy');
	if(groupEditFlag!=1 && bottomFullBar!=1)
	{
		$("#bottom_sites_cont").hide();
		resetBottomToolbar();
	}
	$("#historyQueue").hide();
	if($("#settings_cont").is(":visible") && type!=2 )
	{
		$("#settings_btn").removeClass('active');
		$("#settings_cont").hide();
	}

	if($("#updates_centre_cont").is(":visible") && stopAllAction!=true && type!=1)
	{
		$("#updateCentreBtn").removeClass('active');
		$("#updates_centre_cont").hide();
	}
	if($(".notification_centre_cont").is(":visible")){
		$(".notification_centre_cont").hide();
		$(".notif_btn").removeClass("active");
	}
	if(scheduleAddonFlag==1)
	$(".dialog_cont .time_select_options").hide();
	$(".dropdownToggle").hide();
	$(".dropdown_btn").removeClass('open');

}
function loadFixedNotifications()
{	
	if(fixedNotifications!=null &&  fixedNotifications!=undefined && getPropertyCount(fixedNotifications)>0)
	{
		$.each(fixedNotifications, function(i, object) {
			$(".notification_cont").append('<div class="notification '+object.type.toLowerCase()+'"><div class="notif_title">'+'&nbsp;&nbsp;&nbsp;'+object.title+'</div><div class="notif_body">'+object.message+'</div><div class="n_close"></div></div>');
		});
	}

}

function processFireQueue(){
	if (typeof fireQueue == 'undefined'){
		return false;
	}
	if(typeof fireQueue.stagingCompleted != 'undefined') {
		stagingProcessFireQueue();
	}
	if (typeof fireQueue.installCloneCompleted != 'undefined') {
		installCloneProcessFireQueue();
	}
}
function checkUpdateEmpty()
{
	if($("#siteViewUpdateContent .ind_row_cont").not(".hide,.hideVar").length<1 && $("#siteViewUpdateContent .empty_data_set").not('.hiddenCheck').css('display')=='none')
	$("#siteViewUpdateContent .hiddenCheck").show();
	if($("#themeViewUpdateContent .ind_row_cont").not(".hide,.hideVar").length<1 && $("#themeViewUpdateContent .empty_data_set").not('.hiddenCheck').css('display')=='none')
	$("#themeViewUpdateContent .hiddenCheck").show();
	if($("#pluginViewUpdateContent .ind_row_cont").not(".hide,.hideVar").length<1 && $("#pluginViewUpdateContent .empty_data_set").not('.hiddenCheck').css('display')=='none')
	$("#pluginViewUpdateContent .hiddenCheck").show();
	if($("#WPViewUpdateContent .ind_row_cont").not(".hide,.hideVar").length<1 && $("#WPViewUpdateContent .empty_data_set").not('.hiddenCheck').css('display')=='none')
	{
		$("#WPViewUpdateContent .hiddenCheck").show();
	}
}
function siteSelectorNanoReset(type)
{
	if ( $.browser.msie && $.browser.version=='8.0') {
		$(".site_selector1 .bywebsites .website_items_cont .website_cont:nth-child(3n+3)").css({"width":"235px", "border-right":"0"});
	}
	$(".siteSelectorContainer .nano").nanoScroller({scroll: 'top',stop: true});
	$(".siteSelectorContainer .group_items_cont").css('height',$(".siteSelectorContainer .group_items_cont").height()).addClass('nano');
	$(".siteSelectorContainer .website_items_cont").css('height',$(".siteSelectorContainer .website_items_cont").height()).addClass('nano');
	$(".siteSelectorContainer .nano").nanoScroller();
	if(type==1)
	{
		$("#bottomToolBarSelector .nano").nanoScroller({stop: true});
		$("#bottomToolBarSelector .nano").nanoScroller();
	}
}

function applyChangesCheck()
{
	if($(".actionButton").hasClass('active'))
	$(".status_applyChangesCheck").removeClass('disabled');
	else
	$(".status_applyChangesCheck").addClass('disabled');
}
function historyRefresh()
{
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['getHistoryPanelHTML']=1;
	doCall(ajaxCallPath,tempArray,'reloadHistory','json',"noProgress",1);
	
}
function setTooltipData(data)
{
	toolTipData=data.data.getUserHelp;
	if(toolTipData.manageGroups=="true")
	$(".toggle_manage_groups").qtip({id:"toggleGroupQtip",content: { text: 'Manage Groups' }, position: { my: 'bottom center', at: 'top center',  adjust:{ y: -6} }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 10, height:5} } });
}
/*function processRepository(data)
{
	data=data.data.getWPRepositoryHTML;
	var content=content+data;
	if(activeItem=='plugins')
		content=content+'</div>';
		else
		content=content+'<div class="clear-both"></div></div></div>';
	$(".wp_repository_cont").html(content);
}*/
function getTotalUpdates(mnjson)
{
	var gpluginsjson = mnjson.pluginsView.plugins;
	var gthemesjson = mnjson.themesView.themes;
	var gwpjson = mainJson.coreView.core;
	if(gwpjson!=undefined)
	var WPCount= getPropertyCount(gwpjson);
	else
	var WPCount=0;
	if(gpluginsjson!=undefined)
	var pluginsCount=getPropertyCount(gpluginsjson);
	else
	var pluginsCount=0;
	if(gthemesjson!=undefined)
	var themesCount=getPropertyCount(gthemesjson);
	else
	var themesCount=0;
	var totalCount = WPCount+pluginsCount+themesCount;
	return totalCount;
}
function resetBottomToolbar(){
    if(bottomFullBar != 1){
        $(".showFooterSelector").removeClass('pressed');
        $("#bottom_sites_cont").hide();
    }
    $("#bottomToolBarSelector .bg_yellow").removeClass('bg_yellow');
    $("#bottomToolbarOptions").remove();
    $(".bottomSites").find('a').css('color','#555');
	$('.bottomSites').attr('style', 'border-bottom: 1px solid #d8dcdf;');
}

function processRemoveSite(data)
{
	var errdata=data.actionResult.detailedStatus[0];
	$("#modalDiv .btn_loadingDiv").remove();

	if(errdata.status!='success')
	{
		
		var errorMsg='Site removed from the panel.<br>However following error occurred:<span class="error_txt"> '+errdata.errorMsg+'</span>';
		var errContent='<span class="successMsg"><span class="success_icon"></span>'+errorMsg+'</span>';
		$("#removeSiteCont").html(errContent);
		$("#removeSiteButtons").hide();
		
	}
	else
	setTimeout(function () {	$("#modalDiv").dialog("close");},1000);
	isAddonSuiteMiniLimitExceeded = data.data.checkIsAddonSuiteMiniLimitExceeded;
	addonSuiteMiniLimit = data.data.getAddonSuiteMiniLimit;	
	addonSuiteMiniActivity = data.data.getAddonSuiteMiniActivity;
		
	resetVars(data);
	refreshStats(data,1);
	clientUpdateNotificationPush(data);
	 
}
function processEditSite(data)
{
	$(".addSiteButton").removeClass('disabled');
	$("#modalDiv .btn_loadingDiv").remove();
	if(data.data.updateSite==false)
	{
		var errContent='<span id="addSiteErrorMsg"><span class="fail_icon"></span>'+errorMsg+'</span>';
		$("#addSiteSprite").after(errContent);
	}
	else
	{
		$(".add_site.form_cont").html('<span id="addSiteSuccessMsg"><span class="success_icon"></span>Site successfully edited.</span>');
		$(".addSiteButton").hide();
		setTimeout(function () {
			$("#modalDiv").dialog("close");
			lazyLoadOnlyVisibleImages(); },2000);
		resetVars(data);
		refreshStats(data,1);
	}
	
}
function processAddSite(data)
{

	$(".addSiteButton").removeClass('disabled');
	$("#modalDiv .btn_loadingDiv").remove();
	
	isAddonSuiteMiniLimitExceeded = data.data.checkIsAddonSuiteMiniLimitExceeded;
	addonSuiteMiniLimit = data.data.getAddonSuiteMiniLimit;	
	addonSuiteMiniActivity = data.data.getAddonSuiteMiniActivity;
	if(data.actionResult==false && addonSuiteMiniLimitExceeded('addSite')) return(false);
	
	var errdata=data.actionResult.detailedStatus[0];
	if(errdata.status!='success')
	{
		if(errdata.error=='main_plugin_connection_error')
		{
			var webURL=$("#adminURL").val();
			webURL= webURL.Trim();
			if(webURL.charAt( webURL.length-1 )!="/")
			webURL = webURL+"/";
			var search_wpadmin = webURL.search("wp-admin");
			if(search_wpadmin > 0)
			{
				var plink=webURL+'plugin-install.php?tab=search&type=term&s=InfiniteWP&plugin-search-input=Search+Plugins';
			}
			else
			{
				var plink=webURL+'wp-admin/plugin-install.php?tab=search&type=term&s=InfiniteWP&plugin-search-input=Search+Plugins';
			}
			var errorMsg='Yikes! It appears IWP plugin has not been installed in this site. Click here to <a href="'+plink+'" target="_blank">Install</a> it.<br>OR<br>If you have already installed IWP Plugin, deactivate and activate it now.';
		}
		else
		var errorMsg=errdata.errorMsg;
		if(errdata.error=="404" || errdata.error=="402" || errdata.error=="403")
		errorMsg+='<br><a href="'+supportURL+'support/solutions/articles/212260-402-403-404-error-while-adding-site/'+GAHelpTracking+'" target="_blank">Try this possible solution</a>';
		var errContent='<span id="addSiteErrorMsg"><span class="fail_icon"></span>'+errorMsg+'</span>';
		$("#addSiteSprite").after(errContent);
	}
	else
	{
		$("#modalDiv").dialog("close");
		
		resetVars(data);
		refreshStats(data,1);
		$("#bottom_sites_cont").show();
		$(".showFooterSelector").addClass("pressed");
		var lastKey=getLastKey(data.data.getSites);
		
		//for tweeting
		var this_count = getPropertyCount(data.data.getSites);
		if(this_count >= 3 && threeSitesTweetStatus != "done"){
			showTweetDialog("three_sites");
		}
		
		$("#bottomToolBarSelector #bottom_left .nano").nanoScroller({ scroll: 'bottom' });
		$("#bottomToolBarSelector #bottom_left .bottomSites#s"+lastKey).effect("highlight", {}, 3000);
	}
	if(typeof data.data.getRecentPluginsStatus != 'undefined')
		pluginsStatus = data.data.getRecentPluginsStatus; 
	if(typeof data.data.getRecentThemesStatus != 'undefined')
		themesStatus = data.data.getRecentThemesStatus; 
	clientUpdateNotificationPush(data,1)
}
function processReaddSite(data){
	var tmpData=data;
	data = data.data.getReaddedSite;
	if(typeof data != 'undefined' && typeof data.siteID != 'undefined' ){
		var siteID = data.siteID;
		var obj = $('.ind_sites[sid="'+siteID+'"]');
		obj.removeClass('disconnected');
	}
	$("#modalDiv").dialog("destroy");
	clientUpdateNotificationPush(tmpData,1);

}
function processMaintenanceSite(data){
	$("#maintenanceSiteConfirm").removeClass('disabled');
	$("#loadingDiv").hide();
	data = data.data.iwpMaintenance;
	if(typeof data != 'undefined' && typeof data.siteID != 'undefined' && typeof data.action != 'undefined'){
		var siteID = data.siteID;
		if(data.action == 'maintenance0'){
			$('.ind_sites[sid="'+siteID+'"]').removeClass('maintenance');
			site[siteID].connectionStatus = 1;
		}else if(data.action == 'maintenance1' && !($('.ind_sites[sid="'+siteID+'"]').hasClass('maintenance')) ){
			$('.ind_sites[sid="'+siteID+'"]').addClass('maintenance');
			site[siteID].connectionStatus = 2;
		}
	}
	$("#modalDiv").dialog("destroy");

}
function processUpdateNotes(data){
	data = data.data.iwpUpdateNotes;
	if(typeof data != 'undefined' && typeof data.siteID != 'undefined' ){
		var siteID = data.siteID;
		var notes = data['notes'];
		site[siteID]['notes'] = notes;
		if(notes == '' || notes == null || typeof notes == 'undefined') notes = '<a class="add_notes">Add Notes</a>';
		var btmSiteSnap = $('.site_flap_cont_data[btsiteid="'+siteID+'"]');
		btmSiteSnap.find('.edit_note').show();
		btmSiteSnap.find('.edit_site_notes,.save_note').remove();
		btmSiteSnap.find('.site_notes').html(notes).show();
		btmSiteSnap.find('.edit_links').parent().css({'height':$('.site_links').parent().height()+'px'});
		btmSiteSnap.find('.edit_note').parent().css({'height':$('.site_notes').parent().height()+'px'});
               
	}
}
function processUpdateLinks(data){
	data = data.data.iwpUpdateLinks;
	if(typeof data != 'undefined' && typeof data.siteID != 'undefined' ){
		var siteID = data.siteID;
		var links=data.links;
		var linksHTML='';
		site[siteID]['links'] = links;
		if(typeof links != 'undefined' && links != null && links.length){
			for(var i=0;i<links.length;i++){
				if(links[i] != '' && links[i]!='http://'){
					if(links[i].indexOf('http://') != 0 ) links[i] = 'http://'+links[i];
					linksHTML += '<a style="display:block;" target="_blank" href="'+links[i]+'">'+links[i]+'</a>';
				}
			}
		}
		if(linksHTML == '')linksHTML = '<a class="add_links">Add Links</a>';
		var btmSiteSnap = $('.site_flap_cont_data[btsiteid="'+siteID+'"]');
		btmSiteSnap.find('.edit_links').show();
		btmSiteSnap.find('.edit_site_links,.save_links').remove();
		btmSiteSnap.find('.site_links').html(linksHTML).show();
		btmSiteSnap.find('.edit_links').parent().css({'height':$('.site_links').parent().height()+'px'});
		btmSiteSnap.find('.edit_note').parent().css({'height':$('.site_notes').parent().height()+'px'});
	}
}
function updateCountRefresh()
{
	$("#totalUpdateCount").text(mainJson.totalUpdateCount);
	$("#lastReloadTime").text(mainJson.lastReloadTime);
	if(typeof recentLength != 'undefined' )
	{
		$("#totalCommentCount").text(recentLength);
		if(recentLength<1)
		{
			$('.commentCountClass').hide();
		}
		else
		{
			$('.commentCountClass').text(recentLength).show();
		}
	}
}
function processClientUpdate(data)
{
	if(typeof data == 'undefined'){
		return false;
	}
	if(loadUpdateNotificationPopupShown != 1)
	{   
		if (clientUpdatesAvailable != false) {
		 
		 if(clientUpdatesAvailable.siteIDs != false && clientUpdatesAvailable.siteIDs != 'undefined')  {
		 	clientUpdateSites=clientUpdatesAvailable.siteIDs;
		 }
                var changeLogContent = '';
                var newVersion = '<a href="http://wordpress.org/plugins/iwp-client/changelog/" target="_blank">'+clientUpdatesAvailable.clientUpdateVersion+'</a>';
                if((typeof clientUpdatesAvailable.clientUpdateChangeLog != 'undefined' )&&(clientUpdatesAvailable.clientUpdateChangeLog != '') &&(clientUpdatesAvailable.clientUpdateChangeLog != null)) {
                    changeLogContent = clientUpdatesAvailable.clientUpdateChangeLog;
                    newVersion = clientUpdatesAvailable.clientUpdateVersion;
                }
         }       
         else {
		        if(data.hasOwnProperty('siteIDs')){
					clientUpdateSites=data.siteIDs;
				} 
				var changeLogContent = '';
                var newVersion = '<a href="http://wordpress.org/plugins/iwp-client/changelog/" target="_blank">'+data.clientUpdateVersion+'</a>';
                if((typeof data.clientUpdateChangeLog != 'undefined' )&&(data.clientUpdateChangeLog != '') &&(data.clientUpdateChangeLog != null)) {
                    changeLogContent = data.clientUpdateChangeLog;
                    newVersion = data.clientUpdateVersion;
                }

        }
         
      
    if ($("#modalDiv").dialog( "isOpen" ) != true) {  
		var content='<div class="dialog_cont update_client_plugin"> <div class="th rep_sprite"> <div class="title droid700">IMPORTANT UPDATE</div> <a class="cancel rep_sprite_backup notNowUpdate">cancel</a></div> <div style="padding:20px;"><div style="text-align:center;line-height: 20px;" >An important update to the <span class="droid700">IWP Client Plugin</span> version '+newVersion+' is available.<div>We <span class="droid700">highly recommend</span> that you update it on all your sites.</div></div></div> '+changeLogContent+'<div class="clear-both"></div> <div class="th_sub rep_sprite" style="border-top:1px solid #c6c9ca;"><div class="btn_action float-right"><a class="rep_sprite" id="updateClientConfirm">Update Now</a></div> <span class="float-right cancel notNowUpdate" >Not Now</span> </div> </div>';
			$("#modalDiv").dialog("destroy");
			$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
		}
	}
}
function clientPluginUpdatesNotification(data)
{
	
	if (data) {
		clientPluginUpdateSiteIDsCount=data.length;
	} 
	else if (clientUpdatesAvailable!=false) {
	 	clientPluginUpdateSiteIDsCount=clientUpdatesAvailable.siteIDs.length;
	}

	if (clientPluginUpdateSiteIDsCount<2) {
  		$('.updateClientCount').html(clientPluginUpdateSiteIDsCount+' site now.');

	} 
	else {
  		$('.updateClientCount').html(clientPluginUpdateSiteIDsCount+' sites now.');
	}
	$('.clientUpdateNotification').show(); 

}
function formArrayRefreshStats(data)
{
	formArrayVar[data.actionResult.actionID]={};
	formArrayVar[data.actionResult.actionID]['function']="refreshStats";
}

function formArrayLoadPlugins(data)
{
	formArrayVar[data.actionResult.actionID]={};
	formArrayVar[data.actionResult.actionID]['function']="loadPlugins";
	
}

function formArrayClientUpdate(data)
{
	formArrayVar[data.actionResult.actionID]={};
	formArrayVar[data.actionResult.actionID]['function']="clientUpdate";
}
function formArrayUpdateAll(data)
{
	formArrayVar[data.actionResult.actionID]={};
	formArrayVar[data.actionResult.actionID]['function']="loadUpdateAll";
	
}

function refreshStats(data,refreshClientUpdate,noReload)
{

	if(refreshClientUpdate==undefined)
	refreshClientUpdate=0;
	
	$("#reloadStats").removeClass('disabled');
	$(".btn_reload_drop").show();
	$(".btn_reload_drop").removeClass('disabled');
	$(".btn_reload_drop").closest('div').removeClass('disabled');
	$("#reloadStats").closest('div').removeClass('disabled');
	$('.fa.fa-repeat').removeClass('fa-spin').css('color' ,'');
	$("#clearPluginCache").removeClass('active');
	mainJson=data.data.getSitesUpdates;
	sitesjson = mainJson.siteView;
	pluginsjson = mainJson.pluginsView.plugins;
	themesjson = mainJson.themesView.themes;
	translationsjson=mainJson.translationsView.translations;
	wpjson = mainJson.coreView.core;
	if(!iwpIsEmpty(mainJson.wpVulView)){
		wpVulnsPluginsjson =  mainJson.wpVulView.plugins;
		wpVulnsThemesJson =  mainJson.wpVulView.themes;
		wpVulnsWpJson =  mainJson.wpVulView.wp;
		if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
			mergeWpVulnsDataIntoSitesJson();
		}
	}
	pluginsUpdateCount =arrUpdateCount(pluginsjson);
	WPUpdateCount =arrUpdateCount(wpjson);
	themesUpdateCount=arrUpdateCount(themesjson);
	transUpdateCount =arrUpdateCount(translationsjson);
	hiddenUpdateCount = mainJson.hiddenUpdateCount;
        site=data.data.getSites;
	if((typeof isComment != 'undefined' )&&(isComment == 1))                                                        //To load Recent Comments While Reload Data
	{
		
		if(((data.data.manageCommentsGetRecent)!= undefined))
		{
			
			commentsJson=data;
			recentLength=commentsJson.data.manageCommentsGetRecent.siteView;  
			if(recentLength!=undefined)
			{
				recentLength=objLen(recentLength);
			}
			else
			{
				recentLength=0;
			}
		}
	}	
	if(typeof isGoogle != 'undefined')
	{   
		if(isGoogle==1)
		{
			googleJson=data;
		}
	}
	if(typeof isGoogleWM != 'undefined')
	{   
		if(isGoogleWM==1)
		{
			googleWMJson=data;
		}
	}
	updateCountRefresh();
	if(typeof data.data.getRecentPluginsStatus != 'undefined')
		pluginsStatus = data.data.getRecentPluginsStatus; 
	if(typeof data.data.getRecentThemesStatus != 'undefined')
		themesStatus = data.data.getRecentThemesStatus; 

	if(noReload!=1){
		processPage(currentPage,1);	
	}
	
	if(data.data.getClientUpdateAvailableSiteIDs != undefined){
		clientUpdatesAvailable=data.data.getClientUpdateAvailableSiteIDs;
		if (data.data.getClientUpdateAvailableSiteIDs.siteIDs != undefined) {
			clientPluginUpdatesNotification(data.data.getClientUpdateAvailableSiteIDs.siteIDs);
		}
		
	}
	if(data.data.getClientUpdateAvailableSiteIDs != false && notNowUpdate == false && refreshClientUpdate == 0 )
	{
		processClientUpdate(data.data.getClientUpdateAvailableSiteIDs);
	}
	/* if(1==1)
	{
	processClientUpdate(data.data.getClientUpdateAvailableSiteIDs);
	} */
	if(typeof isComment != 'undefined')
	{
		if($('.recentComments').hasClass('active'))
		{
			loadCommentManage();
		}
	}
	updateSitesStatusColor();
	
	lazyLoadOnlyVisibleImages();
}

function updateSitesStatusColor(){
	updateSitesConnectionStatusColor();
	updateSitesClientUpdateColor();
	if (typeof processPageAccess != "undefined") {
		var res = processPageAccess("updates");
	}
	if(typeof res !="undefined" && res && typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
		updateSiteVulnsStatus();
	}
}

function updateSitesConnectionStatusColor(){
	if(typeof site != 'undefined' && site != null ){
		$.each(site,function(siteID,siteData){
			switch(parseInt(siteData.connectionStatus)){
				case 0: 
					var obj = $('.ind_sites#s'+siteID);
					obj.removeClass('maintenance toUpdate'); 
					if(!obj.hasClass('disconnected')){
						applySiteStatusColor(siteID, 'disconnected');
					}
				break;
				case 1:
					var obj = $('.ind_sites#s'+siteID);
					obj.removeClass('disconnected maintenance');
				break;
				case 2:
					var obj = $('.ind_sites#s'+siteID);
					obj.removeClass('disconnected toUpdate'); 
					if(!obj.hasClass('maintenance')){
						applySiteStatusColor(siteID, 'maintenance');
					}
				break;
			}
		});
	}
}

function updateSitesClientUpdateColor(){
	$('.ind_sites').removeClass('toUpdate');
	if (clientUpdatesAvailable != undefined && clientUpdatesAvailable != false && clientUpdatesAvailable.siteIDs != undefined)  {
		$.each(clientUpdatesAvailable.siteIDs,function(index,siteID){
			applySiteStatusColor(siteID, 'toUpdate');
		});
	}
}

function applySiteStatusColor(siteID, type){
	switch(type) {
	    case 'disconnected':
			var obj = $('.ind_sites#s'+siteID);
			obj.addClass('disconnected');
	        break;
	    case 'maintenance':
	        var obj = $('.ind_sites#s'+siteID);
			obj.addClass('maintenance');
	        break;
		case 'toUpdate':
			var obj = $('.ind_sites#s'+siteID);
			if ( obj.hasClass('disconnected') != true && obj.hasClass('maintenance') != true && obj.hasClass('vulnsUpdate') != true) {
				obj.addClass('toUpdate');
			}
			break;
	    default:
	       return false;
	}
}


function clientUpdateNotificationPush(data,popup){
	if (data != undefined && data.data != undefined) {
		if (data.data.getClientUpdateAvailableSiteIDs == false ) {
			$('.clientUpdateNotification').hide(); 	
			clientPluginUpdateSiteIDsCount=false;
			clientUpdatesAvailable = false;
			updateSitesClientUpdateColor();
		}
		else if (data.data.getClientUpdateAvailableSiteIDs != undefined) {
			clientUpdatesAvailable=data.data.getClientUpdateAvailableSiteIDs;
			if (data.data.getClientUpdateAvailableSiteIDs.siteIDs != undefined) {
				clientPluginUpdatesNotification(data.data.getClientUpdateAvailableSiteIDs.siteIDs);
				updateSitesClientUpdateColor();
			}
			if (popup == 1) {
				processClientUpdate(data.data.getClientUpdateAvailableSiteIDs);
			}
		}
	}
}

function get_settings_loader(data){
	pluginsStatus = data.data.getRecentPluginsStatus; 
	themesStatus  = data.data.getRecentThemesStatus;
	
	var FTPdetails = data.data.getFTPValues;
	var tempArray  = {};
	tempArray['requiredData'] = {};
	tempArray['requiredData']["getConfigFTP"] = 1;
	doCall(ajaxCallPath,tempArray,"showFTPDetails");
	if(typeof FTPdetails != 'undefined' && FTPdetails != null){
		settingsData['data']['getSettingsAll']['settings']['FTP'] = FTPdetails;
	}
	timeZones = data.data.getTimeZones.timeZones;
}

function showFTPDetails (ftp) {
    if(ftp.data.getConfigFTP != 0){
    	settingsData['data']['getSettingsAll']['settings']['FTP'] = ftp.data.getConfigFTP;
    }
}

function validateZipURL(url)
{
	
	var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
	if(url.match(pattern))
	return true;
	else
	return false;
}

function pauseCallRefresh(isData, pauseTheCallInterVal)
{
	//This is the function which will pause the next call once the response is received for n secs.
	if(isData)
	{
		pauseCallRefreshCount++;
	}
	if(pauseCallRefreshCount >= pauseTheCallInterVal)
	{
		clearInterval(pauseCallRefreshInterval);
		pauseCallRefreshCount = 0;
		historyRefresh();
	}
	
}
function reloadHistory(data)
{
	var checkHistoryVar=0;
	bottom_count = bottom_count+1;
	if(!$("#historyQueue").is(":visible"))	 checkHistoryVar=1;
	
	var pauseTheCall = 0;			//deciding whether we need to pause the call for few seconds.
	var pauseTheCallInterVal = 1;	//the secs value for call pause.
	
	$("#historyQueue .nano").nanoScroller({stop: true});
	/*$("#historyQueue .queue_detailed").nanoScroller({stop: true});*/
	
	var returnDataLength = getPropertyCount(data.data);
	
	if(typeof data.showBrowserCloseWarning != "undefined")
	{
		if(typeof data.showBrowserCloseWarning != 'undefined' && data.showBrowserCloseWarning != false)
		{
			showBrowserCloseWarning = data.showBrowserCloseWarning;
		}
		else
		{
			showBrowserCloseWarning = false;
		}
	}
	if(data.data!=undefined && data.data.getWaitData!=undefined && getPropertyCount(data.data.getWaitData)>0 && formArrayVar!=undefined && getPropertyCount(formArrayVar)>0)
	{
		clearInterval(historyRefreshInterval);
		
		if(pauseTheCall == 1)
		{
			//call the pauseCallRefresh function for every one second.
			pauseCallRefreshInterval = setInterval(function () {if(true){ $("#historyQueueUpdateLoading").addClass('loading'); pauseCallRefresh(1,pauseTheCallInterVal);}else if(!$(".queue_detailed").is(":visible")) { $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh(); } }, (1000));
		}
		else
		{
			historyRefreshInterval=  setInterval(function () {if(true){ $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh();}else if(!$(".queue_detailed").is(":visible")) { $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh(); } }, (forcedAjaxInterval*1000));
		}

		$.each(formArrayVar, function(actionID, actionData) {
			if( data.data.getWaitData[actionID] != undefined && data.data.getWaitData[actionID].total!=undefined)
			{
				
				if(formArrayVar[actionID].initated==undefined)
				{
					if(actionData["function"]=="loadUsers")
					{
						$(".loadUsersBtn").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadUsersProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadPlugins")
					{
						$(".fetchInstall").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadPluginsProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadComments")     //Change Load  Comments button..to Green
					{
						$(".fetchManageComments").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadCommentsProcess" style="margin: 8px 30px 8px 0px; width: 89px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadMalwareData")     //Change Load  Comments button..to Green
					{
						$(".loadMalware").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadMalwareProcess" style="margin: 8px 30px 8px 0px; width: 89px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadPosts")     //Change load Posts button to greeen
					{
						$(".fetchPosts").closest(".btn_action").hide().after('<div class="btn_reload_progress float-left" id="loadPostsProcess" style="margin: 8px 30px 8px 0px; width: 66px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadOptimize")     //Change load Optimize button to greeen
					{
						$(".loadOptimize").closest(".btn_action").hide().after('<div class="btn_reload_progress float-left" id="loadOptimizeProcess" style="margin: 8px 30px 8px 0px; width: 64px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadLinks")  //Change load Links button to green
					{
						$(".fetchPosts").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadLinksProcess" style="margin: 8px 30px 8px 0px; width: 60px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="refreshStats")
					{
						$("#reloadStats").closest(".btn_reload").hide().after('<div class="btn_reload_progress float-right" id="refreshStatsProcess" style="border-radius: 3px; padding: 7px 26px 7px;margin: 2px -1px 7px 7px;width:84px;"><i class="fa fa-repeat fa-spin" style="position: relative;font-size: 15px;left: -17px;top: -2px;float: left;color:rgba(82, 140, 79, 0.5);"></i><span class="processCount" style="margin-left:-10px;">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" style="margin: -2px -19px 0 0; font-size:17px;" actionid="'+actionID+'"></div></div>');
						$(".btn_reload_drop").hide();
						$(".btn_stop_reload").qtip({content: { text: 'Stop reload &amp; display loaded data' }, position: { my: "top right",at: "bottom right",adjust:{x:-6,y:10} }, show: { event: 'mouseenter' }, hide: { distance: 20 }, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 9, height:4 } } });
					}
					if(actionData["function"]=="runCode")
					{
						$("#runSnippetCode").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="runCodeProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadbrokenLinksGetAllLinksResult")
					{
						$(".load_bls_main").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadBLAllLinksProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="getBrokenLinksUpdateLink")
					{
						$(".load_bls_main").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadBLUpdateLinkProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="getBrokenLinksUndismissLink")
					{
						$(".load_bls_main").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadBLUndismissLinkProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadbrokenLinksBulkActionsResult")
					{
						$(".load_bls_main").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadBLBulkActionsProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadYoastSEOGetInfoResult")
					{
						$(".load_yseo_main").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadYoastResultsProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="loadYoastSEOSaveInfoResult")
					{
						$(".load_yseo_main").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadYoastResultsProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="getGoogleWebMastersRedirect")
					{
						$(".loadWebMastersBtn").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="googleWebMastersRedirectProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="getGoogleWebMastersRedirectAgain")
					{
						$(".loadWebMastersBtn").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="googleWebMastersRedirectAgainProcess" style="margin: 8px 30px 8px 0px; width: 69px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');
					}
					if(actionData["function"]=="FEUploaderResult")
					{
					}					
                    if(actionData["function"]=="loadwordFenceResult")
                    {
                    $("#LoadWordfence").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="loadWordfenceProcess" style="margin: 8px 30px 8px 0px; width: 150px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');	
                    }
                    if(actionData["function"]=="ithemesSecurityLoad")
                    {
                    $("#LoadIthemesSecurity").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="LoadIthemesSecurityProcess" style="margin: 8px 30px 8px 0px; width: 150px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');	
                    }
                    if(actionData["function"]=="ScanWordFence")
                    {
                    $("#scanWordfence").closest(".btn_action").hide().after('<div class="btn_reload_progress float-right" id="scanWordfenceProcess" style="margin: 8px 30px 8px 0px; width: 70px;"><span class="processCount">'+data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total+'</span> sites<div class="btn_stop_reload stopCall" actionid="'+actionID+'"><div class="tip">Stop reload &amp; display loaded data</div></div></div>');	
                    }
					formArrayVar[actionID].initated=1;
					
				}
				else
				{
					if(actionData["function"]=="refreshStats")
					$("#refreshStatsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="loadPlugins")
					$("#loadPluginsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="loadComments")
					{		 
						$("#loadCommentsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total); //load sites count on load comments button
					}
					if(actionData["function"]=="loadMalwareData")
					{		 
						$("#loadMalwareProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total); //load sites count on load comments button
					}
					if(actionData["function"]=="loadOptimize")
					{		 
						$("#loadCommentsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total); //load sites count on load comments button
					}
					if(actionData["function"]=="loadLinks")
					{		 
						$("#loadLinksProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);  //load sites count on load links button
					}
					if(actionData["function"]=="loadPosts")
					{		 
						$("#loadPostsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);  //load sites count on load links button
					}
					if(actionData["function"]=="loadUsers")
					$("#loadUsersProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="runCode")
					$("#runCodeProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="loadbrokenLinksGetAllLinksResult")
						$("#loadBLAllLinksProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="getBrokenLinksUpdateLink")
						$("#loadBLUpdateLinkProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="getBrokenLinksUndismissLink")
						$("#loadBLUndismissLinkProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="loadbrokenLinksBulkActionsResult")
						$("#loadBLBulkActionsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="loadYoastSEOGetInfoResult")
						$("#loadYoastResultsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="loadYoastSEOSaveInfoResult")
						$("#loadYoastResultsProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="getGoogleWebMastersRedirect")
						$("#googleWebMastersRedirectProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="getGoogleWebMastersRedirectAgain")
						$("#googleWebMastersRedirectAgainProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
					if(actionData["function"]=="FEUploaderResult"){
					}					
                    if(actionData["function"]=="ithemesSecurityLoad") {
                        $("#LoadIthemesSecurityProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
                    }
                    if(actionData["function"]=="loadwordFenceResult") {
                        $("#loadWordfenceProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
                    }
                    if(actionData["function"]=="ScanWordFence") {
                        $("#scanWordfenceProcess .processCount").text(data.data.getWaitData[actionID].loaded+'/'+data.data.getWaitData[actionID].total);
                    }
				}
				
				
			}
			if(data.data.getWaitData[actionID] != undefined && data.data.getWaitData[actionID].data != undefined)
			{
				
				
				if(actionData["function"]=="refreshStats")
				{
					$("#refreshStatsProcess").remove();
					$('.fa.fa-repeat').removeClass('fa-spin').css('color' ,'');
					$("#reloadStats").closest(".btn_reload").show()
					if(currentPage=='updates' || currentPage=='backups')
					refreshStats(data.data.getWaitData[actionID]);
					else
					refreshStats(data.data.getWaitData[actionID],"",1);
				}
				if(actionData["function"]=="clientUpdate")
				{
					clientUpdateNotificationPush(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadPlugins")
				{
					$("#loadPluginsProcess").remove();
					$(".fetchInstall").closest(".btn_action").show();
					loadManagePanel(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadUpdateAll")
				{
					//$("#loadPluginsProcess").remove();
					//$(".fetchInstall").closest(".btn_action").show();
					loadUpdateAllPanel(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadOptimize")
				{
					$("#loadOptimizeProcess").remove();
					$(".loadOptimize").closest(".btn_action").show();
					loadOptimizePanel(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadComments")
				{
					$("#loadCommentsProcess").remove();
					$(".fetchManageComments").closest(".btn_action").show();
					loadManageCommentPanel(data.data.getWaitData[actionID])  //To load Comments on ManageComments panel 
				}
				if(actionData["function"]=="loadMalwareData")
				{
					$("#loadMalwareProcess").remove();
					$(".loadMalware").closest(".btn_action").show();
					processMalwareData(data.data.getWaitData[actionID])  //To load Comments on ManageComments panel 
				}
				if(actionData["function"]=="loadPosts")
				{
					$("#loadPostsProcess").remove();
					$(".fetchPosts").closest(".btn_action").show();
					loadManagePostsPanel(data.data.getWaitData[actionID])   //To load Posts/page on Posts panel
				}
				if(actionData["function"]=="loadLinks")
				{
					$("#loadLinksProcess").remove();
					$(".fetchPosts").closest(".btn_action").show();
					loadManagePostsPanel(data.data.getWaitData[actionID])    //To load links on Posts panel
				}
				if(actionData["function"]=="loadUsers")
				{
					$("#loadUsersProcess").remove();
					$(".loadUsersBtn").closest(".btn_action").show();
					loadProcessedUserData(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="runCode")
				{
					$("#runCodeProcess").remove();
					$("#runSnippetCode").closest(".btn_action").show();
					processSnippetResponse(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadbrokenLinksGetAllLinksResult")
				{
					$("#loadBLAllLinksProcess").remove();
					$(".load_bls_main").closest(".btn_action").removeClass('disabled').show();
					brokenLinksGetAllLinks(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="getBrokenLinksUpdateLink")
				{
					$("#loadBLUpdateLinkProcess").remove();
					$(".load_bls_main").closest(".btn_action").removeClass('disabled').show();
					brokenLinksUpdateLink(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="getBrokenLinksUndismissLink")
				{
					$("#loadBLUndismissLinkProcess").remove();
					$(".load_bls_main").closest(".btn_action").removeClass('disabled').show();
					brokenLinksUndismissLink(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadbrokenLinksBulkActionsResult")
				{
					$("#loadBLBulkActionsProcess").remove();
					$(".load_bls_main").closest(".btn_action").removeClass('disabled').show();
					brokenLinksBulkActions(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadYoastSEOGetInfoResult")
				{
					$("#loadYoastResultsProcess").remove();
					$(".load_yseo_main").closest(".btn_action").removeClass('disabled').show();
					loadYoastSEOGetInfoResult(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="loadYoastSEOSaveInfoResult")
				{
					$("#loadYoastResultsProcess").remove();
					$(".load_yseo_main").closest(".btn_action").removeClass('disabled').show();
					loadYoastSEOSaveInfoResult(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="getGoogleWebMastersRedirect")
				{
					$("#googleWebMastersRedirectProcess").remove();
					$(".processFile").closest(".btn_action").removeClass('disabled').show();
					googleWebMastersRedirect(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="getGoogleWebMastersRedirectAgain")
				{
					$("#googleWebMastersRedirectAgainProcess").remove();
					$(".processFile").closest(".btn_action").removeClass('disabled').show();
					googleWebMastersRedirectAgain(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="FEUploaderResult")
				{
					FileEditorUploader(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="FetchReportResult")
				{
					processReportingNew(data.data.getWaitData[actionID]);
				}
				if(actionData["function"]=="RunNowNewScheduleReport") {
					processsRunNowReportNew(data.data.getWaitData[actionID]);
				}				
                if(actionData["function"]=="ithemesSecurityLoad") {
                    $("#LoadIthemesSecurityProcess").remove();
                    $("#LoadIthemesSecurity").closest(".btn_action").show();
                    ithemesSecurityLoad(data.data.getWaitData[actionID]);
                }
                if(actionData["function"]=="loadwordFenceResult") {
                    $("#loadWordfenceProcess").remove();
                    $("#LoadWordfence").closest(".btn_action").show();
                    wordfenceLoadScan(data.data.getWaitData[actionID]);
                }
                if(actionData["function"]=="ScanWordFence") {
                    $("#scanWordfenceProcess").remove();
                    $("#scanWordfence").closest(".btn_action").show();
                    wordfenceScan(data.data.getWaitData[actionID]);
                }
                if(actionData["function"]=="ScanCheckWordFence") {
                    ScanCheckWordFence(data.data.getWaitData[actionID]);
                }
                if(actionData["function"]=="testCloneConnection") {
                    afterTestConnectionIC(data.data.getWaitData[actionID]);
                    if(data.data.getWaitData[actionID].actionResult.status!=undefined && (data.data.getWaitData[actionID].actionResult.status=="error")){
                        data.data.getWaitData[actionID].actionResult.status = "success";
                    }
                }
				if(actionData["function"]=="backupForClone") {
                    createBackupAndLoad(data.data.getWaitData[actionID]);
                }
				if(actionData["function"]=="stagingDelete"){
					site = data.data.getWaitData[actionID].data.getSites;
				}
				delete formArrayVar[actionID];
				var request = data.data.getWaitData[actionID];
				if(request.actionResult.status!=undefined && (request.actionResult.status=="partial" || request.actionResult.status=="error"))
				{
					$("#historyQueue").show();
					checkHistoryVar=0;
					
				}
			}
		});
	}
	else
	{
		if(typeof(pauseCallRefreshInterval) != 'undefined')
		{
			clearInterval(pauseCallRefreshInterval);
		}
		clearInterval(historyRefreshInterval);
		
		if((returnDataLength == 1)&&(pauseTheCall == 1))
		{
			//call the pauseCallRefresh function for every one second.
			pauseCallRefreshInterval = setInterval(function () {if(true){ $("#historyQueueUpdateLoading").addClass('loading'); pauseCallRefresh(1,pauseTheCallInterVal);} }, (1000));
		}
		
		if((typeof data.sendNextAjaxCallAfter != 'undefined')&&(data.sendNextAjaxCallAfter != null))
		{
			if(data.sendNextAjaxCallAfter != 0)
			{
				if(typeof forcedAjaxInterval != 'undefined')
				{
					if(forcedAjaxInterval <= data.sendNextAjaxCallAfter)
					{
						tempTimeOut = data.sendNextAjaxCallAfter * 1000;
					}
					else
					{
						tempTimeOut = forcedAjaxInterval * 1000;
					}
				}
				else
				{
					tempTimeOut = data.sendNextAjaxCallAfter * 1000;
				}
				
				historyRefreshInterval=  setInterval(function () {if(true){ $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh();}else if(!$(".queue_detailed").is(":visible")) { $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh(); } }, tempTimeOut);
			}
			else
			{
				var tempForcedAjaxTimeOut = 5 * 1000;
				if(typeof forcedAjaxInterval != 'undefined'){
					if(forcedAjaxInterval < 5){
						tempForcedAjaxTimeOut = 5 * 1000;
					}
					else{
						tempForcedAjaxTimeOut = forcedAjaxInterval * 1000;
					}
				}

				historyRefreshInterval=  setInterval(function () {if(true){ $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh();}else if(!$(".queue_detailed").is(":visible")) { $("#historyQueueUpdateLoading").addClass('loading'); historyRefresh(); } }, tempForcedAjaxTimeOut);
			}
		}
		
	}

	var click_history_id = $('.historyItem.active').attr('did');
	if( click_history_id != undefined && $('#'+click_history_id+' .content').is(':visible') ){
		current_history_detailed_scroll_position = $('#'+click_history_id+' .content').scrollTop();
	}
	if(data.data!=undefined && data.data.getHistoryPanelHTML!=undefined) {
		$('#activityPopup').html(data.data.getHistoryPanelHTML);
	}


	// added to solve mac design issue
	if( navigator.platform.indexOf('Mac')!=-1 ){
		$("#activityPopup .nano").removeClass('nanomac').addClass('nanomac');
	}
	$('#historyQueue').show();
	$('.historyItem').each(function(){
		if($(this).attr('did') == click_history_id){
			$(this).click();
			if(typeof current_history_detailed_scroll_position != 'undefined'){
				$('#'+click_history_id+' .content').scrollTop(current_history_detailed_scroll_position);
			}
		}
	});
	

	if(checkHistoryVar==1){
		$("#historyQueue").hide();
	}
	
	$("#historyQueue .nano").nanoScroller();
	if(bottom_count == 1){
		if(sitesList!=null && sitesList!=undefined &&  getPropertyCount(sitesList)>0)
		{
			$.each(site, function(key,value) {
				var favIconClass = ".left_favicon_img_s"+key;
				$(favIconClass).html('<img class="lazyLoad" data-src='+value.favicon+' src="images/custom_wp_favicon.ico"  width="16" height="16">');	
			});
		}
	}
}
function reloadFavourites(data)
{
	favourites = data.data.getFavourites;
	favouritesGroupData = data.data.getFavouritesGroups;
	pluginsThemesFavoritesSelector();
}

function reloadAndLoadFavourites(data)
{
	favourites = data.data.getFavourites;
	favouritesGroupData = data.data.getFavouritesGroups;
	pluginsThemesFavoritesSelector();
	loadFavourites();
	triggerNanoScrollerFavoritesGroup();
}
function createUploader(){            
	var uploader = new qq.FileUploader({
element: document.getElementById('uploaderContent'),
action: systemURL+'uploadScript.php',
debug: true
	});           
}
function uploadFavouriteThemesAndPlugins(){
	var uploader = new qq.FileUploader({
		element  : document.getElementById('uploadFavouriteThemesAndPlugins'),
		action   : systemURL+'uploadScript.php',
		multiple : false,
		params   : { 'uploadFavouriteThemesAndPlugins':1 },
		debug    : true
	});  
	currentUploader = 'uploadFavouriteThemesAndPlugins';
}

function showBackupOptions()
{
	$(".th_btm_info").remove();
	var count=isSiteSelected();
	if(!count)
	$("#enterBackupDetails").addClass('disabled clickNone');
	else
	{
		
		//if(count>1)
		//$("#enterBackupDetails").before('<div class="th_btm_info rep_sprite_backup">If the selected sites are on the same server, make sure it has enough resources to handle the backing up process.</div>');
		$("#enterBackupDetails").removeClass('disabled clickNone');
	}
	$("#modalDiv").dialog( "option", "position", 'center' );
}
function showItemOptions()
{
	if(!isSiteSelected())
	{
		$(".optionsContent").hide();
		$(".advancedInstallOptions").hide();
	}
	else
	{
		
		$(".optionsContent").show();// For manage / install panel
		$(".actionContent").html('');
		if($(".install").hasClass('active'))
		$(".advancedInstallOptions").show();
	}
	// $(".numSiteSelected").text($(".website_cont.active").length); removed
}
function removeDeleteConf()
{
	$(".del_conf").hide();
	$(".ind_groups","#bottom_sites_cont").removeClass('error');
}
function resetSelectors()
{
	siteSelector();
	siteSelector(1);
	$("#bottomToolBarSelector").html(bottomToolbarVar);
	$(".siteSelectorContainer").html(siteSelectorRestrictVar);
	siteSelectorNanoReset(1); // to reset bottomToolBar
	$('.select2_bottom .select_group_toolbar').select2(
	{
width:'177px'
	}); 

	
	
	$(".toggle_manage_groups").qtip({id:"toggleGroupQtip",content: { text: 'Manage Groups' }, position: { my: 'bottom center', at: 'top center',  adjust:{ y: -6} }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 10, height:5} } });
	resetGroup();
	dynamicResize();
	if(sitesList!=null && sitesList!=undefined &&  getPropertyCount(sitesList)>0)
	{
		$.each(site, function(key,value) {
			var favIconClass = ".left_favicon_img_s"+key;
			$(favIconClass).html('<img class="lazyLoad" data-src='+value.favicon+' src="images/custom_wp_favicon.ico"  width="16" height="16">');	
		});
	}
 	lazyLoadOnlyVisibleImages();

}
// From plugin theme manage / install panel
function processCheckUpdate(data)
{
	$("#updates_centre_cont .btn_loadingDiv").remove();
	$(".updateActionBtn").removeClass('disabled');
	if(data.data.forceCheckUpdate==false)
	{
		$(".is_uptodate").remove();
		$(".checkUpdateError").remove();
		$(".updates_descr").remove();
		$(".change_log").remove();
		$(".updatePanelData").append('<div class="is_uptodate rep_sprite_backup">InfiniteWP is up to date</div>');
		
	}
	else if(data.data.forceCheckUpdate.status == 'netError' )
	{
		$(".is_uptodate").remove();
		$(".checkUpdateError").remove();
		$(".updates_descr").remove();
		$(".change_log").remove();
		$(".updatePanelData").append('<div class="checkUpdateError">'+data.data.forceCheckUpdate.errorMsg+'</div>');
	}
	else
	{  
		loadPanelUpdate(data.data.forceCheckUpdate);
	}
	isAddonSuiteMiniLimitExceeded = data.data.checkIsAddonSuiteMiniLimitExceeded;
	addonSuiteMiniLimit = data.data.getAddonSuiteMiniLimit;
	addonSuiteMiniActivity = data.data.getAddonSuiteMiniActivity;
	isAddonSuiteMiniCancelMessage(data.data.isAddonSuiteMiniCancelMessage);
	setTimeout(function(){updateNotificationContent();},2000);
	
}
function resetVars(data)
{
	data=data.data;
	site=data.getSites;
	sitesList=data.getSitesList;
	group=data.getGroupsSites;
	totalSites = getPropertyCount(site);
	totalGroups = getPropertyCount(group);
	totalUpdates =  getPropertyCount(mainJson);
	resetSelectors();
}
function processSaveChange(data)
{
	if($(".toolbar_sites_cont","#bottomToolBarSelector").length>0 && $(".toolbar_sites_cont","#bottomToolBarSelector").is(":visible"))
	tempToolCont="<div class='toolbar_sites_cont'>"+$(".toolbar_sites_cont").html()+"</div>";
	else
	tempToolCont='';
	resetVars(data);
	groupCreateArray={};
	groupChangeArray={};
	groupNameArray={};
	groupDeleteArray={};
	$('.btn_reload_drop').find('.l2').html(data.data.printGroupsForReloaData);
	$("#addWebsiteContainer","#bottomToolBarSelector").after(tempToolCont);
	  $(".emptyGroups").qtip({content: { text: 'No sites in this group' }, position: { my: 'right center', at: 'left center' }, show: { delay: 1000 }, hide: { event: 'click mouseleave',inactive: 1000}, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 5, height:5} } });
}
function processTestEmail(data)
{
	$("#sendTestEmail").removeClass('sending');
	if(data.data.updatesNotificationMailTest ==true)
	$("#sendTestEmail").addClass('success');
	else
	$("#sendTestEmail").addClass('failure');
	
}
function processPage(page,reloadPage)
{
	bottomLinePromotion('pre', page);
	if(page == "updates" && typeof processPageAccess != "undefined"){
		
		var res = processPageAccess(page);
		if(res == false){ 
			return; 
		}
		if(res != true || res != true){ 
			page = res;
		}
	}
	
	currentPage=page;
	if(page=="items")
	{
		if(totalSites>0)
		{
			loadItemManage();
			$(".typePlugin").removeClass('active');
			$(".typePlugin").click();
			$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Plugins & Themes of WordPress sites you add will appear here.</div> <div class="line2">You can install and manage plugins & themes here.</div> </div>');
		}
	}
	else if(page == "clientReporting")
	{
		if(totalSites>0)
		{
			loadReportingMainPage();
			
			//$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">You can generate the reports based on your activities in terms of updates, backups, etc...</div></div>');
		}
	}
	else if(page == "upTimeMonitor")
	{
		if(totalSites>0)
		{
			loadupTimeMonitorMainPage();
			//$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">UpTimeMonitor options from WordPress sites you add will appear here.</div> <div class="line2">You can perform Optimization here.</div> </div>');
		}
	}
	else if(page == "malwareSecurity")
	{
		if(totalSites>0)
		{
			loadMalwareSecurityMainPage();
			//$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Malware Security option from WordPress sites you add will appear here.</div> <div class="line2">You can perform Optimization here.</div> </div>');
		}
	}
	else if(page == "wpOptimize")
	{
		if(totalSites>0)
		{
			loadOptimizeMainPage();
			$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Wp OPtimization option from WordPress sites you add will appear here.</div> <div class="line2">You can perform Optimization here.</div> </div>');
		}
	}
	else if(page=="googleAnalytics")
	{
		if(totalSites>0)
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['googleAnalyticsActiveSites']=1;
			doCall(ajaxCallPath,tempArray,'loadGooglePage');
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Comments from WordPress sites you add will appear here.</div> <div class="line2">You can install and manage plugins & themes here.</div> </div>');
		}
	}
	else if(page=="comments")
	{
		if(totalSites>0)
		{
			loadCommentManage();            //Load Initial comments page on clicking comments page
			$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Comments from WordPress sites you add will appear here.</div> <div class="line2">You can install and manage plugins & themes here.</div> </div>');
		}
	}
	else if(page=="codeSnippets")
	{
		loadSnippetPage();
	}
	else if(page=="posts")
	{
		if(totalSites>0)
		{
			$(".typePosts").removeClass('active');
			$(".typePosts").click();	
			loadPostsManage();                //Load Initial posts page on clicking comments page
			$(".optionsContent").hide();
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Posts and pages of WordPress sites you add will appear here.</div> <div class="line2">You can install and manage plugins & themes here.</div> </div>');
		}
	}
	else if (page=="updates")
	{
		if(totalSites>0)
		{
			
			if(totalUpdates >0)
			{
				getRecentUpdatesStatus();
				loadUpdateContent();
				if(typeof isGoogle != 'undefined')
				{
					if(isGoogle==1)
					loadGoogleContent();
				}
				if(typeof isGoogleWM != 'undefined')
				{
					if(isGoogleWM==1)
						getGoogleWebMastersContent();
				}
				parentFlag=0;
				if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
					mergeWpVulnsDataIntoSitesJson();
					$("#securityViewUpdateContent").html(displayUpdateContent('sites','wpVulns'));
					checkAndShowSecurityUpdatesCount();
					updateSiteVulnsStatus();
				}
				
				$("#siteViewUpdateContent").html(displayUpdateContent('sites'));
				$("#WPViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All WordPress Installations are up-to-date.</div></div>'+displayUpdateContent('wp'));
				$("#themeViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All Themes are up-to-date.</div></div>'+displayUpdateContent('themes'));
				$("#pluginViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All Plugins are up-to-date.</div></div>'+displayUpdateContent('plugins'));
				$("#TranslationViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All translations are up-to-date.</div></div>'+displayUpdateContent('translations'));				
				
				$("#hiddenViewUpdateContent").html(displayUpdateContent('sites','hiddenUpdates'));
				
				
				currentUpdatePage="siteViewUpdateContent";
				$.each(updateCheckArray, function(property, value) { 
					
					checkGeneralSelect(property, '',1);
					$(".count span",".row_"+property).text($(".row_"+property+" .item_ind").not('.hidden').length);
					

				});
				$(function () {	
					
					$(".websites_view").click();
					viewHiddenFlag=0;
					$("#mainUpdateCont .hideVar").hide();
					$("#mainUpdateCont .hidden").hide();
				});
				if(groupOperationFlag == 1)
				{
					hideSitesBasedOnGroup();
					
				}
				$('.update_by_group .select_group_toolbar').select2({
					width:'177px'
				});
					
				
				
				if(typeof isStaging!='undefined' && isStaging == 1){
					if (typeof stagingReplaceUpdateButtonWithStagingButton != 'undefined' && $.isFunction(stagingReplaceUpdateButtonWithStagingButton)) {
					    stagingReplaceUpdateButtonWithStagingButton();
					}else{
						replaceUpdateButtonWithStagingButton();
					}
				}
				
			}
			else
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Everything is up-to-date. Woooo!!</div> <div class="line2">You are smart & safe.</div> </div>');
		}
		else 
		$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Updates to your WP, Plugins & Themes will be listed out here.</div> <div class="line2">You can update them all at once or by items & can also hide all future updates for specific items.</div> </div>');
		
		if (typeof mainJson.updatePageEmailCronReqNotification != 'undefined' && mainJson.updatePageEmailCronReqNotification == 1) {
			$("#pageContent").prepend('<div class="setCronNotification" style="border-left: 2px solid #d0b000; position:relative; background-color: #FEF5C2; padding: 10px;">If you want to receive update notification emails, you need to set up a cron job. Go to <a id="goCronSettings">Settings -> Cron</a> to set it up now. <div class="dismiss_notification"><a>Dismiss</a></div></div>');
		}
	}
	else if (page=="backups")
	{
		if(totalSites>0)
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['getSitesBackupsHTML']=1;
			doCall(ajaxCallPath,tempArray,'loadBackupPage');
		}
		else
		$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Website backups you create will be listed here.</div> <div class="line2">You can restore, download and delete backups.</div> </div>');
	}
	
	else if(page=="history")
	{
		if(totalSites>0)
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['getHistoryPageHTML']=1;
			tempArray['requiredData']['getActivityCategories']=1;
			if(typeof multiUserAddonFlag !='undefined' && multiUserAddonFlag && currentUserAccessLevel == 'admin')	tempArray['requiredData']['getAccessibleUsers']=1;
			doCall(ajaxCallPath,tempArray,'loadHistoryPage');
		}
		else
		$("#pageContent").html('<div class="empty_data_set"> <div class="line1">All your site related activities will be logged here.</div></div>');
	}
	else if(page=="loginLog")
	{	
		$('.page_section_title').html('Login log')
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['getLogPageHTML']=1;
		if(typeof multiUserAddonFlag !='undefined' && multiUserAddonFlag && currentUserAccessLevel == 'admin')	tempArray['requiredData']['getAccessibleUsers']=1;
		doCall(ajaxCallPath,tempArray,'loadLogHistoryPage');
	}
	//WPC start
	else if(page=="installClone") 
	{
		installCloneLoad();
	}
	//WPC end
	else if(page=="addons") 
	{
		loadAddonsPage();
	}
	else if(page=="userManagement") 
	{
		loadUserManagement();
	}
	else if(page=='brokenLinks')
	{
		loadBrokenLinksPage();
	}
	else if(page=='googleWebMasters')
	{
		if(totalSites>0)
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['googleWebMastersActiveSites']=1;
			doCall(ajaxCallPath,tempArray,'loadGoogleWebMastersPage');
		}
		else
		{
			$("#pageContent").html('<div class="empty_data_set"> <div class="line1">Comments from WordPress sites you add will appear here.</div> <div class="line2">You can install and manage plugins & themes here.</div> </div>');
		}
	}
	else if(page=='fileEditor')
	{
		loadFileEditorPage();
	}
	else if(page=='gPageSpeed')
	{
		loadGooglePageSpeedPage();
	}
	else if(page=="wordFence")
	{
		loadWordFenceMainPage();
	}
        else if(page=="ithemesSecurity")
	{
		loadIthemesSecurityMainPage();
	}	
	else if(page=="yoastWpSeo")
	{
		loadYoastWpSeoPage();
	}else if (page == "iwpusers")
	{
		loadAdmins();	
	}
	
	setTimeout(function(){bottomLinePromotion('post', page); },1000);
	bottomLinePromotion('post', page);//codeSprints 
}

//codeSprints
function bottomLinePromotion(run, page){
	content = false;
	if(run == 'pre'){
		//$('#bottomLine').remove();
	}
	else if(run == 'post'){
		content = getContentForBottomLine(page);
		lastBottomLinePage = page;
		if(typeof content != 'undefined' && content != ''){
			$('#bottomLine').remove();
			lastBottomLineContent = '<div id="bottomLine" class="bottom_line_promo">'+ content +'</div>';
			$("#pageContent").append(lastBottomLineContent);
		}
		else{
			lastBottomLineContent = '';
		}
	}
}

function detechAndSetBottomLine(){
		if(lastBottomLinePage == currentPage && lastBottomLineContent.length > 1){
			if($('#bottomLine').length == 0){
				$("#pageContent").append(lastBottomLineContent);
			}			
			else if($('#pageContent').children().last().attr('id') != 'bottomLine'){//make sure last child of #pageContent is #bottomLine
				//$('#bottomLine').remove();
			}
		}
		//else{
//			lastBottomLinePage = currentPage;
//			lastBottomLineContent = '';
//			//$("#pageContent").unbind('DOMNodeInserted DOMNodeRemoved');
//			$('#bottomLine').remove();
//			//setTimeout(function() {   bindPageContentChange();  }, 1);
//		}
}

function getBottomLineByThisPage(thisPage){
	var purchasedAddons = Array();
	finaleReturn = false;
	var nonPurchasedAddon = Array();
	var markBottomLinesAllAddonsSlug = Array();
	i = 0;
	if(typeof purchasedAddonsGlobal != 'undefined' && purchasedAddonsGlobal != null){
		var j = 0;
		jQuery.each(purchasedAddonsGlobal, function(k, v){
			if(k != 'length'){
				purchasedAddons[j] = k;
			}
			j++;
		});
	}
	
	
	if(typeof markBottomLines != 'undefined' && markBottomLines != '' && markBottomLines != null){
		var i = 0;
		$.each( markBottomLines, function( key, addon ){
			if(key == 'mainPanel_backups' || key == 'mainPanel_updates' || key == 'mainPanel_activityLog' || key == 'mainPanel_addons' || key == 'mainPanel_pluginsThemes'){
				if(typeof addon.promoteHere != 'undefined' && addon.promoteHere != '' && addon.promoteHere != null){
					var subKeys = addon.promoteHere.split(",");
				}
				if(typeof subKeys != 'undefined'){
					$.each(subKeys, function(ke, va){
						markBottomLinesAllAddonsSlug[i] = va;
						i++;
					});
				}
			}
			else{
				markBottomLinesAllAddonsSlug[i] = key;
				i++;
			}
		});
	}
	
	
	if(typeof markBottomLinesAllAddonsSlug != 'undefined' && markBottomLinesAllAddonsSlug != '' && markBottomLinesAllAddonsSlug != null){
		$.each( markBottomLinesAllAddonsSlug, function( key, addon ) {
			if(typeof purchasedAddons != 'undefined'){
				nonPurchasedAddon[i] = addon;
				i++;
			}
		});
	}
	if (nonPurchasedAddon.length < 1) {//if user bought all the addons then just return false
		return finaleReturn;
	}
	if(typeof markBottomLines != 'undefined' && markBottomLines != null){
		if(typeof markBottomLines[thisPage] != 'undefined' && markBottomLines[thisPage]){
			thisPagePromoDetails = markBottomLines[thisPage];
			if(typeof thisPagePromoDetails['promoteHere'] != 'undefined' && thisPagePromoDetails['promoteHere'] != null && thisPagePromoDetails['promoteHere'].length > 1){
				promoteHere = thisPagePromoDetails['promoteHere'].split(',');
				randKey = false;
				if(thisPagePromoDetails['isRand'] == '1'){
					randKey = true; //Math.floor((Math.random() * 2) + 0);
				}
				if(typeof promoteHere != 'undefined' && promoteHere != '' && promoteHere != null){
					$.each( promoteHere, function( key, addon ) {
						if( (typeof purchasedAddons != 'undefined' && jQuery.inArray(addon, purchasedAddons) == -1) ){
							if(typeof markBottomLines[addon] != 'undefined' && typeof markBottomLines[addon].promoContent != 'undefined'){
								finaleReturn =  markBottomLines[addon].promoContent;
								if(randKey){
									if(Math.floor((Math.random() * 2) + 0) == 0){//Math.floor((Math.random() * 2) + 0) this will get  0,1,2 in rand
										return false;
									}
									return;//return; equivalent to continue in loop php	
								}
								return false;
							}
						}
						
					});
				}
			}
		}
	}
	return finaleReturn;
}

function getContentForBottomLine(page){
	
	if(page=="items")
	{
		return getBottomLineByThisPage('mainPanel_pluginsThemes');
	}
	else if(page == "clientReporting")
	{
		return getBottomLineByThisPage('clientReporting');
	}
	else if(page == "upTimeMonitor")
	{
		return getBottomLineByThisPage('uptimeMonitorUptimeRobot');
	}
	else if(page == "malwareSecurity")
	{
		return getBottomLineByThisPage('malwareScanningSucuri');
	}
	else if(page == "wpOptimize")
	{
		return getBottomLineByThisPage('wpOptimize');
	}
	else if(page=="googleAnalytics")
	{
		return getBottomLineByThisPage('googleAnalytics');
	}
	else if(page=="comments")
	{
		return getBottomLineByThisPage('manageComments');
	}
	else if(page=="codeSnippets")
	{
		return getBottomLineByThisPage('codeSnippets');
	}
	else if(page=="posts")
	{
		return getBottomLineByThisPage('bulkPublish');
	}
	else if (page=="updates")
	{
		return getBottomLineByThisPage('mainPanel_updates');		
	}
	else if (page=="backups")
	{
		return  getBottomLineByThisPage('mainPanel_backups');

	}	
	else if(page=="history")
	{
		return getBottomLineByThisPage('mainPanel_activityLog');
	}
	else if(page=="installClone") 
	{
		return getBottomLineByThisPage('installClone');
	}
	else if(page=="addons") 
	{
		return getBottomLineByThisPage('mainPanel_addons');
	}
	else if(page=="userManagement") 
	{
		return getBottomLineByThisPage('manageUsers');
	}
	else if(page=='brokenLinks')
	{
		return getBottomLineByThisPage('brokenLinks');
	}
	else if(page=='googleWebMasters')
	{
		return getBottomLineByThisPage('googleWebMasters');
	}
	else if(page=='fileEditor')
	{
		return getBottomLineByThisPage('fileEditor');
	}
	else if(page=='gPageSpeed')
	{
		return getBottomLineByThisPage('googlePageSpeed');
	}
	else if(page=="wordFence")
	{
		return getBottomLineByThisPage('wordFence');
	}
	
	return false;
	}
		
function processHideUpdate(data)
{
	mainJson=data.data.getSitesUpdates;
	sitesjson = mainJson.siteView;
	pluginsjson = mainJson.pluginsView.plugins;
	themesjson = mainJson.themesView.themes;
	translationsjson=mainJson.translationsView.translations;
	hiddenUpdateCount = mainJson.hiddenUpdateCount;
	wpjson = mainJson.coreView.core;
	if(!iwpIsEmpty(mainJson.wpVulView)){
		wpVulnsPluginsjson =  mainJson.wpVulView.plugins;
		wpVulnsThemesJson =  mainJson.wpVulView.themes;
		wpVulnsWpJson =  mainJson.wpVulView.wp;
		if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
			mergeWpVulnsDataIntoSitesJson();
		}
	}
	pluginsUpdateCount =arrUpdateCount(pluginsjson);
	WPUpdateCount =arrUpdateCount(wpjson);
	themesUpdateCount=arrUpdateCount(themesjson);
	transUpdateCount =arrUpdateCount(translationsjson);
	updateViewDropDown();
	updateViewUpdateConten();
	if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
		checkAndShowSecurityUpdatesCount(1);
	}
}

function updateViewUpdateConten(){
if (currentUpdatePage != 'siteViewUpdateContent') {
	$("#siteViewUpdateContent").html(displayUpdateContent('sites'));
		$('#siteViewUpdateContent').find('.update_by_group .select_group_toolbar').select2({
					width:'177px'
		});	
}
if (currentUpdatePage != 'pluginViewUpdateContent') {
	$("#pluginViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All Plugins are up-to-date.</div></div>'+displayUpdateContent('plugins'));
}
if (currentUpdatePage != 'themeViewUpdateContent') {
	$("#themeViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All Themes are up-to-date.</div></div>'+displayUpdateContent('themes'));
}
if (currentUpdatePage != 'WPViewUpdateContent') {
	$("#WPViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All WordPress Installations are up-to-date.</div></div>'+displayUpdateContent('wp'));
}
if (currentUpdatePage != 'TranslationViewUpdateContent') {
	$("#TranslationViewUpdateContent").html('<div class="empty_data_set hiddenCheck" style="display:none"> <div class="line2">Hurray! All translations are up-to-date.</div></div>'+displayUpdateContent('translations'));				
}
if (currentUpdatePage != 'hiddenViewUpdateContent') {
	$("#hiddenViewUpdateContent").html(displayUpdateContent('sites','hiddenUpdates'));
		$('#hiddenViewUpdateContent').find('.update_by_group .select_group_toolbar').select2({
					width:'177px'
		});	
}
if (!iwpIsEmpty(mainJson.wpVulView) && currentUpdatePage != 'securityViewUpdateContent') {
	$("#securityViewUpdateContent").html(displayUpdateContent('sites','wpVulns'));
		$('#securityViewUpdateContent').find('.update_by_group .select_group_toolbar').select2({
					width:'177px'
		});	
}
}

function groupSelected() // For add site
{
	var arrayCounter=0;
	var selectedGroup = {};
	$(".js_addSite.active",".addSiteGroups").each(function () {
		selectedGroup[arrayCounter]=$(this).attr("gid");
		arrayCounter=arrayCounter+1;
	});
	return selectedGroup;
}
function testAlert(request)
{
	
}
function getSelectedSites(extraClass) // Changed for addons having 2 site selector in a single page.
{ 
	var selectedSites= {} ;
	if(extraClass==undefined)
	extraClass=".siteSelectorContainer";
	else
	extraClass=extraClass+" .siteSelectorContainer";
	var  arrayCounter=0;
	$('.website_cont.active',extraClass).each( function () { 
		selectedSites[arrayCounter]= $(this).attr('sid');
		arrayCounter++;
	});
	return selectedSites;
}
function installItems(object,dlink,multiple)
{
	
	
	if(dlink==undefined || dlink=='')
	{
		dLink=$(object).attr('dlink');
		var plugin_slug = $(object).attr('dlink');
	}
	else
	dLink=dlink;
	
	/*  if(mainArray['args']['sites']==undefined)
		mainArray['args']['sites']={};
		mainArray['args']['sites']=selectedSites;*/
	var tempArray={};
	tempArray['args']={};
	tempArray['args']['params']={};
	tempArray['args']['siteIDs']={};
	var params={};
	tempArray['action']="install"+activeItem.toTitleCase();

	params[activeItem]={};
	if(multiple>0)
	{
		tempArray['args']['siteIDs']=getSelectedSites();
		if(multiple==2)
		{
			if(wpRepositoryFlag == 1)
			params[activeItem]=plugin_slug;
			else
			params[activeItem]=dLink;
		}
		else
		{
			if(wpRepositoryFlag == 1)
			params[activeItem][0]=plugin_slug;
			else
			params[activeItem][0]=dLink;
		}
	}
	else
	{
		tempArray['args']['siteIDs'][0]=$(object).attr('sid');
		if(wpRepositoryFlag == 1)
		params[activeItem][0]=plugin_slug;
		else
		params[activeItem]=dLink;
	}

	if($('.activateItem').hasClass('active'))
	params['activate'] = true;
	if($('.overwriteItem').hasClass('active'))
	params['clearDestination'] = true;
	tempArray['args']['params'] = params;
	wpRepositoryFlag = 0;
	$('.installFavourites').removeClass('disabled');
	doHistoryCall(ajaxCallPath,tempArray);

	
}
function processAppSettings(data)
{
	$("#saveSettingsBtn").removeClass('disabled');
	$("#settings_cont .btn_loadingDiv").remove();
	$("#saveSuccess").show();
	setTimeout(function () {	$("#settings_cont").hide(); $("#saveSuccess").hide();},1000);
	$("#settings_btn").removeClass('active');
	loadSettingsPage(data);
	
}
function ajaxRepositoryCall(data)
{
	var content='';
	if(activeItem=='plugins')
	{
		content='<div class="th_sub rep_sprite" style="border-top:1px solid #D2D5D7;"> <div class="label" style="float:left;"><span style="margin-right:193px;">PLUGIN NAME</span></div> <div class="label" style="float:left;"><span style="margin-right:26px;">VERSION</span></div> <div class="label" style="float:left;"><span style="margin-right:28px;">RATING</span></div> <div class="label" style="float:left;"><span style="margin-top: -24px;">DESCRIPTION</span></div> </div><div class="wp_repository_search_results">';
	}
	else
	content='<div class="wp_repository_search_results" style="border-top:1px solid #D2D5D7;"><div class="tr_theme" style="float:left">';
	
	content=content+data.data.getWPRepositoryHTML;
	if(activeItem=='plugins')
	content=content+'</div>';
	else
	content=content+'<div class="clear-both"></div></div></div>';
	$(".wp_repository_cont").html(content);
	$(".searchItem").removeClass('disabled');
	$(".installSubPanel .btn_loadingDiv").remove();
	if($(".searchCont").is(":visible") && $(".searchVar.active").attr('dval')!="search")
	$(".searchCont").hide();
}

function installFavourites()
{
	var installArray={};
	var arrayCounter=0, dlink, type;
	$(".favItems.active a").each(function () {
		dlink=$(this).attr('dlink');
		type=$(this).attr('utype');
		installArray[arrayCounter]=dlink;
		
		arrayCounter++;
	});
	installItems('',installArray,2);
}
function getLinksAndInstallFavourites()
{
	var installArray={};
	var arrayCounter=0, dlink, type,slug;
	$(".favItems.active a[dlink]").each(function () {
		slug = $(this).attr('islug');
		dlink=$(this).attr('dlink');
		type=$(this).attr('utype');
		//if(slug!='')
		installArray[arrayCounter] = {};
		installArray[arrayCounter]['slug']=slug;
		installArray[arrayCounter]['downloadLink']=dlink;
		arrayCounter++;
	});
	getFavDownloadLink(installArray);
	//installItems('',installArray,2);
}
function getFavDownloadLink(installArray)
{
	//$.each(installArray,function(k,v)
	//{
	var tempArray={};
	tempArray['requiredData']={};
	var valArray={};
	valArray['type']=activeItem;
	//valArray['searchVar']=1;
	valArray['searchItem'] = {};
	valArray['searchItem']=installArray;
	tempArray['requiredData']['getFavDownloadLinks']=valArray;
	doCall(ajaxCallPath,tempArray,"processFavLinks","json","none");
	//}};
}

function processFavLinks (data)
{
	//if(typeof data != 'undefined')
	var installArray = data.data.getFavDownloadLinks;
	installItems('',installArray,2);
}


function removeActiveElements() // Used for items management
{
	$("#view_content .applyChangesCheck").each(function () {
		if($(this).hasClass('active'))
		$(this).closest("ul").before('<div class="queued_single">Queued..</div>').remove();
	});
}
function applyChanges(object)
{
	var changeArray={};
	var arrayCounter=0, siteID, dID, type, action, name, valArray;
	closestVar=$(object).closest('.siteSearch');
	$(".applyChangesCheck.active").each(function () {
		
		if(!$(this).closest('.ind_row_cont').hasClass('hide'))
		{
			siteID=$(this).attr('sid');
			dID=$(this).attr('did');
			type=$(this).attr('utype');
			action=$(this).attr('action');
			name=$(this).attr('itemName');
			if(changeArray[siteID]==undefined)
			changeArray[siteID]={};
			if(changeArray[siteID][type]==undefined)
			changeArray[siteID][type]={};
			valArray={};
			valArray['name']=name;
			valArray['path']=dID;
			valArray['action']=action;
			if(activeItem=="themes")
			valArray['stylesheet']=$(this).attr('stylesheet');
			changeArray[siteID][type][arrayCounter]=valArray;
			arrayCounter++;
		}
		
	});
	var tempArray={};
	tempArray['args']={};
	tempArray['args']['params']={};
	tempArray['action']='manage'+activeItem.toTitleCase();
	tempArray['args']['params'] = changeArray;
	doHistoryCall(ajaxCallPath,tempArray,'');
}
function isSiteSelected(object) // Used for managepanel 
{
	
	
	
	var count=$(".website_cont.active",'.siteSearch').length;
	if(count>0)
	return count;
	else
	return false;
	
	
}
function triggerSettingsButton()
{
	$("#saveSettingsBtn").removeClass('disabled');
}
function processSettingsForm(data)
{
	var mainData=data;
	data=data.data.updateAccountSettings;
	$("#saveSettingsBtn").removeClass('disabled');
	$("#settings_cont .btn_loadingDiv").remove();
	if(data.status=='error' && data.error=='invalid_password')
	{
		var closestVar=$("#currentPassword").closest(".valid_cont");
		$(".valid_error",closestVar).show();
		$(".valid_error div",closestVar).text('Invalid password. Kindly Check again.');
	}
	else
	{
		$("#saveSuccess").show();
		setTimeout(function () {	$("#settings_cont").hide(); $("#saveSuccess").hide();},1000);
		$("#settings_btn").removeClass('active');
		loadSettingsPage(mainData);
		
	}
}
function validateSettingsForm()
{
	var hasError = false, closestVar;
	var passwordVal = $("#newPassword").val();
	//var checkVal = $("#newPasswordCheck").val();
	var currentPassword = $("#currentPassword").val();
	if(currentPassword == '')
	{
		closestVar=$("#currentPassword").closest(".valid_cont");
		$(".valid_error",closestVar).show();
		$(".valid_error",closestVar).text('Please enter the current password.');
		$("#currentPassword").addClass("error");
		return true;
	}
	if (passwordVal == '') {
		closestVar=$("#newPassword").closest(".valid_cont");
		$(".valid_error",closestVar).show();
		$(".valid_error",closestVar).text('Please enter a new password.');
		$("#newPassword").addClass("error");
		return true;
	}
	return hasError;
}
function processReport(data)
{
	$("#modalDiv .btn_loadingDiv").remove();
	if(data.data.sendReportIssue==true)
	{
		$("#modalDiv .form_cont").html('<span id="addSiteSuccessMsg"><span class="success_icon"></span>The report has been sent.</span>');
		$("#sendReportBtn").hide();
		setTimeout(function () {	$("#modalDiv").dialog("close");},2000);
	}
	else
	{
		$("#modalDiv .form_cont").html('<span id="addSiteErrorMsg"><span class="fail_icon"></span>The report could not be sent. Try again or contact help@infinitewp.com</span></span>');
		$("#sendReportBtn").hide();
		setTimeout(function () {	$("#modalDiv").dialog("close");},2000);
	}
}
function doCall(url,data,callback,dataType,animationLoad,historyRefreshCheck)
{
	if(stopAllAction==false)
	{
		
		if(data['requiredData']==undefined)
		data['requiredData']={};
		//if(runOffBrowser==0)
		//{
		//	data['requiredData']['runOffBrowserLoad']=1;
		//	runOffBrowser=1;
		//}
		data['requiredData']['getHistoryPanelHTML']=1;
		
		data['requiredData']['isAddonSuiteLimitExceededAttempt']=1;
		data['requiredData']['checkIsMiniExpired']=1;
		data['requiredData']['getCurrentTimestamp']=1;
		
		if(historyRefreshCheck==1 && historyRefreshCheckFlag==0 )
		{
			historyRefreshCheckFlag=1
		}
		else if(historyRefreshCheck==1 && historyRefreshCheckFlag==1)
		{

			return false;
		}
		if(animationLoad!="noProgress")
		$("#process_queue .processQueueMoveOut").addClass('in_progress');
		if(animationLoad==undefined)
		{
			var animationLoad="normal";
			var loaderDiv="#loadingDiv";
		}
		if(animationLoad=="normal")
		$(loaderDiv).show();
		if(dataType==undefined)
		dataType='json';

		$.ajax({    
			traditional: true,
			type: 'post',
			url: url,
			dataType: dataType,
			data: $.param(data),
			success: function(request) {
			if(animationLoad=="normal")
			$(loaderDiv).hide();

			historyRefreshCheckFlag=0;

			if(request!=undefined && request.logout!=undefined && request.logout==true)
			{
				window.location.href = "login.php";
			}

			doCheckDoCallAndHistoryCallForMini(request,data);
			
			if(callback!=undefined)
			eval(callback+"(request)");
			if( dataType=="json" && callback!='reloadHistory')
			{
				reloadHistory(request);
			}
			if(updateAvailable==false && request.updateAvailable!=undefined && request.updateAvailable!=false)
			{
				
				updateAvailable= request.updateAvailable;
				updateAvailableNotify = true; // Assuming we are getting the new updates
				loadPanelUpdate(request.updateAvailable);
			}
			if(request!=undefined && request.notifications!=undefined)
			{
				fixedNotifications = request.notifications;
				loadFixedNotifications();
			}
			if (request != undefined && request.fireQueue != undefined) {
				fireQueue = request.fireQueue;
				processFireQueue();
			}
				if(request!=undefined && request.addonAlertCount!=undefined)
				{
					var addonAlertCount = parseInt(request.addonAlertCount);
					if(addonAlertCount > 0)	{						
						$('#iwpAddonsBtn .count').html(addonAlertCount).show();
					}else{
						$('#iwpAddonsBtn .count').hide();
					}
				}
			if(request!=undefined && request.actionResult!=undefined && request.actionResult.status!=undefined && (request.actionResult.status=="partial" || request.actionResult.status=="error"))
			$("#historyQueue").show();



			},  // End success
			error: function() {
				historyRefreshCheckFlag=0;
				
			}
		});
	}
}
function processUpdateNow(version)
{
	var content='<iframe src="./update.php?action=appUpdate&newVersion='+version+'" height="170px" width="320px" border="0" ></iframe>';
	$(".updatePanelData").html(content);
	
}
function doHistoryCall(url,data,callback,dataType)
{
	if(stopAllAction==false)
	{
		if(data['requiredData']==undefined)
		data['requiredData']={};
		data['requiredData']['getHistoryPanelHTML']=1;
		
		data['requiredData']['isAddonSuiteLimitExceededAttempt']=1;
		data['requiredData']['checkIsMiniExpired']=1;
		data['requiredData']['getCurrentTimestamp']=1;

		$("#process_queue .processQueueMoveOut").addClass('in_progress');
		$("#historyQueue").show();
		
		$(".queue_ind_item_cont .content").prepend('<div class="queue_ind_item historyItem">Adding to queue ...<div class="clear-both"></div></div>');
		
		if(dataType==undefined)
		dataType='json';
		$.ajax({
			traditional: true,
			type: 'post',
			url: url,
			dataType: dataType,
			data: $.param(data),
			success: function(request) {
				if(request.logout!=undefined && request.logout==true)
				{
					window.location.href = "login.php";
				}

				doCheckDoCallAndHistoryCallForMini(request,data);
				
				if(dataType=="json")
				{
					reloadHistory(request);
				}
				if(callback!=undefined){
					eval(callback+"(request)");
				}
				if(updateAvailable==false && request.updateAvailable!=undefined && request.updateAvailable!=false)
				{
					updateAvailable=request.updateAvailable;
					updateAvailableNotify = true; // Assuming we are getting the new updates
					loadPanelUpdate(request.updateAvailable);
				}
				if(request!=undefined && request.notifications!=undefined)
				{
					fixedNotifications = request.notifications;
					loadFixedNotifications();
				}
			}  // End success
		});
	}
}
function cancelEvent(e)

{

	e = e ? e : window.event;

	if(e.stopPropagation)

	e.stopPropagation();

	if(e.preventDefault)

	e.preventDefault();

	e.cancelBubble = true;

	e.cancel = true;

	e.returnValue = false;

	return false;

}


//Code for search fixing case sensitive in search.
jQuery.expr[':'].contains = function(a,i,m){
	return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase())>=0;
};

function getFirstKey(object,count)
{
	var tempVal;
	$.each(object, function(key, value) {
		if(count==2)
		{
			$.each(value, function(key1, value1) {
				tempVal=value1;
				return false;
			});
		}
		else
		{
			tempVal=key;
			return false;
		}
		
	});
	return tempVal;
}
function getLastKey(object)
{
	$.each(object, function(key, value) {
		
		tempVal = key;
	});
	return tempVal;
}
function makeHistorySelection(object,parentClass)
{
	if(parentClass==undefined)
	parentClass="parent";
	$(".queue_detailed","."+parentClass).hide();
	if(!$(object).hasClass('active'))
	{
		$(".historyItem","."+parentClass).removeClass('active');
		$(object).addClass('active');
		$("#historyQueue #"+$(object).attr('did')).nanoScroller({stop: true});
		$("#"+$(object).attr('did')).show();
		$("#historyQueue #"+$(object).attr('did')).nanoScroller();
	}
	else
	{
		$(object).removeClass('active');
		$("#"+$(object).attr('did')).hide();
	}

	
}
function siteSelector(subSitesRestrict)
{
	var scontent='',totalSiteCount;
	var sgcontent='';
	var sscontent='';
	var bcontent='';
	var bgcontent='';
	var bscontent='';
	
	if(group!=null && group!=undefined &&  getPropertyCount(group)>0)
	{
		$.each(group, function(key,value) {
			if(value.siteIDs!=undefined)
			totalSiteCount=value.siteIDs.length;
			else
			totalSiteCount=0;
			sgcontent=sgcontent+'<div class="group_cont rep_sprite" id="g'+key+'" gid="'+key+'" onclick=""><a title="'+value.name+'" >'+value.name+'</a></div>';
			bgcontent=bgcontent+'<div class="ind_groups row_arrow_left" id="g'+key+'" gid="'+key+'" onclick=""><a title="'+value.name+'" onclick=""><span class="count_cont">'+totalSiteCount+'</span><input name="" type="text" value="'+value.name+'" class="groupEditText" /></a>   <div class="del_conf"><div class="label">Sure?</div><div class="yes deleteGroup">Yes</div><div class="no deleteGroup">No</div></div><div class="edit_del_cont"> <div class=" rep_sprite bg "><span class="rep_sprite_backup edit editGroup"></span></div> <div class=" rep_sprite bg"><span class=" rep_sprite_backup del deleteConf"></span></div> </div></div>';
		});
	}
	else
	{
		sgcontent='';
		bgcontent='<div class="empty_data_set websites"> <div class="line1" style="margin-top:200px;">Organize your WordPress sites into groups for easy managing.</div> <div class="line2">Go ahead. Create a group now.</div> <div class="arrow2"></div> </div>';
	}
	//var site = eval('([])');
	if(sitesList!=null && sitesList!=undefined &&  getPropertyCount(sitesList)>0)
	{
		$.each(sitesList, function(key,value) {
			if(value.network==1 && value.parent==0 && subSitesRestrict==1)
			{
				extraRestrictClass="disabled";
				extraTitle = ' - Its a network subsite.';
				
			}
			else
			{
				extraRestrictClass='';
				extraTitle = '';
			}
			sscontent=sscontent+'<div class="website_cont searchable deselectGroups '+extraRestrictClass+'" id="s'+value.siteID+'"  sid="'+value.siteID+'" onclick=""><a title="'+value.name+extraTitle+'">'+value.name+'</a></div>';	
			var faviconSpan = '<span class="left_favicon_img_'+key+'"></span>';
			bscontent=bscontent+'<div class="ind_sites searchable js_sites bottomSites"  blogPublic="'+value.blogPublic+'" id="s'+value.siteID+'" sid="'+value.siteID+'" onclick=""><a title="'+value.name+'" class="site_selector_name">'+faviconSpan+value.name+'</a></div>';
		});
	}
	else
	{
		sscontent='';
		bscontent='<div class="empty_data_set websites"> <div class="line1">No websites added yet..</div> <div class="line2">That makes us sad. Come on.<br /> Lets add one of your WordPress sites.</div> <div class="add_site_arrow"></div> </div>';
	}
	scontent='<div class="site_selector1 shadow_stroke_box siteSearch"> <div class="bygroup"><div class="th rep_sprite"><div class="title">by <span class="droid700">Groups</span></div><div class="clear-both"></div></div>';
	scontent=scontent+'<div class="group_items_cont" ><div class="content"><div class="group_cont rep_sprite" id="g0" onclick=""><a>All Websites</a></div>';
	scontent=scontent+sgcontent+'</div></div></div>'; // class bygroup ends here
	scontent=scontent+'<div class="bywebsites"> <div class="th rep_sprite"> <div class="title">by <span class="droid700">Websites</span></div> <div class="type_filter"> <input name="" placeholder="type to filter" type="text" class="input_type_filter search_site" ><div class="clear_input rep_sprite_backup"  onclick=""></div> </div> <div class="select_cont"><span>Select: </span><a class="siteSelectorSelect">All</a><a class="siteSelectorSelect">Invert</a><a class="siteSelectorSelect">None</a></div> <div class="clear-both"></div> </div> <div class="website_items_cont"><div class="content">';
	scontent=scontent+sscontent+'<div class="no_match hiddenCont" style="display:none">Bummer, there are no websites that match.<br />Try typing fewer characters.</div> <div class="clear-both"></div> </div></div> <div class="clear-both"></div> </div><div class="clear-both"></div></div>';
	bcontent='<div id="bottom_sites_cont" style="display:none"><div id="bottom_left" class="float-left"><div class="list_cont nano"><div class="content"><div class="no_match hiddenCont" style="display:none">Bummer, there are no websites that match.<br />Try typing fewer characters.</div>';
	bcontent=bcontent+bscontent+' </div> </div> <div class="bottom_bar rep_sprite"> <div class="select_box_cont select2_bottom float-left" >'+groupGenerate(1,"bottom")+'</div> <div class="rep_sprite toggle_manage_groups float-right"><a class="rep_sprite_backup" style="position:relative"><i class="fa fa-list-alt" style="top: 4px; left: 3px; color: rgb(115, 121, 135); font-size:16px;"></i></a></div> </div> <div class="bottom_subbar rep_sprite"><div style="position:relative;"><input name="" type="text" class="input_type_filter search_site" placeholder="type to filter" ><div onclick="" class="clear_input rep_sprite_backup"></div></div> </div> </div> <div id="bottom_right" class="float-left"> <div class="list_cont nano"> <div class="content">';
	bcontent=bcontent+bgcontent+' </div> </div> <div class="bottom_bar rep_sprite"><div class="btn_action float-right "><a class="rep_sprite btn_blue" id="save_group_changes" style="display:none" onclick="">Save Changes</a></div></div> <div class="bottom_subbar rep_sprite" id="createGroupCont"> <input name="" type="text" class="input_type_filter  float-left onEnter groupClear" onenterbtn=".btn_create_group" id="newgroup" placeholder="new group" > <div class="btn_create_group rep_sprite float-left user_select_no" >Create</div> </div> </div> </div> <div id="btn" class="showFooterSelector" onclick="">   </div><div class="site_bar_btn add_site rep_sprite float-left" style="margin-left: 54px;" id="addWebsiteContainer"><div class="btn_add_site rep_sprite_backup" id="addWebsite" style="position:relative"><i class="fa fa-plus" style="left: 11px;top: 5px;color: rgb(115, 121, 135);font-size:18px;"></i>Add Website</div></div>';
	bottomToolbarVar=bcontent;
	if(subSitesRestrict==1)
	siteSelectorRestrictVar=scontent;
	else
	siteSelectorVar = scontent;


	
}


function pluginsThemesFavoritesSelector(){
	 scontent ='';
	 totalSiteCount = '';
	 sgcontent = '';
	 sscontent = '';
	 bcontent  = '';
	 bgcontent = '';
	 bscontent = '';
	
	if(favouritesGroupData != null && favouritesGroupData != undefined)
	{
		$.each(favouritesGroupData, function(key,value) {
			if(value.IDs != undefined)
			 totalSiteCount = value.IDs.length;
			 else
			totalSiteCount = 0;
			sgcontent = sgcontent+'<div class="group_cont rep_sprite favGroup ind_site favItems searchable" style="width:250px!important;height: 30px !important;" gid="'+key+'" type="'+value.type+'" onclick=""><a type="'+value.type+'" title="'+value.name+'" >'+value.name+'</a><div class="rep_sprite remove_bg"><span class="rep_sprite_backup del delFavourites"></span></div></div>';
			bgcontent = bgcontent+'<div class="ind_groups" id="g'+key+'" gid="'+key+'" onclick=""><a title="'+value.name+'" onclick=""><span class="count_cont">'+totalSiteCount+'</span><input name="" type="text" value="'+value.name+'" class="groupEditText" /></a>   <div class="del_conf"><div class="label">Sure?</div><div class="yes deleteGroup">Yes</div><div class="no deleteGroup">No</div></div><div class="edit_del_cont"> <div class=" rep_sprite bg "><span class="rep_sprite_backup edit editGroup"></span></div> <div class=" rep_sprite bg"><span class=" rep_sprite_backup del deleteConf"></span></div> </div></div>';
		});
	}
	if(favourites != null && favourites != undefined &&  getPropertyCount(favourites)>0)
	{
		$.each(favourites, function(key1,types) {
			$.each(types, function(key2,value) {
			sscontent = sscontent+'<div class="ind_site favItems searchable" style="width:235px; border-right:1px solid #f1f1f1"><a id="f'+value.ID+'"  utype="'+value.type+'" onclick="" dlink="'+value.URL+'" iname="'+value.name+'" title="'+value.name+'">'+value.name+'</a><div class="rep_sprite remove_bg"><span class="rep_sprite_backup del delFavourites "></span></div> </div>';
			bscontent = bscontent+'<div class="ind_site favItems searchable"><a id="f'+value.ID+'"  utype="'+value.type+'" onclick="" dlink="'+value.URL+'" iname="'+value.name+'" title="'+value.name+'">"'+value.name+'"</a><div class="rep_sprite remove_bg"><span class="rep_sprite_backup del delFavourites "></span></div> </div>';
			});
		});
	}
	else
	{
		sscontent = '';
		bscontent = '<div class="empty_data_set websites"> <div class="line1">No websites added yet..</div> <div class="line2">That makes us sad. Come on.<br /> Lets add one of your WordPress sites.</div> <div class="add_site_arrow"></div> </div>';
	}
	scontent = '<div class="site_selector1 siteSearch"> <div class="bygroup" style="width: 249px;">';
	scontent = scontent+'<div class="group_items_cont favouritesGroup" ><div class="content fav_rows_cont">';
	scontent = scontent+sgcontent+'</div></div></div>'; // class bygroup ends here
	scontent = scontent+'<div class="bywebsites">   <div class="website_items_cont favouritesItems" ><div class="content fav_rows_cont favSearch">';
	scontent = scontent+sscontent+'<div class="no_match hiddenCont" style="display:none">Bummer, there are no websites that match.<br />Try typing fewer characters.</div> <div class="clear-both"></div> </div></div> <div class="clear-both"></div> </div><div class="clear-both"></div></div>';
	bcontent = '<div id="bottom_sites_cont" style="display:none"><div id="bottom_left" class="float-left"><div class="list_cont nano"><div class="content"><div class="no_match hiddenCont" style="display:none">Bummer, there are no websites that match.<br />Try typing fewer characters.</div>';
	bcontent = bcontent+bscontent+' </div> </div> <div class="bottom_bar rep_sprite"> <div class="select_box_cont select2_bottom float-left" >'+groupGenerate(1,"bottom")+'</div> <div class="rep_sprite toggle_manage_groups float-right"><a class="rep_sprite_backup" style="position: relative;"><i class="fa fa-list-alt" style="top: 4px; left: 3px; color: rgb(115, 121, 135); font-size:16px;"></i></a></div> </div> <div class="bottom_subbar rep_sprite"><div style="position:relative;"><input name="" type="text" class="input_type_filter search_site" placeholder="type to filter" ><div onclick="" class="clear_input rep_sprite_backup"></div></div> </div> </div> <div id="bottom_right" class="float-left"> <div class="list_cont nano"> <div class="content">';
	bcontent = bcontent+bgcontent+' </div> </div> <div class="bottom_bar rep_sprite"><div class="btn_action float-right "><a class="rep_sprite btn_blue" id="save_group_changes" style="display:none" onclick="">Save Changes</a></div></div> <div class="bottom_subbar rep_sprite" id="createGroupCont"> <input name="" type="text" class="input_type_filter  float-left onEnter groupClear" onenterbtn=".btn_create_group" id="newgroup" value="new group" > <div class="btn_create_group rep_sprite float-left user_select_no" >Create</div> </div> </div> </div> <div id="btn" class="showFooterSelector" onclick="">   </div><div class="site_bar_btn add_site rep_sprite float-left" style="margin-left: 54px;" id="addWebsiteContainer"><div class="btn_add_site rep_sprite_backup" id="addWebsite">Add Website</div></div>';
	testingBottom = bcontent;
	favoritesGroupsContent = scontent;
}


function triggerNanoScrollerFavoritesGroup(){
	if ($(".favouritesGroup").css('height') != '0px') {
		$('.favouritesGroup').nanoScroller({ alwaysVisible: true, scroll: 'top' });
		$(".favouritesGroup").css('height',"160px").addClass('nano');
		$(".favouritesGroup").nanoScroller();
	}

	$('.favouritesItems').nanoScroller({ alwaysVisible: true, scroll: 'top' });
	$(".favouritesItems").css('height',"160px").addClass('nano');
	$(".favouritesItems").nanoScroller();

}
function updateSites(object,group)
{
	
	var arrayCounter=0;
	var updateArray={};
	var tempArray={}, siteID, dID, type;
	
	$('span.statusSpan',object).text('Queued..'); 
	$('span.statusSpan',object).addClass('updating');
	$('span.typeVar',object).hide();
	
	if(group==1)
	{
		var topParent="#"+currentUpdatePage;
		var theSelector=$(object).attr('selector');
		
		$(".item_ind."+theSelector+".active",topParent).not(".hide").not(".hidden").not('.updating').each(function () {
			if(!$(this).closest('.ind_row_cont').hasClass('hide') && !$(this).closest('.ind_row_cont').hasClass('hidden') )
			{
				
				$('.update_single',this).text('Queued..');
				$('.hideItem',this).hide();
				$(this).addClass('updating');
				$(this).removeClass('active');
				$(".row_checkbox",this).hide();
				siteID=$(this).attr('sid');
				dID=$(this).attr('did');
				type=$(this).attr('utype');
				if(updateArray[siteID]==undefined)
				updateArray[siteID]={};
				if(updateArray[siteID][type]==undefined)
				updateArray[siteID][type]={};
				if(type=="core")
				{
					if(siteID!=undefined && type!=undefined && dID!=undefined)
					updateArray[siteID][type]=dID;
				}
				else
				{
					if(siteID!=undefined && type!=undefined && dID!=undefined)
					updateArray[siteID][type][arrayCounter]=dID;
				}
				arrayCounter++;
			}
		});
	}
	else
	{
		var topDiv=$(object).closest('.item_ind');
		$(topDiv).addClass('updating');
		$('.update_single',topDiv).text('Queued..');
		$('.hideItem',topDiv).hide();
		$(topDiv).removeClass('active');
		$(".row_checkbox",topDiv).hide();
		siteID=$(topDiv).attr('sid');
		dID=$(topDiv).attr('did');
		type=$(topDiv).attr('utype');
		if(updateArray[siteID]==undefined)
		updateArray[siteID]={};
		if(updateArray[siteID][type]==undefined)
		updateArray[siteID][type]={};
		if(type=="core")
		{
			if(siteID!=undefined && type!=undefined && dID!=undefined)
			updateArray[siteID][type]=dID;
			
		}
		else
		{
			if(siteID!=undefined && type!=undefined && dID!=undefined)
			updateArray[siteID][type][0]=dID;
		}
		
		
	}
	// check the root level update option

	var topClosestVar=$(object).closest('.ind_row_cont');
	if($(".item_ind",topClosestVar).not('.hidden').not('.hide').length==$(".item_ind.updating",topClosestVar).not('.hidden').not('.hide').length)
	{
		$(topClosestVar).addClass('updating');
		$(".select_action,.select_action_long",topClosestVar).hide();
	}
	checkUpdateSelect(object);
	/* checkGeneralSelect($(object).attr('selector'),'');
	checkGeneralSelect($(object).attr('parent'),'');
	checkGeneralSelect('ind_row_cont','');*/
	$('span.statusSpan',object).removeClass('updating');	
	tempArray['action']='updateAll';
	tempArray['args']={};
	tempArray['args']['params']=updateArray;
	//checkSelection($(object).attr('selector'));
	//doHistoryCall(ajaxCallPath,tempArray,'');
	doCall(ajaxCallPath,tempArray,'formArrayUpdateAll','json',"none");

}
function resetFilterText(mainElement)
{
	
	$(".input_type_filter",mainElement).val('').focus().blur();
	$(".hiddenCont, .clear_input",mainElement).hide();
	
}

function filterByGroup(object,val,type)
{
	var selectedNow = $('#mainUpdateCont .optionSelect.active').text();
	var classSelector = '',siteAttr = '';
	if(object=='' || object==undefined)
	closestVar=$("#bottomToolBarSelector");
	else
	closestVar=$(object).closest('.siteSearch');
	$(".input_type_filter",closestVar).val('').focus().blur();
	
	$(".hiddenCont, .clear_input",closestVar).hide();
	if(val==0)
	{
		$('.js_sites',closestVar).removeClass('hide').removeClass('groupHide');
		if(groupOperationFlag == 2)
		{
			if(selectedNow == 'Websites')
			{
				$('#siteViewUpdateContent .js_sites').removeClass('hide').removeClass('groupHide');
			}
			else
			{
				$('.plugin_theme_wp_group_hide').removeClass('hide').removeClass('groupHide');
			}
		}
	}
	else
	{	
		$('.js_sites',closestVar).removeClass('groupHide').addClass('hide').addClass('groupHide');
		if(groupOperationFlag == 2)
		{
			if(selectedNow == 'Websites')
			{
				classSelector = '#siteViewUpdateContent .js_sites';
				siteAttr = 'siteid';
			}
			else
			{
				classSelector = '.plugin_theme_wp_group_hide';
				siteAttr = 'sid'
			}
			
			$(classSelector).removeClass('groupHide').addClass('hide').addClass('groupHide');
		}
		if(getPropertyCount(group[val].siteIDs)>0)
		{
			$.each(group[val].siteIDs, function(i,siteID) {

				$('[sid="'+siteID+'"]',"#bottomToolBarSelector").removeClass('hide').removeClass('groupHide');
				if(groupOperationFlag == 2)
				{
					$(classSelector).each(function(){
						if($(this).attr(siteAttr) == siteID)
						{
							$(this).removeClass('hide').removeClass('groupHide');
						}
					});
					
				}
				
			});
		}
	}
	if(type==1) //for updates page to validate the extreme TOP update option
	checkGeneralSelect('ind_row_cont');
	lazyLoadOnlyVisibleImages();
	
}




function filterByGroupUpdate(object,val,type)
{	
	
	closestVar=$(object).closest('.siteSearch');
	var total_sites_in_group = 0;
	var selected_sites_in_group = 0;
	if(val==0)
	{
		$('.js_sites',closestVar).removeClass('hide');
		$('#'+currentUpdatePage+' .js_sites').removeClass('hide').removeClass('groupHide');
	}
	else
	{
		
		$('.js_sites',closestVar).removeClass('groupHide').addClass('hide').addClass('groupHide');
		classSelector = '#'+currentUpdatePage+' .js_sites';
		siteAttr = 'siteid';
		$(classSelector).removeClass('groupHide').addClass('hide').addClass('groupHide');
		if(getPropertyCount(group[val].siteIDs)>0)
		{
			$.each(group[val].siteIDs, function(i,siteID) {

				//$('[sid="'+siteID+'"]',"#bottomToolBarSelector").removeClass('hide').removeClass('groupHide');
				$(classSelector).each(function(){
					if($(this).attr(siteAttr) == siteID)
					{
						$(this).removeClass('hide').removeClass('groupHide');
					}
				});
			});
		}
	}
	$(classSelector).each(function(){
		total_sites_in_group += 1;
		if($(this).hasClass('hide'))
		selected_sites_in_group += 1;
	});
	if(total_sites_in_group == selected_sites_in_group)
	{
		$('#'+currentUpdatePage+' .no_match').next('.group_error_message').remove();
		$('#'+currentUpdatePage+' .no_match').after('<div class="no_match group_error_message" >No updates available in this group</div>');
	}	
	
}



function groupGenerate(type,randVal)
{
	var content='';
	
	if(type==2) var gcontent='';
	if(type==1) // Type toolbar
	{
		var toolVar='_toolbar';
	}
	else
	var toolVar='';
	if(randVal==undefined)
	randVal=incrementRand;
	content='<select name="rand_'+randVal+'" class="select_group'+toolVar+'" tabindex="2"><option value="0">All Websites</option>';

	$.each(group, function(i, object) {
		
		if(type==2)
		gcontent=gcontent+'<div class="ind_group js_addSite g'+i+'" gid="'+i+'"><a>'+object.name+'</a></div>';
		
		content=content+'<option value="'+i+'">'+object.name+'</option>';
		
	});
	content=content+'</select>';
	incrementRand++;
	if(type==2)
	{
		gcontent='<div class="group_selector"><div class="content">'+gcontent+'</div><div class="clear-both"></div> </div>';
		return gcontent;
	}
	else
	return content;
}
function selectorBind(object,className)
{
	if($(object).attr('selector')!='ind_row_cont')
	selection(className,$(object).attr('selector'),object);
	else
	{

		mainUpdateSelection(className,object);
	}


}
function showOrHide(object,className,IDToProcess,extra)
{
	IDToProcess="#"+IDToProcess;
	if(extra==1) // Reset Group for the bottom tool bar
	resetGroup();
	if($(IDToProcess).is(':visible'))
	{
		$(IDToProcess).hide();
		$(object).removeClass(className);
		if(toolTipData.manageGroups!="true" && IDToProcess=='#bottom_sites_cont')
		$(".toggle_manage_groups").qtip('destroy');
	}
	else
	{
		if(toolTipData.manageGroups!="true" && IDToProcess=='#bottom_sites_cont')
		{
			$(".toggle_manage_groups").qtip({events: { hide: function(event, api) { tempArray={}; tempArray['requiredData']={}; valArray={}; valArray['manageGroups']=true; tempArray['requiredData']['updateUserhelp']= valArray; tempArray['requiredData']['getUserHelp']= 1;  doCall(ajaxCallPath,tempArray,'setTooltipData'); } }, id: 'manageGroupsQtip', content: { text: ' ', title: { text: 'Manage Groups', button: true } }, position: { my: 'bottom center', at: 'top center', adjust:{ y: -7} }, show: { event: false, ready: true }, hide: false, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 10, height:5} } });
		}
		
		$(IDToProcess).show();
		$(object).addClass(className);
	}
}
function checkGroupName(name)
{
	var checkVar=true;
	$.each(group, function(gid, array) {
		if(name.toLowerCase()==array.name.toLowerCase())
		{
			checkVar=false;
			return false;
		}
		
	});
	if(groupCreateArray!=undefined)
	{
		
		$.each(groupCreateArray, function(groupID, groupName) {
			if(groupName.toLowerCase()==name.toLowerCase())
			{
				checkVar=false;
				return false;
			}
			
		});
	}
	return checkVar;
}
function createGroup()
{
	
	var generalGroupVal=$("#newgroup").val();
	if(checkGroupName(generalGroupVal) && generalGroupVal!='new group')
	{
		$("#bottom_right > .list_cont > .content").animate({ scrollTop: $("#bottom_right").height() }, "slow");
		var groupVal=generalGroupVal;
		var content='<div class="ind_groups row_arrow_left" id="gnew-'+groupCounter+'" gid="new-'+groupCounter+'" newgroup="1" ><a><span class="count_cont">0</span><input name="" type="text" value="'+groupVal+'" class="groupEditText" /></a><div class="del_conf" style="display: none; "><div class="label">Sure?</div><div class="yes deleteGroup">Yes</div><div class="no deleteGroup">No</div></div><div class="edit_del_cont"> <div class=" rep_sprite bg "><span class="rep_sprite_backup edit editGroup"></span></div> <div class=" rep_sprite bg"><span class=" rep_sprite_backup del deleteConf"></span></div> </div></div>';
		$("#newgroup").val('new group').css('color','rgb(170, 170, 170)');
		if($(".ind_groups","#bottomToolBarSelector").length==0)
		{
			$("#bottom_right > .list_cont > .content").html(content);
			showGroupSelectBox();
		}
		else
		$("#bottom_right > .list_cont > .content").append(content);
		$('#gnew-'+groupCounter).click();

		groupCreateArray['new-'+groupCounter]=groupVal;
		groupCounter++;
		$("#save_group_changes").show();
	}
	else if(generalGroupVal=='new group' || generalGroupVal=='')
	$("#createGroupCont").append("<div id='duplicateGroup'>Enter a group name.</div>");
	else
	{
		
		$("#createGroupCont").append("<div id='duplicateGroup'>Group already exists. Try somethin' else.</div>");
	}

	
	
	
}

//  for footer selector updates_footer_updated
function resetGroup(refreshData)
{
	if(refreshData==undefined)
	refreshData=0;
	$("#bottom_toolbar #bottom_sites_cont .list_cont .ind_sites a").css({'background-position': '-25px 0', 'padding': '11px 0 9px 5px','width': '255px'});
	$("#bottom_right").hide();
	//$(".btn_blue").hide();
	$("#bottom_toolbar #bottom_sites_cont .list_cont .ind_sites").removeClass('active');
	$(".ind_groups").removeClass('active');
	//$("#dk_container_rand_bottom").removeClass('disabled');
	$(".select_group_toolbar").select2("enable");
	$(".toggle_manage_groups").removeClass('active');
	if(refreshData==1)
	{
		
		filterByGroup("",$('.select_group_toolbar').select2("val"));
	}
	groupEditFlag = 0;
	
}
function showGroupSelectBox()
{
	if ( $.browser.msie && $.browser.version=='8.0') {
		$('#bottom_toolbar #bottom_sites_cont .list_cont .ind_sites a').addClass('checkbox').css({'background-position': '0 0','padding-left': '30px','width': '230px'});
	}
	else
	{$('#bottom_toolbar #bottom_sites_cont .list_cont .ind_sites a').addClass('checkbox').animate({
backgroundPosition: '0 0',
			
paddingLeft: '30px',
width: '230px'
		}, 300);
	}
}
function groupEdit(object)
{
	groupEditFlag = 1;
	if($("#bottom_right").is(':visible'))
	{
		$('#bottom_toolbar #bottom_sites_cont .list_cont .ind_sites a').removeClass('checkbox');
		resetGroup(1);
		$(object).removeClass('active');
	}
	else
	{
		
		$("#bottom_right").show();
		$(object).addClass('active');
		
		if($(".ind_groups","#bottomToolBarSelector").length>0)
		{
			showGroupSelectBox();
			//$("#dk_container_rand_bottom").addClass('disabled');
			$(".select_group_toolbar").select2("disable");
			$('.js_sites',"#bottomToolBarSelector").removeClass('hide');
			$(".ind_groups:first","#bottomToolBarSelector").click();

			$("#bottomToolBarSelector .nano").nanoScroller({stop: true});
			$("#bottomToolBarSelector .nano").nanoScroller();
		}
		

	}
	

}

function expandThis(object,className)
{
	var mainDiv = $(object).closest('.ind_row_cont');
	if(className=='summary')
	{
		$('.row_summary',mainDiv).hide();
		$('.row_detailed',mainDiv).show();
	}
	else
	{
		$('.row_summary',mainDiv).show();
		$('.row_detailed',mainDiv).hide();
	}
	
}

// For updates.html page
function optionSelect(object,type)
{
	var sFlag=0;
	if(type==1 && $(object).hasClass('active')) // For plugins / theme page to give the functionality of unselecting the optional select
	{
		$(object).removeClass('active');
	}
	else
	{
		ulClass=$(object).closest('ul');
		if(type==1)
		{
			$(".optionSelectOne",ulClass).removeClass('active');
			if($(object).attr('action')=='activate' && activeItem=='themes')
			$(".site"+$(object).attr('sid')).removeClass('active');
		}
		else
		$(".optionSelect",ulClass).removeClass('active');
		$(object).addClass('active');
		
	}
	
	
}

function checkFavItems()
{

	var className="favItems";
	var totalLength = $('.'+className,".favSearch").length;
	var cFlag=$('.'+className+'.active',".favSearch").length;
	if(cFlag==0){
	$(".status_"+className).addClass('disabled');
		$("#createFavoriteGroup").addClass('disabled');
		$("#createFavoriteGroup").css('opacity','0.5');
	}
	else{
	$(".status_"+className).removeClass('disabled');
		$("#createFavoriteGroup").css('opacity','1.0');
		$("#createFavoriteGroup").removeClass('disabled');
	}

}
function checkUpdateSelect(object,group)
{
	
	var parent=$(object).attr('parent');
	var selector=$(object).attr('selector');
	if(selector=='item_ind')
	{
		var topParent="#"+currentUpdatePage;
		$(object).addClass('disabled');
		
		$(".ind_row_cont.active:not(.hide, .hidden, .hideVar) .row_checkbox",topParent).hide();
		$(".ind_row_cont.active:not(.hide, .hidden, .hideVar) .row_action .update_group",topParent).hide();
		$(".ind_row_cont.active:not(.hide, .hidden, .hideVar) .select_action,.select_action_long",topParent).hide();
		$('.ind_row_cont.active:not(.hide, .hidden, .hideVar)',topParent).not(".hide").removeClass('active').addClass('updating');
		
	}
	else {
		topParent=$(object).closest('.updateTabs');
		
		
		if($(".item_ind."+selector,topParent).not('.hide').not('.hidden').not('.updating').length<1)
		{
			if(parent==selector)
			$(".ind_row_cont."+selector+" .select_action",topParent).hide();
			else
			$(".select_"+selector,topParent).hide();
			
			if(parent!=undefined && $(".item_ind."+parent,topParent).not('.hide').not('.hidden').not('.updating').length<1)
			{
				$(".ind_row_cont."+parent+" .row_checkbox",topParent).hide();
				$(".ind_row_cont."+parent,topParent).removeClass('active');
				$(".status_"+parent,topParent).text('Update All').hide();
			}
		}
		if(parent!=undefined && parent!=selector)
		checkGeneralSelect(parent,topParent);
		checkGeneralSelect(selector,topParent);
		checkGeneralSelect("ind_row_cont",topParent);
	}
	
}
function showHidden()
{
	var topParent="#"+currentUpdatePage;
	$.each(updateCheckArray, function(property, value) { 
		var tLength=$(".item_ind."+property,topParent).not('.hide').not('.updating').length;
		if(tLength>0)
		{
			$(".row_"+property).show();
			
			if(!$(".row_"+property,topParent).closest(".ind_row_cont").is(":visible"))
			$(".row_"+property,topParent).closest(".ind_row_cont'").show();
		}
		

	});
}
function checkGeneralSelect(className,topParent,noTopParent)
{
	var totalLength, cLength, tClosestVar, topClosestVar;
	if(noTopParent==undefined)
	noTopParent=0;
	if(topParent==undefined || topParent=='')
	topParent="#"+currentUpdatePage;
	if(noTopParent==1)
	{
		totalLength=$(".item_ind."+className).not('.hide').not('.hidden').not('.updating').length;
		cLength=$(".item_ind.active."+className).not('.hide').not('.hidden').not('.updating').length;

	}
	else {
		if(className!='ind_row_cont')
		{
			totalLength=$(".item_ind."+className,topParent).not('.hide').not('.hidden').not('.updating').length;
			cLength=$(".item_ind.active."+className,topParent).not('.hide').not('.hidden').not('.updating').length;

		}

		else
		{
			totalLength=$("."+className,topParent).not('.hide').not('.hidden').not('.updating').length;
			cLength=$(".active."+className,topParent).not('.hide').not('.hidden').not('.updating').length;
		}
	}

	if(totalLength==0)
	{
		if(noTopParent==1)
		{
			$(".row_"+className).addClass("hideVar");
			if(viewHiddenFlag==0)
			$(".row_"+className).hide();
			tClosestVar=$(".row_"+className);
			topClosestVar=$(".row_"+className).closest('.ind_row_cont');
			
			if($(".item_ind",tClosestVar).not('.updating').not('.hidden').not('.hide').length<2)
			$(".select_"+className).slideUp();
			if($(".item_ind",topClosestVar).not('.updating').not('.hidden').not('.hide').length<1)
			{
				if(viewHiddenFlag==0)
				$(".row_"+className).closest(".ind_row_cont'").hide();
				$(".row_"+className).closest(".ind_row_cont'").addClass("hideVar").removeClass('active');
			}
		}
		$(".status_"+className).text('Update All ');
		if(className=='ind_row_cont')
		$(".status_"+className).addClass('disabled');
		else 
		$(".status_"+className).closest(".update_group").hide();

	}
	else if(totalLength!=0 )
	{
		
		//$(".select_"+className).show();
		if(noTopParent==1)
		{
			$(".row_"+className).removeClass("hideVar");
			$(".row_"+className).show();
			tClosestVar=$(".row_"+className);
			topClosestVar=$(".row_"+className).closest('.ind_row_cont');

			if($(".item_ind",tClosestVar).not('.updating').not('.hidden').not('.hide').length>1)
			{
				$(".select_"+className).slideDown();
			}
			else
			{
				$(".select_"+className).hide();
			}
			if($(".item_ind",topClosestVar).not('.updating').not('.hidden').not('.hide').length>1)
			{
				$(".row_"+className).closest(".ind_row_cont'").show();
				$(".row_"+className).closest(".ind_row_cont'").removeClass("hideVar");
			}
		}
		if(totalLength==cLength)
		{
			/*if(noTopParent==1)
		$(".select_"+className).show();*/
			$(".status_"+className).text('Update All ');
			if(className!='ind_row_cont')
			{
				//$(".status_"+className).closest(".select_action").show();
				$(".status_"+className).closest(".update_group").show();
			}
			else {
				$(".status_"+className).removeClass('disabled');
			}
		}
		else if(cLength==0)
		{
			
			$(".status_"+className).text('Update All ');
			if(className!='ind_row_cont')
			{
				//$(".status_"+className).closest(".select_action").hide();
				$(".status_"+className).closest(".update_group").hide();
			}
			else
			$(".status_"+className).addClass('disabled');
		}
		else
		{
			
			/*if(noTopParent==1)
		{
		
		$(".select_"+className).show();
		}*/
			$(".status_"+className).text('Update Selected ');
			if(className!='ind_row_cont')
			{
				$(".status_"+className).closest(".select_action").show();
				$(".status_"+className).closest(".update_group").show();
			}
			else
			$(".status_"+className).removeClass('disabled');
		}

	}

	
}
function generalSelect(object, className,updateCheck)
{
	var activeFlag=0;
	var topParent=$(object).closest('.updateTabs');
	var closestVar=$(object,topParent).closest('.ind_row_cont');
	if($(object).hasClass('active'))
	{
		$(object).not('.hidden').not('.hide').not('.updating').removeClass('active')
		activeFlag=0;
	}
	else
	{
		$(object).not('.hidden').not('.hide').not('.updating').addClass('active');
		activeFlag=1;
	}
	
	
	if(className=="ind_row_cont")
	{
		if(activeFlag==0)		
		{
			$(".item_ind",object).not(".hide").not(".hidden").not(".updating").removeClass('active');
			//	$(".status_"+parent).text('Update All');
			//$(".status_"+parent).addClass('disabled');
		}
		else
		$(".item_ind",object).not(".hide").not(".hidden").not(".updating").addClass('active');
		//$(".status_"+parent).removeClass('disabled');
	}
	else {
		if($(".item_ind.selectOption.active",closestVar).not(".hide").not(".hidden").not(".updating").length< $(".item_ind.selectOption",closestVar).not(".hide").not(".hidden").not(".updating").length)
		{
			$(object).closest(".ind_row_cont").removeClass('active');
		}
		else if($(".item_ind.selectOption.active",closestVar).not(".hide").not(".hidden").not(".updating").length==$(".item_ind.selectOption",closestVar).not(".hide").not(".hidden").not(".updating").length)
		$(object).closest(".ind_row_cont").addClass('active');
		
	}
	
	var parent=$(object).attr('parent');
	var selector=$(object).attr('selector');
	if(parent!=undefined)
	checkGeneralSelect(parent,topParent);
	checkGeneralSelect(selector,topParent);
	checkGeneralSelect("ind_row_cont",topParent);
	
	
}



// For SiteSelector.html page
var gFlag=0;
var gAll=0;
var currentGroup=0;
function selectedGroupSelection()
{
	var closestVar=".siteSelectorContainer",temp;
	$(".group_cont.active",closestVar).each(function () { 
		if($(this).attr('gid')!=undefined && $(this).attr('gid')!='')
		{
			
			temp=group[$(this).attr('gid')].siteIDs;
			if(temp!=undefined && getPropertyCount(temp)>0){
				$.each(temp, function(key, value) {
					$("#s"+value,closestVar).not(".disabled").addClass('active');
				});
			}
		}
	});
}

function makeSelectionPluginsThemesFavorites(classNames){
	$(".installFavourites").addClass('disabled');
	$("#createFavoriteGroup").addClass('disabled');
	$("#createFavoriteGroup").css('opacity','0.5');

	if ($(classNames).hasClass('active')){
		var gid = $(classNames).attr('gid');
		$(classNames).removeClass('active');
		$('a[group_id]').parent().removeClass('active');
		if (gid == 'g0') {
			$.each(favourites, function(key1,types) {
				$.each(types, function(key2,value) {
					$('a[ id=f'+value.ID+']' ).parent().removeClass('active');
				});
			});
			$(".installFavourites").addClass('disabled');
			$("#createFavoriteGroup").addClass('disabled');
			$("#createFavoriteGroup").css('opacity','0.5');
		} else {
			if (favouritesGroupData[gid].IDs != undefined) {
				$.each(favouritesGroupData[gid].IDs, function(key1,types) {
					var favoritesCount = $('a[ id=f'+types+']' ).parent().removeClass('active');
				});
			}
			$(".installFavourites").addClass('disabled');
			$("#createFavoriteGroup").addClass('disabled');
			$("#createFavoriteGroup").css('opacity','0.5')
			$(".favGroup.active").each(function () {
				gid = $(this).attr("gid");
				if (favouritesGroupData[gid].IDs != undefined) {
				$.each(favouritesGroupData[gid].IDs, function(key1,types) {
					favoritesCount = $('a[ id=f'+types+']' ).parent().addClass('active');
					if (favoritesCount.length) {
						$("#createFavoriteGroup").removeClass('disabled');
						$("#createFavoriteGroup").css('opacity','1.0');
						$(".installFavourites").removeClass('disabled');
					}
				});
			}
			});
		}
	}else{
		$('a[group_id]').parent().removeClass('active');
		$(classNames).addClass('active');
		var gid = $(classNames).attr('gid');
		if (gid == 'g0') {
			$.each(favourites, function(key1,types) {
				$.each(types, function(key2,value) {
					var favoritesCount = $('a[ id=f'+value.ID+']' ).parent().addClass('active');
					if (favoritesCount.length) {
						$(".installFavourites").removeClass('disabled');
						$("#createFavoriteGroup").removeClass('disabled');
						$("#createFavoriteGroup").css('opacity','1.0');
					}
				});
			});
			
		} else {
			if (favouritesGroupData[gid].IDs != undefined) {
				$.each(favouritesGroupData[gid].IDs, function(key1,types) {
					var favoritesCount = $('a[ id=f'+types+']' ).parent().addClass('active');
					if (favoritesCount.length) {
						$("#createFavoriteGroup").removeClass('disabled');
						$("#createFavoriteGroup").css('opacity','1.0');
						$(".installFavourites").removeClass('disabled');
					}
				});
			}
		}
	}
}
function makeSelection(object,groupFlag,toolbar) // For selector
{
	var tempArray={}, id, temp;
	var tempCount=0;
	var closestVar=$(object).closest('.siteSearch');
	if($(object).hasClass('deselectGroups')) // For site selectors to deselect groups when a site is modified
	$(".group_cont",".siteSelectorContainer").removeClass('active');
	if($(object).hasClass('active'))
	{
		$(object).removeClass('active');
		gFlag=1; // Deactivate  the selected sites
	}
	else
	{
		$(object).addClass('active');
		gFlag=2; // Activate  the selected sites
	}
	
	if(toolbar==1 && groupFlag!=1)
	{
		var checkGroups=$(".ind_groups","#bottomToolBarSelector").length; // When 0 groups don't edit
		if(groupEditFlag==0 || checkGroups==0) // Not allow the sites to be selected when they are not in the edit mode.
		{
			
			$(object).removeClass('active');
		}
		var arrayCounter=0;
		var groupListCountDiv="#bottom_left  .list_cont .content  .active";
		if($(groupListCountDiv,closestVar).length>0)
		{
			$(groupListCountDiv,closestVar).each(function () {
				tempArray[arrayCounter]=$(this).attr('sid')
				//tempVar = tempVar+$(this).attr('sid')+',';
				tempCount++;
				arrayCounter++;
				
			});
		}
		else
		tempArray[0]='empty';

		//tempVar=tempVar.charAt(tempVar.length-1);
		
		groupChangeArray[currentGroup]=tempArray;
		$("#save_group_changes").show();
		$("#g"+currentGroup+"  a  .count_cont").html(tempCount);
	}
	
	if(groupFlag==1) // Selecting a group code.
	{
		//if(gFlag==1)
		
		if(gAll==1)
		{
			$(".group_items_cont  #g0",closestVar).removeClass('active'); // Remove all websites check mark by using the id g0
			$(".website_items_cont  .website_cont",closestVar).removeClass('active');
			gAll=0;
		}
		if(toolbar==1) // Just for toolbar group selection
		{
			currentGroup =$(object).attr('gid');
			removeDeleteConf();
			if(gFlag==2)
			{
				$(".ind_groups",closestVar).removeClass('active');
				$(".ind_sites",closestVar).removeClass('active');
			}
			id=$(object).addClass('active');
		}
		id=$(object).attr('gid');
		if(groupEditFlag==1 && (groupChangeArray[id]!=undefined))
		{
			temp=groupChangeArray[id];
			
		}
		else
		{
			if($(object).attr('newgroup')!=1)
			temp=group[id].siteIDs;
			else
			{
				temp='';
				
			}
			
			
		}
		if(temp!=undefined && getPropertyCount(temp)>0)
		{
			
			$.each(temp, function(key, value) {
				if(gFlag==2)
				{
					
					$("#s"+value,closestVar).not(".disabled").addClass('active');
				}
				else if(gFlag==1 && toolbar!=1)
				$("#s"+value,closestVar).not(".disabled").removeClass('active');
			});
		}
		selectedGroupSelection();
	}
	else if(groupFlag=='all')
	{
		$(".group_items_cont  .group_cont",closestVar).removeClass('active');
		if(gFlag==2)
		{
			
			$(".website_items_cont .website_cont",closestVar).not(".disabled").addClass('active');
			$(object).addClass('active');
			gAll=1
		}
		else if(gFlag==1)
		{
			$(".website_items_cont .website_cont",closestVar).not(".disabled").removeClass('active');
			gAll=0;
		}
		

		
	}
}
function mainUpdateSelection(type,object)
{
	var className="ind_row_cont";
	var topVar="#"+currentUpdatePage;
	if(type=='all')
	{
		$("."+className,topVar).not('.hide,.hidden,.updating,.hideVar').each(function () { 
			$(this).addClass('active');
			$(".item_ind",this).not('.hide,.hidden,.updating,.hideVar').addClass('active');
		});
	}
	else if(type=='none')
	{
		$("."+className,topVar).not('.hide,.hidden,.updating,.hideVar').each(function () { 
			$(this).removeClass('active');

			$(".item_ind",this).not('.hide,.hidden,.updating,.hideVar').removeClass('active');
		});
	}
	
	else if(type=='invert')
	{
		$("."+className,topVar).not('.hide,.hidden,.updating,.hideVar').each(function() {
			if($(this).hasClass('active'))
			{

				$(this).removeClass('active');
				$(".item_ind",this).not('.hide,.hidden,.updating,.hideVar').removeClass('active');
			}
			else
			{
				$(this).addClass('active');
				$(".item_ind",this).not('.hide,.hidden,.updating,.hideVar').addClass('active');
			}
		});
	}
	checkGeneralSelect(className);
	
}
function selection(type,className,object,parentClass)
{	
	if(parentClass==undefined)
	className=$("."+className);
	else
	{
		var closestVar = $(object).closest(parentClass);
		className=$("."+className,closestVar);
	}
	if(type=='all')
	{
		$(className).not('.hide,.updating,.hidden,.ind_row_cont,.disabled').addClass('active');
		
	}
	else if(type=='none')
	$(className).not('.hide,.updating,.hidden,.ind_row_cont,.disabled').removeClass('active');
	else if(type=='invert')
	{
		$(className).not('.hide,.updating,.hidden,.ind_row_cont,.disabled').each(function() {
			if($(this).hasClass('active'))
			$(this).not('.hide,.updating,.hidden,.ind_row_cont,.disabled').removeClass('active');
			else
			$(this).not('.hide,.updating,.hidden,.ind_row_cont,.disabled').addClass('active');
		});
	}
	checkGeneralSelect(className); // Updated from updates.html page
	if(object) // For update Page
	{
		var tempVar = $(object).attr('parent');
		if(tempVar)
		checkGeneralSelect(tempVar);
	}
	
}
function checkSearchedList(className,closestVar,type)
{
	
	if(type==2) //Updates page
	{
		if(closestVar=='')
		var topParent="#"+currentUpdatePage;
		else
		var topParent=closestVar;
		//topParent=$(closestVar,topParent);
		if(viewHiddenFlag==1)
		return $(".ind_row_cont",topParent).not('.hide').length;
		else
		return $(".ind_row_cont",topParent).not('.hide').not('.hideVar').length;
	}
	else
	{
		return $(className,closestVar).not('.hide').length;
	}
}
function searchSites(object,type){
	clearTimeout(timeOut);
	var className = ".searchable";
	if($(object).val().length>0){
	$(object).next().show();
	} else{
	$(object).next().hide();
	}
	if(type == 3) {
	var closestVar='.favSearch';
	} else if(type == 2 ) {
		var closestVar = $("#"+currentUpdatePage);
	} else{
		var closestVar = $(object).closest('.siteSearch');
	}

	var timeOut = setTimeout( function () {
		if($(object).val().length > 0 && $(object).val() != "type to filter" )
		{
			if(type == 2 || type == 4) // For updates and plugins search which has expandables
			{
				$(className,closestVar).closest('.ind_row_cont').not('.groupHide').removeClass('hide');
				$(className+":not(:contains("+$(object).val()+"))",closestVar).closest('.ind_row_cont').not('.groupHide').addClass('hide');
				checkGeneralSelect('ind_row_cont');
				if(type == 2){
					checkSearchListVar=checkSearchedList(className, '', 2);
				} else{
					checkSearchListVar=checkSearchedList(className, closestVar, 2);
				}
				if(checkSearchListVar < 1){
				$(".hiddenCont",closestVar).show();
				}
				else{
				$(".hiddenCont",closestVar).hide();
			}
			}
			else
			{
				$(className,closestVar).not('.groupHide').removeClass('hide').show();
				if (type == 3) {
					$(className+":not(:contains("+$(object).val()+"))",closestVar).hide().addClass('hide');
				} else {
				$(className+":not(:contains("+$(object).val()+"))",closestVar).not('.groupHide').addClass('hide');
				}
				
				if(checkSearchedList(className,closestVar) < 1){
				$(".hiddenCont",closestVar).show();
				}
				else{
				$(".hiddenCont",closestVar).hide();
			}
				
		}
		}
		else
		{
			$(".hiddenCont",closestVar).hide();
			if(type == 2 || type == 4)
			{
				$(className,closestVar).closest('.ind_row_cont').not('.groupHide').removeClass('hide');
				checkGeneralSelect('ind_row_cont');
			}
			else
			{
				if (type == 3) {
					$(className,closestVar).show().removeClass('hide');
				} else {
				$(className,closestVar).not('.groupHide').removeClass('hide');
				}
				
			}
		}
	},300);
	lazyLoadOnlyVisibleImages();
}

function validateForm(id)
{
	var checkFlag=0;
	var tempArray={};
	$("#"+id+" .formVal").removeClass("error").each(function () {
		if($(this).attr("type")=="hidden" || $(this).is(":visible"))
		{
			
			if($(this).hasClass('dropdown'))
			tempArray[$(this).attr('id')]=$(this).attr('dropopt');
			else
			tempArray[$(this).attr('id')]=$(this).val();
			if($(this).hasClass('required') && $(this).val()=='')
			{
				if(!($("#cloneTestConnection").hasClass("testing") && ($(this).hasClass("testException")))){			//just to avoid some fields during install Clone test connection
					$(this).addClass('error');
					checkFlag=1;
				}
			}
		}
		
		
		
		
	});
	$("#"+id+" .checkbox.active").each(function () {
		tempArray[$(this).attr('id')]=1;
		
	});
	if(checkFlag==1)
	tempArray = false;
	if(id=="dropboxRepo")
	{
		tempArray["consumer_key"]=dpConsumerKey;
		tempArray["consumer_secret"]=dpConsumerSecret;
	}
	
	return tempArray;
	
}

function processTestConnection(data,id,buttonID) // Used by clone too
{
	data=data.data.repositoryTestConnection;
	if(data.status=="success")
	{
		$("#"+buttonID).removeClass('testing').addClass('success');
	}
	else
	{
		$("#"+buttonID).removeClass('testing').addClass('error');
		
		$("#"+id+" .inner_cont").append('<div class="conn_test_error_cont profileStatusDiv"><div class="e_close"></div>'+data.errorMsg+'<div class="conn_test_error_cont_arrow"></div></div>');
	}
}

function processFTPTestConnection(data)
{
	data=data.data.FTPTestConnection;
	if(data.status=="success")
	{
		$("#testFTPConnection").removeClass('testing').addClass('successftp');
	}
	else
	{
		$("#testFTPConnection").removeClass('testing').addClass('error');
		$("#completeForm .inner_cont").append('<div class="conn_test_error_cont profileStatusDiv"style="margin-bottom: 32px;margin-right: 99px; position:absolute; "><div class="e_close"></div>'+data.errorMsg+'<div class="conn_test_error_cont_arrow"></div></div>');
	}
}

function installIWPAddons(data,update)
{
	if(update==1)
	var type="UPDATE";
	else
	var type="INSTALL";

	if(addonSuiteMiniLimitExceeded('installAddons')) return(false);
	
	var content = '<div class="dialog_cont" style="width:750px;"> <div class="th rep_sprite"> <div class="title droid700">'+type+' ADDONS</div></div><iframe src="update.php?action='+data+'" height="500px" width="750px" id="infiniteWP"></iframe></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}
function checkIWPAddons(data)
{
	if(data==undefined)
	{
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['forceCheckUpdate']=1;
		tempDataArray['requiredData']['checkIsAddonSuiteMiniLimitExceeded']=1;
		tempDataArray['requiredData']['getAddonSuiteMiniLimit']=1;
		tempDataArray['requiredData']['getAddonSuiteMiniActivity']=1;
		doCall(ajaxCallPath,tempDataArray,'checkIWPAddons','json');
	}
	else if(data.data.forceCheckUpdate.status=='netError')
	{
		var tempVar =[];
		tempVar[0]={message: data.data.forceCheckUpdate.errorMsg,state: "U", title:"Connection Error", type:"E"};
		fixedNotifications = tempVar;
		loadFixedNotifications();
	}
	else{
		isAddonSuiteMiniLimitExceeded = data.data.checkIsAddonSuiteMiniLimitExceeded;
		addonSuiteMiniLimit = data.data.getAddonSuiteMiniLimit;
		addonSuiteMiniActivity = data.data.getAddonSuiteMiniActivity;
		isAddonSuiteMiniCancelMessage(data.data.isAddonSuiteMiniCancelMessage);		
		loadAddonsPage();
	}
}

function processIWPLogin(data)
{
	$(".loginIWP").removeClass('disabled');
	$(".btn_loadingDiv").remove();
	request = data.data.IWPAuthUser;
	if(request.error==1)
	{
		if(request.passwordAuth==1)
		{
			$(".dialog_cont .loginError").html("<div class='loginFailureError'>Invalid Username or Password. Kindly try again.</div>");
			
		}
		else
		{
			$(".dialog_cont .dialog_content").html(request.message).addClass('warn_conn_break_iframe');
			$(".bottom_bar").remove();
		}
	} 
	else if (request.netError==1){
		$(".dialog_cont .loginError").html("<div class='loginFailureError'>"+request.errorMsg+"</div>");
	}
	else if (request.success==1)
	{
		usernameTemp='';
		passwordTemp='';
		$("#modalDiv").dialog("close");
		var message = request.action;
		if(message=="installAddons")
		installIWPAddons("installAddons&downloadToken="+request.token);
		else if(message=="register")
		checkIWPAddons();
	}
	else{
		$(".dialog_cont .loginError").html("<div class='error'>Unknown error</div>");
	}

}
function processAddonActivation(data)
{
	var sURL = unescape(window.location.pathname)+"?page=addons";
	window.location.href=sURL;

}

function objLen(obj) {
	var L=0;
	$.each(obj, function(i, elem) {
		$.each(elem, function(k, v) {
			L++;
		});
	});
	if(obj==0)
	return 0;
	else
	return L;
}

/*function myDate(dateCont)
{
	var d=new Date(dateCont);
	var mon=d.getMonth();
	var date=d.getDate();
	var t=d.getHours();
	var minDate = d.getMinutes();
	if(t>12)
	{
	var p='pm';
	t= t-12;
	}
	else 
	var p='am';
	if(minDate<10)
	{
		minDate='0'+minDate;
	}
	t= t+':'+minDate;
	t=t+p;
	
	switch (mon)
{
case 0:
mon="Jan";
break;
case 1:
mon="Feb";
break;
case 2:
mon="Mar";
break;
case 3:
mon="Apr";
break;
case 4:
mon="May";
break; 
case 5:
mon="Jun";
break;
case 6:
mon="Jul";
break;
case 7:
mon="Aug";
break;
case 8:
mon="Sep";
break;
case 9:
mon="Oct";
break;             
case 10:
mon="Nov";
break;
case 11:
mon="Dec";
break;
}

	return mon+' '+date+' @ '+t;
	
}*/

function maxLen(numData)
{
	if(numData>100)
	return '100+';
	else
	return numData;
}

function hideSitesBasedOnGroup()
{
	var selectedNow = $('#mainUpdateCont .optionSelect.active').text();
	var classSelector = '',siteAttr = '';
	if(selectedNow == 'Websites')
	{
		classSelector = '#siteViewUpdateContent .js_sites';
		siteAttr = 'siteId';
	}
	else
	{
		classSelector = '.plugin_theme_wp_group_hide';
		siteAttr = 'sid';
	}
	
	$(classSelector).removeClass('groupHide').addClass('hide').addClass('groupHide');
	if(typeof group[currentGroupID] != 'undefined')
	{
		if(group[currentGroupID].siteIDs != null)
		{
			$.each(group[currentGroupID].siteIDs, function(i,siteID) {

				$(classSelector).each(function(){
					if($(this).attr(siteAttr) == siteID)
					{
						$(this).removeClass('hide').removeClass('groupHide');
					}
				});
			});
		}
	}
	else
	{
		$(classSelector).removeClass('hide').removeClass('groupHide');
	}
	
	/* else
	{
		
		$('.plugin_theme_wp_group_hide').addClass('hide').addClass('groupHide');
		if(typeof group[currentGroupID] != 'undefined')
		{
			$.each(group[currentGroupID].siteIDs, function(i,siteID) {

				$('.plugin_theme_wp_group_hide').each(function(){
					if($(this).attr('sid') == siteID)
					{
						$(this).removeClass('hide').removeClass('groupHide');
					}
				});
			});
		}
		else
		{
			$('.plugin_theme_wp_group_hide').removeClass('hide').removeClass('groupHide');
		}
	} */
}

function hideUninstallandInactiveSites (pluginsData,checkPlugin) {
	data = {};
	
	if(checkPlugin == 'brokenLinks'){
		plugin_main_file = 'broken-link-checker/broken-link-checker.php';
		pluginName = 'Broken Link Checker'
	}else if(checkPlugin == 'wordFence'){
		plugin_main_file = 'wordfence/wordfence.php';
		pluginName = 'WordFence'
	}else if(checkPlugin == 'yoastWpSeo'){
		plugin_main_file = 'wordpress-seo-premium/wp-seo-premium.php';
		pluginName = 'WP SEO by Yoast'
	}else if(checkPlugin == 'ithemesSecurity'){
		plugin_main_file = 'better-wp-security/better-wp-security.php';
		pluginName = 'iThemes Security'
	}

	$.each(pluginsData,function(siteID,pluginData){
		if(plugin_main_file in pluginData){
			if(typeof pluginData[plugin_main_file] != 'undefined' && pluginData[plugin_main_file] != null){
			data[siteID] = [ pluginData[plugin_main_file]['isInstalled'],pluginData[plugin_main_file]['isActivated'] ];
		}else{
				data[siteID] = [null,null];
			}
		}else{
			data[siteID] = [false,false];
		}
	});
	$(".website_cont").each(function(){
		var id = $(this).attr('sid');
		if(typeof data[id] != 'undefined' && data[id] != null && typeof data[id][0] != 'undefined' && data[id][1] != 'undefined' && data[id][0] != null && data[id][1] != null){
			if (data[id][0] && !(data[id][1]) ) {
				if(checkPlugin == 'yoastWpSeo'){
					if($(this).find('.tips').length == 0)	$(this).append('<div class="tips" >Please Activate Premium '+pluginName+'</div>');
				}else{
					if($(this).find('.tips').length == 0)	$(this).append('<div class="tips" code="1">Click to Activate '+pluginName+'</div>');
				}
				if(!($(this).hasClass('disabled')))		$(this).addClass('disabled');
			}
			else if (!data[id][0] && !(data[id][1]) ) {
				if(checkPlugin == 'yoastWpSeo'){
					if($(this).find('.tips').length == 0)	$(this).append('<div class="tips" >Please Install & Activate Premium '+pluginName+'</div>');
				}else{
					if($(this).find('.tips').length == 0)	$(this).append('<div class="tips"  code="0">Click to Install & Activate '+pluginName+'</div>');
				}
				if(!($(this).hasClass('disabled')))		$(this).addClass('disabled');
			}else if(data[id][0] && (data[id][1]) ){
				$(this).removeClass('disabled');
				$(this).find('.tips').remove();
			}
		}else{
			if($(this).find('.tips').length == 0)	$(this).append('<div class="tips">Not communicable. Please reload data.</div>');
			if(!($(this).hasClass('disabled')))		$(this).addClass('disabled');
		}
	});
}


function getRecentPluginsStatusAndCheck(data){
	if(typeof data != 'undefined' && typeof data.data.getRecentPluginsStatus != 'undefined')
		pluginsStatus = data.data.getRecentPluginsStatus; 
	hideUninstallandInactiveSites(pluginsStatus,currentPage);
	siteSelectorNanoReset();
}

function clickAnchor(){
	
	//function to trigger the download of split zip parts.
		
	var this_download_backup_var = 0;
	var obj = ".this_download_backup_"+this_download_backup_var;
	this_download_backup_var = parseInt($(obj).siblings(".download").attr("back_var"));
	var total_url_parts = parseInt($(obj).siblings(".download").attr("back_var_total"));
	
	if(this_download_backup_var < total_url_parts)
	{
		obj = ".this_download_backup_"+this_download_backup_var;
		this_download_backup_var += 1;
		$(".download").attr("back_var", this_download_backup_var);
		$(obj)[0].click();
	}
}

function getFilesArrayFromIwpPart(backup_file)
{
	var backup_files_array = {};
	if(backup_file.indexOf('_iwp_part') >= 0)
	{
		var orgName = backup_file.substr(0, backup_file.indexOf('_iwp_part_'));
		var totalParts = backup_file.substr((backup_file.indexOf('_iwp_part_') + 10));
		var totalParts = totalParts.substr(0, totalParts.length - 4);
		for(k=0; k<=totalParts; k++)
		{
			if(k == 0)
			{
				backup_files_array[k] = orgName+'.zip';
			}
			else
			{
				backup_files_array[k] = orgName+'_iwp_part_'+k+'.zip';
			}
		}
		return backup_files_array;
	}
	else
	{
		backup_files_array[0] = $backup_file;
		return $backup_file;
	}
}

function getIWPTitle(obj){
	var title = 'IWP';
	var title2 = '';
	if(obj.find('a').length){
		if(obj.find('a').find('span').length){
			title += ' - '+obj.find('a').find('span:first').html();
			title2 = obj.find('a').find('span:first').html();
		}else{
			title += ' - '+obj.find('a').html();
			title2 = obj.find('a').html();
		}
	}
	title = title.replace('&amp;', '&');
	document.title = title;
	$('.page_section_title').html(title2);
}


function processSettingsUpdate(data){
	if(typeof data != 'undefined' && typeof data.data != 'undefined'){
		var mainData = data.data;
		if(typeof mainData.googleServicesSaveAPIKeys != 'undefined'){
			mainData = mainData.googleServicesSaveAPIKeys;
			$("#googleSaveSettingsBtn").removeClass('disabled');
			$(".settings_cont .btn_loadingDiv").remove();
			if(mainData != false){
				$("#googleSaveSuccess").show();
				setTimeout(function () {	$("#googleSaveSuccess").hide();},1000);
				$("#settings_btn").removeClass('active');
				settingsData['data']['getSettingsAll']['settings']['google']['clientID'] = mainData.clientID;
				settingsData['data']['getSettingsAll']['settings']['google']['clientSecretKey'] = mainData.clientSecretKey;
			}
		}else{
			$("#saveSettingsBtn").removeClass('disabled');
			$(".settings_cont .btn_loadingDiv").remove();
			if(typeof mainData.updateAccountSettings != 'undefined'){
				if(mainData.updateAccountSettings.status=='error' && mainData.updateAccountSettings.error=='invalid_password')
				{
					var closestVar=$("#currentPassword").closest(".valid_cont");
					$(".valid_error",closestVar).show();
					$(".valid_error",closestVar).text('You seem to have got the current password wrong. Please check it.');
					$("#currentPassword").addClass("error");
				}
				else
				{
					$("#saveSuccess").show();
					setTimeout(function () {	 $("#saveSuccess").hide();},1000);
					$("#settings_btn").removeClass('active');
					settingsData['data']['getSettingsAll']['accountSettings'] = mainData.getSettingsAll.accountSettings;
					settingsData['data']['getSettingsAll']['settings']['notifications'] = mainData.getSettingsAll.settings.notifications;
				}

			}else if((typeof mainData.updateSettings != 'undefined' && mainData.updateSettings == true ) || (typeof mainData.updateSecuritySettings != 'undefined' && mainData.updateSecuritySettings == true ) ){
				$("#saveSettingsBtn").removeClass('disabled');
				$(".settings_cont").find(".btn_loadingDiv").remove();
				$("#saveSuccess").show();
				setTimeout(function () {	$("#saveSuccess").hide();},1000);
				$("#settings_btn").removeClass('active');
				settingsData['data']['getSettingsAll']['settings']['general'] = mainData.getSettingsAll.settings.general;
				if(typeof mainData.getSettingsAll.settings.emailSettings != 'undefined'){
					settingsData['data']['getSettingsAll']['settings']['emailSettings'] = mainData.getSettingsAll.settings.emailSettings;
				}
			}else if(typeof mainData.updateSecuritySettings != 'undefined' && mainData.updateSecuritySettings == true){
				$("#saveSuccess").show();
				setTimeout(function () { $("#saveSuccess").hide();},1000);
				settingsData['data']['getSettingsAll']['settings']['general']['httpAuth'] = mainData.getSettingsAll.settings.general.httpAuth;
				$("#settings_btn").removeClass('active');
				if($("#enableHTTPS").hasClass('checked')){
                                    window.location.reload();
                                }
			}else if(typeof mainData.saveAppUpdateSettings != 'undefined' && mainData.saveAppUpdateSettings != false){
				$("#saveSuccess").show();
				setTimeout(function () {	 $("#saveSuccess").hide();},1000);
				$("#settings_btn").removeClass('active');
				isDirectFS = mainData.saveAppUpdateSettings.isDirectFS;
				settingsData['data']['getSettingsAll']['settings']['FTP'] = mainData.saveAppUpdateSettings.FTPValues;
			}
		}
	}
}

function openSettingsPage(cat){
        if(currentUserAccessLevel != 'admin') {
            cat = 'Account';
        }
	document.title = 'InfiniteWP - Settings';
	$('.page_section_title').html('Settings');
	$("#header_nav .first-level").removeClass('active_color');
	var content = getSettingsContent(cat);
	$("#pageContent").html(content).show();
	$('.settings_nav li:contains("'+cat+'")').addClass('active');
	$('.settings_nav li:contains("App Update")').removeClass('active');
	
	loadSettingsPage(settingsData,cat);
	closeDialogs(2);
}

function processUpdateNotifCount(data){
	$(".notif_count").hide();
	/* if(typeof data != 'undefined' && typeof data['notif_count'] != 'undefined'){
		if(data[notif_count] == 0){
			$(".notif_count").remove();
		}
		else{
			$(".notif_count").text(data[notif_count]);
		}
	} */
}

function updateNotificationContent(){
	var tempArray = {};
	tempArray['requiredData'] = {};
	tempArray['requiredData']['updateNotificationContent'] = 1;				//for clearing offer notification
	doCall(ajaxCallPath,tempArray,"processUpdateNotificationContent","json","none");
}

function processUpdateNotificationContent(data){
	$(".weekly_deal_cont").remove();
	if(typeof data != 'undefined' && typeof data['data'] != 'undefined'){
		data = data['data'];
		if(typeof data != 'undefined' && typeof data['updateNotificationContent'] != 'undefined'){
			data = data['updateNotificationContent'];
			if(typeof data != 'undefined' && typeof data['center_html'] != 'undefined' && data['center_html'] != ''){
				$(".notif_data_list").html(data['center_html']);
				$(".notif_btn").show();
			}
			if(typeof data != 'undefined' && typeof data['offer_hmtl'] != 'undefined' && data['offer_hmtl'] != ''){
				if(data['offer_hmtl'] == ''){
					$(".weekly_deal_cont").remove();
				}
				else{
					$(".weekly_deal_cont").remove();
					$(".notif_data_list").append('<div class="weekly_deal_cont cf">'+data['offer_hmtl']+'</div>');
					//$(".weekly_deal_cont").html(data['offer_hmtl']);
				}
				$(".notif_btn").show();
			}
			if(typeof data != 'undefined' && typeof data['notif_count'] != 'undefined'){
				$(".notif_count").html(data['notif_count']);
				if(data['notif_count'] != 0){
					$(".notif_count").show();
				}
				else{
					$(".notif_count").hide();
				}
			}
		}
	}

	if (typeof showCustomLanUpdateNotes != 'undefined' && showCustomLanUpdateNotes == 1) {
	var html = '<div id="showCustomLanUpdateNotes" style="border-left-width: 2px; border-left-style: solid; border-left-color: #AAAAAA; padding: 10px; background-color: #e7e9eb;margin-top:20px; line-height:1.5">For client reporting to work effectively and avoid language mismatch, please update the custom language file. <a id="confirmCustomLanUpdateNotes" style="float:right">Dismiss</a></div>';
			$("#panelNotifyHtml").prepend(html);
  	$("#confirmCustomLanUpdateNotes").qtip({content: { text: 'Note: Please dismiss the notification to </br> confirm your update.' }, position: { my: 'top center', at: 'bottom center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 9, height:4 } } });

	}
}

function showTweetDialog(type){
	var updateAllTweetMessages = [ "Update all plugins/themes across multiple %23wordpress sites in a single click with %40infinitewp & save a lot of time infinitewp.com", "Update your plugins/themes on multiple %23wordpress in a single click and save time using %40infinitewp. And its Free%21 infinitewp.com", "I updated all my plugins/themes on multiple %23wordpress sites in a single click. Thanks to %40infinitewp "+IWPSiteURL+" Huge time saver", "Managing updates, installing new plugins across all %23wordpress sites is now a click of a button with %40infinitewp infinitewp.com" ];
	
	var thirdSiteTweetMessages = [ "I use %40infinitewp to manage and update multiple %23wordpress sites. It saves me a lot of time and it\’s Awesome%21%21 infinitewp.com", "Managing multiple %23wordpress sites is a breeze with %40infinitewp. And its Free! infinitewp.com", "I manage and update all my %23wordpress sites using %40infinitewp. It’s simple, fast and free. infinitewp.com" ];
	
	var randomIndex = Math.floor((Math.random() * 3) + 0);
	var tweetMessage = '';
	var success_msg = '';
	if(type == 'update_all'){
		tweetMessage = updateAllTweetMessages[randomIndex];  		//%40 = @(special character, i ve used %40 to support twitter intent)
		success_msg = 'You have updated everything successfully.';
		updateAllTweetStatus = 'done';
	}
	else if(type = 'three_sites'){
		if(randomIndex > 2){ 
			randomIndex -= 1; 
		} 
		tweetMessage = thirdSiteTweetMessages[randomIndex];
		success_msg = 'You have added your wordpress site successfully.';
		threeSitesTweetStatus = 'done';
	}   
	
	var tweetIntentUrl = 'https://twitter.com/intent/tweet?text='+tweetMessage;
	//tweetIntentUrl = encodeURIComponent(tweetIntentUrl);
	var tweetHtml = '';
	tweetHtml = '<div class="dialog_cont" style="width:600px;"> <div class="th rep_sprite"> <div class="title droid700">HI-FIVE</div></div><div style="font-size: 14px; text-align:center; line-height:22px; padding: 10px 0 10px;margin: 40px 30px 20px;background-color: #e0f5de;border-radius: 5px;">'+success_msg+'</div><div style="font-size: 16px; text-align:center; line-height:22px; padding: 20px 0 10px;"> Do you find InfiniteWP useful?<br>   Let your friends know. They will thank you later.</div> <div style="border: 1px solid #ebebeb;border-radius: 5px;text-align: center;margin: 10px 60px 30px;">   <div style="font-weight:600; padding: 30px; font-size: 15px; line-height: 22px;">'+replaceSpecChars(tweetMessage)+'</div>   <div style="background-color:#f9f9f9 ; padding: 15px 0;"><a style="background-color:#53a8e9 ; color:#fff; border:1px solid #3b88c3 ; padding: 10px 20px;border-radius: 5px;display: block;width: 80px;margin: auto;" type="'+type+'" class="tweet_this" tweet_url="'+tweetIntentUrl+'">Tweet</a></div> </div> <div style="text-align:center; padding: 14px 0px 16px 0px; "><a class="twitter_dismiss" type="'+type+'" style="font-size: 11px; color: #555;">No, Thanks</a></div></div>';
	
	$("#modalDiv").dialog("destroy");
	$("#modalDiv").html(tweetHtml).dialog({ width: "auto",modal: true,position: "center",resizable: false,open: function (e, t) {bottomToolBarHide()},close: function (e, t) {bottomToolBarShow()} }); 
}

function replaceSpecChars(thisText){
	var newText = thisText.split('%21').join('!');
	newText = newText.split('%23').join('#');
	return newText.split('%40').join('@');
}

function processIwpTweetStatus(data){
	
}

function processTestSendMail(data){
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['testSendMail']=1;
	doCall(ajaxCallPath,tempArray,"processAfterTestSendMail","json","none");
}

function processAfterTestSendMail(data){
	$(".btn_loadingDiv").remove();
	$(".test_send_mail_smtp").removeClass("disabled");
}

function validateEmailSettingsAndGetValue(){
	var fromEmail = $(".email_settings #fromEmail").val();
	var smtpAuth = 0;
	var smtpEncryption = '';
	var useSmtp = 0;
	if(fromEmail){
		if(!echeck(fromEmail)){
				$(".email_settings #fromEmail").addClass('error');
				$("#saveSettingsBtn").removeClass('disabled');
				$(".btn_loadingDiv").remove();
				return false;
			}
	}
	
	var checkForm ;
	var emailSettingsArray = {};
	emailSettingsArray['fromEmail'] = fromEmail;
	emailSettingsArray['fromName'] = $(".email_settings #fromName").val();
	emailSettingsArray['smtpSettings'] = {};
	
	var valArray = {};	
	/* valArray['fromEmail'] = fromEmail;
	valArray['fromName'] = $(".email_settings #fromName").val(); */
	if($(".email_settings #useSmtp").hasClass('active')){
		checkForm = validateForm("smtpProcess");
		if(checkForm){
			valArray['smtpHost'] = $(".email_settings #smtpHost").val();
			valArray['smtpPort'] = $(".email_settings #smtpPort").val();
			valArray['smtpAuthUsername'] = $(".email_settings #smtpAuthUsername").val();
			valArray['smtpAuthPassword'] = $(".email_settings #smtpAuthPassword").val();
			
			if($(".email_settings #noEncryption").hasClass('active')){ smtpEncryption = ""; }
			if($(".email_settings #sslEncryption").hasClass('active')){ smtpEncryption = "ssl"; }
			if($(".email_settings #tlsEncryption").hasClass('active')){ smtpEncryption = "tls"; }
			valArray['smtpEncryption'] = smtpEncryption;
			
			if($(".email_settings #yesSmtpAuth").hasClass('active')){ smtpAuth = 1; }
			valArray['smtpAuth'] = smtpAuth;
			emailSettingsArray['smtpSettings'] = valArray;
			
		}
		else{
			$("#saveSettingsBtn").removeClass('disabled');
			$(".btn_loadingDiv").remove();
			return false;
		}
	}	
	if($(".email_settings #useSmtp").hasClass('active')){ useSmtp = 1; }
	valArray['useSmtp'] = useSmtp;
	emailSettingsArray['smtpSettings'] = valArray;
	
	return emailSettingsArray;
}

function getAppendedForEach(subHtml){
	var totalHtml = '';
	$.each(subHtml, function(k, v){
		totalHtml += v;
	});
	return totalHtml;
}

function iwpIsEmpty(obj){
	if(obj === undefined){
		return true;
	}
	else if(obj === null){
		return true;
	}
	else if(obj == ''){
		return true;
	}
	else if(obj == 'undefined'){
		return true;
	}
}

//install clone common files

var fileTreePath = 'lib/JqueryfileTree/connectors/jqueryFileTree.php';

function singleSiteSelector(){
	var sContent ='';
	if(sitesList!=null && sitesList!=undefined &&  getPropertyCount(sitesList)>0)
	 {
	$.each(sitesList, function(key,value) {
	
	sContent=sContent+'<div class="single_website_cont searchable"  id="s'+value.siteID+'" sid="'+value.siteID+'" onclick=""><a title="'+value.name+'">'+value.name+'</a></div>';
		
		});
		 sContent= '<div class="single_site_selector shadow_stroke_box siteSearch"><div class="th rep_sprite"><input name="" placeholder="type to filter" type="text" class="input_type_filter search_site" style="color: rgb(170, 170, 170); "></div><div class="single_website_items_cont"><div class="content">'+sContent+'<div class="no_match hiddenCont" style="display:none">Bummer, there are no websites that match.<br />Try typing fewer characters.</div><div class="clear-both"></div></div><div class="pane"></div> <div class="clear-both"></div> <div class="clear-both"></div></div></div>';
	 }
	 else
	 {
	 sContent='<div class="single_website_items_cont"><div class="no_match hiddenCont">No websites added yet.</div> <div class="clear-both"></div> <div class="clear-both"></div></div>';
	 }
	
	 return sContent;
}

function siteSelectorNanoSingle(){
	if ( $.browser.msie && $.browser.version=='8.0') {
		$(".single_website_items_cont .single_website_cont:nth-child(3n+3)").css({"width":"235px", "border-right":"0"});
	}
	$(".single_website_items_cont.nano").nanoScroller({stop: true});
	$(".single_website_items_cont").css('height',$(".single_website_items_cont").height()).addClass('nano');
	$(".single_website_items_cont.nano").nanoScroller();	
}

function loadFileTreeCommon(currentForm , e){
	e.prepend('<div class="loadingFileTree">loading</div>');
	var ftp_details = {};
	if ($('.c_radio.active').attr('id') === 'stagingDomainServer') {
		var siteID = $('.c_radio.active').attr('sid');
		ftp_details['hostName'] = site[siteID].ftpDetails.hostName
		ftp_details['hostPort'] = site[siteID].ftpDetails.hostPort
		ftp_details['hostUserName'] = site[siteID].ftpDetails.hostUserName
		ftp_details['hostPassword'] = site[siteID].ftpDetails.hostPassword
		ftp_details['useSftp'] = site[siteID].ftpDetails.use_sftp;
		ftp_details['root'] = $("#remoteFolder").val();
	}  else if ($('.c_radio.active').attr('id') === 'stagingDefaultServer' ) {
		var mainStagingFTPDetails = settingsData.data.getSettingsAll.settings.mainStagingFtpDetails
		ftp_details['hostName'] = mainStagingFTPDetails.hostName
		ftp_details['hostPort'] = mainStagingFTPDetails.hostPort
		ftp_details['hostUserName'] = mainStagingFTPDetails.hostUserName
		ftp_details['hostPassword'] = mainStagingFTPDetails.hostPassword
		ftp_details['useSftp'] = mainStagingFTPDetails.use_sftp;
		ftp_details['root'] = $("#remoteFolder").val();

	} else {
		if($(currentForm).find('#use_sftp').hasClass("active")){ var use_sftp = 1; } else {	var use_sftp = ''; }
		ftp_details['hostName'] = $(currentForm).find("#hostName").val();
		ftp_details['hostPort'] = $(currentForm).find("#hostPort").val();
		ftp_details['hostUserName'] = $(currentForm).find("#hostUserName").val();
		ftp_details['hostPassword'] = $(currentForm).find("#hostPassword").val();
		ftp_details['useSftp'] = use_sftp;
		ftp_details['root'] = $(currentForm).find("#remoteFolder").val();
		
	}
	if(!ftp_details['root']){
		ftp_details['root'] = '';
	}
	else
	{
		var lastChar = ftp_details['root'].substr(-1); 
		if (lastChar != '/') {
			ftp_details['root'] = ftp_details['root'] + '/';// Append a slash to it.
		}
	}
	var fileTreePath = 'lib/JqueryfileTree/connectors/jqueryFileTree.php';
	$(e).fileTree({e_object: e,root: ftp_details['root'], script: fileTreePath, ftp_details: ftp_details }, function() { 
		
	});
}

function fileTreeValidate(parent){
	var returnVal = true;
	$(".fileTreeVal", parent).each(function(){
		if($(this).val() == "")
		{
			$(this).addClass("error");
			returnVal = false;
		}
	});
	return returnVal;
}

function afterTestConnectionIC(data){
	var testConnectionFailed = 0;
	$("#cloneTestConnection").removeClass('disabled');
	$("#stage_this_site").removeClass('disabled');
	if(typeof data == 'undefined' && typeof data.data == 'undefined'){
		$("#cloneTestConnection").removeClass("testing");
		$("#totalBtnAction").removeClass("disabled");
	}
	if(typeof data.data.getICTestConnectionResult != 'undefined'){
		if(typeof data.data.getICTestConnectionResult.error != 'undefined' && data.data.getICTestConnectionResult.error != ''){
			$("#cloneTestConnection").removeClass("testing").addClass('error');
			$("#clonePanel .inner_cont").append('<div class="conn_test_error_cont profileStatusDiv"><div class="e_close"></div>'+data.data.getICTestConnectionResult.error+'<div class="conn_test_error_cont_arrow"></div></div>');
		}
		else if(typeof data.data.getICTestConnectionResult.success != 'undefined' && data.data.getICTestConnectionResult.success != ''){
			$("#cloneTestConnection").removeClass("testing").addClass("success");
		} else {
			testConnectionFailed = 1;
		}
	}
	else if(typeof data.data.stagingGetICTestConnectionResult != 'undefined'){
		if(typeof data.data.stagingGetICTestConnectionResult.error != 'undefined' && data.data.stagingGetICTestConnectionResult.error != ''){
			$("#cloneTestConnection").removeClass("testing").addClass('error');
			$(".cloneTestConnectionTh").append('<div class="conn_test_error_cont profileStatusDiv" style="margin: 30px -14px;"><div class="e_close"></div>'+data.data.stagingGetICTestConnectionResult.error+'<div class="conn_test_error_cont_arrow"></div></div>');
		}
		else if(typeof data.data.stagingGetICTestConnectionResult.success != 'undefined' && data.data.stagingGetICTestConnectionResult.success != ''){
			$("#cloneTestConnection").removeClass("testing");
			$('#cloneTestConnection').addClass('successftp');
		} else {
			testConnectionFailed = 1;
		}
	}
	else{
		testConnectionFailed = 1;
	}
	if (testConnectionFailed == 1) {
		$("#cloneTestConnection").removeClass("testing").addClass('error');
		$("#clonePanel .inner_cont").append('<div class="conn_test_error_cont profileStatusDiv"><div class="e_close"></div>Could not connect to server. Please try again<div class="conn_test_error_cont_arrow"></div></div>');
	}
	$("#totalBtnAction").removeClass("disabled");
}

function processFillDetails_IC_Common(data){
	$("#fillDetails").removeClass('disabled');
	$("#clonePanel .btn_loadingDiv").remove();
	
	if(typeof data != 'undefined'){
		if(typeof data.error != 'undefined' && data.error != ''){
			$(".cpanelAutofill .errorMsg").html(data.error).show();
		}
		else if(typeof data.data.autoFillInstallCloneCommonCpanel != 'undefined'){
			var fillData = data.data.autoFillInstallCloneCommonCpanel;
		}
		if(typeof fillData != 'undefined' && !iwpIsEmpty(fillData)){
			$("#hostName").val(fillData.cpHost);
			$("#hostUserName").val(fillData.cpUser);
			$("#hostPassword").val(fillData.cpPass);
			$("#dbName").val(fillData.dbName);
			$("#dbPassword").val(fillData.dbPass);
			$("#dbUser").val(fillData.dbUser);
			$(".cancel").click();
		}
	}
}

function formArrayTestCloneConnection(data){
	formArrayVar[data.actionResult.actionID]={};
	formArrayVar[data.actionResult.actionID]['function']="testCloneConnection";
}

function populateProfileDropDown_IC_Common(data){
	if(typeof data != 'undefined' && typeof data.data != 'undefined' && typeof data.data.installCloneGetProfile != 'undefined'){
	var populateValues = data.data.installCloneGetProfile.accountInfo;
		var parClass = '#installClone';
	}
	else if(typeof data.data.stagingGetStagingFtpDetails != 'undefined'){
		var populateValues = data.data.stagingGetStagingFtpDetails;
		var parClass = '.staging_modal #stagingFtpForm';
	}
	else if(typeof data.data.stagingGetMainStagingFtpDetails != 'undefined'){
		var populateValues = data.data.stagingGetMainStagingFtpDetails;
		
		if($('#stagingDefaultServer').hasClass('active') === true){
			parClass = '.staging_modal #stagingFtpForm';
			fillUpdateInStagingSwitch = 0;
		} else if($("#stagingTab").is(":visible")) {
			var parClass = '.settingsItem #stagingFtpForm';
			var fillUpdateInStagingSwitch = 1;
		}
	}
	else if(typeof data.data.stagingGetSiteFtpDetails != 'undefined'){
		var populateValues = data.data.stagingGetSiteFtpDetails;
		var parClass = '.ftpSettingsItem #stagingFtpForm';
		
		if($("#stagingDomainServer").hasClass("active")){
			parClass = '.staging_modal #stagingFtpForm';
		}
	}
	
	$('#adminEmail', parClass).val(populateValues.adminEmail);
	if (populateValues.dbName) {
		$('#dbHost', parClass).val(populateValues.dbHost);
	} else {
		$('#dbHost', parClass).val('localhost');
	}
	$('#dbName', parClass).val(populateValues.dbName);
	$('#dbPassword', parClass).val(populateValues.dbPassword);
	$('#dbPrefix', parClass).val(populateValues.dbPrefix);
	$('#dbUser', parClass).val(populateValues.dbUser);
	$('#hostName', parClass).val(populateValues.hostName);
	if (populateValues.hostPort) {
		$('#hostPort', parClass).val(populateValues.hostPort);
	} else{
		$('#hostPort', parClass).val('21');
	}
	$('#hostPassword', parClass).val(populateValues.hostPassword);
	$('#hostUserName', parClass).val(populateValues.hostUserName);
	$('#newSiteURL', parClass).val(populateValues.newSiteURL).css("color","#676C70");
	$('#newUserName', parClass).val(populateValues.newUserName);
	$('#newUserPassword', parClass).val(populateValues.newUserPassword);
	$('#remoteFolder', parClass).val(populateValues.remoteFolder);
	if(populateValues.hostPassive == "1" || !populateValues.hostPassive){
		$('#hostPassive', parClass).addClass('active');
	} else if(populateValues.hostPassive && populateValues.hostPassive != '1'){
		$('#hostPassive', parClass).removeClass('active');
	}
	
	$(".c_radio.FTPConnectionType:not(.site_toggle)").removeClass('active');
	if(populateValues.use_ftp == "1"){
		$('#use_ftp', parClass).addClass('active');
	} else if(populateValues.hostSSL == "1"){
		$('#hostSSL', parClass).addClass('active');
	} else if(populateValues.use_sftp == "1"){
		$('#use_sftp', parClass).addClass('active');
	} else {
		$('#use_ftp', parClass).addClass('active');
	}
	if(typeof stagingUpdateInStagingSetting != 'undefined' && stagingUpdateInStagingSetting == 1){
		$("#update_in_staging_switch").addClass("active");
	}
}

function doCloneTestConnection(funcParams){
	if(iwpIsEmpty(funcParams)){
		return false;
	}
	var obj = funcParams['divObj'];
	var type = funcParams['type'];
	
	$("#selectDestination .inner_cont .conn_test_error_cont").remove();
	if($(obj).hasClass('testing')){
		return false;
	}
	$(obj).removeClass('error success');
	$(obj).addClass('testing');
	
	if($(obj).hasClass("staging_settings") || $(obj).parent().hasClass("stagingTestConnection") ){
		var checkForm=validateForm("stagingFtpForm");
	}
	else{
		var checkForm=validateForm("clonePanel");
	}
	if(iwpIsEmpty(checkForm)){
		$(obj).removeClass("testing");
		$("#totalBtnAction").removeClass("disabled");
	}
	var tempArray={};
	tempArray['action']='installCloneCommonNewSite';
	tempArray['args']={};
	tempArray['args']['params']={};
	if(checkForm.sourceID!==undefined && checkForm.sourceID!=''){
		tempArray['args']['siteIDs']={};
		tempArray['args']['siteIDs'][0]=checkForm.sourceID;
		checkForm.sourceID={};
		tempArray['action']="installCloneExistingSite";
	}
	tempArray['args']['params']=checkForm;
	//Serialize the data to base64 encode 
	if(checkForm.newSiteURL!=undefined){
		tempArray['args']['params']['newSiteURL_b64encoded'] = $.base64('btoa',checkForm.newSiteURL,true); 
		delete tempArray['args']['params']['newSiteURL'];
	}
	if(checkForm.hostName!=undefined){
		tempArray['args']['params']['hostName_b64encoded'] = $.base64('btoa',checkForm.hostName,true); 
		delete tempArray['args']['params']['hostName'];
	}
	
	if(checkForm.sourceSiteID!==undefined && checkForm.sourceSiteID!=''){
	tempArray['args']['params']['sourceSiteID']=checkForm.sourceSiteID;
	}
	if($('#hostPassive').hasClass("active")){
		tempArray['args']['params']['hostPassive']=1;
	}
	if($('#hostSSL').hasClass("active")){
		tempArray['args']['params']['hostSSL']=1;
	}
	if($('#use_ftp').hasClass("active")){
		tempArray['args']['params']['use_ftp']=1;
	}
	if($('#use_sftp').hasClass("active")){
		tempArray['args']['params']['use_sftp']=1;
	}
	tempArray['args']['params']['backupURL']=false;
	tempArray['args']['params']['isTestConnection']=1;

	var requireDataArray = {};
	if(type == 'installClone'){
		requireDataArray['getICTestConnectionResult']=1;
	}
	else{
		requireDataArray['stagingGetICTestConnectionResult']=1;
		tempArray['args']['params']['isStaging'] = 1;
	}
	tempArray['requiredData']= requireDataArray;
	if (tempArray.args.params != false) {
		doCall(ajaxCallPath,tempArray,"formArrayTestCloneConnection",'json',"none");
		$(obj).addClass('disabled');
		$("#stage_this_site").addClass('disabled');
		$("#totalBtnAction").addClass('disabled');
	} else {
		$(".test_conn").removeClass('disabled');
		$("#stage_this_site").removeClass('disabled');
	}

}

function fillCpanelDetailsInit(funcParams){
	var obj = funcParams['divObj'];
	var reqFunc = funcParams['reqFunc'];
	if(iwpIsEmpty(obj) || iwpIsEmpty(reqFunc)){
		return false;
	}
	
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData'][reqFunc]={};
	tempArray['requiredData'][reqFunc]['cpUser']=$("#cpanelUserName_IC").val();
	tempArray['requiredData'][reqFunc]['cpPass']=$("#cpanelPassword_IC").val();
	tempArray['requiredData'][reqFunc]['cpHost_b64encoded']=$.base64('btoa',$("#cpanelURL_IC").val(),true);
	
	var is_all_filled = true;
	$(".af_validate").each(function(){
		if($(this).val() == ""){	
			is_all_filled = false;
			$(this).addClass("error");
		}
	});
	
	if(is_all_filled === true){
		$(obj).addClass('disabled');
		$(obj).prepend('<div class="btn_loadingDiv left"></div>');
		$(".cpanelAutofill .errorMsg").hide();
		doCall(ajaxCallPath,tempArray,'processFillDetails_IC_Common','json',"none");
	}
}

function processAppUpdateSettings (data){
	var maindata = data.data.appDirPermission;
	$(".uploads_permission_success").remove();
	$(".uploads_permission_error").remove();
	$('.ftpconfig_e').hide();
	$('.ftpconfig').hide();
	$('.checking').remove();
	isConfigWritable = data.data.isConfigWritable;
	
	if(data.data.getConfigFTP != 0){
		if(!isConfigWritable){
				$('.ftpconfig_e').show();
		}else{
				$('.ftpconfig').show();
		}
		$('.settings_main_content').find('.FTP_form_con').css({'opacity':'0.5'});
		$('.settings_main_content').find('.FTP_form').addClass('disabled');
		$('.settings_main_content').find('#FTPHost').attr('disabled',true);
		$('.settings_main_content').find('#FTPPort').attr('disabled',true);
		$('.settings_main_content').find('#FTPBase').attr('disabled',true);
		$('.settings_main_content').find('#FTPUser').attr('disabled',true);
		$('.settings_main_content').find('#FTPPass').attr('disabled',true);
		settingsData['data']['getSettingsAll']['settings']['FTP'] = data.data.getConfigFTP;
	}else{
	settingsData['data']['getSettingsAll']['settings']['FTP']['config'] = 0;
	}
	if (typeof maindata.updates != 'undefined' && maindata.updates) {
	$(".updates_folder").append('<div class="uploads_permission_success" style ="right:6px;" >Temp directory is writable.</div>');
	}
	else{
		$(".updates_folder").append('<div class="uploads_permission_error" style ="right:6px;">[IWP Admin Panel]/updates is not writable. Please set 777 or any writable permission by php to [IWP Admin Panel]/updates.<br></div>');
	}
	if (typeof maindata.uploads != 'undefined' && maindata.uploads) {
	$(".uploads_folder").append('<div class="uploads_permission_success" style ="right:6px;" >[IWP Admin Panel]/uploads is writable.</div>');
	}
	else{
		$(".uploads_folder").append('<div class="uploads_permission_error" style ="right:6px;">[IWP Admin Panel]/uploads is not writable. Please set 777 or any writable permission by php to [IWP Admin Panel]/uploads.<br></div>');
	}
}

function zeroClipboardTrigger(){
	var hasFlash = function() {
		return (typeof navigator.plugins == "undefined" || navigator.plugins.length == 0) ? !!(new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) : navigator.plugins["Shockwave Flash"];
	};
	if(typeof hasFlash() != "undefined"){
		var client = new ZeroClipboard( $("#copyToClipboard") );
		client.on( "ready", function( readyEvent ) {
			client.on( "copy", function(event) {
				var copyContent = $(".selectOnText").val();
				event.clipboardData.setData("text/plain", copyContent.replace(/\\/g,'/'));

			});
		  	client.on( "aftercopy", function( event ) {
				$(".copy_message").show();
				setTimeout('$(".copy_message").hide();',1000);
		  	} );
		} );
		client.on( "error", function(event) {
			ZeroClipboard.destroy();
	    } );
	} else {
		$("#copyToClipboard").remove();
	}
}

function lazyLoadOnlyVisibleImages(){
	setTimeout(function () { $(".lazyLoad").unveil(300);},1000);
}	

function isFavoritesAlreadyExist(typeItem, name){
	var isAlreadyExist = 0;
	if (typeItem === 'plugins') {
		var favData = favourites.plugins;
	}else {
		var favData = favourites.themes;
	}
	$(favData).each(function (key, eachItem) {
		if(eachItem.name === name){
			isAlreadyExist = 1;
		}
	});
	if (isAlreadyExist == 1) {
		$('#favAlreadyExist').html(typeItem.toTitleCase().slice(0,-1)+" already exist.Try something else.").show();
		return true;
	} else {
		return false;
	}
}

function isFavoritesGroupAlreadyExist(typeItem, name){
	var isAlreadyExist = 0;
	$.each(favouritesGroupData, function( key, value ) {
		if(typeItem === value.type && name === value.name){
			isAlreadyExist = 1;
		}
	});
	if (isAlreadyExist == 1) {
		$('#gname').parent('.dialog_content.inner_cont').css('margin','20px 20px 30px');
		$('#favAlreadyExist').show();
		return true;
	} else {
		return false;
	}
}
function manageInitialSetupUsageStatsLinks(refClass){
	linkPositions = ['initialSetupSecurityTab','initialSetupUsageStats'];
	$(".th_sub.rep_sprite").find("a").removeClass('rep_sprite_backup completed current');
	$(".linkDisabled[refclass="+refClass+"]").addClass('rep_sprite_backup current');
	$(".th_sub.rep_sprite").find('a').removeClass('linkDisabled');
	var currentPosition = jQuery.inArray(refClass, linkPositions); 
	for (var i = 0; i < currentPosition; i++) {
		$(".th_sub.rep_sprite").find("a[refclass="+linkPositions[i]+"]").addClass('rep_sprite_backup completed');
	}
	for (var i = (linkPositions.length)-1 ; i > currentPosition; i--) {
		$(".th_sub.rep_sprite").find("a[refclass="+linkPositions[i]+"]").addClass('linkDisabled');
	}
}
function iwpIsEmpty(obj){
	if(obj === undefined){
		return true;
	}
	else if(obj === null){
		return true;
	}
	else if(obj == ''){
		return true;
	}
	else if(obj == 'undefined'){
		return true;
	}
	else{
		return false;
	}
}

function arrUpdateCount(arr){
	if(iwpIsEmpty(arr)){
		return 0;
	}
	var count = 0;
	$.each(arr, function(k, v){
		$.each(v,function(kk,vv){
			if(iwpIsEmpty(vv.hiddenItem) || vv.hiddenItem==false){
				count++;return false;
			}
		});
	});
	return count;
}
function arrLenOneLevel(arr){
	if(iwpIsEmpty(arr)){
		return 0;
	}
	var count = 0;
	$.each(arr, function(k, v){
		count++;
	});
	return count;
}
function getRecentUpdatesStatus(){
	var tempArray={};
	tempArray['requiredData']={};
	
	tempArray['requiredData']['getSitesUpdates']=1;
	
	doCall(ajaxCallPath,tempArray,'getRecentUpdatesStatusResponse');
}

function getRecentUpdatesStatusResponse(data){
	mainJson=data.data.getSitesUpdates;
	sitesjson = mainJson.siteView;
	pluginsjson = mainJson.pluginsView.plugins;
	themesjson = mainJson.themesView.themes;
	translationsjson=mainJson.translationsView.translations;
	wpjson = mainJson.coreView.core;
	if(!iwpIsEmpty(mainJson.wpVulView)){
		wpVulnsPluginsjson =  mainJson.wpVulView.plugins;
		wpVulnsThemesJson =  mainJson.wpVulView.themes;
		wpVulnsWpJson =  mainJson.wpVulView.wp;
		if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
			mergeWpVulnsDataIntoSitesJson();
		}
	}
}
function pluginsThemesSelection(type,className,object,parentClass)
{	
	var splitClassName = className.toString().split('_');
	className=$("."+className);
	
	if(type=='active_all')
	{	
		if(!$(className).not('.ind_row_cont,.disabled').hasClass('active')){
			$(className).not('.ind_row_cont,.disabled').addClass('active');
			$($(".delete_"+splitClassName[1])).not('.ind_row_cont,.disabled').removeClass('active');
		}else{
			$(className).not('.ind_row_cont,.disabled').removeClass('active');
		}
		
	}
	else if(type=='deactivate_all'){
		if(!$(className).not('.ind_row_cont,.disabled').hasClass('active')){
			$(className).not('.ind_row_cont,.disabled').addClass('active');
		}else{
			$(className).not('.ind_row_cont,.disabled').removeClass('active');
		}
	}
	else if(type=='delete_all')
	{
		if(!$(className).not('.ind_row_cont,.disabled').hasClass('active')){
			$(className).not('.ind_row_cont,.disabled').addClass('active');
			$($(".active_"+splitClassName[1])).not('.ind_row_cont,.disabled').removeClass('active');

		}else{
			$(className).not('.ind_row_cont,.disabled').removeClass('active');
		}	
	}
}


function reloadStatsByGroup(data) {
	siteIDs = data.data.getSitesByGroupID.siteIDs;
	var tempArray={};
	tempArray['args']={};
	tempArray['args']['siteIDs']={};
	tempArray['args']['siteIDs']=siteIDs;
	tempArray['args']['params']={};
	tempArray['args']['params']['forceRefresh']=1;
	$('.fa.fa-repeat').addClass('fa-spin').css('color' ,'#49a1de');
	$("#reloadStats").addClass('disabled');
	$(".btn_reload_drop").addClass('disabled');
	$("#reloadStats").closest('div').addClass('disabled');
	$(".btn_reload_drop").closest('div').addClass('disabled');
	tempArray['action']='getStats';
		tempArray['requiredData']={};
		if(typeof isComment != 'undefined' )
		{
			tempArray['requiredData']['manageCommentsGetRecent']=1; // To load the Recents comments on Reload Data
		}
		if(typeof isGoogle != 'undefined')
		{
			if(isGoogle=='1')
			{
				tempArray['requiredData']['googleAnalyticsEditSiteOptions']=1;
			}
		}
		tempArray['requiredData']['getSitesUpdates']=1;
		tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
        tempArray['requiredData']['getRecentPluginsStatus']=1;
        tempArray['requiredData']['getRecentThemesStatus']=1;
        tempArray['requiredData']['getSites']=1;
		doCall(ajaxCallPath,tempArray,"formArrayRefreshStats","json","none");
}