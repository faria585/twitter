<?php
session_start();

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get profile user details
$profile_username = $_GET['user'];
$stmt = $conn->prepare("SELECT id, username FROM users WHERE username = ?");
$stmt->execute([$profile_username]);
$profile_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if profile user exists
if (!$profile_user) {
    die("User not found.");
}

$profile_user_id = $profile_user['id'];

// Check if the user is following the profile user
$stmt = $conn->prepare("SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?");
$stmt->execute([$user_id, $profile_user_id]);
$following = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle follow/unfollow
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['follow'])) {
    if ($following) {
        // Unfollow
        $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$user_id, $profile_user_id]);
    } else {
        // Follow
        $stmt = $conn->prepare("INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $profile_user_id]);
    }
    header("Location: profile.php?user=" . $profile_username);
    exit();
}

// Fetch user's tweets
$stmt = $conn->prepare("SELECT tweet_content, created_at FROM tweets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_user_id]);
$tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | My Twitter</title>
    <style>
        /* Add similar styles as in home.php */
    </style>
</head>
<body>
    <header>
        <h1>@<?php echo htmlspecialchars($profile_user['username']); ?>'s Profile</h1>
    </header>
    <div class="container">
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>

        <!-- Follow/Unfollow Button -->
        <form method="POST" action="profile.php?user=<?php echo urlencode($profile_username); ?>">
            <button type="submit" name="follow">
                <?php echo $following ? "Unfollow" : "Follow"; ?>
            </button>
        </form>

        <!-- Display Tweets -->
        <div class="tweets">
            <h2><?php echo htmlspecialchars($profile_user['username']); ?>'s Tweets</h2>
            <?php foreach ($tweets as $tweet): ?>
                <div class="tweet">
                    <p><?php echo htmlspecialchars($tweet['tweet_content']); ?></p>
                    <small><?php echo $tweet['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
