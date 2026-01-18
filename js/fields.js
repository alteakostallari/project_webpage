function openField(card) {
    const name = card.dataset.name;

    window.location.href = `field-details.php?name=${encodeURIComponent(name)}`;
}

