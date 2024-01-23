<?php
// Start the session
session_start();

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Array of products
$products = array(

// kozmeticki proizvodi

    'product1' => array(
        'name' => 'cream',
        'price' => 2540
    ),

    'product2' => array(
        'name' => 'cream2',
        'price' => 2392
    ),

    'product3' => array(
        'name' => 'cream3',
        'price' => 3500
    ),
 
    // Add more products as needed
);

// Handle product addition to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    echo "Form submitted successfully!<br>"; // Debugging message
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Validate product ID
    if (array_key_exists($product_id, $products)) {
        // Initialize the cart in the session if not already set
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Add product to the cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        echo "Product added to cart successfully!<br>"; // Debugging message
    } else {
        echo "Invalid product ID!<br>"; // Debugging message
    }
} else {
    echo "Form not submitted!<br>"; // Debugging message
}

// Process the order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    // Initialize the cart in the session if not already set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // Extract user and order details
    $name = $_POST["name"];
    $user_address = $_POST["user_address"];
    $email = $_POST["email"];
    $user_number = $_POST["user_number"];

    // Generate order summary
    $order_summary = "Order Details:\n";
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products[$product_id])) {
            $product_name = $products[$product_id]['name'];
            $product_price = $products[$product_id]['price'];
            $total_price = $product_price * $quantity;
            $order_summary .= "$product_name (Quantity: $quantity) - Total Price: $total_price\n";
        }
    }

    // Include user details in the order summary
    $order_summary .= "User Details:\n";
    $order_summary .= "Name: $name\n";
    $order_summary .= "Address: $user_address\n";
    $order_summary .= "Email: $email\n";
    $order_summary .= "Phone Number: $user_number\n";

    // Save order details to a text file
    $order_filename = 'order_details.txt';

    if (file_put_contents($order_filename, $order_summary) !== false) {
        echo "Order details saved to file successfully!<br>";

        // Send the order details as an email attachment
        sendOrderEmail($email, $order_filename);

        // Clear the cart
        unset($_SESSION['cart']);

        echo "Order processed successfully!";
    } else {
        echo "Error saving order details to file.<br>";
    }
}

// Function to send order details as an email attachment
function sendOrderEmail($recipient_email, $attachment_filename)
{
    $smtpUsername = 'e22fad5ddd66a8'; // Replace with your SMTP username
    $smtpPassword = '4c14cee70886b5'; // Replace with your SMTP password
    $to = $recipient_email;

    $subject = 'Order Details';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
        $mail->Port       = 587;

        $mail->setFrom('your_email@example.com', 'Your Name'); // Replace with your email and name
        $mail->addAddress($to);

        $mail->addAttachment($attachment_filename);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = 'Attached are the details of your order.';

        $mail->send();
        echo 'Order details sent via email.';
    } catch (Exception $e) {
        echo "Error sending email: {$mail->ErrorInfo}";
    }
}

// Function to calculate cart total
function calculateCartTotal($cart, $products) {
    $total = 0;

    foreach ($cart as $product_id => $quantity) {
        if (isset($products[$product_id])) {
            $product_price = $products[$product_id]['price'];
            $total += $product_price * $quantity;
        }
    }

    return $total;
}
?>
