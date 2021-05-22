#!/usr/bin/php
<?php
$dir = './';
if(sizeof($argv) > 1){
    $dir = $argv[1];
}

$files = scandir($dir);
foreach($files as $file){
    if(!preg_match('/^.*\.json/i', $file)){
        continue;
    }
    echo $file."\n";
    $content = json_decode(file_get_contents($file));
    if($content){
        file_put_contents($file, json_encode($content,JSON_PRETTY_PRINT));
    }
}