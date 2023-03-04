
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pdf";
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get form data
    $id = $_POST["id"];
    $filename = $_POST["filename"];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["pdf"]["name"]);
    $pdfFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if PDF file is valid
    if($pdfFileType != "pdf") {
        echo "Sorry, only PDF files are allowed.";
        exit();
    }

    // Upload PDF file
    if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["pdf"]["name"])). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
        exit();
    }

    // Insert record into database
    $sql = "INSERT INTO pdf_table (id, filename, pdf_path) VALUES ('$id', '$filename', '$target_file')";
    if (mysqli_query($conn, $sql)) {
        echo "Record inserted successfully.";
    } else {
        echo "Error inserting record: " . mysqli_error($conn);
    }

    // Close database connection
    mysqli_close($conn);
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['pdf_name'])) {
    // Get database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pdf";
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get list of PDF files from database
    $pdf_name = $_GET['pdf_name'];
    $sql = "SELECT filename, pdf_path FROM pdf_table WHERE filename LIKE '%$pdf_name%' AND pdf_path LIKE '%.pdf%' ORDER BY filename";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Display list of PDF files with links to view them
        echo "<h2>List of PDF Files:</h2>";
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf_filename = htmlspecialchars($row["filename"]);
            $pdf_path = $row["pdf_path"];
            echo "<p><a href=\"".$_SERVER["PHP_SELF"]."?pdf_path=".urlencode($pdf_path)."\">$pdf_filename</a></p>";
        }
    } else {
        echo "No PDF files found.";
    }

    // Close database connection
    mysqli_close($conn);

} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['pdf_path'])) {
    // Get PDF path from query parameter
    $pdf_path = $_GET['pdf_path'];

    // Display PDF in browser
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($pdf_path) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    @readfile($pdf_path);
}
?>




<!DOCTYPE html>
<html>
<head>
	<title>PDF Upload Form</title>


</head>
<body>
	<h1>Upload PDF File</h1>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
		<label for="id">ID:</label>
		<input type="text" name="id" id="id" required><br><br>
		<label for="filename">Filename:</label>
		<input type="text" name="filename" id="filename" required><br><br>
		<label for="pdf">PDF File:</label>
		<input type="file" name="pdf" id="pdf" required><br><br>
		<input type="submit" value="Upload PDF">
	</form>

	<h1>View PDF File</h1>
	<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
		<label for="pdf_name">PDF Name:</label>
		<input type="text" name="pdf_name" id="pdf_name"><br><br>
		<label for="pdf_id">PDF ID:</label>
		<input type="text" name="pdf_id" id="pdf_id"><br><br>
		<input type="submit" value="View PDF">
	</form>

</body>
