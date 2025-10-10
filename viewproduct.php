<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "rentalcloth";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* ---------------- ADD PRODUCT ---------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['product_name']);
    $size = $conn->real_escape_string($_POST['size']);
    $price = $conn->real_escape_string($_POST['product_price']);

    $sql = "INSERT INTO add_product (product_name, size, product_price) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssd", $name, $size, $price);
    if ($stmt->execute()) {
        header("Location: viewproduct.php");
        exit;
    } else {
        echo "<p class='error'>Error adding product: " . $conn->error . "</p>";
    }
    $stmt->close();
}

/* ---------------- EDIT PRODUCT ---------------- */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $name = $conn->real_escape_string($_POST['product_name']);
    $size = $conn->real_escape_string($_POST['size']);
    $price = $conn->real_escape_string($_POST['product_price']);

    $sql = "UPDATE add_product SET product_name = ?, size = ?, product_price = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $name, $size, $price, $id);
    if ($stmt->execute()) {
        header("Location: viewproduct.php");
        exit;
    } else {
        echo "<p class='error'>Error updating product: " . $conn->error . "</p>";
    }
    $stmt->close();
}

/* ---------------- DELETE PRODUCT ---------------- */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM add_product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: viewproduct.php");
    exit;
}

/* ---------------- FETCH PRODUCT FOR EDIT ---------------- */
$edit_product = null;
if (isset($_GET['product_id'])) {
    $edit_id = intval($_GET['product_id']);
    $sql = "SELECT * FROM add_product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
    $stmt->close();
}

/* ---------------- SEARCH & FETCH ALL PRODUCTS ---------------- */
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT * FROM add_product";
if ($search) {
    $sql .= " WHERE product_name LIKE '%$search%'";
}
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Products</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 900px; margin: auto; padding: 20px; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .product-table th, .product-table td { border: 1px solid #ddd; padding: 10px; }
        .product-table th { background: #667eea; color: #fff; }
        .btn { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; }
        .edit-btn { background: #3498db; color: #fff; }
        .delete-btn { background: #e74c3c; color: #fff; }
        .submit-btn { background: #2ecc71; color: #fff; margin-top: 10px; }

        /* Back Button Style */
        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 20px;
            background: linear-gradient(45deg, #ff6b6b, #f8e71c, #4cd137, #0097e6, #9b59b6);
            background-size: 300% 300%;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: 0.4s;
            animation: gradientMove 5s ease infinite;
        }
        .back-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Back Button -->
    <a href="user_details.php" class="back-btn">⬅ Back</a>

    <h2>Products</h2>

    <!-- ADD PRODUCT FORM -->
    <h3>Add Product</h3>
    <form method="POST">
        <input type="hidden" name="add_product" value="1">
        <input type="text" name="product_name" placeholder="Product Name" required>
        <input type="text" name="size" placeholder="Size" required>
        <input type="number" step="0.01" name="product_price" placeholder="Price" required>
        <button type="submit" class="submit-btn">Add</button>
    </form>

    <!-- EDIT FORM -->
    <?php if ($edit_product): ?>
        <h3>Edit Product</h3>
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_product['product_id']) ?>">
            <input type="text" name="product_name" value="<?= htmlspecialchars($edit_product['product_name']) ?>" required>
            <input type="text" name="size" value="<?= htmlspecialchars($edit_product['size']) ?>" required>
            <input type="number" step="0.01" name="product_price" value="<?= htmlspecialchars($edit_product['product_price']) ?>" required>
            <button type="submit" class="submit-btn">Update</button>
        </form>
    <?php endif; ?>

    <!-- SEARCH FORM -->
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>                                     
    </form>

    <!-- PRODUCT TABLE -->
    <table class="product-table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Price</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['size']) ?></td>
                    <td><?= number_format($row['product_price'], 2) ?></td>
                    <td><a class="btn edit-btn" href="viewproduct.php?product_id=<?= $row['product_id'] ?>">Edit</a></td>
                    <td><a class="btn delete-btn" href="viewproduct.php?delete_id=<?= $row['product_id'] ?>" onclick="return confirm('Delete this product?')">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5">No products available.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>
