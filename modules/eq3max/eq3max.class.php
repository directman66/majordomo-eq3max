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


if ($this->view_mode=='get') {

$this->get();
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
echo $i.":".$buff[$i]."<hr>";
				    }



}
}





function parsing ($buf){

echo     $buf ."<br><br>";
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
/*
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

/*
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
    }

    $this->devccount = $this->dechex2str($str, 1);
    $this->retarr["meta"]["mDevCount"] = $this->devccount;
    
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

        $this->retarr["devc"][$j]["mNameLength"] = $this->ord2str($str, 1);
        debmes( "devc".$j."mNameLength:".$this->retarr["devc"][$j]["mNameLength"], 'eq3max');

        $this->retarr["devc"][$j]["mDeviceName"] = $this->substr2str($str,$this->retarr["devc"][$j]["mNameLength"]);
        debmes( "devc".$j."mDeviceName:".$this->retarr["devc"][$j]["mDeviceName"], 'eq3max');

        $this->retarr["devc"][$j]["mRoomID"] = $this->dechex2str($str, 1);
        debmes( "devc".$j."mRoomID:".$this->retarr["devc"][$j]["mRoomID"], 'eq3max');
      }
    //}
	

*/

}


if ($buf[0]=='C') {
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

	if ($rec['ID']) {
	SQLUpdate('eq3max_devices', $rec);
	} else 
	{
	SQLInsert('eq3max_devices', $rec);
	}

//debmes('C:data_ecnode:'.$dataenc, 'eq3max');

/*
    switch($hilf["DeviceType"]){
      case "0":
	debmes('C:DeviceType:Cube', 'eq3max');
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
//      dembes("cubec?3:".$this->retarr["cube"]["c?3"], 'eq3max');

      $this->retarr["cube"]["c?4"]=$this->dechex2str($str,8);
//      dembes("cubec?4:".$this->retarr["cube"]["c?4"], 'eq3max');

      $this->retarr["cube"]["c?5"]=$this->dechex2str($str,21);      
//      dembes("cubec?5:".$this->retarr["cube"]["c?5"], 'eq3max');

      $this->retarr["cube"]["c?6"]=$this->dechex2str($str,4);
//      dembes("cubec?6:".$this->retarr["cube"]["c?6"], 'eq3max');

      $this->retarr["cube"]["c?7"]=$this->dechex2str($str,8);
//    dembes("cubec?7:".$this->retarr["cube"]["c?7"], 'eq3max');

      $this->retarr["cube"]["c?8"]=$this->dechex2str($str,21);
//      dembes("cubec?8:".$this->retarr["cube"]["c?8"], 'eq3max');

      $this->retarr["cube"]["cPortalURL"]=$this->substr2str($str,36);      
//      dembes("cPortalURL:".$this->retarr["cube"]["cPortalURL"], 'eq3max');
//      dembes("cPortalURL:".$this->substr2str($str,36), 'eq3max');

      $this->retarr["cube"]["c?9"]=$this->dechex2str($str,60);
//      dembes("cubec?9:".$this->retarr["cube"]["c?9"], 'eq3max');

      $this->retarr["cube"]["c?A"]=$this->dechex2str($str,33);
//      dembes("cubec?A:".$this->retarr["cube"]["c?A"], 'eq3max');

      $this->retarr["cube"]["c?B"]=$this->substr2str($str,3);
//      dembes("cubec?B:".$this->retarr["cube"]["c?B"], 'eq3max');

      $this->retarr["cube"]["c?C"]=$this->dechex2str($str,9);
//      dembes("cubec?C:".$this->retarr["cube"]["c?C"], 'eq3max');

      $this->retarr["cube"]["c?D"]=$this->substr2str($str,4);
//      dembes("cubec?D:".$this->retarr["cube"]["c?D"], 'eq3max');

      $this->retarr["cube"]["c?E"]=$this->dechex2str($str,9);
//      dembes("cubec?E:".$this->retarr["cube"]["c?E"], 'eq3max');
    break;
     
    case "1":
   	  break;
   	 
    case "2":
      // Thermostat
      debmes('C:DeviceType:Thermostat', 'eq3max');
      $key = array_search($hilf["RFAdress"], array_column($this->retarr["devc"], 'mRFAdress'));
      if ($key===false) {
      	$key = count($this->retarr["devc"]) +1;
      } else {
        $key = $key + 1;
      }
        
      $this->retarr["devc"][$key]["c?DataLength"] = $hilf["?1"];

      $this->retarr["devc"][$key]["cRFAdress"] = $hilf["RFAdress"];
      dembes("devc".$key."cRFAdress:".      $this->retarr["devc"][$key]["cRFAdress"], 'eq3max');

      $this->retarr["devc"][$key]["cDeviceType"] = $hilf["DeviceType"];
      dembes("devc".$key."DeviceType:".      $this->retarr["devc"][$key]["DeviceType"], 'eq3max');

      $this->retarr["devc"][$key]["c?2"] = $hilf["?2"];
      $this->retarr["devc"][$key]["c?RoomId"] = $hilf["?RoomId"];
      $this->retarr["devc"][$key]["c?FirmwareVersion"] = $hilf["?FirmwareVersion"];
      $this->retarr["devc"][$key]["c?NumModus"] = $hilf["?NumModus"];
      $this->retarr["devc"][$key]["cSerialNumber"] = $hilf["SerialNumber"];
      dembes("devc".$key."SerialNumber:".      $this->retarr["devc"][$key]["SerialNumber"], 'eq3max');

      $this->retarr["devc"][$key]["cComfortTemperature"] = ($this->ord2str($str, 1) /2) ;
      dembes("devc".$key."cComfortTemperature:".      $this->retarr["devc"][$key]["cComfortTemperature"], 'eq3max');

      $this->retarr["devc"][$key]["cEcoTemperature"] = ($this->ord2str($str, 1) /2) ;
      dembes("devc".$key."cEcoTemperature:".      $this->retarr["devc"][$key]["cEcoTemperature"], 'eq3max');

      $this->retarr["devc"][$key]["cMaxSetPointTemperature"] = ($this->ord2str($str, 1) /2) ;
      dembes("devc".$key."cEcoTemperature:".      $this->retarr["devc"][$key]["cEcoTemperature"], 'eq3max');

      $this->retarr["devc"][$key]["cMinSetPointTemperature"] = ($this->ord2str($str, 1) /2) ;
      dembes("devc".$key."cMinSetPointTemperature:".      $this->retarr["devc"][$key]["cMinSetPointTemperature"], 'eq3max');


      $this->retarr["devc"][$key]["cTemperatureOffse"] = ($this->ord2str($str, 1) /2) -3.5 ;
      dembes("devc".$key."cTemperatureOffse:".      $this->retarr["devc"][$key]["cTemperatureOffse"], 'eq3max');

      $this->retarr["devc"][$key]["cWindowOpenTemperature"] = ($this->ord2str($str, 1) /2) ;
      dembes("devc".$key."cWindowOpenTemperature:".      $this->retarr["devc"][$key]["cWindowOpenTemperature"], 'eq3max');

      $this->retarr["devc"][$key]["cWindowOpenDuration"] = $this->dechex2str($str, 1);
      $this->retarr["devc"][$key]["cBoost"] = $this->strpaddecbin2str($str,1,8);
      $this->retarr["devc"][$key]["cBoostDuration"] = bindec(substr($this->retarr["devc"][$key]["cBoost"],0,3))*5;
      $this->retarr["devc"][$key]["cBoostValue"] = bindec(substr($this->retarr["devc"][$key]["cBoost"],3,5))*5;
      $this->retarr["devc"][$key]["cDecalc"] = $this->strpaddecbin2str($str,1,8);
      $readlen =  1; $this->retarr["devc"][$key]["cDecalcDay"] = bindec(substr($this->retarr["devc"][$key]["cDecalc"],0,3));
      $readlen =  1; $this->retarr["devc"][$key]["cDecalcTime"] = bindec(substr($this->retarr["devc"][$key]["cDecalc"],3,5));
      $this->retarr["devc"][$key]["cMaximumValveSetting"] = $this->dechex2str($str, 1) *(100/255);
      $this->retarr["devc"][$key]["cValveOffset"] = $this->dechex2str($str, 1) *(100/255);
      
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
     
    case "4":
      // Fensterkontakt
      break;

    default:
      // Other
      break;
    }	
*/








}


if ($buf[0]=='U') {
}

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

  $mhdevices=SQLSelect("SELECT * FROM eq3max_devices");
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
 eq3max_devices: RFADDRESS varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DEVICETYPE varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: SERIALNUMBER varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: DEVICENAME varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: ROOMID varchar(100) NOT NULL DEFAULT ''
 eq3max_devices: FIRMWARE varchar(100) NOT NULL DEFAULT ''


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
 





}
// --------------------------------------------------------------------
	


/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDAzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/





// sudo tcpdump  ip dst 192.168.1.82 and  ip src 192.168.1.39 -w dump.cap

