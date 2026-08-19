<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
?>

<html>
<body style="font-family: Arial; font-size: 15px">
    
<? 
		// If form NOT submitted, show the form
		// -----------------------------------------------
	if ($_POST["submit"] == "")
	{
?>
	<center>
		
	<br><br><br><br>
		
    <form method="post" action="mailtest0320tx5753n.php">
        To: <input type="email" name="to"><br><br>
        Subject: <input type="text" name="subject"><br><br>
        Message:<br><textarea name="message" rows="10"></textarea><br><br>
        <input type="submit" name="submit">
    </form>
    
	</center>
<?
	}
		// If form submitted, show what you're sending and then send email
		// -----------------------------------------------
	else
	{
			// First show what you're sending
			// -------------------------------
		?>
		<center>
		
		<br><br><br><br>
		
		Sending email:<br><br>
		<b>To:</b> <?=$_POST["to"]?><br><br>
		<b>Subject:</b> <?=$_POST["subject"]?><br><br>
		<b>Message:</b> <br>
		<?=$_POST["message"]?><br><br>
		
			<!-- Then send email; the mail function returns "1" if successful -->
			<!-- ------------------------------- -->
		
		<?php
			$headers = "From: mailer@hris.maidenlanemedical.com" . "\r\n" . "Reply-To: mailer@hris.maidenlanemedical.com" . "\r\n" . "X-Mailer: PHP";
    	?>
		
		<b>Result ("1" means success):</b> <?= mail($_POST["to"], $_POST["subject"], $_POST["message"], $headers) ?>
		</center>
	<?
	}
	?>
	
</body>
</html>