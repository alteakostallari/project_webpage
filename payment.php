<?php
session_start();
require_once "db.php";
require_once "config.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Retrieve booking details from session (set by booking-process.php)
if (!isset($_SESSION['booking_details'])) {
    header("Location: index.php");
    exit;
}

$bookingDetails = $_SESSION['booking_details'];
$field_name = $bookingDetails['field_name'];
$date = $bookingDetails['date'];
$time = $bookingDetails['start_time'];
$duration = $bookingDetails['duration'];
$price = $bookingDetails['price'];

$page_class = "register-bg";
require_once "includes/header.php";
?>

<div class="payment-wrapper">
    <div class="payment-card">
        <h2>Confirm Reservation</h2>

        <!-- Reservation Summary -->
        <div class="reservation-summary">
            <h3>Booking Details</h3>

            <div class="summary-item">
                <span>Field:</span>
                <strong><?php echo htmlspecialchars($field_name); ?></strong>
            </div>

            <div class="summary-item">
                <span>Hourly Price:</span>
                <strong><?php echo number_format($bookingDetails['price_per_hour'], 2); ?> €</strong>
            </div>

            <div class="summary-item">
                <span>Date & Time:</span>
                <strong><?php echo htmlspecialchars($date . ' ' . $time); ?></strong>
            </div>

            <div class="summary-item">
                <span>Duration:</span>
                <strong><?php echo htmlspecialchars($duration); ?> min</strong>
            </div>

            <div class="summary-total">
                <span>Total to Pay:</span>
                <strong><?php echo number_format($price, 2); ?> €</strong>
            </div>
        </div>

        <!-- Stripe Payment Section -->
        <div id="stripe-payment-form">
            <h3>Secure Payment</h3>
            <form id="payment-form">
                <div id="payment-element">
                    <!--Stripe.js injects the Payment Element-->
                </div>
                <button id="submit" class="btn-book" style="width: 100%;">
                    <div class="spinner hidden" id="spinner"></div>
                    <span id="button-text">Pay now</span>
                </button>
                <div id="payment-message" class="hidden"></div>
            </form>
        </div>

        <!-- Include Stripe script -->
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            // IMPORTANT: Replace with your actual Stripe Publishable Key
            const stripe = Stripe("<?php echo STRIPE_PUBLISHABLE_KEY; ?>");

            // calculating amount in cents (eur 10.00 -> 1000)
            const priceAmount = <?php echo floatval($price) * 100; ?>;

            const items = [{ id: "reservation_<?php echo time(); ?>", amount: priceAmount }];

            let elements;

            initialize();

            async function initialize() {
                const response = await fetch("create-payment-intent.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ items }),
                });
                const { clientSecret } = await response.json();

                const appearance = {
                    theme: 'night',
                    variables: {
                        colorPrimary: '#10b981',
                        colorBackground: '#1f2937',
                        colorText: '#ffffff',
                        colorDanger: '#ef4444',
                        fontFamily: 'Times New Roman, serif',
                        spacingUnit: '4px',
                        borderRadius: '8px',
                    }
                };
                elements = stripe.elements({ appearance, clientSecret });

                const paymentElement = elements.create("payment");
                paymentElement.mount("#payment-element");
            }

            document.getElementById("payment-form").addEventListener("submit", handleSubmit);

            async function handleSubmit(e) {
                e.preventDefault();
                setLoading(true);

                const { error } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        // On success, redirect to success page
                        return_url: window.location.origin + "/WebPage_Project/booking-success.php",
                    },
                });

                // This logic only runs if error
                if (error.type === "card_error" || error.type === "validation_error") {
                    showMessage(error.message);
                } else {
                    showMessage("An unexpected error occurred.");
                }

                setLoading(false);
            }

            function setLoading(isLoading) {
                if (isLoading) {
                    document.querySelector("#submit").disabled = true;
                    document.querySelector("#spinner").classList.remove("hidden");
                    document.querySelector("#button-text").classList.add("hidden");
                } else {
                    document.querySelector("#submit").disabled = false;
                    document.querySelector("#spinner").classList.add("hidden");
                    document.querySelector("#button-text").classList.remove("hidden");
                }
            }

            function showMessage(messageText) {
                const messageContainer = document.querySelector("#payment-message");
                messageContainer.classList.remove("hidden");
                messageContainer.textContent = messageText;
            }
        </script>
        <style>
            /* Spinner CSS */
            .spinner {
                border: 4px solid rgba(0, 0, 0, 0.1);
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border-left-color: #09f;
                animation: spin 1s ease infinite;
                margin: 0 auto;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            .hidden {
                display: none;
            }
        </style>



    </div>
</div>

<?php require_once "includes/footer.php"; ?>