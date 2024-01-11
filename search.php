<?
error_reporting(E_ALL);
session_start();
function GetCookies() {
    $ch = curl_init('https://kataster.skgeodesy.sk/eskn-portal/');
    $file_path = 'cookies.txt';
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $file_path);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $file_path);
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
        'accept: application/json',
        'content-type: application/json',
        'sec-ch-ua: Not/A)Brand";v="99", "Google Chrome";v="115", "Chromium";v="115"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'Upgrade-Insecure-Requests: 1'
    ));
    $r = curl_exec($ch);
    if ($r === false) {
      print_r('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);
}

function Connect($url, $post_data=false)
    {
    Global $link;

      $curl = curl_init();
      $file_path = 'cookies.txt';

      if(!$post_data) {
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: */*',
            'Accept-Language: sk-SK,sk;q=0.9,cs;q=0.8,en-US;q=0.7,en;q=0.6',
            'Connection: keep-alive',
            'Referer: https://zbgis.skgeodesy.sk/',
            'Sec-Fetch-Dest: empty',
            'Sec-Fetch-Mode: no-cors',
            'Sec-Fetch-Site: same-site',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'sec-ch-ua: "Not_A Brand";v="8", "Chromium";v="120", "Google Chrome";v="120"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
        ]);
        curl_setopt($curl, CURLOPT_COOKIE, '_ga=GA1.1.1589983301.1690892585; BIGipServerpool_eskn-kataster=413444288.20480.0000; .ESKN_RECAPTCHA='.urlencode($_SESSION["eskn"]).'; _ga_VPZ1WSV9R2=GS1.1.1704803426.36.1.1704804637.0.0.0');
      }
      else {
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POSTREDIR, 3);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_COOKIEJAR, $file_path);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $file_path);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7", "Accept-Language: sk-SK,sk;q=0.9,cs;q=0.8,en-US;q=0.7,en;q=0.6", "Cache-Control: max-age=0", "Connection: keep-alive",  "Content-Type: application/x-www-form-urlencoded", "Content-length: ".strlen($post_data), "Host: kataster.skgeodesy.sk", "Origin: https://kataster.skgeodesy.sk", "Referer: https://kataster.skgeodesy.sk/eskn-portal/search/owners", "Sec-Fetch-Dest: document", "Sec-Fetch-Mode: navigate", "Sec-Fetch-Site: same-origin", "Sec-Fetch-User: ?1", "Upgrade-Insecure-Requests: 1", "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36"]);
      }

      $response = curl_exec($curl);

      $err = curl_error($curl);

      curl_close($curl);

      if ($err) {
          $response = "cURL Error #:" . $err;
          }
      return $response;
    }
    
function CheckLVs($link) {
    Global $lvs;
    $particip = Connect($link);
    if($particip=="") return false;
    else {
        $part = json_decode($particip, true);
        foreach ($part["value"] as $item) {
            $lvs[] = $item;
        }
        if(isset($part["@odata.nextLink"])) CheckLVs($part["@odata.nextLink"]);
        return $lvs;
    }
}

function CheckParcels($link, $name) {
  Global $geo_urls, $names;
  $parcelse = Connect($link);
  $pce = json_decode($parcelse, true);
  foreach ($pce["value"] as $item) {
      $area = $item["Area"];
      echo "<li>Parcela E č. <b>".$item["NoFull"]."</b> - celkovo ".$area." m<sup>2</sup></li>";
      $geo_urls[] = $item["Id"];
      $names[] = $name;
  }
  if(isset($pce["@odata.nextLink"])) CheckParcels($pce["@odata.nextLink"], $name);
}
    
function CheckParticipants($link, $area, $name) {
    $particip = Connect($link);
    $part = json_decode($particip, true);
    foreach ($part["value"] as $item) {
        if($item["Name"]==$name) {
            $share = round(($area/$item["Denominator"])*$item["Numerator"],1);
            return $share;
        }
    }
    if(isset($part["@odata.nextLink"])) $share = CheckParticipants($part["@odata.nextLink"], $area, $name);
    return $share;
}

if(isset($_GET["new"])) {
    unset($_SESSION["surname"]);
}

if(isset($_GET["getparcel"])) {
    $parcel = Connect("https://kataster.skgeodesy.sk/eskn/rest/services/VRM/parcels_e_view/MapServer/0/query?objectIds=".$_GET["getparcel"]."&returnGeometry=true&outSR=4326&f=json&outFields=DESCRIPTIVE_AREA_OF_PARCEL,PARCEL_NUMBER");
    $json = json_decode($parcel, true);
    $rings = array();
    foreach ($json["features"][0]["geometry"]["rings"][0] as $ring) {
        $rings[] = "[".$ring[1].", ".$ring[0]."]";
    }
    $area = $json["features"][0]["attributes"]["DESCRIPTIVE_AREA_OF_PARCEL"];
    $name = $_GET["name"];
    $share = CheckParticipants('https://kataster.skgeodesy.sk/PortalODataPublic/ParcelsE('.$_GET["getparcel"].')/Kn.Participants?$filter=Type/Code%20eq%201&$select=Id,Name,ValidTo,Numerator,Denominator&$expand=OwnershipRecord($select=Order)&$orderby=OwnershipRecord/Order&$skip=0', $area, $name);
    echo '{"rings":['.implode(", ", $rings).'],"parcel":"'.$json["features"][0]["attributes"]["PARCEL_NUMBER"].'","area":"'.$json["features"][0]["attributes"]["DESCRIPTIVE_AREA_OF_PARCEL"].'","share":"'.$share.'"}';
    exit;
}

echo '
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sumarizácia pozemkov</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  </head>
  <body>
    <div class="container">
    <h1 class="text-center p-3">Sumarizácia pozemkov</h1>';
    
    if(isset($_GET["id"])) {
        $id = $_GET["id"];
        $lvs = $shares = array();
        $lvs = CheckLVs('https://kataster.skgeodesy.sk/PortalODataPublic/Participants?$filter=Subjects/any(p:%20p/Id%20eq%20'.$id.')%20and%20Municipality/Code%20eq%20'.$_SESSION["ku"].'&$select=Name&$expand=Municipality($select=Name;$expand=District($select=Name)),OwnershipRecord($select=Id,Order;$expand=Folio($select=No,Id,OwnersCount,CountOfParcelsC,CountOfParcelsE)),CadastralUnit($select=Name,Code)&$orderby=OwnershipRecord/Folio/No,OwnershipRecord/Order&$skip=0');
        if($lvs===false) echo '<div><a href="search.php?new=1" class="btn btn-primary mb-3">Nové hľadanie</a></div>
        <div class="alert alert-danger" role="alert">
          .ESKN_RECAPTCHA cookie vypršalo. Vráťte sa na úvodnú stranu a zadajte nový kód.
        </div>';
        else {
            $geo_urls = array();
            echo '
            <div><a href="search.php" class="btn btn-primary mb-3">&lt; Späť</a></div>
            <div class="row row-cols-1 row-cols-md-3 g-4">';
            foreach ($lvs as $item) {
                $cu = $item["CadastralUnit"]["Code"];
                $folio = $item["OwnershipRecord"]["Folio"]["No"];
                $name = $item["Name"];
                if($item["OwnershipRecord"]["Folio"]["OwnersCount"]<50) {
                    echo '
                      <div class="col">
                        <div class="card bg-light h-100 shadow-sm">
                          <div class="card-body">
                            <h5 class="card-title">List vlastníctva č.'.$folio.'</h5>
                            <p class="card-text">'.$name.'</p>
                            <ul>';
                              CheckParcels('https://kataster.skgeodesy.sk/PortalODataPublic/ParcelsE?$filter=FolioId%20eq%20'.$item["OwnershipRecord"]["Folio"]["Id"].'&$select=Id,No,NoFull,Area&$orderby=NoSort&$skip=0', $name);
                echo '      </ul>
                          </div>
                        </div>
                      </div>';
                }
            }
            echo '</div>';
            $map = "<div id='progressbar' style='max-width: 360px; margin: 20px auto; padding: 10px;' class='border d-none shadow-sm'>
                      <div id='warning' style='width:100%; max-width:360px; margin: 0 auto 5px auto;'>Načítavam údaje do mapy ...</div>
                      <div id='barbar' class='progress mdl-progress mdl-js-progress' style='width:100%; max-width:360px; margin:0 auto 5px auto;'>
                        <div class='progress-bar progress-bar-striped progress-bar-animated bg-info' style='width: 0%'></div>
                      </div>
                      <div id='etacont' style='width:100%; max-width: 360px; margin: 0 auto;'>
                        <div style='float: left;'><span id='perc'>0</span>%</div>
                        <div style='float: right;'><b>ETA:</b> <span id='eta'>0:00</span> m</div>
                      </div>
                    </div>
                    <script>
                      var geo_urls = [";
                        foreach ($geo_urls as $url) {
                            $map .= '\''.$url.'\', ';
                        }
                      $map .= "];
                      var names = [";
                        $shares_sum=0;
                        foreach ($names as $name_entry) {
                            $map .= '\''.$name_entry.'\', ';
                        }
                      $map .= "];
                    </script>
                    <div id='map' style='height: 1000px;'></div>";
            echo '<div class="alert alert-info mt-4 shadow-sm text-center">Celkový podiel na E-parcelách: <b id="shares-sum">'.$shares_sum.'</b> m<sup>2</sup></div>';
            echo $map;
        }
    }
    else {
        GetCookies();
        if(isset($_POST["surname"])) {
            $_SESSION["eskn"] = $_POST["captcha"];
            $_SESSION["surname"] = $_POST["surname"];
            $_SESSION["ku"] = $_POST["ku"];
            $_SESSION["name"] = $_POST["name"];
        }
        if(!isset($_SESSION["surname"])) {
        echo '
        <form method="post" action="search.php" enct>
            <div class="mb-3">
              <label for="captcha" class="form-label">.ESKN_RECAPTCHA cookie</label>
              <input type="text" class="form-control" id="captcha" name="captcha" value="'.(isset($_SESSION["eskn"]) ? $_SESSION["eskn"]:'').'">
            </div>
            <div class="mb-3">
              <label for="ku" class="form-label">Č. katastrálneho územia</label>
              <input type="text" class="form-control" id="ku" name="ku" value="509655">
            </div>
            <div class="mb-3">
              <label for="surname" class="form-label">Priezvisko</label>
              <input type="text" class="form-control" id="surname" name="surname">
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Meno</label>
              <input type="text" class="form-control" id="name" name="name">
            </div>
            <button type="submit" class="btn btn-primary mb-3">Hľadaj</button>
        </form>';
        }
        else {
            $response = Connect("https://zbgis.skgeodesy.sk/mkzbgis/api/search/kataster/".$_SESSION["ku"]."/mu/".$_SESSION["ku"]."?q=".$_SESSION["surname"]."%20".$_SESSION["name"]);
            $json = json_decode($response, true);
            
            echo '<div><a href="search.php?new=1" class="btn btn-primary mb-3">Nové hľadanie</a></div>
            <h2>Výsledky vyhľadávania:</h2>
            <ol>';
            foreach ($json["items"] as $item) {
                echo "<li><a href='search.php?id=".$item["data"]["id"]."'>".$item["data"]["text"]." (".$item["data"]["description"].")</a></li>";
            }
            echo '</ol>';
        }
    }
    
    ?>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script type='text/javascript'>   
    $(document).ready(function(){
        
      var loop = false;
      // stopping all ajax requests
      $.xhrPool = [];
      $.xhrPool.abortAll = function() {
          $(this).each(function(idx, jqXHR) {
              jqXHR.abort();
          });
          $.xhrPool = [];
      };
    
      $.ajaxSetup({
          beforeSend: function(jqXHR) {
              $.xhrPool.push(jqXHR);
          },
          complete: function(jqXHR) {
              var index = $.xhrPool.indexOf(jqXHR);
              if (index > -1) {
                  $.xhrPool.splice(index, 1);
              }
          }
      });
      
      function StartUpdate()
        {
        var map = L.map('map').setView([49.27797803679885, 19.613327195314586], 16);
        
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        L.tileLayer.wms("https://kataster.skgeodesy.sk/eskn/services/NR/kn_wms_norm/MapServer/WmsServer", {
            layers: ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15'],
            format: 'image/png',
            transparent: true,
            attribution: "© ZBGIS"
        }).addTo(map);
        
        L.tileLayer.wms("https://kataster.skgeodesy.sk/eskn/services/NR/uo_wms_norm/MapServer/WmsServer", {
            layers: '0',
            format: 'image/png',
            transparent: true,
            attribution: "© ZBGIS"
        }).addTo(map);
            
          $('#progressbar').removeClass('d-none').addClass('d-grid');

          var i=j=prog=por=addcount=updcount=delcount=0;
          var start = new Date();
    
          var poc = geo_urls.length;
          ProcessTeams(geo_urls)
          
        function ProcessTeams(teams)
          {
            //if(!loop) return;
            var url = teams[j];
            var name = names[j];
            $.ajax({
              url: 'search.php?getparcel='+url+'&name='+name,
              beforeSend: function()
                {
                $("#warning").html("Načítavam parcelu "+por+" / "+poc);
                },
              complete: function() 
                {
                // progress bar
                por++;
                prog = eval(Math.floor((por/poc)*100));
                UpdateProgress(prog);
                // ETA
                var timenow = new Date() - start;
                var eta = eval(Math.round(((poc*(Math.round(timenow/1000)))/por)-Math.round(timenow/1000)));
                var date = new Date(eta * 1000);
                var mm = date.getUTCMinutes();
                var ss = date.getSeconds();
                if(ss<10) ss="0"+ss;
                $("#eta").html(mm+":"+ss);
                if(por==poc) 
                  {
                  $("#warning").html("Načítanie parciel ukončené");
                  $('.progress-bar').removeClass("progress-bar-animated progress-bar-striped");
                  }
                else ProcessTeams(teams);
                },
              success: function(data){
                  if(data) 
                    {
                    var detail = JSON.parse(data);
                    var polygon = L.polygon(detail["rings"], {color: 'red'}).addTo(map);
                    polygon.bindTooltip("<b>"+detail["parcel"]+"</b><br>Celková plocha: "+detail["area"]+"m<sup>2</sup><br>Podiel: "+detail["share"]+"m<sup>2</sup>");
                    var shares_sum = parseFloat($("#shares-sum").html());
                    shares_sum=shares_sum+parseFloat(detail["share"]);
                    $("#shares-sum").html(shares_sum);
                    }
              }
            });
            j++;
          }
        }
    
      function UpdateProgress(value)
        {
        $('.progress-bar').css('width',value+'%');
        $('#perc').html(value);
        }
      
     <? if(isset($_GET["id"])) echo 'StartUpdate();' ?>
     
    });
    
    </script>
  </body>
</html>