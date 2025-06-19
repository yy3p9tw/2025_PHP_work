<?php
function dd($data)
{
    echo "<pre>";
    print_r($data);
    // var_dump($data);
    echo "</pre>";
}

$input = $_GET;

// $input = [
//     'name' => 'test',
//     'mobile' => '0911',
// ];

$input['rank'] = 'A';
$input['msg'] = 'ok';
// dd($input);

echo json_encode($input);
