<?php
// With this script you can set the temperature for a thermostat. You need to know the rf-address of the 
// thermostat you want to set. If you do not know what the rf-address is, you can run the scrpt "eq3read".

// example to call this page
// http://127.0.0.1/eq3/eq3settemp.php?rfaddress=08d7e0&mode=manu&temp=21
echo "<pre>";

require_once("appsettings.php");
require_once("eq3class.php");


$bolurlok=true;
if ( !isset($_GET['rfaddress']) ) {
  echo "Variable rfadress not in url string";
  $bolurlok=false;	
}
if ( !isset($_GET['temp']) ) {
  echo "Variable temp (temperature) not in url string";
  $bolurlok=false;	
}


if ($bolurlok==true) {
  settemp();
}

function settemp() {
  global $cubehost;
  global $cubeport;
  $eq3arr = array();
  
  
  $eq3 = new Eq3Class;
  $rfaddress = $_GET['rfaddress'];
  $mode="";
  if ( isset($_GET['mode']) ) {
    $mode = $_GET['mode'];
  }
  $temp = $_GET['temp'];

  $eq3arr = $eq3->OpenSock($cubehost,$cubeport);
  $eq3arr = $eq3->GetValues("L:");  

  $key = array_search($rfaddress, array_column($eq3arr["devc"], 'lLiveRFAdress'));
  if ($key===false) {
  } else {
  	$key=$key+1;
  	if ( ($eq3arr["devc"][$key]["lMode"]=="auto") or ($eq3arr["devc"][$key]["lMode"]=="manu") ) {
  	  if ($mode=="") {
  	  	$mode=$eq3arr["devc"][$key]["lMode"];
  	  }
  	  $roomid=$eq3arr["devc"][$key]["mRoomID"];
      $eq3arr = $eq3->SetTemp($rfaddress,$roomid, $mode,$temp);
  	}
  }

  $eq3->CloseSock();

  echo "<br>";
  echo "Done";
  echo "<br>";  
  
  print_r($eq3arr);
}


echo "</pre>";
?>