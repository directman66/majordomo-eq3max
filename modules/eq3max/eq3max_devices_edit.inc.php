\<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='eq3max_devices';
$sql="SELECT * FROM $table_name WHERE ID='$id'";
debmes( $sql, 'eq3max');
  $rec=SQLSelectOne($sql);
  if ($this->mode=='update') {
   $ok=1;
  // step: default
//echo $this->tab;
  if ($this->tab=='info') {
  //updating '<%LANG_TITLE%>' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
   global $ip;
   $rec['IP']=$ip;
   if ($rec['IP']=='') {
    $out['ERR_IP']=1;
    $ok=0;
   }
}

    if ($ok=1)
    SQLUpdate('eq3max_devices', $rec);

}
  // step: data
  if ($this->tab=='data') {
  }
  //UPDATING RECORD
  // step: default
  if ($this->tab=='') {
  }
  if ($this->tab=='data') {
   //dataset2
   $new_id=0;
   global $delete_id;
   if ($delete_id) {
    SQLExec("DELETE FROM eq3max_commands WHERE ID='".(int)$delete_id."'");
   }
   $properties=SQLSelect("SELECT * FROM eq3max_commands WHERE DEVICE_ID='".$rec['ID']."' ORDER BY ID");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    if ($properties[$i]['ID']==$new_id) continue;
    if ($this->mode=='update') {
        /*
      global ${'title'.$properties[$i]['ID']};
      $properties[$i]['TITLE']=trim(${'title'.$properties[$i]['ID']});
      global ${'value'.$properties[$i]['ID']};
      $properties[$i]['VALUE']=trim(${'value'.$properties[$i]['ID']});
        */
      global ${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      global ${'linked_property'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});

      global ${'linked_method'.$properties[$i]['ID']};
      $properties[$i]['LINKED_METHOD'] = trim(${'linked_method'.$properties[$i]['ID']});


      SQLUpdate('eq3max_commands', $properties[$i]);
      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];
//РЎС“Р Т‘Р В°Р В»Р ВµР Р…Р С‘Р Вµ linked
      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
      }
     }///update
//Р Т‘Р С•Р В±Р В°Р Р†Р В»Р ВµР Р…Р С‘Р Вµ linked
       if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
           addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
       }


       
       
       
   }
   $out['PROPERTIES']=$properties;   
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
