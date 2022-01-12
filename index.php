<?php 
require_once __DIR__.'/vendor/autoload.php';
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

ob_start("controlOutput");

// Color Echo
function cecho($color, $text, $bgColor = "transparent"){

    $defaultColor = "0;37"; // light gray    

    $colors = [
        "black" => "0;30",
        "dGrey" => "1;30",
        "red" => "0;31",
        "lRed" => "1;31",
        "green" => "0;32",
        "lGreen" => "1;32",
        "brown" => "0;33",
        "yellow" => "1;33",
        "blue" => "0;34",
        "lBlue" => "1;34",
        "magenta" => "0;35",
        "lMagenta" => "1;35",
        "cyan" => "0;36",
        "lCyan" => "1;36",
        "lGrey" => "0;37",
        "white" => "1;37",
    ];

    $bgColors = [
        "transparent"=>"",
        "black" => ";40",
        "red" => ";41",
        "green" => ";42",
        "yellow" => ";43",
        "blue" => ";44",
        "magenta" => ";45",
        "cyan" => ";46",
        "lgray" => ";47"
    ];

    $textCode = $colors[$color] ?? $defaultColor;
    $bgCode = $bgColors[$bgColor] ?? '';

    echo "\e[".$textCode.$bgCode."m".$text."\e[0m";
}

// Color Echo with new Line
function cechoLn($color, $text){
    cecho ($color, $text."\n");
}

$borderColor    = "white";
$titleColor     = "cyan";
$nameColor      = "lMagenta";
$timeColor      = "dGray";
$markedColor    = "white";
$sourceColor    = "cyan";
$linkColor      = "white";


function cechoTitle($color, $title, $titleColor = null){
    if(!$titleColor)
        $titleColor = $color;

    $t1begin    = "                   ┌─";
    $t1space    = "─";
    $t1end      = "─┐                   ";
    $t2begin    = "┌──────────────────┤  ";
    $t2end      = " ├──────────────────┐";
    $t3begin    = "│                  └─";
    $t3space    = "─";
    $t3end      = "─┘                  │";

    cecho($color, $t1begin);
    cecho($color, str_repeat($t1space, mb_strlen($title)+1));
    cechoLn($color, $t1end);
    
    cecho($color, $t2begin);
    cecho($titleColor, $title);
    cechoLn($color, $t2end);
    
    cecho($color, $t3begin);
    cecho($color, str_repeat($t3space, mb_strlen($title)+1));
    cechoLn($color, $t3end);    
}

function cechoLineFor($color, $title){
    $end1 = "└────────────────────";
    $end2 = "────────────────────┘";
    $space= "─";

    cecho($color, $end1);
    cecho($color, str_repeat($space, mb_strlen($title)+1));
    cechoLn($color, $end2);
}




// istanbul.. 
$url="http://www.namazvakti.com/DailyRSS.php?cityID=16741";

$xmlString = file_get_contents($url);

$xml = simplexml_load_string($xmlString);

$arr = json_decode(json_encode($xml)); // convert the XML string to JSON

$title = trim($arr->channel->title);

$desc = str_replace(["\t",'<p>','<br>'],'',$arr->channel->item->description);

$lines = explode("\n",trim($desc));


foreach($lines as $i => $line)
{
    [$name, $hour, $min] = explode(":", $line);

    $names[] = trim($name);
    $times[trim($name)] = trim($hour).":".trim($min);
}

$nextName = $names[0];
$currentName = $names[count($names)-1];   // Yatsı

$time = date("H:i");
// $time = "13:24";
$currentMoment = strtotime($time);
$diff = 0;

foreach($times as $zone => $tm){    
    $moment = strtotime($tm);    
    if( $currentMoment >= $moment ) $currentName = $zone;    
}

$index = array_search($currentName, $names);

if(isset($names[$index + 1])) $nextName = $names[$index + 1];

$diff = (strtotime($times[$nextName]) - $currentMoment)/60; // saniye->dakika

$leftHours = 0;

if($diff >= 60 ) $leftHours = floor($diff/60);

$leftMinutes = $diff % 60;


// title tek haneli ise çifte tamamlayalim
if(mb_strlen($title) % 2)  $title .= " ";

$maxTitle = 72;
$empty = $maxTitle - mb_strlen($title);

$veryTop    = '┌────────────────────────────────────────────────────────────────────────┐';
$titleBegin = '│';
$titleEnd   = '│';
$underTitle = '├───────────┬───────────┬────────────┬───────────┬───────────┬───────────┤';
$nameZones   = ['│  ','    │   ','   │   ','     │  ','   │   ','   │   ','   │'];
$underZone  = '├───────────┼───────────┼────────────┼───────────┼───────────┼───────────┤';
$timeZones   = ['│   ','   │   ','   │   ','    │   ','   │   ','   │   ','   │'];
$infoBottom = '├───────────┴───────────┴────────────┴───────────┴───────────┴───────────┤';
$veryBottom = '└────────────────────────────────────────────────────────────────────────┘';


cechoLn($borderColor, $veryTop);
cecho($borderColor, $titleBegin);
cecho($titleColor, str_repeat(" ",$empty/2).$title.str_repeat(" ",$empty/2));
cechoLn($borderColor, $titleEnd);
cechoLn($borderColor, $underTitle);


foreach($nameZones as $i => $nz)
{
    cecho($borderColor, $nz);
    
    if (isset($names[$i])) {
        if($names[$i] == $currentName) cecho($markedColor, $names[$i]);
        else cecho($nameColor, $names[$i]);

    }
}

echo "\n";

cechoLn($borderColor, $underZone);

foreach($timeZones as $i => $tz){
    cecho($borderColor, $tz);
    
    if (isset($names[$i])) cecho($timeColor, $times[$names[$i]]);
}


echo "\n";
cechoLn($borderColor, $infoBottom);

cecho($borderColor, $titleBegin);
$message = "Saat: ".$time." ".$nextName." vaktine kalan ".($leftHours ? $leftHours.' saat ' : '').$leftMinutes." dk.";

// mesaj tek haneli ise çifte tamamlayalim
if(mb_strlen($message) % 2)  $message .= " ";

$empty = $maxTitle - mb_strlen($message);

cecho($linkColor, str_repeat(" ",$empty/2).$message.str_repeat(" ",$empty/2));

cechoLn($borderColor, $titleEnd);


cechoLn($borderColor, $veryBottom);
cecho($sourceColor," Kaynak: ");
cechoLn($linkColor, "https://namazvakti.com");


echo "\n\n";

function controlOutput($buffer){
    global $title;

    if(isset($_SERVER['HTTP_USER_AGENT']) && !preg_match("/curl/", $_SERVER['HTTP_USER_AGENT']))
    {
        $converter = new AnsiToHtmlConverter();
        $html = $converter->convert($buffer);

        return '<html>
        <style>
        body{
            background-color:#000;
        }
        pre{
            font-size:75%;
            font-family: "Source Code Pro", "DejaVu Sans Mono", Menlo, "Lucida Sans Typewriter", "Lucida Console", monaco, "Bitstream Vera Sans Mono", monospace;
        }            
        </style>
        <head><title>'.$title.'</title></head>
        <body>
            <pre>'.$html.'</pre>
            '.$_SERVER['HTTP_USER_AGENT'].'
        </body>
        </html>';
    }

    return $buffer;
}
