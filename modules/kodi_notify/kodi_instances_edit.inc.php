<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='kodi_instances';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
   global $enable;
   $rec['ENABLE']=$enable;
   global $ip;
   $rec['IP']=$ip;
   global $port;
   $rec['PORT']=$port;
   global $login;
   $rec['LOGIN']=$login;
   global $password;
   $rec['PASSWORD']=$password;
   global $display_time;
   $rec['DISPLAY_TIME']=$display_time;
   global $level;
   $rec['LEVEL']=$level;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if (!$rec['ID'])
  {    
   $rec['TITLE']='';
   $rec['ENABLE']=1;
   $rec['PORT']=8080;
   $rec['DISPLAY_TIME']=5000;
   $rec['LEVEL']=0;
  }  
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
