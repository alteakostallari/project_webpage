<?php
session_start();
require_once "db.php";

// Check if returning from Stripe with success
if (isset($_GET['payment_intent']) && isset($_SESSION['pending_booking'])) {
    require_once "config.php";

    // Auto-detect Stripe library location
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
    } elseif (file_exists('stripe-php/init.php')) {
        require_once 'stripe-php/init.php';
    }

    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    try {
        $intent = \Stripe\PaymentIntent::retrieve($_GET['payment_intent']);

        if ($intent->status === 'succeeded') {
            $pb = $_SESSION['pending_booking'];
            $userId = $pb['user_id'];

            // 1. Extract Card Info
            $paymentMethodId = $intent->payment_method;
            $pm = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $card = $pm->card;

            // 2. Save Card to payment_cards (if not exists)
            $stmt = $conn->prepare("SELECT id FROM payment_cards WHERE stripe_payment_method_id = ? AND user_id = ?");
            $stmt->bind_param("si", $paymentMethodId, $userId);
            $stmt->execute();
            $cardResult = $stmt->get_result();

            if ($cardResult->num_rows > 0) {
                $cardId = $cardResult->fetch_assoc()['id'];
            } else {
                $insCard = $conn->prepare("INSERT INTO payment_cards (user_id, stripe_payment_method_id, card_brand, card_last4, exp_month, exp_year) VALUES (?, ?, ?, ?, ?, ?)");
                $insCard->bind_param("isssii", $userId, $paymentMethodId, $card->brand, $card->last4, $card->exp_month, $card->exp_year);
                $insCard->execute();
                $cardId = $conn->insert_id;
            }

            // 3. Insert Booking (Set status to 'active' immediately)
            $stmt = $conn->prepare("INSERT INTO bookings (user_id, field_id, booking_date, start_time, end_time, price, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, 'paid', 'active')");
            $stmt->bind_param("iisssd", $userId, $pb['field_id'], $pb['date'], $pb['start_time'], $pb['end_time'], $pb['price']);
            $stmt->execute();

            // 4. Update Stripe Logs (link card_id and mark success)
            $updLog = $conn->prepare("UPDATE stripe_logs SET card_id = ?, status = 'succeeded' WHERE stripe_payment_intent = ?");
            $updLog->bind_param("is", $cardId, $_GET['payment_intent']);
            $updLog->execute();

            // Clear session
            unset($_SESSION['pending_booking']);
            unset($_SESSION['booking_details']);
        }
    } catch (Exception $e) {
        // Handle error silently or log it
    }
} elseif (isset($_GET['redirect_status']) && $_GET['redirect_status'] === 'succeeded' && isset($_SESSION['pending_booking'])) {
    // Fallback for simple success
    $pb = $_SESSION['pending_booking'];
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, field_id, booking_date, start_time, end_time, price, payment_status, status) VALUES (?, ?, ?, ?, ?, ?, 'paid', 'active')");
    $stmt->bind_param("iisssd", $pb['user_id'], $pb['field_id'], $pb['date'], $pb['start_time'], $pb['end_time'], $pb['price']);
    $stmt->execute();
    unset($_SESSION['pending_booking']);
    unset($_SESSION['booking_details']);
} elseif (isset($_GET['redirect_status']) && $_GET['redirect_status'] === 'failed' && isset($_SESSION['pending_booking'])) {
    // Just for safety, clear session if failed
    unset($_SESSION['pending_booking']);
    unset($_SESSION['booking_details']);
}

$page_class = "register-bg";
require_once "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<section class="success-wrapper">
    <div class="success-card">

        <div class="success-icon">
            ✔
        </div>

        <h1>Booking confirmed successfully!</h1>
        <p>
            Your payment has been confirmed and your reservation is now <strong>"Active"</strong>.<br>
            You can view your reservation in your account.
        </p>

        <div class="action-buttons">
            <a href="index.php" class="btn-success btn-primary">
                Back to Home
            </a>
            <a href="my-bookings.php" class="btn-success btn-secondary">
                My Bookings
            </a>
        </div>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>