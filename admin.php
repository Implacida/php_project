<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

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

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_title = $_POST['product_title'];
    $product_price = $_POST['product_price'];
    $discount_price = !empty($_POST['discount_price']) ? $_POST['discount_price'] : NULL;  // If empty, set as NULL
    $cover_link = $_POST['cover_link'];
    $description = $_POST['description'];

    // Validate inputs
    if (empty($product_title) || !is_numeric($product_price)) {
        echo "<p>Invalid input data. Please provide a valid product title and price.</p>";
    } else {
        // Insert product with description, cover link, and NULL or discount price
        $stmt = $conn->prepare("INSERT INTO products (title, price, discount_price, cover, description) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sdsss", $product_title, $product_price, $discount_price, $cover_link, $description);

        if ($stmt->execute()) {
            echo "<p>Product added successfully.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Handle delete product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    // Validate input
    if (empty($product_id) || !is_numeric($product_id)) {
        echo "<p>Invalid product ID.</p>";
    } else {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            echo "<p>Product deleted successfully.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Handle update product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_title = $_POST['product_title'];
    $product_price = $_POST['product_price'];
    $discount_price = ($_POST['discount_price'] === '0' || $_POST['discount_price'] === '') ? NULL : $_POST['discount_price'];  // Handle discount price as NULL when empty or 0
    $cover_link = $_POST['cover_link'];
    $description = $_POST['description'];

    // Validate inputs
    if (
        empty($product_id) || empty($product_title) || 
        !is_numeric($product_id) || !is_numeric($product_price) || 
        (!empty($discount_price) && !is_numeric($discount_price))
    ) {
        echo "<p>Invalid input data. Please check all fields.</p>";
    } else {
        // Update product with cover link and description, handle discount price as NULL
        $stmt = $conn->prepare("UPDATE products SET title = ?, price = ?, discount_price = ?, cover = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sdsssi", $product_title, $product_price, $discount_price, $cover_link, $description, $product_id);

        if ($stmt->execute()) {
            echo "<p>Product updated successfully.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Fetch all products
$result = $conn->query("SELECT * FROM products");
if (!$result) {
    die("Query failed: " . $conn->error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Interface</title>
</head>
<body>
    <h1>Welcome, Admin</h1>

    <h2>Add Product</h2>
    <form method="post">
        <label for="product_title">Product Title:</label><br>
        <input type="text" id="product_title" name="product_title" required><br>
        <label for="product_price">Product Price:</label><br>
        <input type="number" step="0.01" id="product_price" name="product_price" required><br>
        <label for="discount_price">Discount Price:</label><br>
        <input type="number" step="0.01" id="discount_price" name="discount_price" placeholder="Optional"><br>
        <label for="cover_link">Product Cover (Image URL):</label><br>
        <input type="text" id="cover_link" name="cover_link" required placeholder="e.g. https://example.com/image.jpg"><br>
        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea><br>
        <button type="submit" name="add_product">Add Product</button>
    </form>

    <h2>Existing Products</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Price</th>
            <th>Discount Price</th>
            <th>Cover</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo (is_null($row['discount_price']) ? 'N/A' : htmlspecialchars($row['discount_price'], ENT_QUOTES, 'UTF-8')); ?></td>
                <td>
                    <?php if (!empty($row['cover'])) { ?>
                        <img src="<?php echo htmlspecialchars($row['cover'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover Image" style="max-width: 100px;">
                    <?php } else { ?>
                        No cover uploaded
                    <?php } ?>
                </td>
                <td><?php echo nl2br(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8')); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" name="delete_product">Delete</button>
                    </form>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="text" name="product_title" value="<?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="number" step="0.01" name="product_price" value="<?php echo htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        <input type="number" step="0.01" name="discount_price" value="<?php echo htmlspecialchars($row['discount_price'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Discount Price">
                        <input type="text" name="cover_link" value="<?php echo htmlspecialchars($row['cover'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Image URL" required>
                        <textarea name="description" rows="4" cols="50"><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        <button type="submit" name="update_product">Update</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>

<?php
$conn->close();
?>
