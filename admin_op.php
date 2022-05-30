<?php
$mysqli = new mysqli("localhost", "root", "", "uni_db");
// If upload button is clicked ...
if (isset($_POST['upload'])) {

    $filename = $_FILES["uploadfile"]["name"];

    $name = $_POST["eventName"];
    $date = $_POST["eventDate"];
    $location = $_POST["eventLocation"];
    $price = $_POST["price"];
    $capacity = $_POST["capacity"];

    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $folder = "image/" . $filename;

    $db = mysqli_connect("localhost", "root", "", "uni_db");

    // Get all the submitted data from the form
    $createStmt = $mysqli->prepare("INSERT INTO event (eventName, eventDate, eventLocation, filename, price, capacity) VALUES (?, ?, ?, ?, ?, ?)");
    $createStmt->bind_param("ssssdi", $name, $date, $location, $filename, $price, $capacity);
    $createStmt->execute();
    $createStmt->close();

    // Now let's move the uploaded image into the folder: image
    if (move_uploaded_file($tempname, $folder)) {
        $msg = "Image uploaded successfully";

    } else {
        $msg = "Failed to upload image";
    }
}
?>

<?php
if (isset($_POST['update'])) {
//    echo "update";

    if (!isset($_POST['ID']) or $_POST['ID'] < 0) {
        echo "<script>alert('ID NOT SET!');</script>";
    } else {
        $labelsArr = ['eventNameCK', 'eventDateCK', 'eventLocationCK', 'priceCK', 'capacityCK', 'photoCK'];
        $checkedLablesArr = [];
        $updatePart = "";
        $updateQuery = "UPDATE event SET ";
        $numLabels = 0;
        foreach ($labelsArr as &$label) {
            if (isset($_POST[$label])) {
                $numLabels += 1;
                $checkedLablesArr[] = $_POST[$label];
                if ($label == 'priceCK' or $label == 'capacityCK')
                    $updatePart .= substr($label, 0, -2) . "=" . $_POST[substr($label, 0, -2)] . ",";
                else
                    $updatePart .= substr($label, 0, -2) . "=" . "'" . $_POST[substr($label, 0, -2)] . "'" . ",";

            }
        }
        if ($numLabels > 0) {
            $updatePart = substr($updatePart, 0, -1) . " WHERE Id =";
            $updatePart .= $_POST['ID'];
            $updateQuery .= $updatePart . ";";
//            echo $updateQuery;
            $update = $mysqli->prepare($updateQuery);
            $update->execute();
            $update->close();
        }
    }
}
?>

<?php
if (isset($_POST['delete'])) {
    if (!isset($_POST['ID']) or $_POST['ID'] < 0) {
        echo "<script>alert('ID NOT SET!');</script>";
    } else {
        $delete = $mysqli->prepare("DELETE FROM event WHERE Id = ?");
        $delete->bind_param("i", $_POST['ID']);
        $delete->execute();
        $delete->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin</title>
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
    <h2>Admin Page</h2>
    <form method="POST"
          action=""
          enctype="multipart/form-data">
        <div class="input-group mb-3">
            <span class="input-group-text" id="inputGroup-sizing-sm">Id</span>
            <input type="number" name="ID" class="form-control" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div class="input-group mb-3">
            <input type="checkbox" id="eventName" name="eventNameCK" value="eventName" class="custom-control-input">
            <span class="input-group-text" id="inputGroup-sizing-sm">Name</span>
            <input type="text" name="eventName" class="form-control" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div class="input-group date" data-provide="datepicker">
            <input type="checkbox" id="eventDate" name="eventDateCK" value="eventDate">
            <span class="input-group-text" id="inputGroup-sizing-sm">Date</span>
            <input type="date" name="eventDate" class="form-control" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div class="input-group mb-3">
            <input type="checkbox" id="eventLocation" name="eventLocationCK" value="eventLocation">
            <span class="input-group-text" id="inputGroup-sizing-sm">Location</span>
            <input type="text" name="eventLocation" class="form-control" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div class="input-group mb-3">
            <input type="checkbox" id="price" name="priceCK" value="price">
            <span class="input-group-text" id="inputGroup-sizing-sm">Price</span>
            <input type="number" step=0.01 min=0 name="price" class="form-control" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div class="input-group mb-3">
            <input type="checkbox" id="capacity" name="capacityCK" value="capacity">
            <span class="input-group-text" id="inputGroup-sizing-sm">Capacity</span>
            <input type="capacity" step=50 min=50 name="capacity" class="form-control" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div class="custom-file">
            <input type="checkbox" id="photo" name="photoCK" value="filename" class="custom-file-input">
            <label class="custom-file-label" for="validatedCustomFile">Choose Photo...</label>
            <input type="file" name="uploadfile" value="" class="form-control-file" aria-label="Sizing example input"
                   aria-describedby="inputGroup-sizing-sm"/>
        </div>
        <div>
            <button type="submit"
                    name="upload"
                    class="btn btn-success">
                INSERT
            </button>
            <button type="submit"
                    name="update"
                    class="btn btn-secondary">
                UPDATE
            </button>
            <button type="submit"
                    name="delete"
                    class="btn btn-danger">
                DELETE
            </button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2"
        crossorigin="anonymous"></script>
</body>

</html>