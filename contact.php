<?php
session_start();
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        die("Ongeldige CSRF-token.");
    }

    $naam      = filter_input(INPUT_POST, 'naam', FILTER_SANITIZE_SPECIAL_CHARS);
    $email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $onderwerp = filter_input(INPUT_POST, 'onderwerp', FILTER_SANITIZE_SPECIAL_CHARS);
    $bericht   = filter_input(INPUT_POST, 'bericht', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($naam) || empty($email) || empty($onderwerp) || empty($bericht)) {
        $error = "Vul alle verplichte velden in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geef een geldig e-mailadres op.";
    }

    if (empty($error)) {
        $to = "info@jouwdomein.nl"; // <-- pas aan naar jouw mailadres!
        $subject = "Nieuw bericht via contactformulier: $onderwerp";
        $message = "Naam: $naam\nE-mail: $email\n\nBericht:\n$bericht";
        $headers = "From: $email\r\nReply-To: $email";

        mail($to, $subject, $message, $headers);

        header("Location: contact.php?sent=1");
        exit;
    }
}

if (isset($_GET['sent'])) {
    $success = "Bedankt! Je bericht is verzonden.";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact | Cultuur Podium De Bilt</title>
  <link rel="stylesheet" href="style.css" />

  <style>
    .btn-home {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 18px;
        background-color: #444;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        font-size: 14px;
    }
    .btn-home:hover {
        background-color: #222;
    }
  </style>

</head>
<body>
  <div class="container">
    <h1>Contact</h1>

    <a href="Home-Pagina.html" class="btn-home">Home</a>

    <?php if (!empty($success)) echo "<div class='success'>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form action="" method="POST">

      <label for="naam">Naam *</label>
      <input type="text" id="naam" name="naam" required />

      <label for="email">E-mailadres *</label>
      <input type="email" id="email" name="email" required />

      <label for="onderwerp">Onderwerp *</label>
      <input type="text" id="onderwerp" name="onderwerp" required />

      <label for="bericht">Bericht *</label>
      <textarea id="bericht" name="bericht" rows="5" required></textarea>

      <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

      <button type="submit">Verzenden</button>
    </form>
  </div>
</body>
</html>
