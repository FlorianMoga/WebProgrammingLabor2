<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "uni_db");
if (isset($_POST['email'])) {

    // verificam daca s-au completat formurile, cu un default value daca nu au fost completate.
    // posibil aici sa facem si o validare in care verificam daca putem folosii datele de la user.
    $passwd = $_POST['password'] ?? "";

    // cauta daca exista un rand cu id-ul respectiv
    $stmt = $mysqli->prepare("SELECT * FROM login_details WHERE mail = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        //login
        $passwd = md5($passwd);
        $isstmt = $mysqli->prepare("SELECT * FROM login_details WHERE mail = ? and password = ?");
        $isstmt->bind_param("ss", $_POST['email'], $passwd);
        $isstmt->execute();
        $resultis = $isstmt->get_result();
        $isstmt->close();

        if ($resultis->num_rows > 0) {

            $_SESSION['logged'] = true;

            $email = $_POST['email'];
            if ($email == "admin@gmail.com") {
                header('location:http://localhost/programare_web_basics_php/admin_op.php');
                exit;
            } else {
                $_SESSION['username'] = $email;
                header('location:http://localhost/programare_web_basics_php/client_op.php');
                exit;
            }
            #echo '<script>alert("Correct login details")</script>';
        } else {
            echo '<script>alert("Incorrect login details!")</script>';
        }


    } else {
        echo '<script>alert("Email is not registered yet")</script>';
    }
}
?>
<body>

<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet"
          type="text/css"
          href="style.css"/>
    <style>
        h1, h2 {
            text-align: center;
            margin-bottom: 25px;
        }
    </style>
</head>
<h1>MyTickets</h1>

<div id="content">
    <h2>Login</h2>
    <div class="form">
        <form method="post" action="db_login.php">
            <div class="input-group mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">E-mail</span>
                <input type="email" name="email" value="" class="form-control" aria-label="Sizing example input"
                       aria-describedby="inputGroup-sizing-sm"/>
            </div>
            <div class="input-group mb-3">
                <span class="input-group-text" id="inputGroup-sizing-sm">Password</span>
                <input type="password" name="password" value="" class="form-control" aria-label="Sizing example input"
                       aria-describedby="inputGroup-sizing-sm"/>
            </div>
            <button type="submit"
                    class="btn btn-success">Login
            </button>
        </form>
        <form method="post" action="db_register.php">
            <div class="input-group mb-3">
                Don't have an account?
            </div>
            <button type="submit"
                    class="btn btn-secondary">Register
            </button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2"
        crossorigin="anonymous"></script>
</body>

</html>