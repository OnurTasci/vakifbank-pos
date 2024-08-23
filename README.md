# Vakıfbank Ortak Ödeme Sanal POS

Bu proje, Vakıfbank'ın ortak ödeme sanal POS entegrasyonu için bir PHP sınıfı sunmaktadır. Bu sınıf, Vakıfbank'ın API'sine kolayca entegre olmanızı sağlar.

## Gereksinimler

- PHP 7.2 veya üzeri
- cURL eklentisi

## Kullanım

Aşağıda, `example.php` dosyasındaki örnek kodu görebilirsiniz. Bu kod, bir ödeme işlemi yapmak için nasıl kullanılacağını gösterir.


## Ödeme Başlatma


```php

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

```


## Ödeme Callback


```php

include  'vakifBank.php';

$setting = [
    'init'=>'prod',
    'HostMerchantId'=> 'xxxxxxxxx',
    'MerchantPassword'=> 'xxxxxx',
    'HostTerminalId'=> 'xxxxxx',
];

$vakifBank = new vakifBank($setting);

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

```
