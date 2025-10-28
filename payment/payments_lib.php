<?php
// payments_lib.php
declare(strict_types=1);

class PaymentException extends Exception {}
class ValidationException extends PaymentException {}

// Trait for logging
trait LoggerTrait {
    protected function log(string $message): void {
        $time = (new DateTime())->format('Y-m-d H:i:s');
        error_log("[PAY_LOG] {$time} - {$message}");
    }
}

// Interface
interface PaymentMethodInterface {
    public function pay(float $amount, string $currency): string;
    public function refund(string $transactionId, float $amount): bool;
    public function getDetails(): array;
}

// Abstract base
abstract class PaymentMethod implements PaymentMethodInterface {
    use LoggerTrait;

    protected static int $totalTransactions = 0;
    protected static array $supportedCurrencies = ['USD'];

    private string $merchantId;
    protected bool $testMode = true;
    protected PDO $pdo;

    public function __construct(PDO $pdo, string $merchantId, bool $testMode = true) {
        $this->pdo = $pdo;
        $this->setMerchantId($merchantId);
        $this->testMode = $testMode;
    }

    final public function setMerchantId(string $m): void {
        if (trim($m) === '') throw new ValidationException("Merchant ID required.");
        $this->merchantId = $m;
    }
    final public function getMerchantId(): string { return $this->merchantId; }

    protected function validateAmount(float $amount): void {
        if ($amount <= 0) throw new ValidationException("Amount must be > 0.");
    }

    protected function validateCurrency(string $currency): void {
        if (!in_array(strtoupper($currency), static::$supportedCurrencies, true)) {
            throw new ValidationException("Currency {$currency} not supported.");
        }
    }

    protected function incrementCount(): void { static::$totalTransactions++; }
    public static function getTotalTransactions(): int { return static::$totalTransactions; }

    protected function saveTransaction(string $txId, string $gateway, float $amount, string $currency, string $status, array $meta = []): void {
        $sql = "INSERT INTO transactions (transaction_id, gateway, amount, currency, status, meta, created_at)
                VALUES (:tx,:gateway,:amount,:currency,:status,:meta,:created_at)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tx' => $txId,
            ':gateway' => $gateway,
            ':amount' => $amount,
            ':currency' => $currency,
            ':status' => $status,
            ':meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            ':created_at' => (new DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    abstract public function pay(float $amount, string $currency): string;
    abstract public function refund(string $transactionId, float $amount): bool;

    public function getDetails(): array {
        return [
            'merchantId' => $this->getMerchantId(),
            'testMode' => $this->testMode,
            'supported' => static::$supportedCurrencies,
            'class' => static::class,
        ];
    }
}

/* ------------------- PayPal (simulated/REST example) ------------------- */
class PayPalPayment extends PaymentMethod {
    protected static array $supportedCurrencies = ['USD','EUR','GBP','JPY'];
    private string $clientId;
    private string $clientSecret;

    public function __construct(PDO $pdo, string $merchantId, string $clientId, string $clientSecret, bool $testMode=true){
        parent::__construct($pdo, $merchantId, $testMode);
        if (!$clientId || !$clientSecret) throw new ValidationException("PayPal credentials required.");
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    // Simulated pay: in testMode we fake success; in liveMode you'd call PayPal REST token + payment.
    public function pay(float $amount, string $currency): string {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        $this->log("PayPal: processing {$amount} {$currency} (test={$this->testMode})");

        if ($this->testMode) {
            $tx = 'PAYPAL-TX-' . bin2hex(random_bytes(6));
            $this->incrementCount();
            $this->saveTransaction($tx, 'PayPal', $amount, $currency, 'COMPLETED', ['mode'=>'test']);
            $this->log("PayPal: simulated success tx={$tx}");
            return $tx;
        }

        // Example skeleton for live PayPal REST (you must implement OAuth2 and orders API)
        $token = $this->paypalGetAccessToken();
        // Use $token to create order/capture - omitted for brevity
        // After successful capture:
        // $tx = 'PAYPAL-LIVE-' . ...; saveTransaction(...);
        throw new PaymentException("Live PayPal flow is not implemented in this demo. Use sandbox/test mode or implement REST calls.");
    }

    public function refund(string $transactionId, float $amount): bool {
        if (!$transactionId) throw new ValidationException("Transaction id required for refund.");
        $this->validateAmount($amount);
        $this->log("PayPal: refunding {$amount} for {$transactionId} (test={$this->testMode})");
        $this->incrementCount();
        $this->saveTransaction($transactionId, 'PayPal', -1 * $amount, 'REFUND', 'REFUNDED', ['ref_for'=>$transactionId]);
        return true;
    }

    private function paypalGetAccessToken(): string {
        // Example only — do not call without HTTPS and proper credentials
        $this->log("PayPal: requesting access token - not implemented in demo.");
        throw new PaymentException("PayPal OAuth2 token retrieval not implemented in this demo. Implement using clientId/clientSecret and PayPal endpoints.");
    }

    public function getDetails(): array {
        $d = parent::getDetails();
        $d['gateway'] = 'PayPal';
        $d['clientIdMasked'] = substr($this->clientId,0,4) . str_repeat('*', max(0, strlen($this->clientId)-8)) . substr($this->clientId,-4);
        return $d;
    }
}

/* ------------------- Card payments base ------------------- */
abstract class CardPayment extends PaymentMethod {
    protected static array $supportedCurrencies = ['USD','EUR'];
    protected string $cardNumber = '';
    protected string $cardHolder = '';
    protected string $expiry = '';
    protected string $cvv = '';

    public function setCardData(string $cardNumber, string $cardHolder, string $expiry, string $cvv): void {
        if (!preg_match('/^\d{12,19}$/', $cardNumber)) throw new ValidationException("Card number format invalid.");
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) throw new ValidationException("Expiry format MM/YY.");
        if (!preg_match('/^\d{3,4}$/', $cvv)) throw new ValidationException("CVV invalid.");
        $this->cardNumber = $cardNumber;
        $this->cardHolder = $cardHolder;
        $this->expiry = $expiry;
        $this->cvv = $cvv;
    }

    protected function maskCard(): string {
        return str_repeat('*', max(0, strlen($this->cardNumber) - 4)) . substr($this->cardNumber, -4);
    }

    public function getDetails(): array {
        $d = parent::getDetails();
        $d['maskedCard'] = $this->maskCard();
        $d['cardHolder'] = $this->cardHolder;
        return $d;
    }
}

/* ------------------- Visa ------------------- */
class VisaPayment extends CardPayment {
    protected static array $supportedCurrencies = ['USD','EUR','GBP'];

    public function pay(float $amount, string $currency): string {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        if (empty($this->cardNumber)) throw new ValidationException("Card data required.");
        if ($this->cardNumber[0] !== '4') throw new PaymentException("Not a Visa card by BIN check.");

        $tx = 'VISA-TX-' . bin2hex(random_bytes(6));
        $this->incrementCount();
        $this->saveTransaction($tx, 'Visa', $amount, $currency, 'COMPLETED', ['card'=> $this->maskCard()]);
        $this->log("Visa: approved tx={$tx}");
        return $tx;
    }

    public function refund(string $transactionId, float $amount): bool {
        $this->validateAmount($amount);
        $this->log("Visa: refund {$amount} for {$transactionId}");
        $this->incrementCount();
        $this->saveTransaction($transactionId, 'Visa', -1*$amount, 'REFUND', 'REFUNDED', ['ref_for'=>$transactionId]);
        return true;
    }
}

/* ------------------- MasterCard ------------------- */
class MasterCardPayment extends CardPayment {
    protected static array $supportedCurrencies = ['USD','EUR','CAD'];

    public function pay(float $amount, string $currency): string {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        if (empty($this->cardNumber)) throw new ValidationException("Card data required.");
        $first = $this->cardNumber[0];
        if (!in_array($first, ['5','2'], true)) throw new PaymentException("Not a MasterCard by BIN check.");

        $tx = 'MC-TX-' . bin2hex(random_bytes(6));
        $this->incrementCount();
        $this->saveTransaction($tx, 'MasterCard', $amount, $currency, 'COMPLETED', ['card'=> $this->maskCard()]);
        $this->log("MasterCard: approved tx={$tx}");
        return $tx;
    }

    public function refund(string $transactionId, float $amount): bool {
        $this->validateAmount($amount);
        $this->log("MasterCard: refund {$amount} for {$transactionId}");
        $this->incrementCount();
        $this->saveTransaction($transactionId, 'MasterCard', -1*$amount, 'REFUND', 'REFUNDED', ['ref_for'=>$transactionId]);
        return true;
    }
}

/* ------------------- Factory ------------------- */
class PaymentFactory {
    public static function create(string $type, PDO $pdo, array $opts = []): PaymentMethodInterface {
        return match (strtolower($type)) {
            'paypal' => new PayPalPayment($pdo, $opts['merchantId'] ?? 'merchant-paypal', $opts['clientId'] ?? '', $opts['clientSecret'] ?? '', $opts['testMode'] ?? true),
            'visa' => new VisaPayment($pdo, $opts['merchantId'] ?? 'merchant-visa', $opts['testMode'] ?? true),
            'mastercard' => new MasterCardPayment($pdo, $opts['merchantId'] ?? 'merchant-mc', $opts['testMode'] ?? true),
            default => throw new InvalidArgumentException("Unknown gateway: {$type}"),
        };
    }
}
