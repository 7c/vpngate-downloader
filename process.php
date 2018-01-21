<?php
require_once __DIR__."/vendor/autoload.php";

if (count($argv)==1) {
    l("Need redis-server ip as argument");
    exit(1);
}

$redisip=$argv[1];
if (!filter_var($redisip,FILTER_VALIDATE_IP)) {
    l("Invalid ip $redisip");
    exit;
}


$client = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => $redisip,
    'port'   => 6379,
]);



$lenght=$client->llen("vpngate:configs");
l("Total ".$lenght." vpngate configs found");

l("Downloading configurations..");
$cstats=[];
$processed=[];
$total_configs=0;

function getConfig($index) {
    global $cstats,$client,$processed,$total_configs;
    $configs = $client->lrange("vpngate:configs",$index,$index);
    foreach($configs as $config) {
        if ($json=json_decode($config,true)) {
            foreach($json as $row) {
                $ip=$row['ip'];
                if (array_key_exists($ip,$processed)) continue;
                $processed[$ip]='yes';
                $total_configs++;
                unset($row[14]);
                // print_r($row);
                $cc=$row['cc'];
                if (!array_key_exists($cc,$cstats)) $cstats[$cc]=0;
                $cstats[$cc]++;
            }
            
        }
        
    }
    
}


// l("Downloaded ".count($configs)." configs");
for($i=0;$i<$lenght;$i++) getConfig($i);

asort($cstats);
print_r($cstats);
l("Total ".$total_configs." different ips found from ".count($cstats)." countries");
function l($line) {
    echo date('r',time())."\t".$line."\n";
}
