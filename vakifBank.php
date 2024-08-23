<?php

class  vakifBank
{

    private $settins = [

        'init'=> 'test',

        'PostUrl' => [
            'test'=> 'https://cptest.vakifbank.com.tr/CommonPayment/api/RegisterTransaction',
            'prod'=> 'https://cpweb.vakifbank.com.tr/CommonPayment/api/RegisterTransaction'
        ],

        'UIUrl' => [
            'test'=> 'https://cptest.vakifbank.com.tr/CommonPayment/SecurePayment?Ptkn=',
            'prod'=> 'https://cpweb.vakifbank.com.tr/CommonPayment/SecurePayment?Ptkn='
        ],

        'TransactionUrl' => [
            'test'=> 'https://cptest.vakifbank.com.tr/CommonPayment/api/VposTransaction',
            'prod'=> 'https://cpweb.vakifbank.com.tr/CommonPayment/api/VposTransaction'
        ],

        'HostMerchantId' => '',
        'MerchantPassword' => '',
        'HostTerminalId' => '',

        'InstalmentCount' => '',
        'AmountCode' => '',
        'TransactionType' => 'Sale',
        'IsSecure' => 'true',
        'AllowNotEnrolledCard' => 'true',
        'SuccessURL' => '',
        'FailureURL' => '',

    ];

    public function __construct($settins)
    {
        $this->settins = array_merge($this->settins,$settins);

    }

    public  function payment($payment)
    {


        $sendTrnxUrl = $this->settins['UIUrl'][$this->settins['init']];

        $PostUrl = $this->settins['PostUrl'][$this->settins['init']];

        $TransactionId = $payment['TransactionId'];

        $Amount = $payment["Amount"];
        $AmountCode = $payment["AmountCode"];

        $HostMerchantId = $this->settins['HostMerchantId'];
        $MerchantPassword = $this->settins['MerchantPassword'];
        $InstalmentCount = $this->settins['InstalmentCount'];

        $OrderId = $payment["OrderId"];

        $OrderDescription = $payment["OrderDescription"];

        $TransactionType = $this->settins['TransactionType'];

        $IsSecure = $this->settins['IsSecure'];
        $AllowNotEnrolledCard = $this->settins['AllowNotEnrolledCard'];

        $HostTerminalId = $this->settins['HostTerminalId'];

        $SuccessURL = $this->settins['SuccessURL'];
        $FailureURL = $this->settins['FailureURL'];

        $ch = curl_init();



        curl_setopt($ch, CURLOPT_URL,$PostUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS,"HostMerchantId=$HostMerchantId"
            . "&AmountCode=$AmountCode"
            . "&Amount=$Amount"
            . "&MerchantPassword=$MerchantPassword"
            . "&TransactionId=$TransactionId"
            . "&OrderID=$OrderId"
            . "&OrderDescription=$OrderDescription"
            . "&InstallmentCount=$InstalmentCount"
            . "&TransactionType=$TransactionType"
            . "&IsSecure=$IsSecure"
            . "&AllowNotEnrolledCard=$AllowNotEnrolledCard"
            . "&HostTerminalId=$HostTerminalId"
            . "&SuccessURL=$SuccessURL"
            . "&FailURL=$FailureURL");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        $result = curl_exec($ch);
        curl_close($ch);

        $resultArray = $this->xmlParsePayment($result);


        if ( $resultArray["PaymentToken"] !== '') {

            return [
                'status'=>true,
                'PaymentToken'=>$resultArray['PaymentToken'],
                'CommonPaymentUrl'=>$resultArray['CommonPaymentUrl'],
                'RedirectUrl'=>$sendTrnxUrl.$resultArray["PaymentToken"]
                ];

        }

        return [
            'status'=>false,
            'ErrorCode'=>$resultArray['ErrorCode'],
        ];

    }

    public  function callback($callback)
    {

        $TransactionId = $callback["TransactionId"];
        $PaymentToken = $callback["PaymentToken"];

        $HostMerchantId = $this->settins['HostMerchantId'];
        $Password = $this->settins['MerchantPassword'];

        $PostURL = $this->settins['TransactionUrl'][$this->settins['init']];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$PostURL);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type"=>"application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/xml'));
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,"TransactionId=$TransactionId"
            . "&PaymentToken=$PaymentToken"
            . "&HostMerchantId=$HostMerchantId"
            . "&Password=$Password");
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        $result = curl_exec($ch);
        curl_close($ch);

        $resultXml = $this->xmlParseCallback($result);

        return [
            'Rc'=>$callback["Rc"],
            'Message'=>$callback["Message"],
            'TransactionId'=>$callback["TransactionId"],
            'PaymentToken'=>$callback["PaymentToken"],
            'ResultAuthCode'=>$resultXml["AuthCode"],
            'ResultMessage'=>$resultXml["Message"],
            'ResultTransactionId'=>$resultXml["TransactionId"],
            'ResultPaymentToken'=>$resultXml["PaymentToken"],
            'ResultMaskedPan'=>$resultXml["MaskedPan"],
            'ErrorCode'=>$resultXml["ErrorCode"],
        ];

    }

    private function xmlParsePayment($result)
    {
        $resultXmlLoad = simplexml_load_string($result);

        $PaymentToken = (string) $resultXmlLoad->PaymentToken ?? "";
        $CommonPaymentUrl = (string) $resultXmlLoad->CommonPaymentUrl ?? "";
        $ErrorCode = (string) $resultXmlLoad->ErrorCode ?? "";
        $ResponseMessage = (string) $resultXmlLoad->ResponseMessage ?? "";

        if ($ErrorCode) {
            $ErrorCode .= '-' . $ResponseMessage;
        }

        return [
            "CommonPaymentUrl" => $CommonPaymentUrl,
            "PaymentToken" => $PaymentToken,
            "ErrorCode" => $ErrorCode
        ];

    }

    private function xmlParseCallback($result)
    {
        $resultXmlLoad = simplexml_load_string($result);

        $Rc = (string) $resultXmlLoad->Rc ?? "";
        $AuthCode = (string) $resultXmlLoad->AuthCode ?? "";
        $Message = (string) $resultXmlLoad->Message ?? "";
        $TransactionId = (string) $resultXmlLoad->TransactionId ?? "";
        $PaymentToken = (string) $resultXmlLoad->PaymentToken ?? "";
        $MaskedPan = (string) $resultXmlLoad->MaskedPan ?? "";
        $ErrorCode = (string) $resultXmlLoad->ErrorCode ?? "";

        return [
            "Rc" => $Rc,
            "AuthCode" => $AuthCode,
            "Message" => $Message,
            "TransactionId" => $TransactionId,
            "PaymentToken" => $PaymentToken,
            "MaskedPan" => $MaskedPan,
            "ErrorCode" => $ErrorCode
        ];

    }




}
