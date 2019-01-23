<?php
// This script reads the setpoint (temperature set) of the domoticz thermostat and sets this temperature in 
// the eq3 max thermostat. At the and of the script, it calls "domoupdatesensor.php" to update all eq3 devices in domoticz
// based on the actual values in the eq3 max system (cube).

// This script only operates properly if the name Domoticz devices starts with the rf-address of the thermostat. 
// Running "domocreatenewhardw.php" creates the Domoticz devices with the expected syntax in the name of the devices.

// example call this page
// http://127.0.0.1/eq3/domosettemp.php?rfaddress=08d7e0

// second example call this page
// 127.0.0.1/eq3/domosettemp.php?rfaddress=08d7e0&rfaddress2=114883
// In this example the thermostat with "rfaddress2" is set to the same temperature as "rfaddress". 
// You can use this to set the temperature on one device and then the second thermostat will follow this temperature.

echo "<pre>";

require_once("appsettings.php");
require_once("eq3class.php");
$eq3 = new Eq3Class;
$eq3arr = array();
$parsed_jsonvar = array();

$bolurlok=true;
if ( !isset($_GET['rfaddress']) ) {
  echo "Variable rfadress not in url string";
  $bolurlok=false;	
}
if ($bolurlok==true) {
  settemp();
}

function settemp() {
  global $cubehost;
  global $cubeport;
  global $domoticzhost;
  global $domoticzport;
  global $eq3;
  global $eq3arr;
  global $parsed_jsonvar;

  
  $rfaddress = $_GET['rfaddress'];

  $json_string = file_get_contents("http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?type=devices&filter=utility&used=true");
  $parsed_json = json_decode($json_string, true);
  
  $temp="";
  $msg="rfaddress not found in domoticz utility devices";
  foreach ($parsed_json["result"] as $jsonelem) {
  	if ( ($jsonelem["HardwareTypeVal"]=="15") and ($jsonelem["Type"]=="Thermostat") ) {
  		if (substr($jsonelem["Name"],0,strlen($rfaddress))==$rfaddress )  {
  		  $msg="";
  		  $temp=$jsonelem["SetPoint"];
  		  break;
  		}
  	}
  }

  if ($msg<>""){ 
  	writedomolog($rfaddress,$msg);
  } else {
    $rfaddress = $_GET['rfaddress'];
    $eq3arr = $eq3->OpenSock($cubehost,$cubeport);
    $eq3arr = $eq3->GetValues("L:");
    if ($eq3arr["cube"]["hDuty cycle"]>90) {
  	  writedomolog("Temperature not set. Duty cycle is " . $eq3arr["cube"]["hDuty cycle"] . "%");  	
    } else {
      settempperrf($rfaddress,$temp);
      if ( isset($_GET['rfaddress2']) ) {
        settempperrf($_GET['rfaddress2'],$temp);
      }
    }
    $eq3->CloseSock();
  }

  sleep(5);
  $strurlpad = "http://". $_SERVER['SERVER_NAME'] . dirname("" . $_SERVER['PHP_SELF']);
//echo $strurlpad;
  $resultstr = file_get_contents($strurlpad . "/domoupdatesensor.php?force=yes");
//echo $resultstr;
  echo "Done";
  //print_r($parsed_json);
  //print_r($eq3arr);
}

function settempperrf($rfaddress,$temp) {
  global $eq3;
  global $eq3arr;
  global $domoticzhost;
  global $domoticzport;

    $key = array_search($rfaddress, array_column($eq3arr["devc"], 'lLiveRFAdress'));
    if ($key===false) {
  	  writedomolog($rfaddress,"thermostat not found in eq3 info");
    } else {
  	  $key=$key+1;

      if ( ($temp<>"")  
      and ( ($eq3arr["devc"][$key]["lMode"]=="manu") or ($eq3arr["devc"][$key]["lMode"]=="auto") )
      and ($temp<>$eq3arr["devc"][$key]["lTemperature"])
      ) {

        $eq3arr = $eq3->SetTemp($rfaddress,$eq3arr["devc"][$key]["mRoomID"],$eq3arr["devc"][$key]["lMode"],$temp);
        if ($eq3arr["err"]<>"") {
          foreach ($eq3arr["err"] as $msg) {
      	    writedomolog($rfaddress,$msg);
          }
        } else {
      	  $msg = "Temperature set " . $temp . " in modus " . $eq3arr["devc"][$key]["lMode"];
      	  writedomolog($rfaddress,$msg);
        }
      } else {
        if ( ($eq3arr["devc"][$key]["lMode"]<>"manu") and ($eq3arr["devc"][$key]["lMode"]<>"auto") ) {
      	  writedomolog($rfaddress,"Thermostat not in manual or auto modus");
        }
      }	
    }
  
}

function writedomolog($rfaddress,$msg) {
  global $domoticzhost;
  global $domoticzport;
  
  $json_string = file_get_contents("http://" . $domoticzhost . ":" . $domoticzport . "/json.htm?type=command&param=addlogmessage&message=" . urlencode($rfaddress. ": ".$msg) );
	
}


echo "</pre>";
?>