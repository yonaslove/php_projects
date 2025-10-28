<?php
// process.php
declare(strict_types=1);
require __DIR__ . '/payments_lib.php';

$dbFile = __DIR__ . '/data/payments.sqlite';
if (!file_exists($dbFile)) {
    http_response_code(500);
    echo "Database not initialized. Run init_db.php first.";
    exit;
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// simple sanitizers
$gateway = $_POST['gateway'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$currency = strtoupper(trim($_POST['currency'] ?? 'USD'));

try {
    if (!$gateway) throw new ValidationException("Gateway required.");
    if ($amount <= 0) throw new ValidationException("Amount must be > 0.");
    if (!$currency) throw new ValidationException("Currency required.");

    // Create payment instance depending on gateway
    switch (strtolower($gateway)) {
        case 'paypal':
            $clientId = trim($_POST['paypalClientId'] ?? '');
            $clientSecret = trim($_POST['paypalClientSecret'] ?? '');
            $pay = PaymentFactory::create('paypal', $pdo, [
                'merchantId' => 'merchant-paypal-100',
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'testMode' => true, // keep sandbox by default
            ]);
            break;

        case 'visa':
            $pay = PaymentFactory::create('visa', $pdo, [
                'merchantId' => 'merchant-visa-200',
                'testMode' => true,
            ]);
            // set card data
            $cardNumber = preg_replace('/\D+/', '', $_POST['cardNumber'] ?? '');
            $expiry = trim($_POST['expiry'] ?? '');
            $cvv = trim($_POST['cvv'] ?? '');
            $cardHolder = trim($_POST['cardHolder'] ?? 'Unknown');
            if (!$cardNumber) throw new ValidationException("Card number required for Visa.");
            /** @var VisaPayment $pay */
            $pay->setCardData($cardNumber, $cardHolder, $expiry, $cvv);
            break;

        case 'mastercard':
            $pay = PaymentFactory::create('mastercard', $pdo, [
                'merchantId' => 'merchant-mc-300',
                'testMode' => true,
            ]);
            $cardNumber = preg_replace('/\D+/', '', $_POST['cardNumber'] ?? '');
            $expiry = trim($_POST['expiry'] ?? '');
            $cvv = trim($_POST['cvv'] ?? '');
            $cardHolder = trim($_POST['cardHolder'] ?? 'Unknown');
            if (!$cardNumber) throw new ValidationException("Card number required for MasterCard.");
            /** @var MasterCardPayment $pay */
            $pay->setCardData($cardNumber, $cardHolder, $expiry, $cvv);
            break;

        default:
            throw new ValidationException("Unknown gateway: {$gateway}");
    }

    // attempt payment
    $tx = $pay->pay($amount, $currency);

    // Friendly output
    echo "<h2>Payment Successful</h2>";
    echo "<p>Gateway: " . htmlentities($gateway) . "</p>";
    echo "<p>Transaction ID: <strong>" . htmlentities($tx) . "</strong></p>";
    echo "<p>Amount: {$amount} {$currency}</p>";
    echo "<p><a href='index.php'>Back</a></p>";

} catch (ValidationException $ve) {
    http_response_code(400);
    echo "<h3>Validation Error</h3><p>" . htmlentities($ve->getMessage()) . "</p><p><a href='index.php'>Back</a></p>";
} catch (PaymentException $pe) {
    http_response_code(402);
    echo "<h3>Payment Error</h3><p>" . htmlentities($pe->getMessage()) . "</p><p><a href='index.php'>Back</a></p>";
} catch (Throwable $t) {
    http_response_code(500);
    echo "<h3>Unexpected Error</h3><pre>" . htmlentities($t->getMessage()) . "</pre><p><a href='index.php'>Back</a></p>";
}
