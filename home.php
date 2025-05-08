<?php
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'db.php';

// Get user data from session
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle posting a tweet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet'])) {
    $tweet = trim($_POST['tweet']);
    if (!empty($tweet)) {
        // Insert tweet into the database
        $stmt = $conn->prepare("INSERT INTO tweets (user_id, tweet_content) VALUES (?, ?)");
        $stmt->execute([$user_id, $tweet]);
        header("Location: home.php");  // Refresh the page to show the new tweet
        exit();
    } else {
        $error = "Tweet cannot be empty!";
    }
}

// Fetch all tweets from the database
$stmt = $conn->prepare("
    SELECT tweets.id, tweets.tweet_content, tweets.created_at, users.username 
    FROM tweets 
    JOIN users ON tweets.user_id = users.id 
    ORDER BY tweets.created_at DESC
");
$stmt->execute();
$tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | My Twitter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #1DA1F2;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        .tweet-box {
            margin-top: 20px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .tweet-box textarea {
            width: 100%;
            height: 80px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            resize: none;
        }
        .tweet-box button {
            background-color: #1DA1F2;
            color: #fff;
            border: none;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .tweet-box button:hover {
            background-color: #0d8adf;
        }
        .tweets {
            margin-top: 20px;
        }
        .tweet {
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .tweet h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #1DA1F2;
        }
        .tweet p {
            margin: 0;
            font-size: 14px;
        }
        .tweet small {
            color: #666;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .logout {
            text-align: right;
            margin-top: 10px;
        }
        .logout a {
            text-decoration: none;
            color: #f00;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    </header>
    <div class="container">
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>

        <!-- Tweet Form -->
        <div class="tweet-box">
            <form method="POST" action="home.php">
                <textarea name="tweet" placeholder="What's happening?" required></textarea>
                <button type="submit">Tweet</button>
            </form>
            <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
        </div>

        <!-- Display Tweets -->
        <div class="tweets">
            <h2>Recent Tweets</h2>
            <?php foreach ($tweets as $tweet): ?>
                <div class="tweet">
                    <h3><a href="profile.php?user=<?php echo urlencode($tweet['username']); ?>"><?php echo htmlspecialchars($tweet['username']); ?></a></h3>
                    <p><?php echo htmlspecialchars($tweet['tweet_content']); ?></p>
                    <small><?php echo $tweet['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
