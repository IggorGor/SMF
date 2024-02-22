<?php
set_time_limit(0);
global $db_server, $db_user, $db_passwd, $db_name, $db_port;
require_once 'Settings.php';

$connect = mysqli_connect($db_server, $db_user, $db_passwd, $db_name, $db_port);

$query = "select m.id_member, m.email_address, m.posts from members m limit 100";
$result = mysqli_query($connect, $query);


?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Document</title>
</head>
<body>
<table>
	<tr>
		<th>Member ID</th>
		<th>Email Address</th>
		<th>Posts</th>
	</tr>
	<?php
	while ($row = mysqli_fetch_assoc($result)) {
		$emailParts = explode('@', $row['email_address']);
		$domain = array_pop($emailParts);

		// If the domain is not valid and there are no posts by the user...
		if (!checkdnsrr($domain, 'MX') && $row['posts'] === 0) {
			$deleteMemberQuery = "DELETE FROM members WHERE id_member=" . $row['id_member'];
			mysqli_query($connect, $deleteMemberQuery);
			$deleteMailQueueQuery = "DELETE FROM mail_queue WHERE recipient='" . $row['email_address'] . "'";
			mysqli_query($connect, $deleteMailQueueQuery);
			echoRow($row, "Invalid Domain - User and Associated Mails Deleted");
		} else if (!checkdnsrr($domain, 'MX')) {
			echoRow($row, "Invalid Domain - User Not Deleted");
		}
	}

	function echoRow($row, $note) {
		echo "<tr>";
		echo "<td>" . $row["id_member"] . "</td>";
		echo "<td>" . $row["email_address"] . "</td>";
		echo "<td>" . $row["posts"] . "</td>";
		echo "<td>" . $note . "</td>";
		echo "</tr>";
	}
	?></table>
</body>
</html>
