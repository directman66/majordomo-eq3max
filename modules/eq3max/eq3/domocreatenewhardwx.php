<?php

// This script creates virtual devices in Domoticz. With the these devices you can set temperature of your Eq3 Max thermostats
// When you have install new Eq3 Max thermostats you can run this script again and Domoticz devices will be added.
// For each Eq3 Max this script will create:
// - Thermostat (subtype Setpoint). This will show the temperature and will be able to set the temperature.
// - Text device. This Domoticz device will only show info like temperature, modus (auto or manual) of the thermostat,
//                battery status.

// The Domoticz devices created will have the name of the thermostat you configured in the Eq3 Max configuration. Also
// the rf-address of the thermostat will be in the name. Do not remove this from the name of domoticz device. It is needed
// for the scripts to run properly!!!!
 
// Again: do not change the name of the domoticz devices created.!!!!

// Also the first time this script is run a virtuale hardware is created and one device. If you change the name of one of
// these, they will be recreated once you run this script again.



// example to call this page
// http://127.0.0.1/eq3/domocreatenewhardw.php

echo "<pre>";

require_once("appsettings.php");
require_once("eq3class.php");
$eq3 = new Eq3Class;
$eq3arr = array();
$hid=-1 ;

$bolurlok=true;


if ($bolurlok==true) {
  settemp();
}
  sleep(5);
  $strurlpad = "http://". $_SERVER['SERVER_NAME'] . dirname("" . $_SERVER['PHP_SELF']);


function settemp() {
  global $cubehost;
  global $cubeport;
  global $domoticzhost;
  global $domoticzport;
  global $eq3;
  global $eq3arr;

  $parsed_json = getdomoinfo("type=devices&filter=utility&used=true");

 
  $eq3arr = $eq3->OpenSock($cubehost,$cubeport);
  $eq3arr = $eq3->GetValues("all");
  
  $strmm =  date('i', time() - date('Z'));
  
  $eq3genalert=true;
  if (isset($eq3arr["devc"])) {
  	$eq3genalert=false;
    foreach ($eq3arr["devc"] as $devc) {
      $bolthermo = false;
      $bolgeneral = false;

      foreach ($parsed_json["result"] as $jsonelem) {
  	    if ($jsonelem["HardwareTypeVal"]=="15") {
  		  if (substr($jsonelem["Name"],0,strlen($devc["lLiveRFAdress"]))==$devc["lLiveRFAdress"] )  {
  		    if ( ($jsonelem["Type"]=="General") and ($jsonelem["SubType"]=="Text") ) {
              $bolgeneral=true;
  		    }
  		    if ( ($jsonelem["Type"]=="Thermostat") and ($jsonelem["SubType"]=="SetPoint") ) {
	          $bolthermo  = true ;
  		    }
  		  }
  		}
  	    if (substr($jsonelem["Name"],0,strlen("Eq3GeneralAlert"))=="Eq3GeneralAlert" )  {
  	      if ( ($jsonelem["Type"]=="General") and ($jsonelem["SubType"]=="Alert") ) {
             $eq3genalert=true;
  		  }
  	    }
  	  }

  	  if ($bolthermo==false) {
        createthermo($devc["lLiveRFAdress"],"x",$devc["mDeviceName"]);
  	  }
      if ($bolgeneral==false) {
       creategeneral($devc["lLiveRFAdress"],"x",$devc["mDeviceName"]);
  	  }
    }
  }
  if ($eq3genalert==false) {
    $hid = gethid();
  	$strcmd="type=createvirtualsensor&idx=" . $hid . "&sensorname=Eq3GeneralAlert&sensortype=7";
    $parsed_json=getdomoinfo($strcmd);  	
  }
 
  $eq3->CloseSock();

  echo "<br>";
  echo "Done";
  echo "<br>";
  print_r($parsed_json);
  print_r($eq3arr["err"]);
  print_r($eq3arr["devc"]);
}

function createthermo($rfaddress,$room,$thermo) {
  $hid = gethid();
  $strcmd="type=createvirtualsensor&idx=" . $hid . "&sensorname=" . urlencode($rfaddress . " " . $thermo) . "&sensortype=8";
  $parsed_json=getdomoinfo($strcmd);
  print_r($parsed_json);
}

function creategeneral($rfaddress,$room,$thermo) {
  $hid = gethid();
  $strcmd="type=createvirtualsensor&idx=" . $hid . "&sensorname=" . urlencode($rfaddress . " " . $thermo . " txt") . "&sensortype=5";
  $parsed_json=getdomoinfo($strcmd);
  print_r($parsed_json);
}

function gethid () {
  global $hid;

  if ($hid<0) {
    $hid=gethidsearch();
  }
  if ($hid<0) {
  	$strcmd="type=command&param=addhardware&htype=15&port=1&name=Eq3Virtual&enabled=true";
    $parsed_json=getdomoinfo($strcmd);
    print_r($parsed_json);
    $hid=gethidsearch();
  } 
  return $hid;
}

function gethidsearch () {
  global $hid;
  
  $parsed_json = getdomoinfo("type=hardware");
  foreach ($parsed_json["result"] as $jsonelem) {
   if ($jsonelem["Name"]=="Eq3Virtual") {
        $hid = $jsonelem["idx"];  		  
  	}
  }
  return $hid;
}


function getdomoinfo ($strcmd) {
  global $domoticzhost;
  global $domoticzport;
  
  $url="http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?" . $strcmd;
  $json_string = file_get_contents($url);
  $parsed_json = json_decode($json_string, true);
  return $parsed_json;
}

echo "</pre>";

?>