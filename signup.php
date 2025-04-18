<?php
// Include the database connection
include('db_connect.php');

// Form processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $user_id = $_POST['user_id'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Simple validations
    if ($password !== $confirm_password) {
        echo "<script>alert('❌ Passwords do not match!');</script>";
    } else {
        // Check if email or ID already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ? OR user_id = ?");
        $check->bind_param("ss", $email, $user_id);
        $check->execute();
        $result = $check->get_result();

        if ($result && $result->num_rows > 0) {
            echo "<script>alert('❌ Email or Student ID already registered.');</script>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert data into the database
            $stmt = $conn->prepare("INSERT INTO users (full_name, user_id, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $user_id, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                echo "<script>alert('✅ Registration successful!'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('❌ Registration failed. Please try again.');</script>";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign-Up | Out Pass Manager</title>
    <link rel="stylesheet" href="signup.css">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/10799/10799971.png">
</head>
<body>
    <div id="wrapper">
        <header>
            <div id="logo">OUT PASS MANAGER</div>
            <nav>
                <ul>
                    <li class="page"><a href="homepage.html">Home</a></li>
                    <li class="page"><a href="signup.php">Sign-Up</a></li>
                    <li class="page"><a href="login.php">Login</a></li>
                    <li class="page"><a href="#">Contacts</a></li>
                </ul>
            </nav>
        </header>

        <section>
            <div id="credential">
                <div id="login">SIGN-UP</div>
                <form method="POST" action="">
                    <div id="combine">
                        <div id="left">
                            <div class="cred">
                                <label>Enter your Full Name</label>
                                <input type="text" name="full_name" placeholder="Full Name" required>
                            </div>

                            <div class="cred">
                                <label>Enter your Roll Number / Student ID</label>
                                <input type="text" name="user_id" placeholder="Student ID" required>
                            </div>

                            <div class="cred">
                                <label>Enter your Email</label>
                                <input type="email" name="email" placeholder="Email" required>
                            </div>

                            <div class="cred">
                                <label>Enter your Phone number</label>
                                <input type="text" name="phone" placeholder="Phone Number" required>
                            </div>
                        </div>

                        <div id="right">
                            <div class="cred">
                                <label>Enter Password</label>
                                <input type="password" name="password" placeholder="Password" required>
                            </div>

                            <div class="cred">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" placeholder="Re-enter Password" required>
                            </div>
                        </div>
                    </div>
                    <button id="submit">Sign-Up</button>
                </form>
            </div>
        </section>

        <footer></footer>
    </div>
</body>
</html>
