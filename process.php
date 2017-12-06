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
$configs = $client->lrange("vpngate:configs",0,0);
l("Downloaded ".count($configs)." configs");

$cstats=[];
foreach($configs as $config) {
    if ($json=json_decode($config,true)) {
        foreach($json as $row) {
            unset($row[14]);
            print_r($row);
            $cc=$row['cc'];
            if (!array_key_exists($cc,$cstats)) $cstats[$cc]=0;
            $cstats[$cc]++;
        }
        
    }
    
}

asort($cstats);
print_r($cstats);

function l($line) {
    echo date('r',time())."\t".$line."\n";
}
