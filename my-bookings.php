<?php
$page_class = "register-bg";
session_start();
require_once "db.php";
require_once "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$now = time();

// Auto-maintenance: Update active bookings to 'past' if time has elapsed
// This fulfills the "stored in database" requirement
$now_date = date('Y-m-d');
$now_time = date('H:i:s');
$conn->query("UPDATE bookings 
              SET status = 'past' 
              WHERE status = 'active' 
              AND (booking_date < '$now_date' OR (booking_date = '$now_date' AND end_time < '$now_time'))");

/* Merr rezervimet e userit */
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.booking_date,
        b.start_time,
        b.end_time,
        b.price,
        b.payment_status,
        b.status,
        f.name AS field_name,
        f.location
    FROM bookings b
    JOIN fushat f ON f.id = b.field_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.start_time DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="my-bookings">
    <h2>My Bookings</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>You have no bookings yet.</p>
    <?php else: ?>
        <table class="bookings-table">
            <tr>
                <th>Field</th>
                <th>Location</th>
                <th>Date</th>
                <th>Time</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php while ($b = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($b['field_name']) ?></td>
                    <td><?= htmlspecialchars($b['location']) ?></td>
                    <td><?= date("d M Y", strtotime($b['booking_date'])) ?></td>
                    <td>
                        <?= substr($b['start_time'], 0, 5) ?>
                        -
                        <?= substr($b['end_time'], 0, 5) ?>
                    </td>
                    <td><?= number_format($b['price'], 2) ?> €</td>
                    <td>
                        <?php if ($b['status'] === 'past'): ?>
                            <span class="status-past">Past Booking</span>
                        <?php elseif ($b['status'] === 'cancelled'): ?>
                            <span class="status-cancelled">Cancelled</span>
                        <?php elseif ($b['status'] === 'active'): ?>
                            <span class="status-paid">Active</span>
                        <?php else: ?>
                            <span class="status-pending"><?= ucfirst($b['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $booking_start = strtotime($b['booking_date'] . ' ' . $b['start_time']);
                        if ($now < $booking_start && $b['status'] !== 'cancelled'): ?>
                            <a href="cancel-booking.php?id=<?= $b['id'] ?>" class="btn-action btn-cancel"
                                onclick="return confirm('Are you sure you want to cancel this booking? Note: No refund will be issued.')">
                                Cancel
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.php"; ?>