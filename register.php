<?php
session_start();
include 'db.php';

if(isset($_POST['register'])){
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if($password !== $password_confirm){
        $error = "Mật khẩu không trùng khớp!";
    } else {
        $check = $conn->prepare("SELECT * FROM users WHERE username=?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        if($result->num_rows > 0){
            $error = "Username đã tồn tại!";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, fullname, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $fullname, $password_hash);
            if($stmt->execute()){
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = $fullname;
                $_SESSION['role'] = 'user';
                header("Location: index.php");
                exit;
            } else {
                $error = "Lỗi: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="d-flex flex-column min-vh-100 auth-bg">
    <?php include 'header.php'; ?>

    <main>
        <div class="register-container">
            <h1>Đăng ký</h1>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="post">
                <label>Username:</label>
                <input type="text" name="username" required>

                <label>Họ và tên:</label>
                <input type="text" name="fullname" required>

                <label>Mật khẩu:</label>
                <input type="password" name="password" required>

                <label>Nhập lại mật khẩu:</label>
                <input type="password" name="password_confirm" required>

                <button type="submit" name="register">Đăng ký</button>
            </form>
            <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
        </div>
    </main>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
