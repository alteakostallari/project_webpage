<?php
$page_class = "register-bg";
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Marrim të dhënat nga URL
$sportId = isset($_GET['sport_id']) ? (int) $_GET['sport_id'] : 1;
$sportName = isset($_GET['name']) ? $_GET['name'] : 'Sport';

// Marrja e lokacioneve dinamike vetëm për këtë sport_id
$stmt = $conn->prepare("SELECT DISTINCT location FROM fushat WHERE sport_id = ? AND statusi = 'active'");
$stmt->bind_param("i", $sportId);
$stmt->execute();
$result = $stmt->get_result();
$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row['location'];
}

require_once "includes/header.php";
?>

<section class="details-section">
    <div class="details-card">
        <h2>Rezervo: <?= htmlspecialchars($sportName) ?></h2>
        <form action="booking-process.php" method="POST" id="bookingForm">
            <input type="hidden" name="sport_id" value="<?= $sportId ?>">

            <label>Date: <input type="date" name="date" id="bookDate" min="<?= date('Y-m-d') ?>" required></label>
            <label>Time: <input type="time" name="start_time" id="bookTime" required></label>

            <label>Duration:
                <select name="duration" id="bookDuration">
                    <option value="60">1 hour</option>
                    <option value="90">1.5 hour</option>
                    <option value="120">2 hours</option>
                </select>
            </label>

            <div class="radio-group">
                <p>Field Type:</p>
                <div class="radio-options">
                    <label><input type="radio" name="field_type" value="indoor" checked> Indoor</label>
                    <label><input type="radio" name="field_type" value="outdoor"> Outdoor</label>
                </div>
            </div>

            <label>Location:
                <select id="bookLocation" name="location" required>
                    <option value="">Zgjidh qytetin</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Available fields <?= htmlspecialchars($sportName) ?>:
                <select name="field_id" id="availableFields" required>
                    <option value="">Fill the fields above...</option>
                </select>
            </label>

            <div id="priceDisplay" style="margin: 10px 0; font-weight: bold; color: #2ecc71; display: none;">
                Price per hour: <span id="hourlyPrice">0</span> €
            </div>



            <button type="submit" class="btn-book" id="btnBook" disabled>Rezervo Tani</button>
        </form>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // JavaScript mbetet i njëjtë, ai thjesht dërgon sport_id që mori nga PHP
        $('#bookDate, #bookTime, #bookDuration, #bookLocation, input[name="field_type"]').on('change', function () {
            checkAvailability();
        });

        $('#availableFields').on('change', function () {
            const selectedField = $(this).find('option:selected');
            const price = selectedField.data('price');
            if (price) {
                $('#hourlyPrice').text(parseFloat(price).toFixed(2));
                $('#priceDisplay').show();
            } else {
                $('#priceDisplay').hide();
            }
        });
    });

    function checkAvailability() {
        const data = {
            sport_id: <?= $sportId ?>,
            date: $('#bookDate').val(),
            start_time: $('#bookTime').val(),
            duration: $('#bookDuration').val(),
            location: $('#bookLocation').val(),
            field_type: $('input[name="field_type"]:checked').val()
        };

        if (!data.date || !data.start_time || !data.location) return;

        $.post("available-fields.php", data, function (res) {
            let html = '<option value="">Select specific field</option>';
            if (res.fields && res.fields.length > 0) {
                res.fields.forEach(f => {
                    html += `<option value="${f.id}" data-price="${f.price_per_hour}">${f.name}</option>`;
                });
                $('#btnBook').prop('disabled', false);
            } else {
                html = '<option value="">No available fields</option>';
                $('#btnBook').prop('disabled', true);
            }
            $('#availableFields').html(html);
        }, "json");
    }
</script>