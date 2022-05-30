<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "uni_db");
if (isset($_POST['email'])) {

    // verificam daca s-au completat formurile, cu un default value daca nu au fost completate.
    // posibil aici sa facem si o validare in care verificam daca putem folosii datele de la user.
    $passwd = $_POST['password'] ?? "";
    $passwd_re = $_POST['password_re'] ?? "";

    // cauta daca exista un rand cu id-ul respectiv
    $stmt = $mysqli->prepare("SELECT * FROM login_details WHERE mail = ?");
    $stmt->bind_param("s", $_POST['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // daca exista => update
    if ($result->num_rows > 0) {
        //eroare nu pot fi 2 useri cu acelasi mail

        echo '<script>alert("There already is a user with this email!")</script>';

    } else {
        // daca nu exista => create
        if($passwd != $passwd_re){
            echo '<script>alert("Passwords dont match!")</script>';
        }
        else{
            $passwd = md5($passwd);
            $createStmt = $mysqli->prepare("INSERT INTO login_details (mail,password) VALUES (?, ?)");
            $createStmt->bind_param("ss",$_POST['email'], $passwd);
            $createStmt->execute();
            $createStmt->close();

            $_SESSION['logged'] = true;
            $_SESSION['username'] = $_POST['email'];
            header('location:http://localhost/programare_web_basics_php/client_op.php');
            exit;
        }
    }
}
?>

<head>
    <title>Register</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet"
          type="text/css"
          href="style.css"/>
    <style>
        h1, h2 {text-align: center;
            margin-bottom: 25px;}
    </style>
</head>

<body>
<h1>MyTickets</h1>
    <div id="content">
        <h2>Register</h2>
        <div class="form">
            <form method="post" action="db_register.php">
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
                <div class="input-group mb-3">
                    <span class="input-group-text" id="inputGroup-sizing-sm">Re-enter Password</span>
                        <input type="password" name="password_re" value="" class="form-control" aria-label="Sizing example input"
                               aria-describedby="inputGroup-sizing-sm"/>
                </div>
                <button type="submit" class="btn btn-success">Register</button>
            </form>
            <form action="db_login.php" method="post">
                <div class="input-group mb-3">
                        Already registered?
                </div>
                <button type="submit" class="btn btn-secondary">Login</button>
            </form>
        </div>
    </div>
</body>