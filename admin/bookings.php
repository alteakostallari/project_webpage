<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$page_class = "register-bg";
require_once "../includes/header.php";

// Auto-maintenance: Update active bookings to 'past' if time has elapsed
$now_date = date('Y-m-d');
$now_time = date('H:i:s');
$conn->query("UPDATE bookings 
              SET status = 'past' 
              WHERE status = 'active' 
              AND (booking_date < '$now_date' OR (booking_date = '$now_date' AND end_time < '$now_time'))");

$result = $conn->query("SELECT b.*, u.email, f.name as field_name FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        JOIN fushat f ON b.field_id = f.id
                        ORDER BY b.created_at DESC");
?>

<div class="bookings-wrapper">
    <h2>Bookings Management</h2>
    <table class="admin-bookings-table">
        <tr>
            <th>User</th>
            <th>Field</th>
            <th>Date</th>
            <th>Time</th>
            <th>Price</th>
            <th>Status</th>
        </tr>
        <?php while ($b = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($b['email']) ?></td>
                <td><?= htmlspecialchars($b['field_name']) ?></td>
                <td><?= date("d M Y", strtotime($b['booking_date'])) ?></td>
                <td><?= substr($b['start_time'], 0, 5) ?> - <?= substr($b['end_time'], 0, 5) ?></td>
                <td><?= number_format($b['price'], 2) ?> €</td>
                <td>
                    <?php if ($b['status'] === 'active'): ?>
                        <span class="status-paid">Active</span>
                    <?php elseif ($b['status'] === 'past'): ?>
                        <span class="status-past">Past</span>
                    <?php elseif ($b['status'] === 'cancelled'): ?>
                        <span class="status-cancelled">Cancelled</span>
                    <?php else: ?>
                        <span class="status-pending"><?= strtoupper($b['status']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require_once "../includes/footer.php"; ?>