<?php
/**
* Kodi Notifier 
* @package project
* @author Eraser <eraser1981@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 07:04:58 [Apr 07, 2016])
*/
//
//
class kodi_notify extends module {
/**
* kody_notify
*
* Module class constructor
*
* @access private
*/
function kodi_notify() {
  $this->name="kodi_notify";
  $this->title="Kodi Notifier";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['TITLE']=$this->config['TITLE'];
 if (!$out['TITLE']) {
  $out['TITLE']="Majordomo";
 }
 $out['IMAGE_PATH']=$this->config['IMAGE_PATH'];
 if ($this->view_mode=='update_settings') {
   global $title;
   $this->config['TITLE']=$title;
   global $image_path;
   $this->config['IMAGE_PATH']=$image_path;
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='kodi_instances' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_kodi_instances') {
   $this->search_kodi_instances($out);
  }
  if ($this->view_mode=='edit_kodi_instances') {
   $this->edit_kodi_instances($out, $this->id);
  }
  if ($this->view_mode=='delete_kodi_instances') {
   $this->delete_kodi_instances($this->id);
   $this->redirect("?");
  }
 }
 global $test;
 if ($test) {
    $this->sendNotifyAll("Test message");
    $this->redirect("?");
 }  
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* kodi_instances search
*
* @access public
*/
 function search_kodi_instances(&$out) {
  require(DIR_MODULES.$this->name.'/kodi_instances_search.inc.php');
 }
/**
* kodi_instances edit/add
*
* @access public
*/
 function edit_kodi_instances(&$out, $id) {
  require(DIR_MODULES.$this->name.'/kodi_instances_edit.inc.php');
 }
/**
* kodi_instances delete record
*
* @access public
*/
 function delete_kodi_instances($id) {
  $rec=SQLSelectOne("SELECT * FROM kodi_instances WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM kodi_instances WHERE ID='".$rec['ID']."'");
 }
 
 function processSubscription($event, $details='') {
 $this->getConfig();
  if ($event=='SAY') {
    $level=$details['level'];
    $message=$details['message'];
   
    $query = "SELECT * FROM kodi_instances WHERE enable=1 AND level<=".$level;
    $res=SQLSelect($query); 
    foreach ($res as $row) {
        $this->sendNotify($row['IP'],$row['PORT'],$row['LOGIN'],$row['PASSWORD'],$message,$row['DISPLAY_TIME']); 
    } 
  }
 }
 
 function sendNotifyAll($message)
 {
    $this->getConfig();
    $query = "SELECT * FROM kodi_instances";
    $res=SQLSelect($query); 
    foreach ($res as $row) {
        $this->sendNotify($row['IP'],$row['PORT'],$row['LOGIN'],$row['PASSWORD'],$message,$row['DISPLAY_TIME']); 
    } 
 }
 
 function sendNotifByName($name,$message)
 {
    $this->getConfig();
    $query = "SELECT * FROM kodi_instances WHERE TITLE='".$name."'";
    $res=SQLSelect($query); 
    foreach ($res as $row) {
        $this->sendNotify($row['IP'],$row['PORT'],$row['LOGIN'],$row['PASSWORD'],$message,$row['DISPLAY_TIME']); 
    } 
 }
 
     
 function sendNotify($ip,$port,$login,$password,$message,$timeout)
 {
    $title = $this->config['TITLE'];
    if(!$title)
        $title = "Majordomo";
    $image = $this->config['IMAGE_PATH'];
    
    $host = $ip.":".$port;

    if(!$timeout)
        $timeout = 3000;
     
    $url = 'http://'.$host;
 
    $out=array();
    $out["jsonrpc"] = "2.0";
    $out["method"] = "GUI.ShowNotification";
    $out["params"] = array();
    $out["params"]["title"] = $title;
    $out["params"]["message"] = $message;
    if ($image!= null)
        $out["params"]["image"] = $image;
    $out["params"]["displaytime"] = (int)$timeout;
    $out["id"] = 1;
    $json = json_encode($out);
    $qs = http_build_query(array('request' => $json));
    $req = $url."/jsonrpc?".$qs;
    //registerError('kodi_notify', $req);
    try
    {
      $contents =  getURL($req, 0, $login, $password);
      $obj = json_decode($contents);
      if ($obj->{'result'} != "OK")
        registerError('kodi_notify',$contents. 'URL='. $req);
    }
    catch (Exception $e)
    {
        registerError('kodi_notify', 'Error send query - '.$req.' == '.get_class($e) . ', ' . $e->getMessage());
    }
 } 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  subscribeToEvent($this->name, 'SAY');
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS kodi_instances');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall() {
/*
kodi_instances - 
*/
  $data = <<<EOD
 kodi_instances: ID int(10) unsigned NOT NULL auto_increment
 kodi_instances: TITLE varchar(100) NOT NULL DEFAULT ''
 kodi_instances: ENABLE int(10) NOT NULL DEFAULT '1'
 kodi_instances: IP varchar(255) NOT NULL DEFAULT ''
 kodi_instances: PORT int(10) NOT NULL DEFAULT '8080'
 kodi_instances: LOGIN varchar(255) NOT NULL DEFAULT ''
 kodi_instances: PASSWORD varchar(255) NOT NULL DEFAULT ''
 kodi_instances: DISPLAY_TIME int(10) NOT NULL DEFAULT '5000'
 kodi_instances: LEVEL int(10) NOT NULL DEFAULT '1'
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDA3LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
