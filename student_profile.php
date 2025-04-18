<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_no = $_POST['room_no'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $guardian_name = $_POST['guardian_name'];
    $guardian_phone = $_POST['guardian_phone'];
    $address = $_POST['address'];

    // Check if user already has a profile
    $check = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $check->bind_param("s", $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update existing profile
        $stmt = $conn->prepare("UPDATE user_profiles SET room_no=?, dob=?, gender=?, guardian_name=?, guardian_phone=?, address=? WHERE user_id=?");
        $stmt->bind_param("sssssss", $room_no, $dob, $gender, $guardian_name, $guardian_phone, $address, $user_id);
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, room_no, dob, gender, guardian_name, guardian_phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $user_id, $room_no, $dob, $gender, $guardian_name, $guardian_phone, $address);
    }

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully!";
    } else {
        $message = "❌ Error updating profile.";
    }

    $stmt->close();
    $check->close();
}

// Fetch user and profile data
$userQuery = $conn->prepare("SELECT full_name, email, phone FROM users WHERE user_id = ?");
$userQuery->bind_param("s", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

$profileQuery = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$profileQuery->bind_param("s", $user_id);
$profileQuery->execute();
$profileResult = $profileQuery->get_result();
$profile = $profileResult->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            color: #1e3a8a;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            background: #1e3a8a;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
        }

        button:hover {
            background: #274ecf;
        }

        .message {
            margin-top: 15px;
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>My Profile</h2>
        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST" action="">
            <label>Full Name</label>
            <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>

            <label>Email</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>

            <label>Phone</label>
            <input type="text" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>

            <label>Room No</label>
            <input type="text" name="room_no" value="<?php echo $profile['room_no'] ?? ''; ?>">

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?php echo $profile['dob'] ?? ''; ?>">

            <label>Gender</label>
            <select name="gender">
                <option value="">-- Select --</option>
                <option value="Male" <?php if (($profile['gender'] ?? '') == "Male") echo "selected"; ?>>Male</option>
                <option value="Female" <?php if (($profile['gender'] ?? '') == "Female") echo "selected"; ?>>Female</option>
                <option value="Other" <?php if (($profile['gender'] ?? '') == "Other") echo "selected"; ?>>Other</option>
            </select>

            <label>Guardian's Name</label>
            <input type="text" name="guardian_name" value="<?php echo $profile['guardian_name'] ?? ''; ?>">

            <label>Guardian's Phone</label>
            <input type="text" name="guardian_phone" value="<?php echo $profile['guardian_phone'] ?? ''; ?>">

            <label>Address</label>
            <textarea name="address"><?php echo $profile['address'] ?? ''; ?></textarea>

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
