<?php
session_start(); // Right at the top of your script
require_once("dbcontroller.php");
$db_handle = new DBController();

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "uni_db";

//create connection
$connection = mysqli_connect($host, $user, $pass, $db_name);
$mysqli = new mysqli("localhost", "root", "", "uni_db");

$query = "SELECT * FROM event";

$idFromMailQuery = "SELECT Id FROM login_details WHERE mail = '" . $_SESSION["username"] . "'";
$idFromMail = current($mysqli->query($idFromMailQuery)->fetch_assoc());

$myBookingQuery = "SELECT e.eventName,e.eventDate, u.number_tickets FROM event e
INNER JOIN user_event u
ON e.Id = u.event_id
WHERE u.user_id = '" . $idFromMail . "'";

$result = $mysqli->query($myBookingQuery);
$myBooking = "CURRENT BOOKINGS\nNAME DATE NO. TICKETS\n";
foreach ($result as $exx) {
    $myBooking .= implode(" ", $exx);
    $myBooking .= "\n";
}
//test if connection failed
if (mysqli_connect_errno()) {
    die("connection failed: "
        . mysqli_connect_error()
        . " (" . mysqli_connect_errno()
        . ")");
}

if (isset($_POST['submit'])) {
    global $mysqli;

//    echo " quantity: " . $_POST["quantity"];
//    echo " Id: " . $_POST["Id"];

    $mail = $_SESSION["username"];
//    echo " username: " . $mail;
    $capacity_query = $mysqli->prepare("SELECT capacity FROM event WHERE Id = ?");
    $capacity_query->bind_param("i", $_POST["Id"]);
    $capacity_query->execute();
    $capacity = $capacity_query->get_result();
    $capacity = $capacity->fetch_array();
    $capacity_query->close();
//    echo "CAPACITY:" . $capacity[0];

    //checking if the input quantity exceeds event capacity
    if ($capacity[0] - $_POST["quantity"] >= 0) {
        //getting id from email
        $id_stm = $mysqli->prepare("SELECT Id FROM login_details WHERE mail = ?");
        $id_stm->bind_param("s", $_SESSION["username"]);
        $id_stm->execute();
        $id = $id_stm->get_result();
        $id_stm->close();

        $id = $id->fetch_array();
        $id = intval($id[0]);

        //checking if he already has a ticket to the event
        $MMquery = $mysqli->prepare("SELECT number_tickets FROM user_event WHERE event_id = ? and user_id = ?");
        $MMquery->bind_param("ii", $_POST["Id"], $id);
        $MMquery->execute();
        $num_tickets = $MMquery->get_result();
        $MMquery->close();
        $num_tickets = $num_tickets->fetch_array();
        //yes -> update the number of tickets
        if ($num_tickets != NULL) {
            $num_tickets = intval($num_tickets[0]);
            $update_query = $mysqli->prepare("UPDATE user_event SET number_tickets = number_tickets + ? WHERE event_id = ? and user_id = ?");
            $update_query->bind_param("iii", $_POST["quantity"], $_POST["Id"], $id);
            $update_query->execute();
            $update_query->close();
        } //no -> insert new row
        else {
            $num_tickets = 0;
            $insert_query = $mysqli->prepare("INSERT INTO user_event VALUES(?, ?, ?)");
            $insert_query->bind_param("iii", $_POST["Id"], $id, $_POST["quantity"]);
            $insert_query->execute();
            $insert_query->close();
        }
        //decreasing the event capacity by the no. of tickets bought
        $decrease_query = $mysqli->prepare("UPDATE event SET capacity = capacity - ? WHERE Id = ?");
        $decrease_query->bind_param("ii", $_POST["quantity"], $_POST["Id"]);
        $decrease_query->execute();
        $decrease_query->close();

        header('location:http://localhost/programare_web_basics_php/client_op.php');
        exit;
    } else {
        echo "<script type='text/javascript'>alert('Capacity Excedeed!');</script>";
    }
}

if (isset($_POST['sort_price'])) {
    $query = "SELECT * FROM event";
    $value = 0;
    switch ($_POST['sort_price']) {
        case 1:
            $value = 2;
            $query .= " ORDER BY capacity DESC";
            break;
        case 0:
            $value = 1;
            $query .= " ORDER BY capacity ASC";
            break;
        default:
            break;
    }
}

if (isset($_POST['filter'])) {
    $category = $_POST['location'];

    $query = "SELECT * FROM event WHERE eventLocation='$category'";

} else if (isset($_POST['reset'])) {
    $query = "SELECT * FROM event";
}

if (isset($_POST['logout'])) {
//    echo "pressed";
    header('location:http://localhost/programare_web_basics_php/db_login.php');
    exit;
}

?>

<script>
    function myFunction() {
        var myServerData = <?=json_encode($myBooking)?>; // Don't forget to sanitize
        alert(myServerData); // Value set with PHP.
    }
</script>

<HTML>
<HEAD>
    <TITLE>MyTickets</TITLE>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <link rel="stylesheet"
          type="text/css"
          href="style.css"/>
    <style>
        h1 {
            text-align: center;
            margin-bottom: 25px;
            margin-top: 25px;
        }
    </style>
</HEAD>
<BODY>

<h1>Welcome to MyTickets!</h1>

<div id="content">
    <form name="Log Out" method="POST" action="client_op.php">
        <button type="submit" name="logout" class="btn btn-warning" value="Log Out">Log Out</button>
    </form>
    <form name="Table Properties" method="post" action="client_op.php?>">
        <button type="submit" name="sort_price" class="btn btn-secondary" value="<?php echo $value; ?>"> Sort Capacity
        </button>
    </form>
    <form>
        <input type="button" onclick="myFunction()" class="btn btn-secondary" value="My Bookings">
    </form>
    <form method="POST" action="">
        <div class="input-group mb-3">
            <span class="input-group-text" id="inputGroup-sizing-default">Location</span>
            <select class="form-control" name="location">
                <option value="Cluj-napoca">Cluj-napoca</option>
                <option value="Bucuresti">Bucuresti</option>
                <option value="Timisoara">Timisoara</option>
            </select>
            <button class="btn btn-primary" name="filter">Filter</button>
            <button class="btn btn-danger" name="reset">Reset</button>
        </div>
    </form>
</div>


<div id="product-grid">
    <?php
    $product_array = $db_handle->runQuery($query);
    if (!empty($product_array)) {
        foreach ($product_array as $key => $value) {
            ?>
            <div class="product-item">
                <form method="post" action="client_op.php?>">
                    <div class="product-image"><img src="<?php echo 'image/' . $product_array[$key]["filename"]; ?>">
                    </div>
                    <div class="product-tile-footer">
                        <div class="product-title"> Name : <?php echo $product_array[$key]["eventName"]; ?></div>
                        <div class="product-location"> Location
                            : <?php echo $product_array[$key]["eventLocation"]; ?></div>
                        <div class="product-date"> Date : <?php echo $product_array[$key]["eventDate"]; ?></div>
                        <div class="product-capacity"> Capacity : <?php echo $product_array[$key]["capacity"]; ?></div>
                        <div class="product-price">Price : <?php echo "LEI " . $product_array[$key]["price"]; ?></div>
                        <div class="input-group mb-3">
                            <input type="text" class="product-quantity" name="quantity" value="1" size="2"
                                   class="form-control" aria-label="Sizing example input"
                                   aria-describedby="inputGroup-sizing-sm"/>
                            <input type="hidden" class="product-quantity" name="Id"
                                   value=<?php echo $product_array[$key]["Id"]; ?> size="2"/>
                            <input type="submit" value="buy" class="btn btn-secondary" name="submit"/>
                        </div>
                    </div>
                </form>
            </div>
            <?php
        }
    }
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2"
        crossorigin="anonymous"></script>
</BODY>
</HTML>