<?php
session_start(); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

$message = "";


$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fname = $conn->real_escape_string($_POST["fname"]);
  $lname = $conn->real_escape_string($_POST["lname"]);
  $email = $conn->real_escape_string($_POST["email"]);
  $company = $conn->real_escape_string($_POST["company"]);
  $jobtitle = $conn->real_escape_string($_POST["jobtitle"]);
  $phone = $conn->real_escape_string($_POST["phone"]);
  $country = $conn->real_escape_string($_POST["country"]);

  $sql = "INSERT INTO contact_info (fname, lname, email, company, jobtitle, phone, country)
          VALUES ('$fname', '$lname', '$email', '$company', '$jobtitle', '$phone', '$country')";

  if ($conn->query($sql) === TRUE) {
    $_SESSION['success_msg'] = "Thank you! We'll get in touch soon.";
    header("Location: contact.php");  
    exit();
  } else {
    $_SESSION['error_msg'] = "Error: " . $conn->error;
    header("Location: contact.php");
    exit();
  }

  $conn->close();
}

?>

<?php include('includes/header.php'); ?>

<main class = "content">
    <section class="contact-form">
        <h2>Contact Us.</h2>
        <?php
            if (isset($_SESSION['success_msg'])) {
            echo "<p class='success-msg'>" . $_SESSION['success_msg'] . "</p>";
            unset($_SESSION['success_msg']);
            }
            if (isset($_SESSION['error_msg'])) {
            echo "<p class='error-msg'>" . $_SESSION['error_msg'] . "</p>";
            unset($_SESSION['error_msg']);
            }
        ?>
            <form class="contact-form-grid" action="contact.php" method="post">
                <input type="text" name="fname" id="fname" placeholder="First Name:*" required>
                <input type="text" name="lname" id="lname" placeholder="Last Name:*" required>
                <input type="email" name="email" id="id" placeholder="Work Email:*" required>
                <input type="text" name="company" id="company" placeholder="Company:*" required>
                <input type="text" name="jobtitle" id="jobtitle" placeholder="Job Title:*" required>
                <input type="tel" name="phone" placeholder="Enter phone number" pattern="[0-9]{10}" title="Please enter a 10-digit number" required>
                <select id="country" name="country" class="centered-select" required>
                    <option value="">Select your country</option>
                    <option>India</option>
                </select>
                <p class="form-agreement">
                    By clicking "Send Message", you agree to our <a href="#">Terms & Conditions</a>.
                </p>
                <button type="submit" class="btn">SUBMIT</button>
            </form> 
    </section>
</main>
<?php include('includes/footer.php'); ?>