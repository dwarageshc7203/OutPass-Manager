<?php
session_start();
include 'db_connect.php';  // Make sure the connection is established

// Check if an action is triggered (approve/decline)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    $status = $action === 'approve' ? 'Approved' : 'Declined';

    // Update status in the database
    $stmt = $conn->prepare("UPDATE outpass_requests SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    // Get the student info to display a success message
    $student = $conn->query("SELECT name, rollno, blockno FROM outpass_requests WHERE id=$id")->fetch_assoc();

    if ($status === 'Approved') {
        $_SESSION['approved'] = "Outpass approved for " . $student['name'] . " (Roll No: " . $student['rollno'] . ")";
    } else {
        $_SESSION['declined'] = "Outpass declined for " . $student['name'] . " (Roll No: " . $student['rollno'] . ")";
    }
    // Redirect to refresh the page and show the message
    header("Location: warden_dashboard.php");
    exit();
}

// Fetch all pending outpass requests
$result = $conn->query("SELECT * FROM outpass_requests WHERE status='Pending'");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Dashboard - Outpass System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        header {
            background: #1e3a8a;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .section {
            margin-bottom: 30px;
        }
        h2 {
            color: #1e3a8a;
        }
        .outpass-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fafafa;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            background-color: #f0f8ff;
            border: 1px solid #ccc;
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <h1>Warden Dashboard</h1>
</header>

<div class="container">
    <?php if (isset($_SESSION['approved'])): ?>
        <div class="message">
            <p><?php echo $_SESSION['approved']; ?></p>
        </div>
        <?php unset($_SESSION['approved']); ?>
    <?php elseif (isset($_SESSION['declined'])): ?>
        <div class="message">
            <p><?php echo $_SESSION['declined']; ?></p>
        </div>
        <?php unset($_SESSION['declined']); ?>
    <?php endif; ?>

    <div class="section">
        <h2>Pending Outpass Requests</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="outpass-card">
                <p><strong><?php echo $row['name']; ?> - <?php echo $row['rollno']; ?></strong> is requesting from Block <?php echo $row['blockno']; ?>.</p>
                <a href="?action=approve&id=<?php echo $row['id']; ?>"><button>Approve</button></a>
                <a href="?action=decline&id=<?php echo $row['id']; ?>"><button>Decline</button></a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
