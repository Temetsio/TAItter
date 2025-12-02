<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Asetukset – seuratut & tykätyt</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        h2 {
            margin-top: 40px;
        }
        .item {
            background: white;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .nav {
            margin-bottom: 20px;
        }
        .nav a {
            margin-right: 20px;
        }
        button {
            background: #ff4f4f;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        a {
            color: #1d9bf0;
            text-decoration: none;
        }
    </style>
</head>

<body>

<h1>Seuratut aihetunnisteet & tykätyt käyttäjät</h1>

<div class="nav">
    <a href="index.php">Feed</a>
    <a href="profile.php">Profiili</a>
    <a href="logout.php">Kirjaudu ulos</a>
</div>

<h2>Seuratut #aihetunnisteet</h2>
<div id="tags"></div>

<h2>Tykätyt @käyttäjät</h2>
<div id="likedUsers"></div>

<script>
function loadTags() {
    fetch("../api/hashtags/followed_tags.php")
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById("tags");
            container.innerHTML = "";

            if (data.length === 0) {
                container.innerHTML = "<i>Et seuraa vielä yhtään hashtagia.</i>";
                return;
            }

            data.forEach(tag => {
                const row = document.createElement("div");
                row.className = "item";
                row.innerHTML = `
                    <a href="hashtag.php?tag=${tag.tag}">#${tag.tag}</a>
                    <button onclick="unfollowTag('${tag.tag}')">Poista</button>
                `;
                container.appendChild(row);
            });
        });
}

function unfollowTag(tag) {
    fetch("../api/hashtags/follow_tag.php", {
        method: "DELETE",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({tag})
    })
    .then(r => r.json())
    .then(() => loadTags());
}


function loadLikedUsers() {
    fetch("../api/users/liked_users.php")
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById("likedUsers");
            container.innerHTML = "";

            if (data.length === 0) {
                container.innerHTML = "<i>Et ole tykännyt vielä yhdestäkään käyttäjästä.</i>";
                return;
            }

            data.forEach(user => {
                const row = document.createElement("div");
                row.className = "item";
                row.innerHTML = `
                    <a href="profile.php?user=${user.username}">@${user.username}</a>
                    <button onclick="unlikeUser('${user.username}')">Poista</button>
                `;
                container.appendChild(row);
            });
        });
}

function unlikeUser(username) {
    fetch("../api/users/like_user.php", {
        method: "DELETE",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({username})
    })
    .then(r => r.json())
    .then(() => loadLikedUsers());
}

loadTags();
loadLikedUsers();
</script>

</body>
</html>
