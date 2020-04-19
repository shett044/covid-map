<?php
require("db.php");

// Gets data from URL parameters.
if(isset($_GET['add_location'])) {
    add_location();
}
if(isset($_GET['confirm_location'])) {
    confirm_location();
}
if(isset($_GET['get_confirmed_locations'])) {
    get_confirmed_locations();
}

if(isset($_GET['automate_confirm_location'])) {
    automate_confirm_location();
}



function add_location(){
    $con=mysqli_connect ("sql101.epizy.com","epiz_25484260", "xiJMzzwuqR","epiz_25484260_covid123");
    if (!$con) {
        die('Not connected : ' . mysqli_connect_error());
    }
    $lat = $_GET['lat'];
    $lng = $_GET['lng'];
    $description =$_GET['description'];
    $date = date('Y-m-d h:i:s', time());
    // Inserts new row with place data.
    $query = sprintf("INSERT INTO mumbai_locations " .
        " (Id, lat, lng, base_type, base64, issue_color, address, reported_date, is_verified_BMC) " .
        " VALUES (NULL, '%s', '%s', NULL,NULL,'Purple','%s', '%s',0);",
        mysqli_real_escape_string($con,$lat),
        mysqli_real_escape_string($con,$lng),
        mysqli_real_escape_string($con,$description),
        mysqli_real_escape_string($con,$date)
        );

    $result = mysqli_query($con,$query);
    echo"Inserted Successfully";
    if (!$result) {
        die('Invalid query: ' . mysqli_error($con));
    }
}
function confirm_location(){
    $con=mysqli_connect ("sql101.epizy.com","epiz_25484260", "xiJMzzwuqR","epiz_25484260_covid123");
    if (!$con) {
        die('Not connected : ' . mysqli_connect_error());
    }
    $id =$_GET['id'];
    $confirmed =$_GET['confirmed'];
    // update location with confirm if admin confirm.
    $query = "update locations set location_status = $confirmed WHERE id = $id ";
    $result = mysqli_query($con,$query);
    echo "Inserted Successfully";
    if (!$result) {
        die('Invalid query: ' . mysqli_error($con));
    }
}


/**
 * Encode array from latin1 to utf8 recursively
 * @param $dat
 * @return array|string
 */
function convert_from_latin1_to_utf8_recursively($dat)
   {
      if (is_string($dat)) {
         return utf8_encode($dat);
      } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = convert_from_latin1_to_utf8_recursively($d);

         return $ret;
      } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = convert_from_latin1_to_utf8_recursively($d);

         return $dat;
      } else {
         return $dat;
      }
   }


function get_confirmed_locations(){
    try{
    $con=mysqli_connect ("sql101.epizy.com","epiz_25484260", "xiJMzzwuqR","epiz_25484260_covid123");
    if (!$con) {
        die('Not connected : ' . mysqli_connect_error());
    }
    // update location with location_status if admin location_status.
    $sqldata = mysqli_query($con,"
SELECT Id,Lat, lng, address, reported_date, issue_color, is_verified_BMC
from mumbai_locations;
  ");
    $rows = array();

    while($r = mysqli_fetch_assoc($sqldata)) {
        $rows[] = $r;

    }
    $indexed = array_map('array_values', $rows);
    // echo is_array($indexed);
    $indexed = convert_from_latin1_to_utf8_recursively($indexed);
    //  $array = array_filter($indexed);

    echo json_encode($indexed);
    if (!$rows) {
        return null;
    }
    }
    catch (Exception $e) {
    echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
}

//
// function automate_confirm_location(){
//     $con=mysqli_connect ("sql101.epizy.com","epiz_25484260", "xiJMzzwuqR","epiz_25484260_covid123");
//     if (!$con) {
//         die('Not connected : ' . mysqli_connect_error());
//     }
//     // update location with confirm if admin confirm.
//     $query = "update locations set location_status = 1  WHERE location_status = 0 ";
//     $result = mysqli_query($con,$query);
//     echo "Updated Successfully";
//     if (!$result) {
//         die('Invalid query: ' . mysqli_error($con));
//     }
// }

?>