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
    'password' => 'aehrfuwj'
]);
// $client->lpush('list1', 'bar');

l("Downloading configuration...");
$config = downloadConfig();
l("Downloaded configuration with ".count($config)." items");
if (count($config)>0) {
    $client->lpush('vpngate:configs',json_encode($config));    
}
l("Finished operation");

function l($line) {
    echo date('r',time())."\t".$line."\n";
}

function downloadConfig() {    
    $response = explode("\n", file_get_contents('http://www.vpngate.net/api/iphone/'));
    $total=0;
    $countries=[];
    $count = 0;
    $ret=[];
    foreach ($response as $row) {
        // skip first two rows
        if ($count < 2) { 
            $count++;
            continue;
        }
        $connection = str_getcsv($row);
        if (!isset($connection[6])) {
            continue;
        }
        if (!array_key_exists($connection[6], $countries)) $countries[$connection[6]]=0;
        $countries[$connection[6]]++;
        $total++;

        $connection['hostname']=$connection[0];
        $connection['ip']=$connection[1];
        $connection['cc']=$connection[6];
        $connection['country']=$connection[5];
        $ret[]=$connection;
    }
    return $ret;
}