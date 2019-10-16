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
ini_set ('display_errors', 'off');
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

  $this->getConfig();
  $out['IPADDR']=$this->config['IPADDR'];

  if ($this->view_mode=='' || $this->view_mode=='info') {
$this->search_devices($out);
  }


  if ($this->view_mode=='update_settings') {
    global $ipaddr;
    $this->config['IPADDR']=$ipaddr;
    

    $this->saveConfig();
    $this->redirect("?");
  }




if ($this->view_mode=='scan') {

$this->scan();
//   $this->search_devices($out);
}  




if ($this->view_mode=='setpointtemp_set') {

$rfaddr=$_REQUEST['rfaddr'];
$roomid=$_REQUEST['roomid'];
//$selectmode=$_REQUEST['selectmode'];
//$setpointtemp=$_REQUEST['setpointtemp'];

global $selectmode;
global $setpointtemp;

debmes("view_mode:".$this->view_mode . "    rfaddr:".$rfaddr."    roomid:".$roomid. "   selectmode:".$selectmode."  setpointtemp:".$setpointtemp  , 'eq3max');


SQLExec("update eq3max_devices set SETPOINTTEMP='".$setpointtemp."', setmode='".$selectmode."' where RFADDRESS='".$rfaddr."'");

$this->SetTemp($rfaddr,$roomid, $selectmode,$setpointtemp);


$cmd='
include_once(DIR_MODULES . "eq3max/eq3max.class.php");
$eq3 = new eq3max();
$eq3->get(); 
debmes("задача get выполнена", "eq3max");
';
 SetTimeOut('eq3max_getvalues',$cmd, '1'); 




//   $this->search_devices($out);
}  


if ($this->view_mode=='get') {

$this->get();
//   $this->search_devices($out);
}  



if ($this->view_mode=='delete_devices') {
$this->delete_once($this->id);
}  

  if ($this->view_mode=='edit_devices') {

debmes('$this->view_mode:'.$this->view_mode, 'eq3max');
   $this->edit_devices($out, $this->id);

//   $this->redirect("?view_mode=edit_devices&id=".$this->id."&tab=edit_device");

    }






  if ($this->view_mode=='getinfo') {
//sg('test.sql',$this->id.';'.$sql);
   $this->getinfo2($this->id);
    }



//sg('test.bra', $this->view_mode);
}  
////////////////////////////////////////////
////////////////////////////////////////////
////////////////////////////////////////////


 function get() {

debmes("1111111111111111111111111111111111111111111111111111111111111111",'eq3max');
  $this->getConfig();
  $host=$this->config['IPADDR'];
  $port = "62910";
//https://www.domoticaforum.eu/viewtopic.php?f=66&t=6654

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));

$bufff="";
        if (socket_connect($socket, $host, $port)) {  //Connect


	while(@socket_recvfrom($socket, $buf, 1024, 0, $host, $port)){

//sg('test.buf',$buf);



			if($buf != NULL){


//$buff[]=$buf;
$bufff.=$buf;

} 
else {
@socket_shutdown($cs, 2);
socket_close($cs);
debmes('Закрыли сокет, обмен закончен', 'eq3max');
}
}
        $buff=explode(chr(10),$bufff);
	$total=count( $buff);
         for ($i=0;$i<$total;$i++) {
        $this->parsing($buff[$i]);
//echo $i.":".$buff[$i]."<hr>";
				    }



}
}


////////////////////////////////////////////
////////////////////////////////////////////
////////////////////////////////////////////


function parsing ($buf){

//echo     $buf ."<br><br>";
debmes($buf[0]."-------------",'eq3max');
debmes($buf,'eq3max');

if ($buf[0]=='H') {

debmes('H-ответ содержит информацию о кубе' ,'eq3max');
/*
H-ответ («Incoming Hello») 

H-ответ содержит информацию о кубе. 
Пример: 
H: IEQ0123456,00b3b4,0102,00000000,355df98a, 03,32

Полезная нагрузка может быть разделена запятой на несколько частей:
КОД: ВЫБРАТЬ ВСЕ
IEQ0123456  Serial number 
00b3b4      RF address, hexadecimal
0102        Firmware version, 1.0.2
00000000    ?
355df98a    ?
03          ?
32          ?
*/



$ar=explode(',',$buf);
$sn=$ar[0];
$rfaddr=$ar[1];
$firmware=$ar[2];


    $v=$buf;
    $arr2 = explode(',',substr($v,2,strlen($v)));
    $str = base64_decode($arr2[2]);
    $this->retarr["cube"]["hSerialNumber"] = $arr2[0];
    $this->retarr["cube"]["hRFAdress"] = $arr2[1];
    $this->retarr["cube"]["hFirmware"] = $arr2[2];
    $this->retarr["cube"]["h1?"] = $arr2[3]; //00000000
    $this->retarr["cube"]["hHTTP-ConnID"] = $arr2[4];
    $this->retarr["cube"]["hDuty cycle"] = hexdec($arr2[5]);
    $this->retarr["cube"]["hFree memory slots"] = hexdec($arr2[6]); //31
    $this->retarr["cube"]["hCube Date"] = hexdec(substr($arr2[7],4,2))."-".hexdec(substr($arr2[7],2,2))."-".hexdec(substr($arr2[7],0,2));
    $this->retarr["cube"]["hCube Time"] = hexdec(substr($arr2[8],0,2)).":".hexdec(substr($arr2[8],2,2));
    //$this->retarr["cube"]["hTimestamp"] = mktime(hexdec(substr($arr2[8],0,2)),hexdec(substr($arr2[8],2,2)),0,hexdec(substr($arr2[7],2,2)),hexdec(substr($arr2[7],4,2)),hexdec(substr($arr2[7],0,2)));
    $this->retarr["cube"]["hStateCubeTime"] = $arr2[9];
    $this->retarr["cube"]["hNtpCount"] = $arr2[10];



debmes('H:serial number:'.$sn.' rfaddr='.$rfaddr." firmware=".$firmware, 'eq3max');
debmes('H:hSerialNumber:'.$this->retarr["cube"]["hSerialNumber"], 'eq3max');
debmes('H:hFree memory slot:'.$this->retarr["cube"]["hFree memory slots"], 'eq3max');
debmes('H:hCube Date:'.$this->retarr["cube"]["hCube Date"], 'eq3max');
debmes('H:hStateCubeTime:'.$this->retarr["cube"]["hCube Time"], 'eq3max');
debmes('H:hNtpCount:'.$this->retarr["cube"]["hNtpCount"], 'eq3max');



}


if ($buf[0]=='M') {
debmes('-------------------------------' ,'eq3max');
debmes('Ответ M содержит информацию о дополнительных данных, таких как определенные комнаты, устройства и имена, которые им были даны, и как комнаты и устройства связаны друг с другом. ' ,'eq3max');
/*
Ответ M («Метаданные»)

Ответ M содержит информацию о дополнительных данных, таких как определенные комнаты, устройства и имена, которые им были даны, и как комнаты и устройства связаны друг с другом. 
Пример:
M: 00,01, VgIBAQpIb2JieWthbWVyADUIAQEANQhJRVEwMTA5MTI1DFRoZXJtb3N0YXQgMQEB
Декодировано:
КОД: ВЫБРАТЬ ВСЕ
00: 56 02 01 01 0A 48 6F 62 62 79 6B 61 6D 65 72 00 | V....Hobbykamer.
10: 35 08 01 01 00 35 08 49 45 51 30 31 30 39 31 32 | 5....5.IEQ010912
20: 35 0C 54 68 65 72 6D 6F 73 74 61 74 20 31 01 01 | 5.Thermostat 1..

Пока не все данные известны, но можно распознать следующие 2 «структуры». Номер имеет следующую структуру:

КОД: ВЫБРАТЬ ВСЕ
Description        Startpos    Length      Example Value

Room id            00          1           1
Room name length   01          1           0A
Room name          02          variable    Hobbykamer
Address(?)                     3           003508


Устройство имеет следующую структуру:
КОД: ВЫБРАТЬ ВСЕ
Description        Startpos    Length      Example Value

Device type        00          1           1
Address            01          3           003508
Serial Number      04          10          IEQ0109125
Name length        0E          1           0C
Name               0F          variable    Thermostat 1
Room id                        1           01


Некоторые из байтов до сих пор неизвестны. 
*/


$ar=explode(',',$buf);
//$sn=$ar[1];
//$rfaddr=$ar[2];
$data=$ar[2];
//echo "<pre>";
//echo $data;
//echo "</pre>";
$dataenc=base64_decode($data);


//debmes('M:data:'.$data, 'eq3max');
//debmes('M:data_deсode:'.$dataenc, 'eq3max');
   $v=$buf;
    $arr2 = explode(',',$v);
    $str = base64_decode($arr2[2]);

    $this->pos = 0;
    $this->retarr["meta"]["m?1"] = $this->dechex2str($str, 1);
    $this->retarr["meta"]["m?2"] = $this->dechex2str($str, 1);
    $this->roomcount = $this->dechex2str($str, 1);
    $this->retarr["meta"]["mRoomCount"] = $this->roomcount;
    
    for($j = 1 ; $j <= $this->roomcount ; $j++) {
      $RoomID = $this->dechex2str($str, 1);
      $this->retarr["room"][$RoomID]["mRoomID"]=$RoomID;

      $this->retarr["room"][$RoomID]["mRoomNameLength"]=$this->ord2str($str, 1);
      $this->retarr["room"][$RoomID]["mRoomName"]= $this->substr2str($str,$this->retarr["room"][$RoomID]["mRoomNameLength"]);
      $this->retarr["room"][$RoomID]["mFirstRFAdress"] = $this->strpaddechex2str($str, 3,2);

       debmes( "RoomID:".$RoomID." RoomNameLength:" .$this->retarr["room"][$RoomID]["mRoomName"]." mFirstRFAdress:".      $this->retarr["room"][$RoomID]["mFirstRFAdress"],'eq3max');




	$rec2=SQLSelectOne("SELECT * FROM eq3max_rooms where ID='".$RoomID."'");


        $rec2['TITLE']=$this->retarr["room"][$RoomID]["mRoomName"];
        $rec2['FIRSTRFADDRESS']=$this->retarr["room"][$RoomID]["mFirstRFAdress"];


//         debmes($rec2, 'eq3max');

	if ($rec2['ID']) {
	SQLUpdate('eq3max_rooms', $rec2);
	} else 
	{
        $rec2['ID']=$RoomID;
	SQLInsert('eq3max_rooms', $rec2);
	}





    }

    $this->devccount = $this->dechex2str($str, 1);
    $this->retarr["meta"]["mDevCount"] = $this->devccount;
     debmes( "mDevCount:".$this->retarr["meta"]["mDevCount"], 'eq3max');
    
    //if (($msgtype=="all") or ($msgtype=="M:") ) {
      for($j = 1 ; $j <= $this->devccount; $j++) {
        //The devicetype indicates what type of device it is:
        //0       Cube
        //1       Heating Thermostat
        //2       Heating Thermostat Plus
        //3       Wall mounted Thermostat
        //4       Shutter contact
        //5       Push Button  




        $this->retarr["devc"][$j]["mDeviceType"] = $this->dechex2str($str, 1);
        debmes( "devc".$j."mDeviceType:".$this->retarr["devc"][$j]["mDeviceType"], 'eq3max');

        $this->retarr["devc"][$j]["mRFAdress"] = $this->strpaddechex2str($str, 3,2);
        debmes( "devc".$j."mRFAdress:".$this->retarr["devc"][$j]["mRFAdress"], 'eq3max');


        $this->retarr["devc"][$j]["mSerialNumber"] = $this->substr2str($str,10);
        debmes( "devc".$j."mSerialNumber:".$this->retarr["devc"][$j]["mSerialNumber"], 'eq3max');


	$rec=SQLSelectOne("SELECT * FROM eq3max_devices where SERIALNUMBER='".$this->retarr["devc"][$j]["mSerialNumber"]."'");



        $this->retarr["devc"][$j]["mNameLength"] = $this->ord2str($str, 1);
        debmes( "devc".$j."mNameLength:".$this->retarr["devc"][$j]["mNameLength"], 'eq3max');

        $this->retarr["devc"][$j]["mDeviceName"] = $this->substr2str($str,$this->retarr["devc"][$j]["mNameLength"]);
        debmes( "devc".$j."mDeviceName:".$this->retarr["devc"][$j]["mDeviceName"], 'eq3max');
	$rec['TITLE']=$this->retarr["devc"][$j]["mDeviceName"];

        $this->retarr["devc"][$j]["mRoomID"] = $this->dechex2str($str, 1);
        debmes( "devc".$j."mRoomID:".$this->retarr["devc"][$j]["mRoomID"], 'eq3max');

        $rec['UPDATED']=date('Y-m-d H:i:s');

	if ($rec['ID']) {
	SQLUpdate('eq3max_devices', $rec);
	} else 
	{
if ($rec['RFADDRESS'])	
{
	SQLInsert('eq3max_devices', $rec);
	}}


      }
    //}
	



}


if ($buf[0]=='C') {
debmes('-------------------------------' ,'eq3max');
debmes('Ответ C содержит информацию о конфигурации устройства. ' ,'eq3max');
/*

Ответ C содержит информацию о конфигурации устройства. 
Пример: 
C: 003508,0gA1CAEBFP9JRVEwMTA5MTI1KCg9CQcoAzAM / wBESFUIRSBFIEUgRSBFIEUgRSBFIEUgRSBFIERIVQhFIEUgRSBFIEUgRSBFIEUgRSBFIEUg 
REhUbETMVRRFIEUgRSBFIEUgRSBFIEUgRSBESFRsRMxVFEUgRSBFIEUgRSBFIEUgRSBFIERIUmxEzFUURSBFIEUgRSBFIEUgRSBFIEUgREhUbETMVRRFIE 
UgRSBFIEUgRSBFIEUgRSBESFRsRMxVFEUgRSBFIEUgRSBFIEUgRSBFIA ==

Полезная нагрузка может быть разделена на символ запятой в 2 -х частей:
КОД: ВЫБРАТЬ ВСЕ
003508        RF address of the device
0gA1...IA==   Base 64 encoded configuration data

Вторая часть расшифрована в шестнадцатеричном виде:
КОД: ВЫБРАТЬ ВСЕ
00: D2 00 35 08 01 01 14 FF 49 45 51 30 31 30 39 31  |Ò.5....ÿIEQ01091
10: 32 35 28 28 3D 09 07 28 03 30 0C FF 00 44 48 55  |25((=..(.0.ÿ.DHU
20: 08 45 20 45 20 45 20 45 20 45 20 45 20 45 20 45  |.E E E E E E E E
30: 20 45 20 45 20 45 20 44 48 55 08 45 20 45 20 45  | E E E DHU.E E E
40: 20 45 20 45 20 45 20 45 20 45 20 45 20 45 20 45  | E E E E E E E E
50: 20 44 48 54 6C 44 CC 55 14 45 20 45 20 45 20 45  | DHTlDÌU.E E E E
60: 20 45 20 45 20 45 20 45 20 45 20 44 48 54 6C 44  | E E E E E DHTlD
70: CC 55 14 45 20 45 20 45 20 45 20 45 20 45 20 45  |ÌU.E E E E E E E
80: 20 45 20 45 20 44 48 52 6C 44 CC 55 14 45 20 45  | E E DHRlDÌU.E E
90: 20 45 20 45 20 45 20 45 20 45 20 45 20 45 20 44  | E E E E E E E D
A0: 48 54 6C 44 CC 55 14 45 20 45 20 45 20 45 20 45  |HTlDÌU.E E E E E
B0: 20 45 20 45 20 45 20 45 20 44 48 54 6C 44 CC 55  | E E E E DHTlDÌU
C0: 14 45 20 45 20 45 20 45 20 45 20 45 20 45 20 45  |.E E E E E E E E
D0: 20 45 20                                         | E              

Смысл всего этого:
КОД: ВЫБРАТЬ ВСЕ
Start Length  Value       Description
==================================================================
00         1  D2          Length of data: D2 = 210(decimal) = 210 bytes
01         3  003508      RF address
04         1  01          Device Type
05         3  0114FF      ?
08        10  IEQ0109125  Serial Number       
12         1  28          Comfort Temperature      
13         1  28          Eco Temperature          
14         1  3D          MaxSetPointTemperature  
15         1  09          MinSetPointTemperature  
16         1  07          Temperature Offset * 2
                          The default value is 3,5, which means the offset = 0 degrees. 
                          The offset is adjustable between -3,5 and +3,5 degrees, 
                          which results in a value in this response between 0 and 7 (decoded already)       
17         1  28          Window Open Temperature   
18         1  03          Window  Open Duration      
19         1  30          Boost Duration and Boost Valve Value
                          The 3 MSB bits gives the duration, the 5 LSB bits the Valve Value%.
                          Duration: With 3 bits, the possible values (Dec) are 0 to 7, 0 is not used. 
                          The duration in Minutes is: if Dec value = 7, then 30 minutes, else Dec value * 5 minutes
                          Valve Value: dec value 5 LSB bits * 5 gives Valve Value in %
1A         1  0C          Decalcification: Day of week and Time
                          In bits: DDDHHHHH 
                          The three most significant bits (MSB) are presenting the day, Saturday = 1, Friday = 7
                          The five least significant bits (LSB) are presenting the time (in hours)     
1B         1  FF          Maximum Valve setting; *(100/255) to get in %
1C         1  00          Valve Offset ; *(100/255) to get in %
1D         ?  44 48 ...   Weekly program (see The weekly program)

*/



$ar=explode(',',$buf);
$rfadr=$ar[0];
//$rfaddr=$ar[2];
$data=$ar[1];
//echo "<pre>";
//echo $data;
//echo "</pre>";
  $dataenc=base64_decode ($data);

    $str = base64_decode($data);


    unset($hilf);
    $this->pos = 0;
    $hilf["?1"] = $this->ord2str($str,1);

    $hilf["RFAdress"] = $this->strpaddechex2str($str, 3, 2);
    $hilf["DeviceType"] = $this->dechex2str($str,1);
    
    $hilf["?2"] = $this->dechex2str($str,3);
    $this->pos = $this->pos -3;
    
    ////$deviceconf[$hilf["RFAdress"]]["?FirmwareXX"]="";
    ////$readlen =  2; for($i = $this->pos; $i < $readlen+$this->pos ; $i++) $deviceconf[$hilf["RFAdress"]]["?FirmwareXX"] .= dechex(ord(substr($str,$i,1)))." ";  $this->pos += $readlen;
    ////$this->pos = $this->pos -2;
    
    $hilf["?RoomId"] = $this->ord2str($str,1);
    $hilf["?FirmwareVersion"] = $this->ord2str($str,1);
    $hilf["?NumModus"] = $this->ord2str($str,1);
    
    $hilf["SerialNumber"]= $this->substr2str($str,10);


debmes('C:RFAdress:'.$hilf["RFAdress"], 'eq3max');
debmes('C:RoomId:'.$hilf["?RoomId"], 'eq3max');
debmes('C:FirmwareVersion:'.$hilf["?FirmwareVersion"], 'eq3max');
debmes('C:SerialNumber:'.$hilf["SerialNumber"], 'eq3max');
debmes('C:rfadr:'.$rfadr, 'eq3max');
debmes('C:data:'.$data, 'eq3max');
debmes('C:DeviceType:'.$hilf["DeviceType"], 'eq3max');


	$rec=SQLSelectOne("SELECT * FROM eq3max_devices where RFADDRESS='".$hilf["RFAdress"]."'");
	$rec['RFADDRESS']=$hilf["RFAdress"];
	$rec['FIRMWARE']=$hilf["?FirmwareVersion"];
	$rec['ROOMID']=$hilf["?RoomId"];
	$rec['SERIALNUMBER']=$hilf["SerialNumber"];
	$rec['DEVICETYPE']=$hilf["DeviceType"];
/*
	if ($rec['ID']) {
	SQLUpdate('eq3max_devices', $rec);
	} else 
	{
	SQLInsert('eq3max_devices', $rec);
	}

*/

//debmes('C:data_ecnode:'.$dataenc, 'eq3max');

        //The devicetype indicates what type of device it is:
        //0       Cube
        //1       Heating Thermostat
        //2       Heating Thermostat Plus
        //3       Wall mounted Thermostat
        //4       Shutter contact
        //5       Push Button  


    switch($hilf["DeviceType"]){
      case "0":
	debmes('C:DeviceType:Cube', 'eq3max');
	$rec['DEVICETYPETEXT']='Cube';
      // Cube
      $this->retarr["cube"]["c?DataLength"] = $hilf["?1"];
      $this->retarr["cube"]["cRFAdress"] = $hilf["RFAdress"];
      $this->retarr["cube"]["cDeviceType"] = $hilf["DeviceType"];
      $this->retarr["cube"]["c?2"] = $hilf["?2"];
      $this->retarr["cube"]["c?RoomId"] = $hilf["?RoomId"];
      $this->retarr["cube"]["c?FirmwareVersion"] = $hilf["?FirmwareVersion"];
      $this->retarr["cube"]["c?NumModus"] = $hilf["?NumModus"];
      $this->retarr["cube"]["cSerialNumber"] = $hilf["SerialNumber"];
      $this->retarr["cube"]["cPortalEnabled"] = $this->dechex2str($str,1);
      
      $this->retarr["cube"]["c?3"]=$this->dechex2str($str,4);
      debmes("cubec?3:".$this->retarr["cube"]["c?3"], 'eq3max');

      $this->retarr["cube"]["c?4"]=$this->dechex2str($str,8);
      debmes("cubec?4:".$this->retarr["cube"]["c?4"], 'eq3max');

      $this->retarr["cube"]["c?5"]=$this->dechex2str($str,21);      
      debmes("cubec?5:".$this->retarr["cube"]["c?5"], 'eq3max');

      $this->retarr["cube"]["c?6"]=$this->dechex2str($str,4);
      debmes("cubec?6:".$this->retarr["cube"]["c?6"], 'eq3max');

      $this->retarr["cube"]["c?7"]=$this->dechex2str($str,8);
    debmes("cubec?7:".$this->retarr["cube"]["c?7"], 'eq3max');

      $this->retarr["cube"]["c?8"]=$this->dechex2str($str,21);
      debmes("cubec?8:".$this->retarr["cube"]["c?8"], 'eq3max');

      $this->retarr["cube"]["cPortalURL"]=$this->substr2str($str,36);      
      debmes("cPortalURL:".$this->retarr["cube"]["cPortalURL"], 'eq3max');


      $this->retarr["cube"]["c?9"]=$this->dechex2str($str,60);
      debmes("cubec?9:".$this->retarr["cube"]["c?9"], 'eq3max');

      $this->retarr["cube"]["c?A"]=$this->dechex2str($str,33);
      debmes("cubec?A:".$this->retarr["cube"]["c?A"], 'eq3max');

      $this->retarr["cube"]["c?B"]=$this->substr2str($str,3);
      debmes("cubec?B:".$this->retarr["cube"]["c?B"], 'eq3max');

      $this->retarr["cube"]["c?C"]=$this->dechex2str($str,9);
      debmes("cubec?C:".$this->retarr["cube"]["c?C"], 'eq3max');

      $this->retarr["cube"]["c?D"]=$this->substr2str($str,4);
      debmes("cubec?D:".$this->retarr["cube"]["c?D"], 'eq3max');

      $this->retarr["cube"]["c?E"]=$this->dechex2str($str,9);
      debmes("cubec?E:".$this->retarr["cube"]["c?E"], 'eq3max');
    break;
     
//    case "1":
//   	  break;
   	 
    case "1":
      // Thermostat
      debmes('C:DeviceType:Thermostat', 'eq3max');
	$rec['DEVICETYPETEXT']='Thermostat';
      $key = array_search($hilf["RFAdress"], array_column($this->retarr["devc"], 'mRFAdress'));
      if ($key===false) {
      	$key = count($this->retarr["devc"]) +1;
      } else {
        $key = $key + 1;
      }
        
      $this->retarr["devc"][$key]["c?DataLength"] = $hilf["?1"];

      $this->retarr["devc"][$key]["cRFAdress"] = $hilf["RFAdress"];
      debmes("devc".$key."cRFAdress:".      $this->retarr["devc"][$key]["cRFAdress"], 'eq3max');

      $this->retarr["devc"][$key]["cDeviceType"] = $hilf["DeviceType"];
      debmes("devc".$key."DeviceType:".      $this->retarr["devc"][$key]["DeviceType"], 'eq3max');

      $this->retarr["devc"][$key]["c?2"] = $hilf["?2"];
      debmes("devc".$key."c?2:".      $this->retarr["devc"][$key]["c?2"], 'eq3max');

      $this->retarr["devc"][$key]["c?RoomId"] = $hilf["?RoomId"];
      debmes("devc".$key."RoomId:".      $this->retarr["devc"][$key]["c?RoomId"], 'eq3max');

      $this->retarr["devc"][$key]["c?FirmwareVersion"] = $hilf["?FirmwareVersion"];
      debmes("devc".$key."FirmwareVersion:".      $this->retarr["devc"][$key]["c?FirmwareVersion"], 'eq3max');

      $this->retarr["devc"][$key]["c?NumModus"] = $hilf["?NumModus"];
      debmes("devc".$key."NumModus:".      $this->retarr["devc"][$key]["c?NumModus"], 'eq3max');
	$rec['NUMMODUS']=$this->retarr["devc"][$key]["c?NumModus"];

      $this->retarr["devc"][$key]["cSerialNumber"] = $hilf["SerialNumber"];
      debmes("devc".$key."SerialNumber:".      $this->retarr["devc"][$key]["SerialNumber"], 'eq3max');


      $this->retarr["devc"][$key]["cComfortTemperature"] = ($this->ord2str($str, 1) /2) ;
      debmes("devc".$key."cComfortTemperature:".      $this->retarr["devc"][$key]["cComfortTemperature"], 'eq3max');
	$rec['COMFORTTEMP']=$this->retarr["devc"][$key]["cComfortTemperature"];

      $this->retarr["devc"][$key]["cEcoTemperature"] = ($this->ord2str($str, 1) /2) ;
      debmes("devc".$key."cEcoTemperature:".      $this->retarr["devc"][$key]["cEcoTemperature"], 'eq3max');
	$rec['ECOTEMP']=$this->retarr["devc"][$key]["cEcoTemperature"];

      $this->retarr["devc"][$key]["cMaxSetPointTemperature"] = ($this->ord2str($str, 1) /2) ;
      debmes("devc".$key."cMaxSetPointTemperature:".      $this->retarr["devc"][$key]["cMaxSetPointTemperature"], 'eq3max');
	$rec['MAXSETPOINTTEMP']=$this->retarr["devc"][$key]["cMaxSetPointTemperature"];

      $this->retarr["devc"][$key]["cMinSetPointTemperature"] = ($this->ord2str($str, 1) /2) ;
      debmes("devc".$key."cMinSetPointTemperature:".      $this->retarr["devc"][$key]["cMinSetPointTemperature"], 'eq3max');
	$rec['MINSETPOINTTEMP']=$this->retarr["devc"][$key]["cMinSetPointTemperature"];


      $this->retarr["devc"][$key]["cTemperatureOffse"] = ($this->ord2str($str, 1) /2) -3.5 ;
      debmes("devc".$key."cTemperatureOffse:".      $this->retarr["devc"][$key]["cTemperatureOffse"], 'eq3max');


      $this->retarr["devc"][$key]["cWindowOpenTemperature"] = ($this->ord2str($str, 1) /2) ;
      debmes("devc".$key."cWindowOpenTemperature:".      $this->retarr["devc"][$key]["cWindowOpenTemperature"], 'eq3max');
	$rec['WINDOWOPENTEMP']=$this->retarr["devc"][$key]["cWindowOpenTemperature"];

      $this->retarr["devc"][$key]["cWindowOpenDuration"] = $this->dechex2str($str, 1);
      debmes("devc".$key."cWindowOpenDuration:".      $this->retarr["devc"][$key]["cWindowOpenDuration"], 'eq3max');
	$rec['WINDOWOPENDURATION']=$this->retarr["devc"][$key]["cWindowOpenDuration"];

     $this->retarr["devc"][$key]["cBoost"] = $this->strpaddecbin2str($str,1,8);
      debmes("devc".$key."cBoost:".      $this->retarr["devc"][$key]["cBoost"], 'eq3max');
	$rec['BOOST']=$this->retarr["devc"][$key]["cBoost"];


      $this->retarr["devc"][$key]["cBoostDuration"] = bindec(substr($this->retarr["devc"][$key]["cBoost"],0,3))*5;
      debmes("devc".$key."cBoostDuration:".      $this->retarr["devc"][$key]["cBoostDuration"], 'eq3max');
	$rec['BOOSTDURATION']=$this->retarr["devc"][$key]["cBoostDuration"];

      $this->retarr["devc"][$key]["cBoostValue"] = bindec(substr($this->retarr["devc"][$key]["cBoost"],3,5))*5;
      debmes("devc".$key."cBoostValue:".      $this->retarr["devc"][$key]["cBoostValue"], 'eq3max');
	$rec['BOOSTVALUE']=$this->retarr["devc"][$key]["cBoostValue"];

      $this->retarr["devc"][$key]["cDecalc"] = $this->strpaddecbin2str($str,1,8);
      debmes("devc".$key."cDecalc:".      $this->retarr["devc"][$key]["cDecalc"], 'eq3max');
	$rec['DECALC']=$this->retarr["devc"][$key]["DECALC"];

      $readlen =  1; $this->retarr["devc"][$key]["cDecalcDay"] = bindec(substr($this->retarr["devc"][$key]["cDecalc"],0,3));
      $readlen =  1; $this->retarr["devc"][$key]["cDecalcTime"] = bindec(substr($this->retarr["devc"][$key]["cDecalc"],3,5));
      $this->retarr["devc"][$key]["cMaximumValveSetting"] = $this->dechex2str($str, 1) *(100/255);
      debmes("devc".$key."cMaximumValveSetting:".      $this->retarr["devc"][$key]["cMaximumValveSetting"], 'eq3max');
	$rec['MAXVALVESETING']=$this->retarr["devc"][$key]["cMaximumValveSetting"];


      $this->retarr["devc"][$key]["cValveOffset"] = $this->dechex2str($str, 1) *(100/255);
      debmes("devc".$key."cValveOffset:".      $this->retarr["devc"][$key]["cValveOffset"], 'eq3max');
	$rec['VALVEOFFSET']=$this->retarr["devc"][$key]["cValveOffset"];
      
      for ($j = 1 ; $j <= 7 ; $j++) {
        $readlen = 26;// Sat, Sun, Mon, Tue, Weg, Thu, Fri
        $idx=0;
        for($i = $this->pos; $i < $readlen+$this->pos ; $i+=2){
          $idx=$idx+1;
          $bin  = str_pad(decbin(hexdec(dechex(ord(substr($str,$i,1))))),8,"0",STR_PAD_LEFT).str_pad(decbin(hexdec(dechex(ord(substr($str,$i+1,1))))),8,"0",STR_PAD_LEFT);
          $deg = bindec(substr($bin,0,7));
          $min = bindec(substr($bin,7,9));
          if ( ($idx>1) and ($this->retarr["devc"][$key]["cWeeklyProgramm"][$j][$idx-1]["deg"]==($deg/2)) ) { 
            $idx=$idx-1;
          }
          $this->retarr["devc"][$key]["cWeeklyProgramm"][$j][$idx]["deg"]= ($deg/2);
          $devctime= gmdate("H:i", ($min * 5 * 60));
          if ( ($devctime == '00:00') and ($min>0) ) {
            $devctime = '24:00';
          }
          $this->retarr["devc"][$key]["cWeeklyProgramm"][$j][$idx]["time"]= $devctime ;
        }
        $this->pos += $readlen;
      }
      break;


    case "3":
      // Fensterkontakt
	$rec['DEVICETYPETEXT']='Remote';
      break;


     
    case "4":
      // Fensterkontakt
      break;

    default:
      // Other
      break;
    }	
        $rec['UPDATED']=date('Y-m-d H:i:s');

	if ($rec['ID']) {
	SQLUpdate('eq3max_devices', $rec);
	} else 
	{

if ($rec['RFADDRESS'])	
{
	SQLInsert('eq3max_devices', $rec);
}
	}







}


if ($buf[0]=='L') {
debmes('-------------------------------' ,'eq3max');
debmes('Этот ответ содержит информацию об устройствах в режиме реального времени.  ' ,'eq3max');
/*
L response («Список устройств») 
Этот ответ содержит информацию об устройствах в режиме реального времени. 
Пример:
L: CwA1CAASGiAshYsu
Декодировано в шестнадцатеричном виде:

0b 18f621091218000a000000 0c177190091218040a000000f90b18f604091218000a0000000b184af3091218000a0000000b18f614091218000a0000000b18f71ef11219000a000000

КОДє ВЫБРАТЬ ВСЕ
00: 0B 00 35 08 00 12 1A 20 2C 85 8B 2E              |..5.... ,…‹.    

    0b 18 f6 21 09 12 18 00 0a 00 00 00               0b длинна 11 байт RF=18f621  09? 12= 00010010b(Valid, Initialized) 18 = 00011000b (00 auto,  1 DST active, 1 Gateway known, 0 Panel unlocked, Linkstatus OK,Battery OK)  valve 0% 5gr
    0c 17 71 90 09 12 18 04 0a 00 00 00	f9	      0c длинна 12 байт RF=177190  09? 12= 00010010b(Valid, Initialized) 18 = 00011000b (00 auto,  1 DST active, 1 Gateway known, 0 Panel unlocked, Linkstatus OK,Battery OK)  valve 4% 5gr
    0c 17 71 90 09 12 18 04 2a 32 13 29 f7
      0a& 0x80 +f9
      2a  f f7  = 24.7
(data[pos + 8] & 0x80) << 1) + data[pos + 12]) / 10.0

    0b 18 f6 04 09 12 18 00 0a 00 00 00               f9 длинна 11 байт RF=18f604  09? 12= 00010010b(Valid, Initialized) 18 = 00011000b (00 auto,  1 DST active, 1 Gateway known, 0 Panel unlocked, Linkstatus OK,Battery OK)  valve 0% 5gr
    0b 18 4a f3 09 12 18 00 0a 00 00 00	              0b длинна 11 байт RF=184af3  09? 12= 00010010b(Valid, Initialized) 18 = 00011000b (00 auto,  1 DST active, 1 Gateway known, 0 Panel unlocked, Linkstatus OK,Battery OK)  valve 0% 5gr
    0b 18 f6 14 09 12 18 00 0a 00 00 00               0b длинна 11 байт RF=18f614  09? 12= 00010010b(Valid, Initialized) 18 = 00011000b (00 auto,  1 DST active, 1 Gateway known, 0 Panel unlocked, Linkstatus OK,Battery OK)  valve 0% 5gr
    0b 18 f7 1e f1 12 19 00 0a 00 00 00               0b длинна 11 байт RF=18f71e  f1? 12= 00010010b(Valid, Initialized) 19 = 00011001b (01 manual,1 DST active, 1 Gateway known, 0 Panel unlocked, Linkstatus OK,Battery OK)  valve 0% 5gr
    0b 18 f7 1e 03 1a 1a 00 2a 32 13 29
    0b 18 f6 14 09 12 18 0b 2a 00 e3 00
    0b 18 f7 1e 03 1a 18 00 2a 00 00 00
            1001 + 00 = 100 = 64 /10 = 6.4
              101010 110010 = 110010 = 5





0b 18 f6 21 09 12 18 00 2a 00 00 00  
0c 17 71 90 09 12 18 04 2a 32 13 29 f7   24.7
0b 18 f6 04 09 12 18 00 2a 00 00 00  
0b 18 4a f3 09 12 18 00 2a 00 ea 00  23.2      ea = 11101010 = 234/10 = 23.4
0b 18 f6 14 09 12 18 09 2a 00 e5 00  22.8      e5 = 11100101 = 229/10=22.9
0b 18 f7 1e 03 1a 18 00 2a 00 00 00 
0b 18 f6 21 09 12 18 00 2a 00 00 00   
0c 17 71 90 09 12 18 04 2a 32 13 29 f7
0b 18 f6 04 09 12 18 00 2a 00 00 00  
0b 18 4a f3 09 12 18 00 2a 00 ea 00   23.4
0b 18 f6 14 09 12 18 09 2a 00 e5 00   22.9
0b 18 f7 1e 03 1a 18 00 2a 00 00 00  

32 13 = 110010 10011 = 010011 = 19


    ((data[pos + 9] & 0xFF) * 256 + (data[pos + 10] & 0xFF)) / 10.0
    42*256/10



thermostat

9       Actual Temperature  2           205

offset|      9    | ... |      10    |
hex   |     01    |     |     32    |
binary| 0000 0001 | ... | 0011 0010 |
                |         |||| ||||
                +---------++++-++++--- actual temperature (°C*10): 100110010 = 30.6°C


Фактическая температура (настенный термостат)
11      Actual Temperature  1           219
Температура в помещении измеряется настенным термостатом в ° C * 10. Например, 0xDB = 219 = 21,9 ° C Температура представлена ​​9 битами; 9-й бит доступен как верхний бит со смещением 8

offset|      8    | ... |     12    |
hex   |     B2    |     |     24    |
binary| 1011 0010 | ... | 0010 0100 |
        | || ||||         |||| ||||
        | ++-++++--------------------- temperature (°C*2):            110010 = 25.0°C
        |                 |||| ||||
        +-----------------++++-++++--- actual temperature (°C*10): 100100100 = 29.2°C


КОДє ВЫБРАТЬ ВСЕ
Start Length  Value       Description
==================================================================
0          1  0B          Length of data: 0B = 11(decimal) = 11 bytes
1 3        3  003508      RF address
4          1  00          ?
5          1  12          bit 4     Valid              0=invalid;1=information provided is valid
                          bit 3     Error              0=no; 1=Error occurred
                          bit 2     Answer             0=an answer to a command,1=not an answer to a command
                          bit 1     Status initialized 0=not initialized, 1=yes
                                
                          12  = 00010010b
                              = Valid, Initialized
                          
6       1     1A          bit 7     Battery       1=Low
                          bit 6     Linkstatus    0=OK,1=error
                          bit 5     Panel         0=unlocked,1=locked
                          bit 4     Gateway       0=unknown,1=known
                          bit 3     DST setting   0=inactive,1=active
                          bit 2     Not used
                          bit 1,0   Mode         00=auto/week schedule
                                                 01=Manual
                                                 10=Vacation
                                                 11=Boost   
                          1A  = 00011010b

                                00011010 - VACATION
                                00011001   MANUAL
				00011000 - AUTO	
				00011011  - BOOST

                              = Battery OK, Linkstatus OK, Panel unlocked, Gateway known, DST active, Mode Vacation.

7       1     20          Valve position in %
8       1     2C          Temperature setpoint, 2Ch = 44d; 44/2=22 deg. C
9       2     858B        Date until (05-09-2011) (see Encoding/Decoding date/time)
B       1     2E          Time until (23:00) (see Encoding/Decoding date/time)

*/

    $v=$buf;
    $v = substr($v,2,strlen($v));
	debmes($v, 'eq3max');
    $str = base64_decode($v);
	debmes($str, 'eq3max');
    $bin = bin2hex($str);
	debmes($bin, 'eq3max');
    $this->pos = 0;

//    for($j = 1 ; $j <= $this->devccount; $j++) {

//    $devccount = hexdec($this->dechex2str($str, 1));
//    $devccount=6;

      $devccount=SQLSelectOne('select count(*) value from eq3max_devices')['value'];
//      debmes("33333333333333333333333333333333333", 'eq3max');
      debmes("devccount:".$devccount, 'eq3max');

    for($j = 0 ; $j <= $devccount+1; $j++) {
      debmes("33333333333333333333333333333333333", 'eq3max');
      debmes("device:".$j, 'eq3max');
      debmes("bin:".$bin, 'eq3max');
      debmes("33333333333333333333333333333333333", 'eq3max');
      unset($hilf);
//      $hilf["ReadLength"] = $this->ord2str($str, 1);
$lenght=hexdec(substr($bin,0,2))+1;
        debmes("lenght:".substr($bin,0,2).":" .$lenght, 'eq3max');


$tempstr=substr($bin,0,$lenght*2);
debmes("обрезаем bin на первые ".$lenght*2 . " символов" , 'eq3max');
debmes("было:  ".$bin  , 'eq3max');
$bin=substr($bin,$lenght*2);
debmes("стало: ".$bin  , 'eq3max');


//      debmes("ReadLength:".substr($bin,1,2).":" .$hilf["ReadLength"], 'eq3max');

      debmes("Readstring:".$tempstr, 'eq3max');
      debmes($tempstr, 'eq3max_debug');


$rfadress=substr($tempstr,2,6);
//      $hilf["RFAdress"] = $this->strpaddechex2str($str, 3, 2);  
      debmes("RFAdress:".$rfadress, 'eq3max');



	$rec=SQLSelectOne("SELECT * FROM eq3max_devices where RFADDRESS='".$rfadress."'");
//	$rec['RFADDRESS']=$hilf["RFAdress"];
	$rec['RFADDRESS']=$rfadress;
        $rec['DEBUG']=$tempstr;
      $hilf["?1"] = $this->dechex2str($str, 1);
      debmes("?1:".$hilf["?1"], 'eq3max');

      $data1 = str_pad(base_convert(substr($tempstr,10,2), 16, 2),8,"0",STR_PAD_LEFT);
      debmes("Data1:".substr($tempstr,10,2).":".$data1, 'eq3max');

      debmes("INITIALIZED:".$data1[6], 'eq3max');

switch ($data1[6]) {

	   case "1":
	   $rec['INITIALIZED']='yes';
      debmes("INITIALIZED:yes", 'eq3max');
	   break;

	   case "0":
       debmes("INITIALIZED:no", 'eq3max');
	   $rec['INITIALIZED']='no';
	   break;
}

      debmes("ANSWER:".$data1[5], 'eq3max');
switch ($data1[5]) {

	   case "1":
         debmes("ANSWER:not an answer to a command", 'eq3max');
	   $rec['ANSWER']='not an answer to a command';
	   break;

	   case "0":
           debmes("ANSWER:an answer to a command", 'eq3max');
	   $rec['ANSWER']='an answer to a command';
	   break;
}

      debmes("ERROR:".$data1[4], 'eq3max');
switch ($data1[4]) {

	   case "1":
	   $rec['ERROR']='Error occurred';
	   break;

	   case "0":
	   $rec['ERROR']='no';
	   break;
}

      debmes("VALID:".$data1[3], 'eq3max');
switch ($data1[3]) {

	   case "1":
	   $rec['VALID']='information provided is valid';
	   break;

	   case "0":
	   $rec['VALID']='invalid';
	   break;
}


///////////////////////////////

      $data2 = str_pad(base_convert(substr($tempstr,12,2), 16, 2),8,"0",STR_PAD_LEFT);
      debmes("Data2:".substr($tempstr,12,2).":".$data2, 'eq3max');


      debmes("MODE:".$data2[6].$data2[7], 'eq3max');

switch ($data2[6].$data2[7]) {
//00=auto/week schedule  
//01=Manual              
//10=Vacation            
//11=Boost               



	   case "00":
	   $rec['MODE']='auto';
           debmes("MODE:auto", 'eq3max');
	   break;

	   case "01":
           debmes("MODE:manual", 'eq3max');
	   $rec['MODE']='manual';
	   break;

	   case "10":
           debmes("MODE:vacation", 'eq3max');
	   $rec['MODE']='vacation';
	   break;

	   case "11":
           debmes("MODE:boost", 'eq3max');
	   $rec['MODE']='boost';
	   break;


}

//                          bit 3     DST setting   0=inactive,1=active
      debmes("VALID:".$data2[4], 'eq3max');
switch ($data2[4]) {

	   case "1":
	   $rec['DSTSET']='active';
          debmes("DSTSET:active", 'eq3max');
	   break;

	   case "0":
	   $rec['DSTSET']='inactive';
          debmes("DSTSET:inactive", 'eq3max');
	   break;
}


//                          bit 4     Gateway       0=unknown,1=known
      debmes("GATEWAY:".$data2[3], 'eq3max');
switch ($data2[3]) {

	   case "1":
	   $rec['GATEWAY']='known';
          debmes("GATEWAY:known", 'eq3max');
	   break;

	   case "0":
	   $rec['GATEWAY']='unknown';
          debmes("GATEWAY:unknown", 'eq3max');
	   break;
}


//bit 7     Battery       1=Low
      debmes("BATTERY:".$data2[1], 'eq3max');
switch ($data2[1]) {

	   case "1":
          debmes("BATTERY:Low", 'eq3max');
	   $rec['BATTERY']='Low';
	   break;

	   case "0":
          debmes("BATTERY:Good", 'eq3max');
	   $rec['BATTERY']='Good';
	   break;
}






//      $hilf["Data2"] = $this->strpaddecbin2str($str,1,8);
//      debmes("Data2:".$hilf["Data2"], 'eq3max');

      $valve=substr($tempstr,14,2);
        debmes("ValvePosition:".$valve, 'eq3max');
	$rec['VALVEPOSITION']=$valve;

        $temp=hexdec(substr($tempstr,16,2))/2;
        debmes("Temperature:".$temp, 'eq3max');
	$rec['TEMPERATURE']=$temp;


     if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY'])  {
     setglobal($rec['LINKED_OBJECT'].".".$rec['LINKED_PROPERTY'],$temp, array($this->name=>'0') );
        debmes("setglobal(".$rec['LINKED_OBJECT'].".".$rec['LINKED_PROPERTY'].",".$temp.")", 'eq3max');
}


       $rec['UPDATED']=date('Y-m-d H:i:s');
$oldacttemp=$rec['ACTUALTEMP'];

if ($lenght==12) {
//base_convert(substr($tempstr,10,2), 16, 2)
     $atemp=base_convert(substr(base_convert(substr($tempstr,18,2),16,2),-1).base_convert(substr($tempstr,20,2),16,2),2,10)/10;
      if ( ($atemp>=6)&&($atemp<=31))
       {debmes("ACTUALTEMP:".$atemp, 'eq3max');
//	$rec['DEBUG']=substr(base_convert(substr($tempstr,18,2),16,2),-1).base_convert(substr($tempstr,20,2),16,2);
	$rec['ACTUALTEMP']=$atemp;

     if ($rec['LINKED_OBJECT2'] && $rec['LINKED_PROPERTY2'])  {
     setglobal($rec['LINKED_OBJECT2'].".".$rec['LINKED_PROPERTY2'],$atemp, array($this->name=>'0') );
        debmes("setglobal(".$rec['LINKED_OBJECT2'].".".$rec['LINKED_PROPERTY2'].",".$atemp.")", 'eq3max');
}

   debmes("getglobal(".$rec['LINKED_OBJECT'].".".$rec['LINKED_PROPERTY'].":".getglobal($rec['LINKED_OBJECT'].".".$rec['LINKED_PROPERTY']), 'eq3max');
   debmes("getglobal(".$rec['LINKED_OBJECT2'].".".$rec['LINKED_PROPERTY2'].":".getglobal($rec['LINKED_OBJECT2'].".".$rec['LINKED_PROPERTY2']), 'eq3max');

}
//else $rec['ACTUALTEMP']='';


}

   if ($lenght==13){ 
   $atemp=base_convert(substr(base_convert(substr($tempstr,16,2),16,2),1,1).base_convert(substr($tempstr,24,2),16,2),2,10)/10;

if  (($atemp>=6)&&($atemp<=31))
 {

        debmes("ACTUALTEMP:".$atemp, 'eq3max');
if ($atemp!=0) 	{
$rec['ACTUALTEMP']=$atemp;
//	$rec['DEBUG']=substr(base_convert(substr($tempstr,16,2),16,2),1,1).base_convert(substr($tempstr,24,2),16,2);

     if ($rec['LINKED_OBJECT2'] && $rec['LINKED_PROPERTY2'])  {
     setglobal($rec['LINKED_OBJECT2'].".".$rec['LINKED_PROPERTY2'],$atemp, array($this->name=>'0') );
        debmes("setglobal(".$rec['LINKED_OBJECT2'].".".$rec['LINKED_PROPERTY2'].",".$atemp, 'eq3max');
}


}
}
//else $rec['ACTUALTEMP']='';

}









//        ltrim($bin, $length);

debmes($rec, 'eq3max');
	if ($rec['ID']) {
debmes('SQLUpdate', 'eq3max');
       $rec['UPDATED']=date('Y-m-d H:i:s');
	SQLUpdate('eq3max_devices', $rec);
	} else 
	{
debmes('SQLInsert', 'eq3max');
if ($rec['RFADDRESS'])	
{SQLInsert('eq3max_devices', $rec);
    $rec['UPDATED']=date('Y-m-d H:i:s');
}
	}

}
}
      
/*

      if (!isset($this->retarr["devc"])){
      	$this->retarr["devc"]=array();
      }

       debmes( "RFAdress:". $hilf["RFAdress"], 'eq3max');

      $key = array_search($hilf["RFAdress"], array_column($this->retarr["devc"], 'mRFAdress'));
       debmes( "keys:".  $key, 'eq3max');



      if ($key===false) {
      	$key = count($this->retarr["devc"]) +1;
      } else {
        $key = $key + 1;
      }
      

      $this->retarr["devc"][$key]["lLiveReadLength"] = $hilf["ReadLength"];
      $this->retarr["devc"][$key]["lLiveRFAdress"] = $hilf["RFAdress"];
      $this->retarr["devc"][$key]["lLive?1"] = $hilf["?1"];

	$rec=SQLSelectOne("SELECT * FROM eq3max_devices where RFADDRESS='".$hilf["RFAdress"]."'");
	$rec['RFADDRESS']=$hilf["RFAdress"];


      if ($hilf["ReadLength"] == 11) {
        $this->retarr["devc"][$key]["lValvePosition"] = $hilf["ValvePosition"];



        $this->retarr["devc"][$key]["lTemperature"] = $hilf["Temperature"];
        $this->retarr["devc"][$key]["lTemperature"] = $hilf["Temperature"];
	debmes("temp:".$key.":".$this->retarr["devc"][$key]["lTemperature"], 'eq3max');

        $hilf["TimeUntil"] =  $this->ord2str($str, 1);

        $this->retarr["devc"][$key]["lDateUntil"] = $hilf["DateUntil"];
        $year = substr($hilf["DateUntil"],-6,6);
        $month = substr($hilf["DateUntil"],0,3).substr($hilf["DateUntil"],8,1);
        $day = substr($hilf["DateUntil"],3,5);
        $this->retarr["devc"][$key]["lDateUntil"] = bindec($day).".".bindec($month).".".bindec($year);
        $this->retarr["devc"][$key]["lTimeUntil"] = $hilf["TimeUntil"];
        $this->retarr["devc"][$key]["lTimestampUntil"] = mktime(floor($hilf["TimeUntil"]),($hilf["TimeUntil"]-floor($hilf["TimeUntil"]))*60,0,bindec($month),bindec($day),bindec($year));
      }

      $this->retarr["devc"][$key]["lvalid"] = substr($hilf["Data1"],3,1);
      $this->retarr["devc"][$key]["lError"] = substr($hilf["Data1"],4,1);
      $this->retarr["devc"][$key]["lisAnswer"] = substr($hilf["Data1"],5,1);
      $this->retarr["devc"][$key]["linitialized"] = substr($hilf["Data1"],6,1);
      $this->retarr["devc"][$key]["lLiveData7"] = substr($hilf["Data1"],7,1);

      $this->retarr["devc"][$key]["lLowBatt"] = substr($hilf["Data2"],0,1);
      $this->retarr["devc"][$key]["lLinkError"] = substr($hilf["Data2"],1,1);
      $this->retarr["devc"][$key]["lPanelLock"] = substr($hilf["Data2"],2,1);
      $this->retarr["devc"][$key]["lGatewayOK"] = substr($hilf["Data2"],3,1);
      $this->retarr["devc"][$key]["lDST"] = substr($hilf["Data2"],4,1);
      $this->retarr["devc"][$key]["lNot used"] = substr($hilf["Data2"],5,1);
      switch (substr($hilf["Data2"],6,2)) {
        case "00" : $this->retarr["devc"][$key]["lMode"] = "auto"; break;
        case "01" : $this->retarr["devc"][$key]["lMode"] = "manu"; break;
        case "10" : $this->retarr["devc"][$key]["lMode"] = "vacation"; break;
        case "11" : $this->retarr["devc"][$key]["lMode"] = "boost"; break;
      }
	$rec['MODE']=$this->retarr["devc"][$key]["lMode"];
	debmes("mode:".$key.":".$this->retarr["devc"][$key]["lMode"], 'eq3max');
 
    }
         $rec['UPDATED']=date('Y-m-d H:i:s');



	if ($rec['ID']) {
	SQLUpdate('eq3max_devices', $rec);
	} else 
	{
	SQLInsert('eq3max_devices', $rec);
	}





*/
}



function SetTempId($id, $imode,$temp){

//debmes('SetTempId', 'eqq3');

$rec=SQLSelectOne('select * from eq3max_devices where id='.$id);
//debmes($rec, 'eqq3');

$rfadress=$rec['RFADDRESS'];
$roomid=$rec['ROOMID'];

debmes($rfadress.' '.$roomid.' '. $imode.' '.$temp, 'eqq3');

//$this->SetTemp($rfadress,$roomid, $imode,$temp);


$cmd='
include_once(DIR_MODULES . "eq3max/eq3max.class.php");
$eq3 = new eq3max();
$eq3->SetTemp("'.$rfadress.'",str_pad('.$roomid.', 2, "0", STR_PAD_LEFT), '.$imode.','.$temp.');
debmes("задача get выполнена", "eq3max");
';

debmes($cmd, "eqq3");
 SetTimeOut('eq3max_setvalues_'.$rfadress,$cmd, '1'); 



$cmd='
include_once(DIR_MODULES . "eq3max/eq3max.class.php");
$eq3 = new eq3max();
$eq3->get(); 
debmes("задача get выполнена", "eq3max");
';
 SetTimeOut('eq3max_getvalues',$cmd, '5'); 


}






//}


  function SetTemp($rfadress,$roomid, $imode,$temp){

debmes('SetTemp', 'eqq3');
debmes('SetTemp:'.$rfadress.' ' .$roomid.' ' . $imode.' ' .$temp, 'eqq3');

debmes('1', 'eqq3');    

//echo $rfadress;
//echo "-";
//echo $roomid;
//echo "-";
//echo $imode;
//echo "-";
//echo $temp;
//echo "<br>";  	
  	$this->retarr["err"]= "";
$err="";
  	//$command = "00 04 40 00 00 00 00 FE 30 01 A8 8B 8B 1F";
  	
  	$mode=$imode;
    switch ($mode){
      case "auto": $mode = '00'; break;
      case "manu": $mode = '01'; break;
      //case "vacation": $mode = '10'; break;
      //case "boost": $mode = '11'; break;
      default: $mode = '01';
    }

debmes('2', 'eqq3');    

    $strrem="";
    $pos = strpos($temp, ".");
    if ($pos !== false) {
      $strrem = substr($temp,$pos);
      $temp = substr($temp,0,$pos);
    }

debmes('21', 'eqq3');    
    if ($strrem==".0") {
    	$strrem="";
    }

debmes('22', 'eqq3');    

/*
    if ( (!ctype_digit($temp)) or ( ($strrem<>"") and ($strrem<>".5") ) ) {
debmes('222', 'eqq3');    
//  	   $this->retarr["err"][]= "The temperature value is not a valid number. Only halve or whole digits allowed . For example 17 or 17.0 or 17.5";   
  $err="The temperature value is not a valid number. Only halve or whole digits allowed . For example 17 or 17.0 or 17.5";
debmes('The temperature value is not a valid number. Only halve or whole digits allowed . For example 17 or 17.0 or 17.5', 'eqq3');    
debmes('223', 'eqq3');    
    }                                           	
*/


debmes('23', 'eqq3');    
    $temp=$temp . $strrem;
    if ($this->retarr["err"]=="") {	
      if (  ($temp>30) or ($temp<0) ) {
//  	   $this->retarr["err"][]= "The temperature should be between 0 and 30";   
  $err="The temperature should be between 0 and 30";
debmes('The temperature should be between 0 and 30', 'eqq3');    
      } else {
      	if ($temp<5) {
      		$temp=4;
      	}
      }
    }

debmes('3', 'eqq3');    
    if (strlen($rfadress)<>6) {
//  	  $this->retarr["err"][]= "The rfaddress is not a (hexadecimal) 6 character address";    	
  	  $err= "The rfaddress is not a (hexadecimal) 6 character address";    	
         debmes('The rfaddress is not a (hexadecimal) 6 character address', 'eqq3');    
    } else {
      if (!ctype_xdigit($rfadress)) {	
//  	    $this->retarr["err"][]= "The rfaddress is not a (hexadecimal) 6 character address";         	
  	    $err= "The rfaddress is not a (hexadecimal) 6 character address";         	
           debmes('The rfaddress is not a (hexadecimal) 6 character address', 'eqq3');    
      }
    } 
    if (!ctype_digit($roomid))  {
  	   $this->retarr["err"][]= "The roomid is not a valid digit.";   
  	   $err= "The roomid is not a valid digit.";   
          debmes('The roomid is not a valid digit.', 'eqq3');    

    } else {
      if (  ($roomid>99) or ($roomid<0) ) {
//  	    $this->retarr["err"][]= "Only two digits allowed for roomid";
	    $err= "Only two digits allowed for roomid";
           debmes('Only two digits allowed for roomid', 'eqq3');    

      }  	
    }
debmes('4', 'eqq3');    
debmes($this->retarr, 'eqq3');
    $deg = strtoupper(dechex(bindec($mode . str_pad(decbin($temp*2),6,"0",STR_PAD_LEFT) )));

debmes($deg.":".$deg, 'eqq3');

    //$command = "00 04 40 00 00 00 ".strtoupper($rfadress). str_pad($roomid,2,"0",STR_PAD_LEFT) . $deg."";
    $command = "00 00 40 00 00 00 ".strtoupper($rfadress). str_pad($roomid,2,"0",STR_PAD_LEFT) . $deg."";
    $send = "s:".$this->hex_to_base64(str_replace(" ","",$command))."\r\n";
    //$send = "s:AARAAAAAARf1BHI=\r\n";
debmes('5', 'eqq3');    

//    if ($this->retarr["err"]=="") {

debmes($err, 'eqq3');    

    if ($err=="") {

debmes('6', 'eqq3');    
//$log = date('H:i:s') . " Rfaddress:" . $rfadress . " mode:" . $imode . $mode . " temperature:" . $temp . " command:" . $command . "\n";
//$handle = fopen('log/weblog_'.date('Y-m-d').'.txt','a');
//fputs($handle,$log);
//fclose($handle); 



debmes("222222222222222222222222222222222222222222222222222222222222222222",'eq3max');

  $this->getConfig();
  $host=$this->config['IPADDR'];
  $port = "62910";


  $hostt=$this->config['IPADDR'];
  $portt = "62910";


//https://www.domoticaforum.eu/viewtopic.php?f=66&t=6654

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>5, "usec"=>0));

$bufff="";

        if (socket_connect($socket, $host, $port)) {  //Connect

//        socket_sendto($socket, $send, strlen($send), 0, $host, $port);
debmes('socket created host:'.$host." port:". $port, 'eq3max');


	while(@socket_recvfrom($socket, $buf, 1024, 0, $host, $port)){

//debmes('reading socket  host:'.$host." port:". $port." buf:".$buf, 'eq3max');
//sg('test.buf',$buf);


//      fputs($socket,$send);
//      $this->GetBuff("S:");
//      $typeS=false;


			if($buf != NULL){
//$bufff.=$buf;
$bufff="";
//null;

} 
//else {

}

socket_sendto($socket, $send, strlen($send), 0, $hostt, $portt);
debmes('socket_sendto:'.$send. " strlen:". strlen($send)." host:".$hostt." port:". $portt, 'eq3max');
	while(@socket_recvfrom($socket, $buf, 1024, 0, $host, $port)){

			if($buf != NULL){$bufff.=$buf;} else {

@socket_shutdown($cs, 2);
socket_close($cs);
debmes('Закрыли сокет, обмен закончен', 'eq3max');
}
}


}

    }        

        $buff=explode(chr(10),$bufff);
	$total=count( $buff);
         for ($i=0;$i<$total;$i++) {
        $this->parsing($buff[$i]);
//echo $i.":".$buff[$i]."<hr>";
				    }



debmes($this->retarr, 'eq3max');
    return $this->retarr; 
  }



//////////////////////////////////////
//////////////////////////////////////
//////////////////////////////////////
//////////////////////////////////////
//////////////////////////////////////


 function propertySetHandle($object, $property, $value) {


debmes('Сработал propertySetHandle object:'.$object." property:". $property." value:". $value,  'eq3max');
$sql="SELECT * FROM eq3max_devices WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'";
debmes($sql, 'eq3max');

if (!$value) { debmes('value не содержит данных', 'eq3max'); } else 
{

   $rec=SQLSelect($sql);
   $total=count($rec);

debmes($object.":". $property.":". $value. ' найдено результатов '. $total, 'eq3max');

   if ($total) {
    for($i=0;$i<$total;$i++) {

//проверяем тип устройства



$tip=$rec[$i]['DEVICETYPETEXT'];
$rfaddress=$rec[$i]['RFADDRESS'];
$roomid=$rec[$i]['ROOMID'];
//$changedprop=$bleprop[$i]['TITLE'];

     debmes('DEVICE_ID:'.$rec[$i]['ID']. '   $rfaddress:'.$rfaddress.' тип. уст:'.$tip.  "  value:".$value, 'eq3max');

if ($tip=='Thermostat') {
//echo $tip;

     debmes('Отправляем устройству  rfaddr:'.$rfaddress.' тип. уст:'.$tip. 'новое значение:'.$value, 'eq3max');

$this->SetTemp($rfaddress,$roomid, 'manu',$value);


$cmd='
include_once(DIR_MODULES . "eq3max/eq3max.class.php");
$eq3 = new eq3max();
$eq3->get(); 
debmes("задача get выполнена", "eq3max");
';
 SetTimeOut('eq3max_getvalues',$cmd, '1'); 

}


//нужно проверить, может ли свойство  управляться




    }
   }  
}
 }


            

   
function edit_devices(&$out, $id) {
debmes('run '.DIR_MODULES.$this->name . '/eq3max_devices_edit.inc.php', 'eq3max');
require(DIR_MODULES.$this->name . '/eq3max_devices_edit.inc.php');
}




 function search_devices(&$out) {

$mhdevices=SQLSelect("SELECT * FROM eq3max_devices");

//  $mhdevices=SQLSelect("SELECT * FROM eq3max_devices");
  $mhdevices=SQLSelect("SELECT eq3max_devices.*,ROOMNAME  FROM eq3max_devices left join (select eq3max_rooms.*, eq3max_rooms.TITLE ROOMNAME from eq3max_rooms) eq3max_rooms on eq3max_devices.ROOMID=eq3max_rooms.ID");
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

//echo $buf;

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

function processSubscription($event_name, $details='') {
  if ($event_name=='HOURLY') {
  $this->get();
  }
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
  subscribeToEvent($this->name, 'HOURLY');
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
 eq3max_devices: RFADDRESS varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DEVICETYPE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DEVICETYPETEXT varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: SERIALNUMBER varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DEVICENAME varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ROOMID varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: FIRMWARE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ECOTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: MINSETPOINTTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: MAXSETPOINTTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: COMFORTTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: WINDOWOPENTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: WINDOWOPENDURATION varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: BOOST varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: BOOSTVALUE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: BOOSTDURATION varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DECALC varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: MAXVALVESETING varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: VALVEOFFSET varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: NUMMODUS varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: VALVEPOSITION varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: TEMPERATURE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DATEUNTIL varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: TIMEUNTIL varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: MODE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: SETPOINTTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: SETMODE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: STATUS varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: UPDATED datetime
 eq3max_devices: INITIALIZED varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ANSWER varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DEBUG varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ACTUALTEMP varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ERROR varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: VALID varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DSTSET varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: GATEWAY varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: BATTERY varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_OBJECT2 varchar(255) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_PROPERTY2 varchar(255) NOT NULL DEFAULT ''
 eq3max_devices: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''



               







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
 eq3max_rooms: ID int(10) unsigned NOT NULL auto_increment
 eq3max_rooms: TITLE varchar(100) NOT NULL DEFAULT ''
 eq3max_rooms: FIRSTRFADDRESS varchar(100) NOT NULL DEFAULT ''
 eq3max_rooms: UPDATED datetime


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





  function strpaddechex2str($str, $readlen, $padlen){
    $outstr="";
    for ($idx = 1 ; $idx <= $readlen ; $idx++) {
	  $outstr = $outstr . str_pad(dechex(ord(substr($str,$this->pos,1))),$padlen,"0",STR_PAD_LEFT);
	  $this->pos = $this->pos + 1;
    }
    return $outstr;
  }
  

  function strpaddecbin2str($str, $readlen, $padlen){
    $outstr="";
    for ($idx = 1 ; $idx <= $readlen ; $idx++) {
	  $outstr = $outstr .  str_pad(decbin(ord(substr($str,$this->pos,1))),8,"0",STR_PAD_LEFT);
	  $this->pos = $this->pos + 1;
    }
    return $outstr;
  }

  function dechex2str($str, $readlen){
    $outstr="";
    for ($idx = 1 ; $idx <= $readlen ; $idx++) {
	  $outstr = $outstr . dechex(ord(substr($str,$this->pos,1)));
	  $this->pos = $this->pos + 1;
    }
    return $outstr;
  }

  function ord2str($str, $readlen) {
    $outstr="";
    for ($idx = 1 ; $idx <= $readlen ; $idx++) {
	  $outstr = $outstr . ord(substr($str,$this->pos,1));
	  $this->pos = $this->pos + 1;
    }
    return $outstr;
  }
  
  function substr2str($str, $readlen) {
  	$outstr="";
      $outstr= substr($str,$this->pos,$readlen);
      $this->pos = $this->pos + $readlen;
    return $outstr;	
  }
  
  function hex_to_base64($hex){
    $return = '';
    foreach(str_split($hex, 2) as $pair){
      $return .= chr(hexdec($pair));
    }
    return base64_encode($return);
  } 




  function GetBuff($lastmsgtype) {
  	$finished=0;
    $jetzt = time();
    $this->buffarr = "";
    while (!feof($this->fp) && time() < $jetzt+20 && $finished == 0) {
      $line = fgets($this->fp);
      if (strpos($line,$lastmsgtype) !== false) $finished = 1;
      //if ($line != "")  $this->buff .= $line."\n";
//      if ($line != "")  $this->buffarr[]=substr($line,0,-1);
      //sleep(1);
    }
    if ($finished != 1) {
  	  $this->retarr["err"][]= "No Connection - Unknown error";
  	  return false;
    } else {
      $this->retarr["buff"] = $this->buffarr;
      return true;
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

