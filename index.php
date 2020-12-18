<?php

require 'Payhesap.php'; 

$payhesap = new Payhesap();

$payhesap->set_config([
    'hash'         => '', # Payhesap Hash
    'callback_url' => 'http://127.0.0.1/CallBack.php', # CallBack Url
    'success_url'  => 'http://127.0.0.1/CallBack.php', # Success Url
    'fail_url'     => 'http://127.0.0.1/CallBack.php' # Failed Url
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


if ($payhesap->get_error() != null) {
    echo $payhesap->get_error();
} else {
    header("Location: " . $payhesap->init());
}