<?php
session_start();
include('db_connect.php');

// Validation regex
$emailRegex = "/^[a-zA-Z0-9_@.]{3,50}$/"; 
$idRegex = "/^[0-9]{6,15}$/"; 
$passwordRegex = "/^[a-zA-Z0-9!@#$%^&*]{6,20}$/";

$email = $id = $password = $role = "";
$emailErr = $idErr = $passwordErr = $roleErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $id = $_POST['id'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validation
    if (!preg_match($emailRegex, $email)) {
        $emailErr = "Invalid email.";
    }

    if (!preg_match($idRegex, $id)) {
        $idErr = "Invalid ID.";
    }

    if (!preg_match($passwordRegex, $password)) {
        $passwordErr = "Invalid password.";
    }

    if (empty($role)) {
        $roleErr = "Please select a role.";
    }

    if (empty($emailErr) && empty($idErr) && empty($passwordErr) && empty($roleErr)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id = ?");
        $stmt->bind_param("ss", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['full_name'];
                $_SESSION['role'] = $role;
                $_SESSION['user_id'] = $user['user_id'];  // Store user_id in session

                header("Location: student_dashboard.php");
                exit();
            } else {
                echo "<script>alert('❌ Incorrect password.');</script>";
            }
        } else {
            echo "<script>alert('❌ No matching user found.');</script>";
        }

        $stmt->close();
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="utf8">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/10799/10799971.png">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div id="wrapper">
        <header>
            <div id="logo">OUT PASS MANAGER</div>
            <nav>
                <ul>
                    <li class="page">Home</li>
                    <li class="page">Sign-Up</li>
                    <li class="page">Login</li>
                    <li class="page">Contacts</li>
                </ul>
            </nav>
        </header>

        <section>
            <div id="credential">
                <div id="login">LOGIN</div>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="cred">
                        <label>Enter your Email</label>
                        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
                        <span class="error"><?php echo $emailErr; ?></span>
                    </div>

                    <div class="cred">
                        <label>Enter your Roll Number / Student ID</label>
                        <input type="text" name="id" placeholder="ID" value="<?php echo htmlspecialchars($id); ?>" required>
                        <span class="error"><?php echo $idErr; ?></span>
                    </div>

                    <div class="cred">
                        <label>Enter password</label>
                        <input type="password" name="password" placeholder="Password" value="<?php echo htmlspecialchars($password); ?>" required>
                        <span class="error"><?php echo $passwordErr; ?></span>
                    </div>

                    <div class="cred">
                        <label>Select your role</label>
                        <select name="role" required>
                            <option value="" disabled selected hidden>Role</option>
                            <option value="student" <?php echo ($role == "student") ? "selected" : ""; ?>>Student</option>
                            <option value="warden" <?php echo ($role == "warden") ? "selected" : ""; ?>>Warden</option>
                            <option value="parent" <?php echo ($role == "parent") ? "selected" : ""; ?>>Parent</option>
                        </select>
                        <span class="error"><?php echo $roleErr; ?></span>
                    </div>

                    <button id="submit">Login</button>
                </form>
            </div>
        </section>

        <footer></footer>
    </div>
</body>
</html>
