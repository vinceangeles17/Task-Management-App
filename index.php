<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'connection.php';

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_POST['add_task'])) {
    $task_name = $_POST['task_name'];
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, priority, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $task_name, $priority, $due_date);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $new_status, $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['edit_task_submit'])) {
    $task_id = $_POST['task_id'];
    $edited_task_name = $_POST['task_name'];
    $edited_priority = $_POST['priority'];
    $edited_due_date = $_POST['due_date'];

    $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, priority = ?, due_date = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $edited_task_name, $edited_priority, $edited_due_date, $task_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}

$user_id = $_SESSION['user_id'];
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$priority_filter = isset($_GET['priority_filter']) ? $_GET['priority_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';

$query = "SELECT * FROM tasks WHERE user_id = $user_id";

if ($status_filter) {
    $query .= " AND status = '$status_filter'";
}
if ($priority_filter) {
    $query .= " AND priority = '$priority_filter'";
}
if ($date_filter) {
    $query .= " AND due_date = '$date_filter'";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Scheduler</title>
    <link rel="stylesheet" href="styles.css">
    <script src="notifications.js"></script>
</head>
<body>
    <header>
        <div class="header-left-container">
            <div class="header-left-title">Your Personalized Task Manager</div>
            <h1 class="welcome-header">Welcome, <?php echo $_SESSION['username']; ?></h1>
        </div>
    </header>
    <main class="Index">
        <div class="index-container">
            <form method="POST" action="index.php" class="task-form">
                <input type="text" name="task_name" placeholder="Task Name" required>
                <select name="priority">
                <option value="">>>Choose Priority Level<<</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
                <input type="date" name="due_date" required>
                <button type="submit" name="add_task" class="add-task-btn">Add Task</button>
            </form>

            <h2>Your Tasks</h2>

            <form method="GET" action="index.php" class="filter-form">
                <label for="status_filter">Filter by Status:</label>
                <select name="status_filter" id="status_filter">
                    <option value="">All</option>
                    <option value="To Do" <?php if ($status_filter == 'To Do') echo 'selected'; ?>>To Do</option>
                    <option value="In Progress" <?php if ($status_filter == 'In Progress') echo 'selected'; ?>>In Progress</option>
                    <option value="Done" <?php if ($status_filter == 'Done') echo 'selected'; ?>>Done</option>
                </select>

                <label for="priority_filter">Filter by Priority:</label>
                <select name="priority_filter" id="priority_filter">
                    <option value="">All</option>
                    <option value="High" <?php if ($priority_filter == 'High') echo 'selected'; ?>>High</option>
                    <option value="Medium" <?php if ($priority_filter == 'Medium') echo 'selected'; ?>>Medium</option>
                    <option value="Low" <?php if ($priority_filter == 'Low') echo 'selected'; ?>>Low</option>
                </select>

                <label for="date_filter">Filter by Due Date:</label>
                <input type="date" name="date_filter" id="date_filter" value="<?php echo $date_filter; ?>">

                <button type="submit">Filter</button>
            </form>

            <div class="task-list">
                <?php while ($task = $result->fetch_assoc()): ?>
                    <div class="task">
                        <?php if (isset($_POST['edit_task']) && $_POST['task_id'] == $task['id']): ?>
                            <form method="POST" action="index.php" class="edit-task-form">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="text" name="task_name" value="<?php echo htmlspecialchars($task['task_name']); ?>" required>
                                <select name="priority">
                                    <option value="High" <?php if ($task['priority'] == 'High') echo 'selected'; ?>>High</option>
                                    <option value="Medium" <?php if ($task['priority'] == 'Medium') echo 'selected'; ?>>Medium</option>
                                    <option value="Low" <?php if ($task['priority'] == 'Low') echo 'selected'; ?>>Low</option>
                                </select>
                                <input type="date" name="due_date" value="<?php echo $task['due_date']; ?>" required>
                                <button type="submit" name="edit_task_submit">Save</button>
                            </form>
                        <?php else: ?>
                            <p><?php echo $task['task_name']; ?> - Priority: <?php echo $task['priority']; ?> - Due: <?php echo $task['due_date']; ?></p>
                            <p>Status: 
                                <?php if (isset($_POST['edit_status']) && $_POST['task_id'] == $task['id']): ?>
                                    <form method="POST" action="index.php" style="display:inline;">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <select name="status">
                                            <option value="To Do" <?php if ($task['status'] == 'To Do') echo 'selected'; ?>>To Do</option>
                                            <option value="In Progress" <?php if ($task['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                            <option value="Done" <?php if ($task['status'] == 'Done') echo 'selected'; ?>>Done</option>
                                        </select>
                                        <button type="submit" name="update_status">Update Status</button>
                                    </form>
                                <?php else: ?>
                                    <?php echo $task['status']; ?>
                                    <form method="POST" action="index.php" style="display:inline;">
                                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" name="edit_status">Edit Status</button>
                                        <button type="submit" name="edit_task">Edit Task</button>
                                        <button type="submit" name="delete_task" onclick="return confirmDelete();">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <form method="POST" action="index.php" class="task-form">
                <button type="submit" name="logout" class="add-task-btn">Logout</button>
            </form>
        </div>
    </main>
<script>
<?php
if (isset($_POST['add_task'])) {
    echo "notifyAddTask();";
} elseif (isset($_POST['update_status'])) {
    echo "notifyChangeStatus();";
} elseif (isset($_POST['edit_task_submit'])) {
    echo "notifyEditTask();";
} elseif (isset($_POST['delete_task'])) {
    echo "notifyDeleteTask();";
}
?>
</script>
<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this task?");
}
</script>
</body>
</html>

<?php
$conn->close();
?>
