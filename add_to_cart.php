<?php
session_start();

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image']
    ];
    array_push($_SESSION['cart'], $item);
}

// Redirect back to the index page
header('Location: index.html');
exit;
?>