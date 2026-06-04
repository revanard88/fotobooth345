<?php
session_start();
include 'koneksi.php';

if(isset($_POST['login'])){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $data  = mysqli_fetch_assoc($query);

    if($data){

        if(password_verify($password, $data['password'])){

            $_SESSION['login'] = true;
            $_SESSION['nama']  = $data['nama'];

            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Password salah!";
        }

    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login Fotobooth</title>

<style>

body{
    margin:0;
    padding:0;
    font-family:Arial;
    background:linear-gradient(135deg,#4f46e5,#9333ea);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card{
    width:350px;
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

h2{
    text-align:center;
    margin-bottom:25px;
}

.input{
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border:none;
    background:#f1f5f9;
    border-radius:10px;
    font-size:15px;
}

.btn{
    width:100%;
    padding:14px;
    border:none;
    background:#4f46e5;
    color:white;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
}

.btn:hover{
    background:#3730a3;
}

.error{
    background:#fecaca;
    color:#991b1b;
    padding:10px;
    border-radius:10px;
    margin-bottom:15px;
    text-align:center;
}

</style>
</head>
<body>

<div class="card">

<h2>LOGIN FOTOBOOTH</h2>

<?php if(isset($error)){ ?>
<div class="error"><?= $error ?></div>
<?php } ?>

<form method="POST">

<input type="text" name="username" placeholder="Username" class="input" required>

<input type="password" name="password" placeholder="Password" class="input" required>

<button type="submit" name="login" class="btn">
Masuk Dashboard
</button>

</form>

</div>

</body>
</html>