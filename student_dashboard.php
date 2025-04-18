<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include 'db_connect.php';

$sql = "ALTER TABLE outpass_requests MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL";
$conn->query($sql);

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    die("❌ User not logged in.");
}

$user_id = intval($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination = $_POST['destination'];
    $reason = $_POST['reason'];
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    $transport_mode = $_POST['transport'];
    $name = $_SESSION['username'];
    $rollno = $_POST['rollno'] ?? null;
    $blockno = $_POST['blockno'] ?? null;

    $stmt = $conn->prepare("INSERT INTO outpass_requests (user_id, destination, reason, from_date, to_date, transport_mode, name, rollno, blockno, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("issssssss", $user_id, $destination, $reason, $from_date, $to_date, $transport_mode, $name, $rollno, $blockno);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Outpass request submitted successfully!";
    } else {
        $_SESSION['error_message'] = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    header("Location: student_dashboard.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM outpass_requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
      position: relative;
    }
    .profile-icon {
      position: absolute;
      right: 20px;
      top: 20px;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: white;
      border: 2px solid #1e3a8a;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #1e3a8a;
    }
    .container {
      max-width: 1100px;
      margin: 30px auto;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h2 {
      color: #1e3a8a;
    }
    label {
      display: block;
      margin: 10px 0 5px;
    }
    input, textarea, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      background: #1e3a8a;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .status-approved {
      color: green;
      font-weight: bold;
    }
    .status-pending {
      color: orange;
      font-weight: bold;
    }
    .status-declined {
      color: red;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: center;
    }
  </style>
</head>
<body>

<header>
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (ID: <?php echo $user_id; ?>)</h1>
  <div class="profile-icon" onclick="window.location.href='student_profile.php'">👤</div>
</header>

<div class="container">
  <div class="section">
    <h2>Request New Outpass</h2>

    <?php if (!empty($_SESSION['success_message'])): ?>
      <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
    <?php elseif (!empty($_SESSION['error_message'])): ?>
      <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
    <?php endif; ?>

    <form method="POST">
      <label for="destination">Destination</label>
      <input type="text" id="destination" name="destination" required>

      <label for="reason">Reason</label>
      <textarea id="reason" name="reason" required></textarea>

      <label for="fromDate">From Date & Time</label>
      <input type="datetime-local" id="fromDate" name="from_date" required>

      <label for="toDate">To Date & Time</label>
      <input type="datetime-local" id="toDate" name="to_date" required>

      <label for="transport">Mode of Transport</label>
      <select id="transport" name="transport">
        <option>Bus</option>
        <option>Train</option>
        <option>Car</option>
        <option>Walking</option>
      </select>

      <label for="rollno">Roll Number</label>
      <input type="text" id="rollno" name="rollno" required>

      <label for="blockno">Block Number</label>
      <input type="text" id="blockno" name="blockno" required>

      <button type="submit">Submit Request</button>
    </form>
  </div>

  <div class="section">
    <h2>My Outpasses</h2>
    <table id="outpassTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Destination</th>
          <th>From</th>
          <th>To</th>
          <th>Status</th>
          <th>Download</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['destination']); ?></td>
            <td><?php echo $row['from_date']; ?></td>
            <td><?php echo $row['to_date']; ?></td>
            <td class="status-<?php echo strtolower($row['status']); ?>">
              <?php echo $row['status']; ?>
            </td>
            <td>
              <?php if (strtolower($row['status']) === 'approved'): ?>
                <a href="#" class="download-link" data-outpass='<?php echo json_encode($row); ?>'>Download</a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  document.querySelectorAll('.download-link').forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const outpass = JSON.parse(this.getAttribute('data-outpass'));

      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();

      doc.setFontSize(16);
      doc.text("Outpass Details", 20, 20);
      doc.setFontSize(12);

      let y = 30;
      for (const key in outpass) {
        doc.text(`${key}: ${outpass[key]}`, 20, y);
        y += 10;
      }

      doc.save(`Outpass_ID_${outpass.id}.pdf`);
    });
  });
</script>

</body>
</html>
