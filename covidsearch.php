<?php
function cscsdgettablename(){
  global $wpdb;
  return $wpdb->prefix . "covidstatsCSCList";
}


function cscsdgetdatabasefromjson(){
  $response = wp_remote_get( 'http://moduloinfo.ca/covid/graphs/lastmodified.txt' );
  $last_modified = wp_remote_retrieve_header( $response, 'last-modified' );
  if (!get_option( 'cscsdlast_downloaded' )){
    add_option( 'cscsdlast_downloaded', $last_modified );
  }else{
    if(get_option( 'cscsdlast_downloaded' ) == $last_modified){
      return;
    }else{
    update_option( 'cscsdlast_downloaded' , $last_modified );
    }

  }
  $response = wp_remote_get( 'http://moduloinfo.ca/covid/graphs/csclist.json' );

  $body = wp_remote_retrieve_body( $response );
  $data = json_decode($body,true);
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = cscsdgettablename();
  $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    country text NOT NULL,
    state text NOT NULL,
    city text NOT NULL,
    latitude float NOT NULL,
    longitude float NOT NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
  $delete = $wpdb->query("TRUNCATE TABLE $table_name");

  foreach ($data as $location) {
    $country = $location['Country.Region'];
    $state = $location['Province.State'];
    $city = $location['Admin2'];
    $latitude = $location['Latitude'];
    $longitude = $location['Longitude'];
    $wpdb->insert(
    	$table_name,
    	array(
    		'country' => $country,
    		'state' => $state,
    		'city' => $city,
    	)
    );
  }
}


function cscsdstripparenthesis($mystring){
    $badcharacter = array("(", ")");
    $mystring = str_replace($badcharacter, "", $mystring);
    return $mystring;
}


function cscsdfixbadchars($mystring){
    $badcharacter = array(" ");
    $mystring = str_replace($badcharacter, "_", $mystring);
    return $mystring;
}


function cscsdgetcountrynames(){
  global $wpdb;
  $a=array();
  $table_name = cscsdgettablename();
  $results = $wpdb->get_results( "SELECT DISTINCT country FROM $table_name", OBJECT );
  foreach( $results as $key => $row) {
      array_push($a,$row->country);
    }
  return $a;
}


function cscsdgetcountrylist(){
  $countryarray = "var countries = [";
      foreach (cscsdgetcountrynames() as $country){
      //echo($country);
      $countryarray =  $countryarray."\"".$country."\",";
      }
  $countryarray = substr($countryarray, 0, -1)."];";
  echo($countryarray."\n");
}
function cscsdiscountry($text){
  global $wpdb;
  $table_name = cscsdgettablename();
  $results = $wpdb->get_results( "SELECT country FROM $table_name WHERE country = '$text'", OBJECT );
  if(count($results)>0){
    return true;
  }
  return false;
}

function cscsdgetstatenames(){
  global $wpdb;
  $a=array();
  $table_name = cscsdgettablename();
  $results = $wpdb->get_results( "SELECT DISTINCT state,country FROM $table_name WHERE state != 'NA'", OBJECT );
   foreach( $results as $key => $row) {
     $badcharacter = array("(", ")");
     $row->state = str_replace($badcharacter, " ", $row->state);
     $row->country = str_replace($badcharacter, " ", $row->country);
     array_push($a,$row->state."(".$row->country.")");
     }
  return $a;
}


function cscsdgetstatelist(){
  $statearray = "var states = [";
  foreach (cscsdgetstatenames() as $state) {
  $statearray =  $statearray."\"".$state."\",";
  }
  $statearray = substr($statearray, 0, -1)."];";
  echo($statearray."\n");
}

function cscsdisstate($text){
  global $wpdb;
  $table_name = cscsdgettablename();
  $text = substr($text, 0, strpos($text, "("));
  $results = $wpdb->get_results( "SELECT state FROM $table_name WHERE state = '$text'", OBJECT );
  if(count($results)>0){
    return true;
  }
  return false;
}

function cscsdgetcitynames(){
global $wpdb;
$a=array();
$table_name = cscsdgettablename();
$sql = "SELECT DISTINCT city, state FROM $table_name WHERE city != 'NA'";
$results = $wpdb->get_results( $sql, OBJECT );

foreach( $results as $key => $row) {
  $badcharacter = array("(", ")");
  $row->city = str_replace($badcharacter, " ", $row->city);
  $row->state = str_replace($badcharacter, " ", $row->state);
  array_push($a,$row->city."(".$row->state.")");
  }
  return $a;
}


function cscsdgetcitylist(){
  $cityarray = "var citys = [";
  foreach (cscsdgetcitynames() as $city) {
    $cityarray =  $cityarray."\"".$city."\",";
  }
  $cityarray = substr($cityarray, 0, -1)."];";
  echo($cityarray."\n");
}


function cscsdiscity($text){
  global $wpdb;
  $table_name = cscsdgettablename();
  $text = substr($text, 0, strpos($text, "("));
  $results = $wpdb->get_results( "SELECT city FROM $table_name WHERE city = '$text'", OBJECT );
  if(count($results)>0){
    return true;
  }
  return false;
}

function cscsdimagenocache(){
  return "?dummy=".strval(rand(0,1000000));
}


function cscsdremove_http($url) {
   $disallowed = array('http://', 'https://');
   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }
   return $url;
}

/*
function cscsddoes_url_exists($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code == 200) {
        $status = true;
    } else {
        $status = false;
    }
    curl_close($ch);
    return $status;
}*/

function cscsddoes_url_exists($url) {
  $response = wp_remote_get( $url );
  $code = wp_remote_retrieve_response_code( $response );
  if ($code == 200) {
      $status = true;
  } else {
      $status = false;
  }
return $status;
}


?>

<?php
cscsdgetdatabasefromjson();
//$cssautocomplete = plugin_dir_url( __DIR__ ) . 'covid-19-statistics-displayer/css/autocomplete.css';
//$cssadaptative = plugin_dir_url( __DIR__ ) . 'covid-19-statistics-displayer/css/adaptivetable.css';
$activepage = get_permalink();
/*
$jspath =  plugin_dir_url( __DIR__ ) . 'covid-19-statistics-displayer/js/autocomplete.js';
wp_enqueue_script(
        'csautocomplete',
        $jspath
    );*/
/*wp_enqueue_style(
        'csautocomplete',
        $cssautocomplete
);
wp_enqueue_style(
        'csadapatative',
        $cssadaptative
);*/
?>

<script type="text/javascript">
<?php
cscsdgetcountrylist();
cscsdgetstatelist();
cscsdgetcitylist();
?>
</script>



<form autocomplete="off" action="<?php echo $activepage; ?>" name="CSCform">

      <div class="Rtable Rtable--3cols Rtable--collapse Rtable--Center">
            <div class="autocomplete"><div class="Rtable-cell"><input id="accountry" type="text" name="myCountry" placeholder="Country" onchange="submitform(this.form) autocomplete='chrome-off'"></div>  </div>
           <div class="autocomplete"> <div class="Rtable-cell"><input id="acstate" type="text" name="myState" placeholder="State" onchange="submitform(this.form) autocomplete='chrome-off'"></div> </div>
           <div class="autocomplete"> <div class="Rtable-cell"><input id="accity" type="text" name="myCity" placeholder="City (US only)" onchange="submitform(this.form autocomplete='chrome-off'"></div>  </div>
      </div>

    <br><input type="submit">
</form>











<?php
if(isset($_GET['myCountry']) && !empty($_GET['myCountry'])) {
$basedir = "http://moduloinfo.ca/covid/graphs/country";
$country = sanitize_text_field($_GET['myCountry']);
if (cscsdiscountry($country)){
  //clearstatcache();
  $file = esc_url($basedir.cscsdfixbadchars($country));
  //echo($file.'Confirmed.png');
  //echo(does_url_exists($file.'Confirmed.png'));
    if (cscsddoes_url_exists($file.'Confirmed.png')){
      echo("<div class='Rtable Rtable--2cols Rtable--collapse'>");
      echo("<div class='Rtable-cell'><center><img src='".$file."Confirmed.png".cscsdimagenocache()."'></center></div>");

      echo("<div class='Rtable-cell'><center><img src='".$file."Confirmeddiff.png".cscsdimagenocache()."'></center></div>");

      echo("<div class='Rtable-cell'><center><img src='".$file."Deaths.png".cscsdimagenocache()."'></center></div>");

      echo("<div class='Rtable-cell'><center><img src='".$file."Deathsdiff.png".cscsdimagenocache()."'></center></div>");

        if (cscsddoes_url_exists($file."Recovered.png")){
        echo("<div class='Rtable-cell'><center><img src='".$file."Recovered.png".cscsdimagenocache()."'></center></div>");

        echo("<div class='Rtable-cell'><center><img src='".$file."Recovereddiff.png".cscsdimagenocache()."'></center></div>");
        }
      echo("</div>");
      }else{
        echo("<br /><h2>No prediction available for $country now come back a few day later</h2>");
      }
  } else {
    echo("<br /><h2>Please select a country by using the autocomplete function.</h2>");
  }
}


if(isset($_GET['myState']) && !empty($_GET['myState'])) {
$basedir = "http://moduloinfo.ca/covid/graphs/state";
$state = sanitize_text_field($_GET['myState']);
if (cscsdisstate($state)){
  // Split state and country and remove  parenthesis
  $parenthesis = strpos($state, "(");
  $country = cscsdstripparenthesis(substr($state,$parenthesis));
  $state = substr($state,0,$parenthesis);
  $file = esc_url($basedir.cscsdfixbadchars($state));
  if (cscsddoes_url_exists($file."Confirmed.png")) {
    echo("<div class='Rtable Rtable--2cols Rtable--collapse'>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Confirmed.png".cscsdimagenocache()."'></center></div>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Confirmeddiff.png".cscsdimagenocache()."'></center></div>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Deaths.png".cscsdimagenocache()."'></center></div>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Deathsdiff.png".cscsdimagenocache()."'></center></div>");
    if (cscsddoes_url_exists($file."Recovered.png")){
      echo("<div class='Rtable-cell'><center><img src='".$file."Recovered.png".cscsdimagenocache()."'></center></div>");
      echo("<div class='Rtable-cell'><center><img src='".$file."Recovereddiff.png".cscsdimagenocache()."'></center></div>");
    }
    echo("</div>");
    }else
    {
    echo("<br /><h2>No prediction available for $state now come back a few day later</h2>");
    }
  }else {
    echo("<br /><h2>Please select a state by using the autocomplete function.</h2>");
  }
}

if(isset($_GET['myCity']) && !empty($_GET['myCity'])) {
$basedir = "http://moduloinfo.ca/covid/graphs/city";
$city = sanitize_text_field($_GET['myCity']);
if (cscsdiscity($city)){
  //echo($state);
  // Split state and country and remove  parenthesis
  $parenthesis = strpos($city, "(");
  $state = cscsdstripparenthesis(substr($city,$parenthesis));
  $city = substr($city,0,$parenthesis);
  $file = esc_url($basedir.cscsdfixbadchars($city)."state".cscsdfixbadchars($state));
  //echo('graphs/city'.fixbadchars($city)."state".fixbadchars($state).'Confirmed.png');
    if (cscsddoes_url_exists($file.'Confirmed.png')){
    //echo($country."<br />".$state);
    echo("<div class='Rtable Rtable--2cols Rtable--collapse'>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Confirmed.png".cscsdimagenocache()."'></center></div>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Confirmeddiff.png".cscsdimagenocache()."'></center></div>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Deaths.png".cscsdimagenocache()."'></center></div>");
    echo("<div class='Rtable-cell'><center><img src='".$file."Deathsdiff.png".cscsdimagenocache()."'></center></div>");
    if (cscsddoes_url_exists($file."Recovered.png")){
      echo("<div class='Rtable-cell'><center><img src='".$file."Recovered.png".cscsdimagenocache()."'></center></div>");
      echo("<div class='Rtable-cell'><center><img src='".$file."Recovereddiff.png".cscsdimagenocache()."'></center></div>");
    }
    echo("</div>");
    } else {
       echo("<br /><h2>No prediction available for $city now come back a few day later</h2>");
    }
  } else {
    echo("<br /><h2>Please select a city by using the autocomplete function.</h2>");
  }

}
?>
