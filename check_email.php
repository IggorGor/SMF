<?php
set_time_limit(0);
global $db_server, $db_user, $db_passwd, $db_name, $db_port;
require_once 'Settings.php';

$connect = mysqli_connect($db_server, $db_user, $db_passwd, $db_name, $db_port);

$query = "select m.id_member, m.email_address, m.posts from members m";
$result = mysqli_query($connect, $query);

// Prepare the queries
$deleteMemberQuery = mysqli_prepare($connect, "DELETE FROM members WHERE id_member=?");
$deleteMailQueueQuery = mysqli_prepare($connect, "DELETE FROM mail_queue WHERE recipient=?");

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<title>Удаление зловредных посетителей</title>
</head>
<body>
<table class="table table_stripped">
	<tr>
		<th>Member ID</th>
		<th>Email Address</th>
		<th>Posts</th>
		<th>Result</th>
	</tr>
	<?php
	while ($row = mysqli_fetch_assoc($result)) {
		$emailParts = explode('@', $row['email_address']);
		$domain = array_pop($emailParts);

		$checkDomain = checkdnsrr($domain, 'MX');
		// If the domain is not valid and there are no posts by the user...
		if (!$checkDomain)
			if ($row['posts'] == 0) {
			mysqli_stmt_bind_param($deleteMemberQuery, 'i', $row['id_member']);
			mysqli_stmt_execute($deleteMemberQuery);
			mysqli_stmt_bind_param($deleteMailQueueQuery, 's', $row['email_address']);
			mysqli_stmt_execute($deleteMailQueueQuery);
			echoRow($row, "Invalid Domain - User and Associated Mails Deleted");
		} else {
			echoRow($row, "Invalid Domain - User Not Deleted");
		}
	}

	function echoRow($row, $note): void
	{
		echo "<tr>";
		echo "<td>" . $row["id_member"] . "</td>";
		echo "<td>" . $row["email_address"] . "</td>";
		echo "<td>" . $row["posts"] . "</td>";
		echo "<td>" . $note . "</td>";
		echo "</tr>";
	}

	?></table>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>