<?php
require_once 'config.php';
if (!current_user_id()) { header('Location: login.php'); exit; }

$filterHashtag = $_GET['hashtag'] ?? null;
$uid = current_user_id();
$like = '%@'.current_username().'%';
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
.dropdown { position:relative; display:inline-block; }
.dd-menu {
  display:none;
  position:absolute;
  right:0;
  top:26px;
  background:#fff;
  border:1px solid #ccc;
  width:320px;
  max-height:300px;
  overflow-y:auto;
  z-index:999;
}
.dd-menu div {
  padding:8px;
  border-bottom:1px solid #eee;
  font-size:90%;
}
.dd-menu small { color:#666; }
.dd-menu.show { display:block; }
.dd-item:hover { background:#f5f5f5; cursor:pointer; }
</style>
</head>
<body>

<div style="max-width:1200px;margin:10px auto;display:flex;justify-content:space-between;align-items:center;">

  <div>
    Logged in as <strong><?=htmlspecialchars(current_username())?></strong>
  </div>

  <div style="display:flex;gap:15px;align-items:center;">

    <?php
    $stmt = $mysqli->prepare("
      SELECT COUNT(*) AS cnt
      FROM posts p
      WHERE p.content LIKE ?
        AND p.user_id != ?
        AND p.created_at > IFNULL(
          (SELECT last_seen_mentions FROM users WHERE user_id = ?),
          '1970-01-01'
        )
    ");
    $stmt->bind_param("sii",$like,$uid,$uid);
    $stmt->execute();
    $m = $stmt->get_result()->fetch_assoc()['cnt'];


    $stmt = $mysqli->prepare("
      SELECT COUNT(*) AS cnt
      FROM reposts r
      JOIN posts p ON r.post_id = p.post_id
      WHERE p.user_id = ?
        AND r.created_at > IFNULL(
          (SELECT last_seen_shares FROM users WHERE user_id = ?),
          '1970-01-01'
        )
    ");
    $stmt->bind_param("ii",$uid,$uid);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_assoc()['cnt'];

    $stmt = $mysqli->prepare("
      SELECT COUNT(*) AS cnt
      FROM likes l
      JOIN posts p ON l.post_id = p.post_id
      WHERE p.user_id = ?
        AND l.created_at > IFNULL(
          (SELECT last_seen_likes FROM users WHERE user_id = ?),
          '1970-01-01'
        )
    ");
    $stmt->bind_param("ii",$uid,$uid);
    $stmt->execute();
    $lk = $stmt->get_result()->fetch_assoc()['cnt'];
    ?>

    <div class="dropdown">
      <a href="#" onclick="toggleDropdown('mentions', this);return false;">
        🔔 Mentions <span class="badge"><?= $m ?></span>
      </a>
      <div id="mentions" class="dd-menu">
        <?php
        $stmt = $mysqli->prepare("
          SELECT u.username AS username, p.content AS content, p.created_at AS created_at
          FROM posts p
          JOIN users u ON p.user_id = u.user_id
          WHERE p.content LIKE ?
            AND p.user_id != ?
            AND p.created_at > IFNULL(
              (SELECT last_seen_mentions FROM users WHERE user_id = ?),
              '1970-01-01'
            )
          ORDER BY p.created_at DESC
          LIMIT 10
        ");
        $stmt->bind_param("sii",$like,$uid,$uid);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) {
            echo "<div class='dd-item'>
              <b>@".htmlspecialchars($row['username'])."</b><br>
              ".htmlspecialchars($row['content'])."<br>
              <small>{$row['created_at']}</small>
            </div>";
        }
        ?>
      </div>
    </div>

    <div class="dropdown">
      <a href="#" onclick="toggleDropdown('shares', this);return false;">
        🔁 Shares <span class="badge"><?= $s ?></span>
      </a>
      <div id="shares" class="dd-menu">
        <?php
        $stmt = $mysqli->prepare("
          SELECT u.username AS username, p.content AS content, r.created_at AS created_at
          FROM reposts r
          JOIN posts p ON r.post_id = p.post_id
          JOIN users u ON r.user_id = u.user_id
          WHERE p.user_id = ?
            AND r.created_at > IFNULL(
              (SELECT last_seen_shares FROM users WHERE user_id = ?),
              '1970-01-01'
            )
          ORDER BY r.created_at DESC
          LIMIT 10
        ");
        $stmt->bind_param("ii",$uid,$uid);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) {
            echo "<div class='dd-item'>
              🔁 <b>".htmlspecialchars($row['username'])."</b> shared:<br>
              ".htmlspecialchars($row['content'])."<br>
              <small>{$row['created_at']}</small>
            </div>";
        }
        ?>
      </div>
    </div>


    <div class="dropdown">
      <a href="#" onclick="toggleDropdown('likes', this);return false;">
        ❤️ Likes <span class="badge"><?= $lk ?></span>
      </a>
      <div id="likes" class="dd-menu">
        <?php
        $stmt = $mysqli->prepare("
          SELECT u.username AS username, p.content AS content, l.created_at AS created_at
          FROM likes l
          JOIN posts p ON l.post_id = p.post_id
          JOIN users u ON l.user_id = u.user_id
          WHERE p.user_id = ?
            AND l.created_at > IFNULL(
              (SELECT last_seen_likes FROM users WHERE user_id = ?),
              '1970-01-01'
            )
          ORDER BY l.created_at DESC
          LIMIT 10
        ");
        $stmt->bind_param("ii",$uid,$uid);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) {
            echo "<div class='dd-item'>
              ❤️ <b>".htmlspecialchars($row['username'])."</b> liked:<br>
              ".htmlspecialchars($row['content'])."<br>
              <small>{$row['created_at']}</small>
            </div>";
        }
        ?>
      </div>
    </div>

    <a href="logout.php">Logout</a>
  </div>
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
      $stmt->bind_param('i',$uid); $stmt->execute(); $r=$stmt->get_result();
      while($row=$r->fetch_assoc()){
        echo '<div><a href="index.php?hashtag='.urlencode($row['tag_name']).'">#'.htmlspecialchars($row['tag_name']).'</a></div>';
      }
      ?>
    </div>
    
    <div class="card">
      <h4>Following users</h4>
      <?php
      $stmt = $mysqli->prepare("
        SELECT u.username 
        FROM follows f 
        JOIN users u ON f.following_id = u.user_id 
        WHERE f.follower_id = ? 
        ORDER BY f.created_at DESC
        LIMIT 10
      ");
      $stmt->bind_param('i',$uid); 
      $stmt->execute(); 
      $r=$stmt->get_result();
      if($r->num_rows > 0) {
        while($row=$r->fetch_assoc()){
          echo '<div><a href="profile.php?u='.urlencode($row['username']).'">@'.htmlspecialchars($row['username']).'</a></div>';
        }
      } else {
        echo '<div style="color:#999;font-size:90%;">Not following anyone yet</div>';
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

<script>
function toggleDropdown(id, el) {
  document.querySelectorAll('.dd-menu').forEach(menu => {
    if (menu.id !== id) menu.classList.remove('show');
  });

  let box = document.getElementById(id);
  let open = box.classList.toggle('show');

  if (open) {
    markSeen(id);
    let badge = el.querySelector('.badge');
    if(badge) badge.textContent = '0';
  }
}

function markSeen(type) {
  fetch("mark_seen.php?type=" + type);
}

function editPost(postId, currentContent) {
  let contentDiv = document.querySelector('.post-content-' + postId);
  let form = document.createElement('form');
  form.onsubmit = function(e) {
    e.preventDefault();
    savePost(postId);
  };
  
  let textarea = document.createElement('textarea');
  textarea.id = 'edit-' + postId;
  textarea.value = currentContent;
  textarea.style.width = '100%';
  textarea.style.minHeight = '60px';
  textarea.maxLength = 144;
  
  let btnSave = document.createElement('button');
  btnSave.textContent = 'Tallenna';
  btnSave.type = 'submit';
  
  let btnCancel = document.createElement('button');
  btnCancel.textContent = 'Peruuta';
  btnCancel.type = 'button';
  btnCancel.onclick = function() {
    location.reload();
  };
  
  form.appendChild(textarea);
  form.appendChild(document.createElement('br'));
  form.appendChild(btnSave);
  form.appendChild(document.createTextNode(' '));
  form.appendChild(btnCancel);
  
  contentDiv.innerHTML = '';
  contentDiv.appendChild(form);
  textarea.focus();
}

function savePost(postId) {
  let textarea = document.getElementById('edit-' + postId);
  let content = textarea.value;
  
  let formData = new FormData();
  formData.append('post_id', postId);
  formData.append('content', content);
  
  fetch('edit_post.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (response.ok) {
      location.reload();
    } else {
      alert('Virhe tallennuksessa');
    }
  })
  .catch(err => {
    alert('Virhe: ' + err);
  });
}

function toggleLike(postId, button, uniqueCardId) {
  console.log('toggleLike called for post:', postId, 'uniqueCardId:', uniqueCardId);
  
  let formData = new FormData();
  formData.append('post_id', postId);
  
  fetch('like.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    console.log('Response status:', response.status);
    return response.json();
  })
  .then(data => {
    console.log('Response data:', data);
    
    if (!data.success) {
      alert('Virhe: ' + (data.error || 'Tuntematon virhe'));
      return;
    }
    
    let icon = document.getElementById('like-icon-' + uniqueCardId);
    let text = document.getElementById('like-text-' + uniqueCardId);
    let count = document.getElementById('like-count-' + uniqueCardId);
    
    console.log('Elements found:', {icon, text, count});
    
    if (icon && text && count) {
      if (data.action === 'liked') {
        icon.textContent = '❤️';
        text.textContent = 'Unlike';
      } else {
        icon.textContent = '🤍';
        text.textContent = 'Like';
      }
      count.textContent = data.count;
      console.log('UI updated successfully');
    } else {
      console.error('Could not find UI elements for:', uniqueCardId);
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    alert('Virhe: ' + err);
  });
}

function refreshFeed() {
  fetch('fetch_posts.php?' + new URLSearchParams(window.location.search))
    .then(response => response.text())
    .then(html => {
      document.getElementById('feed').innerHTML = html;
    })
    .catch(err => {
      console.error('Virhe feedin päivityksessä:', err);
    });
}

document.addEventListener("click", function(e) {
  if (!e.target.closest('.dropdown')) {
    document.querySelectorAll('.dd-menu').forEach(el => el.classList.remove('show'));
  }
});
</script>

</body>
</html>