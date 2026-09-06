<?php
$env = file_get_contents(__DIR__ . '/.env');
preg_match_all('/OPENAI_API_KEY=(.*)/', $env, $matches);
$apiKey = '';
foreach ($matches[1] as $match) {
    if (trim($match) !== '') $apiKey = trim($match);
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/models');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $apiKey));
$res = curl_exec($ch);
curl_close($ch);
$data = json_decode($res, true);
if(isset($data['data'])) {
    foreach($data['data'] as $model) {
        if (strpos($model['id'], 'gpt') !== false) {
            echo $model['id'] . PHP_EOL;
        }
    }
} else {
    print_r($data);
}
