<?php
// This script will update Domoticz device values based on the cube info. 
// F.e. when you physically change the temperature on your thermostat, you would like to notice this in you domoticz info.
// Be aware that if you change the thermostat by hand, or with the mobile eq3 app, this takes a couple of minutes before your 
// eq3 max cube will get this info. This is how the eq3 system and software works and is not caused by this script or 
// any other of my scripts.

// example to call this page
// http://127.0.0.1/eq3/domoupdatesensor.php
// Normally this script will execute if every 5 minutes. Doing it more frequent will use up the duty cycle which is 
// about 36 seconds per hour. This seems to be a legal limitations for 868MHz radio comms. Once the maximum duty cycle
// is reached, no communication with the cube is posible. Communications will be paused with an hour.

// example to call this page
// http://127.0.0.1/eq3/domoupdatesensor.php?force=yes
// You can execute this script with force=yes. But be a aware of the limitions of the duty cycle of the eq3 max cube.

echo "<pre>";

require_once("appsettings.php");
require_once("eq3class.php");
$eq3 = new Eq3Class;
$eq3arr = array();
$eq3alertarr = array();

// Either have sensor info updated if 
$bolurlok=false;
if ( isset($_GET['force']) ) {
  $bolurlok=true;
} else {
  //$strmm =  date('i', time() - date('Z'));
  //if ( (substr($strmm,1,1)=="0") or (substr($strmm,1,1)=="5") ) {
    $bolurlok=true;  	
  //}	
}
echo "test";
if ($bolurlok==true) {
  updatesensor();
}

function updatesensor() {
  global $cubehost;
  global $cubeport;
  global $domoticzhost;
  global $domoticzport;
  global $eq3;
  global $eq3arr;
  global $eq3alertarr;

  $json_string = file_get_contents("http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?type=devices&filter=utility&used=true");
  $parsed_json = json_decode($json_string, true);
  
  $eq3arr = $eq3->OpenSock($cubehost,$cubeport);
  $eq3arr = $eq3->GetValues("L:");
  $eq3->CloseSock();
  
  if ($eq3arr["cube"]["hDuty cycle"]>75) {
  	writedomolog("Duty cycle is " . $eq3arr["cube"]["hDuty cycle"]);  	
  }

  $strmm =  date('i', time() - date('Z'));
  if ( isset($_GET['force']) ) {
  	$strmm="99";
  }
  
  if (isset($eq3arr["devc"])) {
//print_r($eq3arr);
    foreach ($eq3arr["devc"] as $devc) {
      foreach ($parsed_json["result"] as $jsonelem) {
  	    if ($jsonelem["HardwareTypeVal"]=="15") {
  		  if (substr($jsonelem["Name"],0,strlen($devc["lLiveRFAdress"]))==$devc["lLiveRFAdress"] )  {
  		    if ( ($jsonelem["Type"]=="General") and ($jsonelem["SubType"]=="Text") ) {
  		      $txtmods = "Modus: " . $devc["lMode"];
  		      $txttemp = "Temperature: " . $devc["lTemperature"]; 
  		      $txtbatt = "Battery: " ;   
  		      if ($devc["lLowBatt"]==0) {
  		      	$txtbatt = $txtbatt . "normal";
  		      }	else {
  		      	$txtbatt = $txtbatt . "LOW !!!"; 
  		      	$eq3alertarr[] = $devc["lLiveRFAdress"] . " battery low"; 		      	
  		      }
  		      if ( ($strmm=="00") or (strpos($jsonelem["Data"],$txtmods)===false) or (strpos($jsonelem["Data"],$txttemp . " ")===false) or (strpos($jsonelem["Data"],$txtbatt)===false) ) {
  		      	domoudevice($jsonelem["idx"], $txtmods . " - " . $txttemp . " - " . $txtbatt,0 );  
  		      }		      
  		    }
  		    if ( ($jsonelem["Type"]=="Thermostat") and ($jsonelem["SubType"]=="SetPoint") ) {
  		      if ( ($strmm=="00") or ($devc["lTemperature"]<>$jsonelem["Data"]) ) {
// echo "<br>";  
//echo $jsonelem["idx"];
//echo "-";
//echo $devc["lLiveRFAdress"];
//echo "-";
//echo $jsonelem["Name"];
//  		      	echo "<br>";
  	writedomolog($devc["lLiveRFAdress"] . "-" . substr($jsonelem["Name"],0,strlen($devc["lLiveRFAdress"])) . " strmm: " . $strmm . " devctemp: ". $devc["lTemperature"] . " jsondtemp: " . $jsonelem["Data"]);  
                //$url = "http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?type=command&param=updateuservariable&vname=Eq3SetExt" . $devc["lLiveRFAdress"] . "&vtype=0&vvalue=2";
                //$json_string = file_get_contents($url );
  		        domoudevice($jsonelem["idx"], $devc["lTemperature"],0);
  		      }		
  		    }
  		  }
  		}
  	  }
    }
  }
 
  Eq3Alert($eq3alertarr, $parsed_json);

  echo "<br>";
  echo "Done";
  echo "<br>";
  //print_r($parsed_json);
  print_r($eq3arr);
}

function Eq3Alert($msgarr,$parsed_json) {
//print_r($msgarr);
//print "-----";
//print count($msgarr);
  $idx=-1;
  foreach ($parsed_json["result"] as $jsonelem) {
   if ($jsonelem["HardwareTypeVal"]=="15") {
     if (substr($jsonelem["Name"],0,strlen("Eq3GeneralAlert"))=="Eq3GeneralAlert" )  {
  	   if ( ($jsonelem["Type"]=="General") and ($jsonelem["SubType"]=="Alert") ) {
  		  domoudevice($jsonelem["idx"], "No alerts",0);
  		  $idx=$jsonelem["idx"];
  		  break;
  		}
  	  }
   }
  }
  if  ($idx>-1)  {
  	$strmsg="";
  	foreach ($msgarr as $msg) {
  	  if ($strmsg<>"") {
  	  	$strmsg = $strmsg ."<br>";
  	  }
  	  $strmsg=$strmsg . $msg;
  	}
  	if ($strmsg<>"") {
  	  domoudevice($idx, $strmsg,3);  		
  	}
  }
}

function domoudevice($idx,$svalue, $nvalue) {
  global $domoticzhost;
  global $domoticzport;

  $url = "http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?type=command&param=udevice&idx=" . $idx . "&nvalue=". $nvalue . "&svalue=" . urlencode($svalue);
  $json_string = file_get_contents($url );
//echo $url;
//echo "<br>";
}

function writedomolog($msg) {
  global $domoticzhost;
  global $domoticzport;
  
  $json_string = file_get_contents("http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?type=command&param=addlogmessage&message=" . urlencode($msg) );
	
}

echo "</pre>";
?>