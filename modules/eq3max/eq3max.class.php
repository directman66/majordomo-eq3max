<?php
/**
* eq3-max  module by dmitriy sannikov for majordomo
* sannikovdi@yandex.ru
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 10:01:31 [Jan 03, 2018])
*/
//
//
//ini_set('max_execution_time', '600');
//i_set ('display_errors', 'off');
class eq3max extends module {
/**
* milur
*
* Module class constructor
*
* @access private
*/
function eq3max() {
  $this->name="eq3max";
  $this->title="eq3-MAX!";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
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



$cmd_rec = SQLSelectOne("SELECT VALUE FROM eq3max_config where parametr='DEBUG'");
$debug=$cmd_rec['VALUE'];

$out['MSG_DEBUG']=$debug;



 $this->search_devices($out);


  $this->data=$out;
//  $this->checkSettings();




  
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
// $this->search_devices($out);

  if ($this->view_mode=='' || $this->view_mode=='info') {
$this->search_devices($out);
  }



if ($this->view_mode=='scan') {

$this->scan();
//   $this->search_devices($out);
}  

if ($this->view_mode=='delete_devices') {
$this->delete_once($this->id);
}  

  if ($this->view_mode=='edit_devices') {
   $this->edit_devices($out, $this->id);
    }











  if ($this->view_mode=='getinfo') {
//sg('test.sql',$this->id.';'.$sql);
   $this->getinfo2($this->id);
    }



//sg('test.bra', $this->view_mode);
}  


 function getinfo2($id) {

//https://www.domoticaforum.eu/viewtopic.php?f=66&t=6654

// This script shows the eq3 info in a simple output format.

// example to call this page
// http://127.0.0.1/eq3/eq3read.php

//echo "<pre>";



  $cubehost = "95.161.217.86";
// The port number of the cube. This seems to be the default port
  $cubeport = "62910";



//$cmd_rec = SQLSelectOne($sql);
//$host=$cmd_rec['IP'];
$host='95.161.217.86';
$port=62910;

if(!($sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp"))))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    die("Couldn't create socket: [$errorcode] $errormsg \n");
}

//Connect socket to remote server
if(!socket_connect($sock , $host , $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);


}
//81:8a:8b:96
//$message="81:8a:8b";
//$message=str_replace(":","",$message);
//$message=$message.$this->csum($message);
//sg('test.message', $message);
//$hexmessage=hex2bin($message);

//    socket_sendto($sock, $hexmessage, strlen($hexmessage), 0, $host, $port);

//            $receiveStr = "";
            $receiveStr = socket_read($sock, 1024, PHP_BINARY_READ);  // The 2 band data received 
                      $receiveStrHex = bin2hex ($receiveStr);   // the 2 hexadecimal data convert 16 hex



socket_close($sock);

$buf= $receiveStrHex;

echo $buf;

debmes($buf, 'eq3max');

}




 function propertySetHandle($object, $property, $value) {

$sql="SELECT eq3max_commands.* FROM eq3max_commands WHERE eq3max_commands.LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND eq3max_commands.LINKED_PROPERTY LIKE '" . DBSafe($property) . "'";
//sg('test.sql',$sql);

     $properties = SQLSelect($sql);
     $total = count($properties);
     if ($total) {

         for ($i = 0; $i < $total; $i++) {
$sql="SELECT * FROM eq3max_devices WHERE ID=".(int)$properties[$i]['DEVICE_ID'];
//sg('test.sql2',$sql);
             $device=SQLSelectOne($sql);
             $host=$device['IP'];

	     $deviceid=$device['ID'];
             $type=$device['MODEL']; //0 = white, 1 = rgb
             $command=$properties[$i]['TITLE'];
             $meth=$properties[$i]['LINKED_METHOD'];
             $state=$properties[$i]['VALUE'];             
             $magichomeObject = new magichome();
             $properties[$i]['VALUE']=$value;
             $properties[$i]['UPDATED']=date('Y-m-d H:i:s');

             SQLUpdate('eq3max_commands',$properties[$i]);	

}
 }

}


            

   
function edit_devices(&$out, $id) {
require(DIR_MODULES.$this->name . '/eq3max_devices_edit.inc.php');
}




 function search_devices(&$out) {

$mhdevices=SQLSelect("SELECT * FROM eq3max_devices");
$total = count($mhdevices);
for ($i = 0; $i < $total; $i++)
{ 
$ip=$mhdevices[$i]['IP'];
$lastping=$mhdevices[$i]['LASTPING'];
//echo time()-$lastping;
if (time()-$lastping>300) {
$online=ping(processTitle($ip));
    if ($online) 
{SQLexec("update e3max_devices set ONLINE='1', LASTPING=".time()." where IP='$ip'");} 
else 
{SQLexec("update eq3max_devices set ONLINE='0', LASTPING=".time()." where IP='$ip'");}
}}


  $mhdevices=SQLSelect("SELECT *, substr(CURRENTCOLOR,13,6) CCOLOR, substr(CURRENTCOLOR,10,2) BR, substr(CURRENTCOLOR,5,2) TURN FROM eq3max_devices");
     $total = count($mhdevices);
         for ($i = 0; $i < $total; $i++) {


}


  if ($mhdevices[0]['ID']) {

   $out['DEVICES']=$mhdevices;

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

*
* @access public
*/
 





//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
//////////////////////////////////////////////
 function scan() {

$ip = "255.255.255.255";
$port = 23272;

//$str  = 'HF-A11ASSISTHREAD';


//$str= 0x65 0x51 0x33 0x4d 0x61 0x78 0x2a 0x00 0x2a 0x2a 0x2a 0x2a 0x2a 0x2a 0x2a 0x2a 0x2a 0x2a 0x49;
$str= 0x6551334d61782a002a2a2a2a2a2a2a2a2a2a49;


		$cs = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

		if(!$cs){
echo "error socket";
		}

		socket_set_option($cs, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($cs, SOL_SOCKET, SO_BROADCAST, 1);
		socket_set_option($cs, SOL_SOCKET, SO_RCVTIMEO, array('sec'=>1, 'usec'=>128));
		socket_bind($cs, 0, 0);

socket_sendto($cs, $str, strlen($str), 0, $ip, $port);
                    //socket_recvfrom($sock, $buf,100, 0, $ip, $port);
		while(@socket_recvfrom($cs, $buf, 1024, 0, $ip, $port)){

//sg('test.buf',$buf);



			if($buf != NULL){

echo $buf;

if ($ip) {

$par=explode(",",$buf);

  $mhdevices=SQLSelect("SELECT * FROM eq3max_devices where MAC='".$par[1]."' and IP='$ip'");
 if ($mhdevices[0]['ID']) {} else 

{ 
//$id=0;
// $mhdevices=SQLSelect("SELECT max(ID) ID FROM magichome_devices");
//  if ($mhdevices[0]['ID']) {
//   $id=$mhdevices[0]['ID']+1;} 

//$id=100;

$mac=$par[1];

$par1=array();
//$par1['ID'] = $id;
//$par['TITLE'] = 'RGB LED';

$par1['TITLE'] = $par[2];
$par1['IP'] = $ip;
$par1['PORT'] = $port;
$par1['MODEL'] = $par[2];
$par1['MAC'] = $mac;
$par1['FIND'] = date('m/d/Y H:i:s',time());		
SQLInsert('eq3max_devices', $par1);		 

$sql="SELECT ID FROM eq3max_devices where MAC='$mac' and  IP='$ip'";
//sg( 'test.sql', $sql);
$idd=SQLSelectOne($sql)['ID'];
//sg( 'test.sql', $sql);
//sg( 'test.id', $id.":".$idd);


$sql="SELECT max(ID) ID FROM eq3max_commands where DEVICE_ID='$idd' ";
$cmd=SQLSelectOne($sql);
  if ( $cmd['ID']) { null;} else {


$commands=array('status','level', 'color', 'answer', 'command');
$total = count($commands);
     for ($i = 0; $i < $total; $i++) {

               $cmd_rec=array();
               $cmd_rec['DEVICE_ID']=$idd;
               $cmd_rec['TITLE']=$commands[$i];
//               $cmd_rec['MODEL']=$commands[$i];
               SQLInsert('eq3max_commands',$cmd_rec);
           
}}}}}}

		@socket_shutdown($cs, 2);
		socket_close($cs);







}

 function edit_eq3max_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/eq3max_devices_edit.inc.php');
 }






function delete_once($id) {
  SQLExec("DELETE FROM eq3max_devices WHERE id=".$id);
  SQLExec("DELETE FROM eq3max_commands WHERE DEVICE_ID='$id'");
  $this->redirect("?");
 }
















function set_favorit($id, $color) {
  SQLExec("update eq3max_devices set FAVORITCOLOR='$color' WHERE id=".$id);
  $this->redirect("?");
 }







/**
*
* @access public
*/
 
/**
* milur_devices delete record
*
* @access public
*/
 
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
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
   parent::uninstall();
  SQLExec('DROP TABLE IF EXISTS eq3max_devices');
  SQLExec('DROP TABLE IF EXISTS eq3max_config');
  SQLExec('DROP TABLE IF EXISTS eq3max_commands');
  SQLExec('delete from settings where NAME like "%eq3max%"');

 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data = '') {

 $data = <<<EOD
 eq3max_devices: ID int(10) unsigned NOT NULL auto_increment
 eq3max_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: IP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: PORT varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: MAC varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ONLINE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: LASTPING varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: FAVORITCOLOR varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: CURRENTCOLOR varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: FIND varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: MODEL varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ZONE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);


 $data = <<<EOD
 eq3max_commands: ID int(10) unsigned NOT NULL auto_increment
 eq3max_commands: TITLE varchar(100) NOT NULL DEFAULT ''
 eq3max_commands: VALUE varchar(255) NOT NULL DEFAULT ''
 eq3max_commands: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 eq3max_commands: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 eq3max_commands: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 eq3max_commands: LINKED_METHOD varchar(100) NOT NULL DEFAULT '' 
 eq3max_commands: UPDATED datetime
EOD;
  parent::dbInstall($data);




 $data = <<<EOD
 eq3max_config: parametr  varchar(300) 
 eq3max_config: value varchar(10000)  
EOD;
  parent::dbInstall($data);

  $mhdevices=SQLSelect("SELECT *  FROM eq3max_commands");
  if (!$mhdevices[0]['ID']) 
{
$par=array();		 
$par['TITLE'] = 'command';
$par['ID'] = "1";		 
SQLInsert('eq3max_commands', $par);		 

}





 }



 





}
// --------------------------------------------------------------------
	


/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDAzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/





// sudo tcpdump  ip dst 192.168.1.82 and  ip src 192.168.1.39 -w dump.cap

