<?php
/**
* Kodi Notify 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 10:04:51 [Apr 06, 2016])
*/
//
//
class kody_notify extends module {
/**
* kody_notify
*
* Module class constructor
*
* @access private
*/
function kody_notify() {
  $this->name="kody_notify";
  $this->title="Kodi Notify";
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
 $out['IP']=$this->config['IP'];
 $out['PORT']=$this->config['PORT'];
 $out['LOGIN']=$this->config['LOGIN'];
 $out['PASSWORD']=$this->config['PASSWORD'];
 $out['TIMEOUT']=$this->config['TIMEOUT'];
 if ($this->config['TIMEOUT'] == "")
     $out['TIMEOUT'] = 5000;
 if ($this->view_mode=='update_settings') {
   global $ip;
   $this->config['IP']=$ip;
   global $port;
   $this->config['PORT']=$port;
   global $login;
   $this->config['LOGIN']=$login;
   global $password;
   $this->config['PASSWORD']=$password;
   global $timeout;
   $this->config['TIMEOUT']=$timeout;
   $this->saveConfig();
   $this->redirect("?");
 }
 
 global $test;
 if ($test) {
    $this->sendNotify("Test message",9999);
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
 function processSubscription($event, $details='') {
 $this->getConfig();
  if ($event=='SAY') {
   $level=$details['level'];
   $message=$details['message'];
   $this->sendNotify($message,$level);
  }
 }
 
 function sendNotify($message,$level)
 {
    $host = $this->config['IP'].":".$this->config['PORT'];
    $login = $this->config['LOGIN'];
    $password = $this->config['PASSWORD'];
    if ($login!="")
    {
        $host=$login.":".$password."@".$host;
    }
    
    $timeout = (int)$this->config['TIMEOUT'];
    if(!$timeout)
        $timeout = 3000;
     
    $url = 'http://'.$host;
 
    $out=array();
    $out["jsonrpc"] = "2.0";
    $out["method"] = "GUI.ShowNotification";
    $out["params"] = array();
    $out["params"]["title"] = "Majordomo";
    $out["params"]["message"] = $message;
    $out["params"]["image"] = "http://127.0.0.1/img/logo_small.png";
    $out["params"]["displaytime"] = $timeout;
    $out["id"] = 1;
    $json = json_encode($out);
    $qs = http_build_query(array('request' => $json));
    $req = $url."/jsonrpc?".$qs;
    try
    {
      $contents = file_get_contents($req);
      $obj = json_decode($contents);
      if ($obj->{'result'} != "OK")
        registerError('kodi_notify', $contents);
    }
    catch (Exception $e)
    {
      registerError('kodi_notify', get_class($e) . ', ' . $e->getMessage());
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
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgQXByIDA2LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
