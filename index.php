<?php

require 'Payhesap.php'; 

$payhesap = new Payhesap();

$payhesap->set_config([
    'hash'         => '', # Payhesap Hash
    'callback_url' => 'http://127.0.0.1/CallBack.php', # CallBack Url
    'success_url'  => 'http://127.0.0.1/Success.php', # Success Url
    'fail_url'     => 'http://127.0.0.1/Failed.php' # Failed Url
]);

$payhesap->set_order_id('1');
$payhesap->set_price('1');

$payhesap->set_buyer([
    'name'    => "", # Buyer Fullname
    'email'   => "", # Buyer Email
    'phone'   => "", # Buyer Phone
    'city'    => "", # Buyer City
    'state'   => "", # Buyer State
    'address' => "" # Buyer Address
]);


$init = $payhesap->init();

if ($init == null) {
    print_r($payhesap->get_error());
} else {
    header("Location: " . $init);
}