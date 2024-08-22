<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle adding new todo items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
    $status = 'pending';  // Default status is 'pending'

    $sql = "INSERT INTO todo_items (user_id, title, description, due_date, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $user_id, $title, $description, $due_date, $status);
    $stmt->execute();
    $stmt->close();
}

// Handle marking tasks as completed
if (isset($_GET['complete'])) {
    $task_id = $_GET['complete'];
    $sql = "UPDATE todo_items SET status='completed' WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: todo.php");
    exit();
}

// Handle deleting tasks
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $sql = "DELETE FROM todo_items WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: todo.php");
    exit();
}

// Fetch todo items
$sql = "SELECT * FROM todo_items WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-Do List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .container {
            width: 400px;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .todo-form {
            margin-bottom: 30px;
        }

        .todo-form label {
            font-weight: bold;
        }

        .todo-form input[type="text"],
        .todo-form input[type="date"],
        .todo-form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .todo-form button {
            width: 100%;
            background-color: #28a745;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }

        .todo-form button:hover {
            background-color: #218838;
        }

        .todo-list {
            list-style-type: none;
            padding: 0;
        }

        .todo-list li {
            background: #f9f9f9;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease-in-out;
        }

        .todo-list li.completed {
            background: #d4edda;
            color: #155724;
        }

        .todo-list li input[type="checkbox"] {
            position: absolute;
            top: 15px;
            left: 15px;
            transform: scale(1.5);
            cursor: pointer;
        }

        .todo-list li button {
            background-color: #dc3545;
            color: #fff;
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 15px;
        }

        .todo-list li button:hover {
            background-color: #c82333;
        }

        .todo-list li strong {
            display: block;
            margin-left: 40px;
        }

        .todo-list li em {
            display: block;
            margin-left: 40px;
            color: #6c757d;
            font-size: 14px;
        }

        .signup-button {
            display: block;
            width: 100%;
            text-align: center;
            background-color: #007bff;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: none;
        }

        .signup-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>My To-Do List</h2>
        <form action="todo.php" method="POST" class="todo-form">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required><br><br>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea><br><br>
            
            <label for="due_date">Due Date:</label>
            <input type="date" id="due_date" name="due_date"><br><br>

            <button type="submit">Add To-Do</button>
        </form>

        <h3>Tasks</h3>
        <ul class="todo-list">
            <?php while ($row = $result->fetch_assoc()) : ?>
                <li class="<?php echo $row['status'] == 'completed' ? 'completed' : ''; ?>">
                    <input type="checkbox" onchange="window.location.href='todo.php?complete=<?php echo $row['id']; ?>'" <?php echo $row['status'] == 'completed' ? 'checked' : ''; ?>>
                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                    <?php echo htmlspecialchars($row['description']); ?><br>
                    <em>Due Date: <?php echo $row['due_date']; ?></em>
                    <em>Status: <?php echo ucfirst($row['status']); ?></em>
                    <!-- <em>Created at: <?php echo $row['created_at']; ?></em> -->
                    <button onclick="window.location.href='todo.php?delete=<?php echo $row['id']; ?>'">Delete</button>
                </li>
            <?php endwhile; ?>
        </ul>

        <a href="signup.html" class="signup-button"> Sign Up</a>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
