<?php
require_once 'config.php';
if (!current_user_id()) { header('Location: login.php'); exit; }

$filterHashtag = $_GET['hashtag'] ?? null;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Taitter - timeline</title>
<style>
:root{font-family:Arial,Helvetica,sans-serif}
.container {display:grid; grid-template-columns: 250px 1fr 300px; gap:16px; max-width:1200px; margin:20px auto;}
.card {border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:6px; background:#fff;}
.left, .right {position:sticky; top:10px;}
textarea {width:100%; min-height:60px;}
.card-header small{color:#666; font-size:90%;}
a {text-decoration:none; color:#0366d6;}
</style>
</head>
<body>
<div style="max-width:1200px;margin:10px auto;text-align:right;">
    Logged in as <strong><?=htmlspecialchars(current_username())?></strong> — <a href="logout.php">Logout</a>
</div>

<div class="container">
  <div class="left">
    <div class="card">
      <h3>Profile</h3>
      <p><strong><?=htmlspecialchars(current_username())?></strong></p>
      <p><a href="profile.php?u=<?=urlencode(current_username())?>">View profile</a></p>
      <p><a href="index.php">Home</a></p>
    </div>
    <div class="card">
      <h4>Following hashtags</h4>
      <?php
      $stmt = $mysqli->prepare("SELECT h.tag_name FROM followed_hashtags fh JOIN hashtags h ON fh.hashtag_id=h.hashtag_id WHERE fh.user_id = ? LIMIT 10");
      $uid = current_user_id(); $stmt->bind_param('i',$uid); $stmt->execute(); $r=$stmt->get_result();
      while($row=$r->fetch_assoc()){
        echo '<div><a href="index.php?hashtag='.urlencode($row['tag_name']).'">#'.htmlspecialchars($row['tag_name']).'</a></div>';
      }
      ?>
    </div>
  </div>

  <div>
    <div class="card">
      <form method="post" action="post.php">
        <textarea name="content" maxlength="144" placeholder="What's happening?"></textarea><br>
        <button>Post</button>
      </form>
    </div>

    <div id="feed">
      <?php include 'fetch_posts.php'; ?>
    </div>
  </div>

  <div class="right">
    <div class="card">
      <h4>Trending hashtags</h4>
      <?php
      $q = "SELECT h.tag_name, COUNT(*) AS cnt
      FROM post_hashtags ph
      INNER JOIN hashtags h ON ph.hashtag_id = h.hashtag_id
      WHERE h.tag_name <> ''
      GROUP BY h.tag_name
      HAVING cnt > 0
      ORDER BY cnt DESC
      LIMIT 10";

      $r = $mysqli->query($q);
      while ($row = $r->fetch_assoc()) {
          echo '<div><a href="index.php?hashtag='.urlencode($row['tag_name']).'">#'.htmlspecialchars($row['tag_name'])."</a> ({$row['cnt']})</div>";
      }
      ?>
    </div>

    <div class="card">
      <h4>Search</h4>
      <form method="get" action="index.php">
        <input name="hashtag" placeholder="hashtag (without #)" value="<?=htmlspecialchars($filterHashtag)?>">
        <button>Filter</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
