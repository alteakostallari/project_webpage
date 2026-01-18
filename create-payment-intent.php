<?php
// create-payment-intent.php

if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} elseif (file_exists('stripe-php/init.php')) {
    require_once 'stripe-php/init.php';
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe library not found. Please install via Composer or download specifically to "stripe-php" folder.']);
    exit;
}
require_once 'config.php';

// Enable error reporting for debugging logs (but return JSON to client)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session to get user_id
session_start();

// Connect to Database
require_once 'db.php';

// Set Stripe API Key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

header('Content-Type: application/json');

// Helper function to log to DB
function logStripeToDb($conn, $userId, $stripePaymentIntent, $stripeChargeId, $amountDecimal, $currency, $status, $failureReason)
{
    try {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $sql = "INSERT INTO stripe_logs (
                    user_id, 
                    stripe_payment_intent, 
                    stripe_charge_id, 
                    amount, 
                    currency, 
                    status, 
                    failure_reason, 
                    ip_address
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Types: i = int, s = string, d = double/decimal
            $stmt->bind_param(
                "issdssss",
                $userId,
                $stripePaymentIntent,
                $stripeChargeId,
                $amountDecimal,
                $currency,
                $status,
                $failureReason,
                $ipAddress
            );
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        // Silently fail logging/write to file
        error_log("Database Logging Failed: " . $e->getMessage());
    }
}

try {
    // Retrieve JSON from POST body
    $jsonStr = file_get_contents('php://input');
    $jsonObj = json_decode($jsonStr);

    if (!isset($jsonObj->items)) {
        throw new Exception("Invalid request: 'items' missing");
    }

    $items = $jsonObj->items;
    $amountCents = $items[0]->amount;

    // Convert cents to decimal for DB (e.g. 1000 cents -> 10.00)
    $amountDecimal = $amountCents / 100;

    $userId = $_SESSION['user_id'] ?? null;
    $currency = 'eur';

    // Create a PaymentIntent with the specified amount and currency
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amountCents,
        'currency' => $currency,
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
    ]);

    // LOG SUCCESS (Intent Created - Status: pending)
    logStripeToDb(
        $conn,
        $userId,
        $paymentIntent->id,       // stripe_payment_intent
        null,                     // stripe_charge_id (none yet)
        $amountDecimal,
        $currency,
        'pending',                // status
        null                      // failure_reason
    );

    $output = [
        'clientSecret' => $paymentIntent->client_secret,
    ];

    echo json_encode($output);

} catch (Error $e) {
    // LOG FAILURE
    // NOTE: We may not have a payment intent ID if the API call failed entirely.
    // The DB schema requires stripe_payment_intent NOT NULL.
    // We use "FAILED_INIT" or similar if needed, or skip DB logging if strict.

    $uId = $_SESSION['user_id'] ?? null;
    $amt = (isset($amountCents) ? $amountCents / 100 : 0);

    logStripeToDb(
        $conn,
        $uId,
        "FAILED_INIT",            // Placeholder for NOT NULL column
        null,
        $amt,
        'eur',
        'failed',
        $e->getMessage()
    );

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Exception $e) {
    // LOG FAILURE
    $uId = $_SESSION['user_id'] ?? null;
    $amt = (isset($amountCents) ? $amountCents / 100 : 0);

    logStripeToDb(
        $conn,
        $uId,
        "FAILED_INIT",
        null,
        $amt,
        'eur',
        'failed',
        $e->getMessage()
    );

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>