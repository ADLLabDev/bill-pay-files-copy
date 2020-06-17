<?php
require_once './vendor/autoload.php';

use GlobalPayments\Api\ServicesConfig;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\PaymentMethods\CreditCardData;

if (!empty($_POST['payment_token'])) {
    error_log('configuring sdk');
    $hl_config = new ServicesConfig();
    $hl_config->secretApiKey = "skapi_cert_MVI8AgCt42EAaTBZ8ihXJjFxzz3F1ifo4MDJY8GCXg";
    $hl_config->developerId = "000000"; ##change these after certification
    $hl_config->versionNumber = "0000"; ##change these after certification
    $hl_config->serviceUrl = "https://cert.api2.heartlandportico.com";
    ServicesContainer::configure($hl_config);

    error_log('creating card object');
    $card = new CreditCardData();
    $card->token = $_POST['payment_token'];

    error_log('creating address object');
    $address = new Address(); ##add more fields in here?
    $address->postalCode = "19020";

    error_log('attempting authorization request');
    try{
        $response = $card->charge(12.34)
        ->withCurrency("USD")
        ->withAddress($address)
        ->execute();

        error_log('success');
        print_r($response);
    }catch(ApiException $e) {
        error_log('error');
        // handle error
        echo $e->getMessage();
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Payment</title>
</head>
<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
<script src="https://api2.heartlandportico.com/SecureSubmit.v1/token/gp-1.3.0/globalpayments.js"></script>

<script type="text/javascript">
    $(function(){
        console.log("Configuring account..");
        // Configure account
        GlobalPayments.configure({
            publicApiKey: "pkapi_cert_32EF7iyP2IZOLrs4AF"
        });

        // Create Form
        const cardForm = GlobalPayments.creditCard.form("#credit-card");
        console.log(cardForm);

        cardForm.ready(() => {
            console.log("Registration of all credit card fields occurred");
        });

        cardForm.on("token-success", (resp) => {
            console.log("token success");
            // add payment token to form as a hidden input
            const token = document.createElement("input");
            token.type = "hidden";
            token.name = "payment_token";
            token.value = resp.paymentReference;

            // Submit data to the integration's backend for processing
            const form = document.getElementById("payment-form");
            form.appendChild(token);
            form.submit();
        });

        cardForm.on("token-error", (resp) => {
            // show error to the consumer
            console.log(resp);
        });

        cardForm.on("card-number", "register", () => {
            console.log("Registration of Card Number occurred");
        });
    });
</script>
<body>
    <form id="payment-form" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <!-- Other input fields to capture relevant data -->
        <label for="billing_zip">Billing Zip Code</label>
        <input id="billing_zip" name="billing_zip" value="47150" type="tel" />

        <!-- Target for the credit card form -->
        <div id="credit-card"></div>
    </form>
</body>
</html>
