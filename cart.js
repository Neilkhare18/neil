const express = require('express');
const session = require('express-session');
const path = require('path');
const app = express();
const PORT = 3000;

app.use(express.static('public'));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(session({ secret: 'your_secret_key', resave: false, saveUninitialized: true }));

// Initialize cart
app.use((req, res, next) => {
    if (!req.session.cart) {
        req.session.cart = [];
    }
    next();
});

app.get('/cart', (req, res) => {
    const cart = req.session.cart;
    const totalPrice = cart.reduce((sum, item) => sum + parseFloat(item.price), 0);
    
    res.send(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shopping Cart</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <style>
            body { background-color: #f4f4f4; font-family: Arial, sans-serif; padding: 20px; }
            .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
            .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #ddd; }
            .cart-item img { width: 50px; height: 50px; object-fit: cover; margin-right: 10px; }
            .cart-item button { background-color: red; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1 class="text-center mb-4">Shopping Cart</h1>
            <div id="cart-items">
                ${cart.map((item, index) => `
                    <div class="cart-item">
                        <img src="/images/${item.image}" alt="${item.name}">
                        <span>${item.name} - $${item.price}</span>
                        <a href="/remove-from-cart?index=${index}" class="btn btn-danger">Remove</a>
                    </div>
                `).join('')}
            </div>
            <h3 class="mt-3">Total: $${totalPrice.toFixed(2)}</h3>
            <button onclick="generatePDF()" class="btn btn-primary w-100 mt-3">Print</button>
            <a href="/" class="btn btn-primary w-100 mt-3">Back to Store</a>
        </div>
        
        <script>
            async function generatePDF() {
                const { jsPDF } = window.jspdf;
                let doc = new jsPDF();
                let cart = ${JSON.stringify(cart)};
                let y = 20;
                let totalPrice = 0;

                doc.setFontSize(18);
                doc.text("Shopping Cart Bill", 80, 10);
                doc.setFontSize(12);

                for (let i = 0; i < cart.length; i++) {
                    let item = cart[i];
                    let price = parseFloat(item.price);
                    totalPrice += price;
                    
                    doc.text(`${i + 1}. ${item.name} - $${item.price}`, 10, y);
                    y += 10;
                }
                
                doc.text(`Total Price: $${totalPrice.toFixed(2)}`, 10, y + 10);
                doc.save("Shopping_Bill.pdf");
            }
        </script>
    </body>
    </html>
    `);
});

app.get('/remove-from-cart', (req, res) => {
    const index = parseInt(req.query.index);
    if (!isNaN(index) && req.session.cart[index]) {
        req.session.cart.splice(index, 1);
    }
    res.redirect('/cart');
});

app.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}`);
});
