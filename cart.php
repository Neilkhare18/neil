<?php
session_start();

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate total price
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += (float) $item['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .cart-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
        }
        .cart-item button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Shopping Cart</h1>
        <div id="cart-items">
            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <div class="cart-item">
                    <img src="images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                    <span><?php echo $item['name']; ?> - $<?php echo $item['price']; ?></span>
                    <a href="remove_from_cart.php?index=<?php echo $index; ?>" class="btn btn-danger">Remove</a>
                </div>
            <?php endforeach; ?>
        </div>
        <h3 class="mt-3">Total: $<?php echo number_format($totalPrice, 2); ?></h3>
        <button onclick="generatePDF()" class="btn btn-primary w-100 mt-3">Print</button>
        <a href="index.html" class="btn btn-primary w-100 mt-3">Back to Store</a>
    </div>

    <script>
        async function toDataURL(url) {
            return new Promise((resolve, reject) => {
                let img = new Image();
                img.crossOrigin = 'Anonymous';
                img.onload = function () {
                    let canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    let ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    resolve(canvas.toDataURL('image/jpeg'));
                };
                img.onerror = () => reject(new Error("Image load error"));
                img.src = url;
            });
        }

        async function generatePDF() {
            const { jsPDF } = window.jspdf;
            let doc = new jsPDF();
            let cart = <?php echo json_encode($_SESSION['cart']); ?>;
            let y = 20;
            let totalPrice = 0;

            doc.setFontSize(18);
            doc.text("Shopping Cart Bill", 80, 10);
            doc.setFontSize(12);

            for (let i = 0; i < cart.length; i++) {
                let item = cart[i];
                let price = parseFloat(item.price.replace("$", ""));
                totalPrice += price;
                
                doc.text(`${i + 1}. ${item.name} - $${item.price}`, 10, y);
                
                try {
                    let imageData = await toDataURL(`images/${item.image}`);
                    doc.addImage(imageData, "JPEG", 150, y - 5, 30, 30);
                } catch (error) {
                    console.log("Error loading image: ", error);
                }

                y += 40;
            }
            
            doc.text(`Total Price: $${totalPrice.toFixed(2)}`, 10, y + 10);
            doc.save("Shopping_Bill.pdf");
        }
    </script>
</body>
</html>
