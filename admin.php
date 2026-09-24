<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database/database.php';

// Handle Add / Edit Actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? '';
        $tier_level = $_POST['tier_level'] ?? '';
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $processor = trim($_POST['processor'] ?? '');
        $ram = trim($_POST['ram'] ?? '');
        $storage = trim($_POST['storage'] ?? '');
        $graphics = trim($_POST['graphics'] ?? '');
        $image_path = trim($_POST['image_path'] ?? '');
        $stock_quantity = filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT) ?: 10;

        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO products (name, category, tier_level, price, processor, ram, storage, graphics, image_path, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdsssssi", $name, $category, $tier_level, $price, $processor, $ram, $storage, $graphics, $image_path, $stock_quantity);
            if ($stmt->execute()) {
                $message = "Product added successfully!";
            } else {
                $message = "Error adding product: " . $conn->error;
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'edit' && isset($_POST['product_id'])) {
            $product_id = (int)$_POST['product_id'];
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, tier_level=?, price=?, processor=?, ram=?, storage=?, graphics=?, image_path=?, stock_quantity=? WHERE product_id=?");
            $stmt->bind_param("sssdsssssii", $name, $category, $tier_level, $price, $processor, $ram, $storage, $graphics, $image_path, $stock_quantity, $product_id);
            if ($stmt->execute()) {
                $message = "Product updated successfully!";
            } else {
                $message = "Error updating product: " . $conn->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $message = "Product deleted successfully!";
        } else {
            $message = "Error deleting product: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
$products = [];
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();

include 'header.php';
?>

<main class="admin-wrapper">
    <div class="admin-container">
        
        <div class="admin-header">
            <h1>Admin Dashboard - Product Management</h1>
            <p>Manage, add, edit, or delete store products for stellar_db.</p>
            <?php if (!empty($message)): ?>
                <div class="admin-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
        </div>

        <!-- Action Bar: Add Product Trigger -->
        <div class="admin-action-bar">
            <button id="open-add-modal-btn" class="btn-pill">Add New Product</button>
        </div>

        <!-- Listings Table -->
        <div class="admin-table-responsive">
            <table class="admin-products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Tier</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No products found in the database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?php echo (int)$p['product_id']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="" class="admin-thumb"></td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td><?php echo htmlspecialchars($p['category']); ?></td>
                                <td><?php echo htmlspecialchars($p['tier_level']); ?></td>
                                <td>₱<?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo (int)$p['stock_quantity']; ?></td>
                                <td>
                                    <button class="btn-edit" 
                                        data-id="<?php echo (int)$p['product_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                                        data-category="<?php echo htmlspecialchars($p['category'], ENT_QUOTES); ?>"
                                        data-tier="<?php echo htmlspecialchars($p['tier_level'], ENT_QUOTES); ?>"
                                        data-price="<?php echo htmlspecialchars($p['price'], ENT_QUOTES); ?>"
                                        data-processor="<?php echo htmlspecialchars($p['processor'], ENT_QUOTES); ?>"
                                        data-ram="<?php echo htmlspecialchars($p['ram'], ENT_QUOTES); ?>"
                                        data-storage="<?php echo htmlspecialchars($p['storage'], ENT_QUOTES); ?>"
                                        data-graphics="<?php echo htmlspecialchars($p['graphics'], ENT_QUOTES); ?>"
                                        data-image="<?php echo htmlspecialchars($p['image_path'], ENT_QUOTES); ?>"
                                        data-stock="<?php echo (int)$p['stock_quantity']; ?>">
                                        Edit
                                    </button>
                                    <form action="admin.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$p['product_id']; ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- Add/Edit Modal Form Container -->
    <div id="product-modal" class="admin-modal" style="display: none;">
        <div class="admin-modal-content">
            <span id="close-modal-btn" class="admin-close">&times;</span>
            <h2 id="modal-title">Add New Product</h2>
            
            <form id="product-form" action="admin.php" method="POST">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="product_id" id="form-product-id" value="">

                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" id="form-name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" id="form-category" required>
                            <option value="Workstation">Workstation</option>
                            <option value="Gaming">Gaming</option>
                            <option value="Personal">Personal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tier Level</label>
                        <select name="tier_level" id="form-tier" required>
                            <option value="SE">SE</option>
                            <option value="Pro">Pro</option>
                            <option value="Eclipse">Eclipse</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₱)</label>
                        <input type="number" step="0.01" name="price" id="form-price" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="form-stock" value="10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Processor</label>
                    <input type="text" name="processor" id="form-processor" required>
                </div>

                <div class="form-group">
                    <label>RAM / Memory</label>
                    <input type="text" name="ram" id="form-ram" required>
                </div>

                <div class="form-group">
                    <label>Storage</label>
                    <input type="text" name="storage" id="form-storage" required>
                </div>

                <div class="form-group">
                    <label>Graphics</label>
                    <input type="text" name="graphics" id="form-graphics" required>
                </div>

                <div class="form-group">
                    <label>Image Path</label>
                    <input type="text" name="image_path" id="form-image" value="Images/Products/Workstation.png" required>
                </div>

                <button type="submit" class="btn-pill" style="width: 100%; margin-top: 15px;">Save Product</button>
            </form>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>