<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config.inc.php';

$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';

$argument1 = $argv[1];

$getfield = '?screen_name='.$argument1;
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
$result = $twitter->setGetfield($getfield)
    ->buildOauth($url, $requestMethod)
    ->performRequest();

$result = json_decode($result);

if (!is_array($result)) {
    //This is probably a rate limit error, but lets make sure.
    
    if (isset($result->errors[0]) && $result->errors[0]->code == 88) {
        //Slow things down for a bit and retry
        sleep(60);
        exit();
    }
    
    //otherwise, echo the errors
    if (isset($result->errors)) {
        foreach ($result->errors as $error) {
            echo $error->message . PHP_EOL;
        }
    } else { 
        echo 'unknown error' . PHP_EOL;
    }
    
    exit();
}

$newest = array_shift($result)->text;

$storage_dir = __DIR__ . '/tweets/';

if (!file_exists($storage_dir)) {
    mkdir($storage_dir, 0777, true);
}

$storage_file = $storage_dir.$argument1.'.txt';

if (!file_exists($storage_file)) {
  file_put_contents($storage_file, '');
}

$last = file_get_contents($storage_file);

if ($newest != $last) {
  file_put_contents($storage_file, $newest);
  echo $newest . PHP_EOL;
}
