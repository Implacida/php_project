<?php
session_start();

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "OnlineStore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    $sql = "SELECT * FROM accounts WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($input_password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            
            if ($user['username'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
    
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];

    // Validate inputs
    if (!empty($new_username) && !empty($new_password)) {
        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Insert the new account into the database
        $stmt = $conn->prepare("INSERT INTO accounts (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_username, $hashed_password);

        if ($stmt->execute()) {
            echo "<p>Registration successful. You can now <a href='index.php'>log in</a>.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Please fill in all fields.</p>";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .product, .account { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
        .product img { max-width: 100px; height: auto; display: block; margin-bottom: 10px; }
        .product .price { font-weight: bold; color: green; }
        .product .old-price { text-decoration: line-through; color: gray; }
        .product .discount-price { color: red; font-weight: bold; }
        .login-form { max-width: 300px; margin: 20px 0; }
        .error { color: red; }
    </style>
</head>
<body>

<h1>Welcome to the Online Store</h1>

<?php if (isset($_SESSION['username'])): ?>
    <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="index.php?logout=1">Log out</a></p>

    <h2>Products</h2>
    <?php
    $sql_products = "SELECT title, description, cover, price, discount_price FROM products";
    $result_products = $conn->query($sql_products);

    if ($result_products->num_rows > 0): ?>
        <div>
            <?php while($row = $result_products->fetch_assoc()): ?>
                <div class="product">
                    <img src="<?php echo htmlspecialchars($row['cover']); ?>" alt="Product Image">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>

                    <div class="price">
                        <?php if (!is_null($row['discount_price']) && $row['discount_price'] > 0): ?>
                            <!-- Display normal price with strikethrough and discounted price -->
                            <span class="old-price">Price: $<?php echo number_format($row['price'], 2); ?></span>
                            <span class="discount-price">Discount Price: $<?php echo number_format($row['discount_price'], 2); ?></span>
                        <?php else: ?>
                            <!-- Display only normal price if no discount -->
                            <span class="price">Price: $<?php echo number_format($row['price'], 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>

<?php else: ?>

    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form class="login-form" method="POST" action="index.php">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit" name="login">Log in</button>
        </div>
    </form>

    <h2>Register</h2>
    <form method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>
        <button type="submit" name="register">Register</button>
    </form>

<?php endif; ?>


</body>
</html>

<?php
// Close connection
$conn->close();
?>
