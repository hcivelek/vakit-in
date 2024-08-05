<!DOCTYPE html>
<?php 
require_once "./cities.php";


$cityID = $_GET['city_id'] ?? '16741';

// $file = date("Y")."-".$cityID.".xml";
// echo $file;exit;


// istanbul.. 
// $url="http://www.namazvakti.com/DailyRSS.php?cityID=".$cityID;
$url="https://www.namazvakti.com/XML.php?cityID=".$cityID; // Yıllık rss, (sabah vakti var)

$xmlString = file_get_contents($url);

$xml = simplexml_load_string($xmlString);

$arr = json_decode(json_encode($xml)); // convert the XML string to JSON

$theDay = date("z") + 1;

$index = [
    "0" => "İmsak",
    "1" => "Sabah",
    "2" => "Güneş",
    // "3" => "işrak",
    // "4" => "kerahet",
    "5" => "Öğle",
    "6" => "İkindi",
    // "7" => "asr-ı sani",
    // "8" => "isfirar",
    "9" => "Akşam",
    // "10" => "iştibak",
    "11" => "Yatsı",
    // "12" => "işâ-i sânî",
    // "13" => "kıble saati"
];


// 00:00 01:00 02:00
$times = explode("	",$arr->prayertimes[$theDay]);
$data = [];

array_map(function($i) use ($times, $index, &$data){
    $data[$index[$i]] = $times[$i];
}, array_keys($index));
 

// $title = trim($arr->channel->title);
// $desc = str_replace(["\t",'<p>','<br>'],'',$arr->channel->item->description);
// $lines = explode("\n",trim($desc));
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Namaz Vakitleri</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>        
        <script>
            function submitForm() {
                document.getElementById("vakitForm").submit();
            }
        </script>
    </head>
    <body>

    <table class="table table-striped">
        <tr>
            <td colspan="2">
                <form action="vakit.php" method="get" id="vakitForm">
                    <select class="form-select" name="city_id" onchange="submitForm()">
                        <?php foreach($cities as $id => $city):?>
                        <option value="<?php echo $id;?>" <?php echo $cityID == $id ? 'selected' :  '';?>><?php echo $city;?></option>
                        <?php endforeach;?>
                    </select>
                </form>
            </td>
        </tr>
        <?php foreach($data as $vakit => $saat):?>
        <tr>
            <td class="text-right"><b><?php echo $vakit;?></b></td>
            <td><?php echo $saat;?></td>
        </tr>
        <?php endforeach?>
    </table>

    </body>
</html>