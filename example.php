<?php

include  'vakifBank.php';

$setting = [
    'init'=>'prod',
    'HostMerchantId'=> 'xxxxxxxxx',
    'MerchantPassword'=> 'xxxxxx',
    'HostTerminalId'=> 'xxxxxx',
    'SuccessURL'=>'https://example.com/callback.php',
    'FailureURL'=>'https://example.com/callback.php',

];

$vakifBank = new vakifBank($setting);

$orderID = time().rand(0,999);
$amount = number_format('100',2);
$amountCode = 949;
$OrderDescription = '#'.$orderID.'Order';

$paymentData = [
    'TransactionId' => $orderID,
    'OrderId' => $orderID,
    'Amount' => $amount,
    'AmountCode' => $amountCode,
    'OrderDescription' => $OrderDescription,
];


/*** Ödeme Başlatma **/
$paymentResult = $vakifBank->payment($paymentData);


print_r($paymentResult);


/*** Ödeme Callback **/
$callbackData = [
    'Rc' => $_GET['Rc'],
    'Message' => $_GET['Message'],
    'TransactionId' => $_GET['TransactionId'],
    'PaymentToken' => $_GET['PaymentToken'],

];

$callbackResult = $vakifBank->callback($callbackData);

print_r($callbackResult);


if (isset($callbackResult['Rc']) && $callbackResult['Rc'] == '0000') {

/** Ödeme Başarılı **/

}else{

/** Ödeme Hata **/

}
