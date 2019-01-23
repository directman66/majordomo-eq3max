<?php
// This code base for this class is taken from http://www.mega-nas.de/max/
// Thanks Mega!!!!
// I just took the code and isolated the code from the html and created this class.
// Furthermore I did some re-writing of the code, to get a better understanding. 
// Solved some issues I ran into.

// Some excellent documentation about the eq3 max protocol:
// https://github.com/Bouni/max-cube-protocol 
// http://www.domoticaforum.eu/viewtopic.php?f=66&t=6654

// This php script can not be executed as a normal php script. But "eq3read.php" and "eq3settemp" are
// examples that use this class.
// This class can read your eq3 max information. Example in "eq3read.php"
// Also this class can set the temperature of a certain thermostat. Example in "eq3settemp.php"

// It can read the cube info and the info of the thermostats.
// As I only bought the cube and 4 thermostats, I do not have Wall mounted thermostat and Shutter contact. 
// Therefore this scripts does not read info about devices I have not bought. I simply cannot test the code.

// It can set the temperature for a specific thermostat. It cannot set the temperature for all thermostats in one room.
// I simply set the temperature for each thermostat in a room, by calling the script several time, for each thermostat once.
// It can set the temperature and the mode (manual or auto). If you do not supply the mode, it will take the actuale mode  
// of the thermostat. Only modes manual or auto are supported with this class. I simply have no use for other modes.

// Use this call at your own risk. I have tested for my own purposes and I think no harm can be done, but I cannot prove 
// nor can I garantee. It is not supported. Not by me, not by Eq3. This is no official code. I just share it with you, as 
// you might like to use it.

Class Eq3Class 
{
  
  var $fp;
  var $roomcount = 0;
  var $devccount = 0;
  var $pos=0;
  var $buffarr;
  var $bufffinished = 0;
  var $retarr = array();

  
  function OpenSock($cubehost,$cubeport) {
  	
  	$this->retarr["err"]="";
    $this->buffarr = "";
    $this->fp = @fsockopen($cubehost, $cubeport, $errno, $errstr, 5);

    if (!$this->fp){
      if ($errno == 111) {
	    $this->retarr["err"][]= "Local software is running - ". $errstr;
      } elseif ($errno == 113) {
  	    $this->retarr["err"][]= "No Connection - ".$errstr;
      }  else {
  	    $this->retarr["err"][]= $errno . "Connection Error - ".$errstr;
      }
    } else {
      socket_set_blocking($this->fp,false);
      sleep(1);
      $this->GetBuff("L:");
    }
    return $this->retarr;
  }
  
  
  function GetValues($msgtype) {
  	$this->retarr="";
  	
  	
    foreach ($this->buffarr as $v) {
      $type =substr($v,0,2);
      if (($msgtype=="C:") or ($msgtype=="L:")) {
        if ($type == "M:") {
          $this->Eq3typeM($msgtype,$v);
        }		
  	  }
      if (($msgtype=="all") or ($msgtype=="L:") or ($msgtype==$type)) {
        if ($type == "H:"){
		  $this->Eq3typeH($v);
        }
        if ($type == "M:") {
          $this->Eq3typeM($msgtype,$v);
        }
        if ($type == "C:"){
		  $this->Eq3typeC($v);
        }
        if ($type == "L:") {
          $this->Eq3typeL($v);
        }
      }
    }
    return $this->retarr;  
  }
  
  
  function SetTemp($rfadress,$roomid, $imode,$temp){

//echo $rfadress;
//echo "-";
//echo $roomid;
//echo "-";
//echo $imode;
//echo "-";
//echo $temp;
//echo "<br>";  	
  	$this->retarr["err"]= "";
  	//$command = "00 04 40 00 00 00 00 FE 30 01 A8 8B 8B 1F";
  	
  	$mode=$imode;
    switch ($mode){
      case "auto": $mode = '00'; break;
      case "manu": $mode = '01'; break;
      //case "vacation": $mode = '10'; break;
      //case "boost": $mode = '11'; break;
      default: $mode = '01';
    }

    $strrem="";
    $pos = strpos($temp, ".");
    if ($pos !== false) {
      $strrem = substr($temp,$pos);
      $temp = substr($temp,0,$pos);
    }
    if ($strrem==".0") {
    	$strrem="";
    }
    if ( (!ctype_digit($temp)) or ( ($strrem<>"") and ($strrem<>".5") ) ) {
  	   $this->retarr["err"][]= "The temperature value is not a valid number. Only halve or whole digits allowed . For example 17 or 17.0 or 17.5";   
    }
    $temp=$temp . $strrem;
    if ($this->retarr["err"]=="") {	
      if (  ($temp>30) or ($temp<0) ) {
  	   $this->retarr["err"][]= "The temperature should be between 0 and 30";   
      } else {
      	if ($temp<5) {
      		$temp=4;
      	}
      }
    }
    if (strlen($rfadress)<>6) {
  	  $this->retarr["err"][]= "The rfaddress is not a (hexadecimal) 6 character address";    	
    } else {
      if (!ctype_xdigit($rfadress)) {	
  	    $this->retarr["err"][]= "The rfaddress is not a (hexadecimal) 6 character address";         	
      }
    } 
    if (!ctype_digit($roomid))  {
  	   $this->retarr["err"][]= "The roomid is not a valid digit.";   
    } else {
      if (  ($roomid>99) or ($roomid<0) ) {
  	    $this->retarr["err"][]= "Only two digits allowed for roomid";
      }  	
    }
    

    $deg = strtoupper(dechex(bindec($mode . str_pad(decbin($temp*2),6,"0",STR_PAD_LEFT) )));
    //$command = "00 04 40 00 00 00 ".strtoupper($rfadress). str_pad($roomid,2,"0",STR_PAD_LEFT) . $deg."";
    $command = "00 00 40 00 00 00 ".strtoupper($rfadress). str_pad($roomid,2,"0",STR_PAD_LEFT) . $deg."";
    $send = "s:".$this->hex_to_base64(str_replace(" ","",$command))."\r\n";
    //$send = "s:AARAAAAAARf1BHI=\r\n";


    if ($this->retarr["err"]=="") {
//$log = date('H:i:s') . " Rfaddress:" . $rfadress . " mode:" . $imode . $mode . " temperature:" . $temp . " command:" . $command . "\n";
//$handle = fopen('log/weblog_'.date('Y-m-d').'.txt','a');
//fputs($handle,$log);
//fclose($handle); 
      fputs($this->fp,$send);
      $this->GetBuff("S:");
      $typeS=false;
      foreach ($this->buffarr as $v) {
        $type =substr($v,0,2);
        if ($type == "S:"){
      	  $typeS=true;
          $arr2 = explode(',',substr($v,2,strlen($v)));
          if ($arr2["1"]=="1") {
  	        $this->retarr["err"][]= "Command to set temperature executed with error";          	
          }
        }
      }
      if ($typeS==false) {
  	    $this->retarr["err"][]= "Command to set temperature could not be executed";    	
      }
    }        
    return $this->retarr; 
  }
  
  
  function GetBuff($lastmsgtype) {
  	$finished=0;
    $jetzt = time();
    $this->buffarr = "";
    while (!feof($this->fp) && time() < $jetzt+20 && $finished == 0) {
      $line = fgets($this->fp);
      if (strpos($line,$lastmsgtype) !== false) $finished = 1;
      //if ($line != "")  $this->buff .= $line."\n";
      if ($line != "")  $this->buffarr[]=substr($line,0,-1);
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
  
  
  function CloseSock() {
    fclose($this->fp);  	
  }
  
  
  function Eq3typeH($v){
  
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
  }

  function Eq3typeM($msgtype, $v) {
  
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
        $this->retarr["devc"][$j]["mRFAdress"] = $this->strpaddechex2str($str, 3,2);
        $this->retarr["devc"][$j]["mSerialNumber"] = $this->substr2str($str,10);
        $this->retarr["devc"][$j]["mNameLength"] = $this->ord2str($str, 1);
        $this->retarr["devc"][$j]["mDeviceName"] = $this->substr2str($str,$this->retarr["devc"][$j]["mNameLength"]);
        $this->retarr["devc"][$j]["mRoomID"] = $this->dechex2str($str, 1);
      }
    //}
	
  }

  
  function Eq3typeC($v) {

    $arr2 = explode(',',$v);
    $str = base64_decode($arr2[1]);

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

    switch($hilf["DeviceType"]){
      case "0":
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
      $this->retarr["cube"]["c?4"]=$this->dechex2str($str,8);
      $this->retarr["cube"]["c?5"]=$this->dechex2str($str,21);      
      $this->retarr["cube"]["c?6"]=$this->dechex2str($str,4);
      $this->retarr["cube"]["c?7"]=$this->dechex2str($str,8);
      $this->retarr["cube"]["c?8"]=$this->dechex2str($str,21);
      $this->retarr["cube"]["cPortalURL"]=$this->substr2str($str,36);      
      $this->retarr["cube"]["c?9"]=$this->dechex2str($str,60);
      $this->retarr["cube"]["c?A"]=$this->dechex2str($str,33);
      $this->retarr["cube"]["c?B"]=$this->substr2str($str,3);
      $this->retarr["cube"]["c?C"]=$this->dechex2str($str,9);
      $this->retarr["cube"]["c?D"]=$this->substr2str($str,4);
      $this->retarr["cube"]["c?E"]=$this->dechex2str($str,9);
    break;
     
    case "1":
   	  break;
   	 
    case "2":
      // Thermostat
      $key = array_search($hilf["RFAdress"], array_column($this->retarr["devc"], 'mRFAdress'));
      if ($key===false) {
      	$key = count($this->retarr["devc"]) +1;
      } else {
        $key = $key + 1;
      }
        
      $this->retarr["devc"][$key]["c?DataLength"] = $hilf["?1"];
      $this->retarr["devc"][$key]["cRFAdress"] = $hilf["RFAdress"];
      $this->retarr["devc"][$key]["cDeviceType"] = $hilf["DeviceType"];
      $this->retarr["devc"][$key]["c?2"] = $hilf["?2"];
      $this->retarr["devc"][$key]["c?RoomId"] = $hilf["?RoomId"];
      $this->retarr["devc"][$key]["c?FirmwareVersion"] = $hilf["?FirmwareVersion"];
      $this->retarr["devc"][$key]["c?NumModus"] = $hilf["?NumModus"];
      $this->retarr["devc"][$key]["cSerialNumber"] = $hilf["SerialNumber"];
      $this->retarr["devc"][$key]["cComfortTemperature"] = ($this->ord2str($str, 1) /2) ;
      $this->retarr["devc"][$key]["cEcoTemperature"] = ($this->ord2str($str, 1) /2) ;
      $this->retarr["devc"][$key]["cMaxSetPointTemperature"] = ($this->ord2str($str, 1) /2) ;
      $this->retarr["devc"][$key]["cMinSetPointTemperature"] = ($this->ord2str($str, 1) /2) ;
      $this->retarr["devc"][$key]["cTemperatureOffse"] = ($this->ord2str($str, 1) /2) -3.5 ;
      $this->retarr["devc"][$key]["cWindowOpenTemperature"] = ($this->ord2str($str, 1) /2) ;
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
  }

  function Eq3typeL($v) {
 
    $v = substr($v,2,strlen($v));
    $str = base64_decode($v);

    $this->pos = 0;

    for($j = 1 ; $j <= $this->devccount; $j++) {
      unset($hilf);
      $hilf["ReadLength"] = $this->ord2str($str, 1);
      $hilf["RFAdress"] = $this->strpaddechex2str($str, 3, 2);  
      $hilf["?1"] = $this->dechex2str($str, 1);
      $hilf["Data1"] = $this->strpaddecbin2str($str,1,8);
      $hilf["Data2"] = $this->strpaddecbin2str($str,1,8);


      if($hilf["ReadLength"] == 11) {
        $hilf["ValvePosition"]= $this->ord2str($str, 1);
        $hilf["Temperature"] = $this->ord2str($str, 1)/2;
        $hilf["DateUntil"] = $this->strpaddecbin2str($str,2,8);
//$this->pos = $this->pos - 2;
//$hilf["DateUntil1"] = $this->strpaddecbin2str($str,1,8);
//$hilf["DateUntil2"] = $this->strpaddecbin2str($str,1,8);
        $hilf["TimeUntil"] =  $this->ord2str($str, 1);
      }

      if (!isset($this->retarr["devc"])){
      	$this->retarr["devc"]=array();
      }
      $key = array_search($hilf["RFAdress"], array_column($this->retarr["devc"], 'mRFAdress'));
      if ($key===false) {
      	$key = count($this->retarr["devc"]) +1;
      } else {
        $key = $key + 1;
      }
      
      $this->retarr["devc"][$key]["lLiveReadLength"] = $hilf["ReadLength"];
      $this->retarr["devc"][$key]["lLiveRFAdress"] = $hilf["RFAdress"];
      $this->retarr["devc"][$key]["lLive?1"] = $hilf["?1"];
      if ($hilf["ReadLength"] == 11) {
        $this->retarr["devc"][$key]["lValvePosition"] = $hilf["ValvePosition"];
        $this->retarr["devc"][$key]["lTemperature"] = $hilf["Temperature"];
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
  
 ?>