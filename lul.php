<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config.inc.php';

if (!isset($argv[1])) {
  echo 'usage: lul.php username [list_name]' . PHP_EOL;
  exit();
}

$url            = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$user_name      = $argv[1];
$query_string   = '?screen_name='.$user_name;
$request_method = 'GET';

$list = '';
if (isset($argv[2])) {
  $list = $argv[2];
  $url = 'https://api.twitter.com/1.1/lists/statuses.json';
  $query_string = '?owner_screen_name='.$user_name.'&slug='.$list;
}

$twitter = new TwitterAPIExchange($settings);
$result = $twitter->setGetfield($query_string)
    ->buildOauth($url, $request_method)
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

$newest = array_shift($result);

$storage_dir = __DIR__ . '/tweets';

if (!file_exists($storage_dir)) {
    mkdir($storage_dir, 0777, true);
}

$storage_file = $storage_dir.'/'.$user_name.'_'.$list.'.txt';

if (!file_exists($storage_file)) {
  file_put_contents($storage_file, '');
}

$last = file_get_contents($storage_file);

if ($newest->text != $last) {
  file_put_contents($storage_file, $newest->text);
  echo $newest->user->name . ' says ' . $newest->text . PHP_EOL;
}
