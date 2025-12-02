<?php
session_start();
if (isset($_SESSION['user_id'])) {
header("Location: /public/");
exit();
}
?>
<DOCTYPE html>
    <html lang="fi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Kirjautuminen</title>
            <link rel="stylesheet" href="styles.css">

            
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        header {
            background: #1d9bf0;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }

        #postForm {
            background: white;
            padding: 15px;
            margin: 15px auto;
            width: 500px;
            border-radius: 10px;
        }

        textarea {
            width: 100%;
            height: 60px;
            resize: none;
        }

        #posts {
            width: 500px;
            margin: 20px auto;
        }

        .post {
            background: white;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 10px;
        }

        .post small {
            color: gray;
        }

        a {
            color: #1d9bf0;
            text-decoration: none;
        }

        .nav {
            text-align: center;
            margin: 10px;
        }

        .nav a {
            margin: 0 15px;
        }
    </style>
</head>

<body>

<header>TAItter</header>

<div class="nav">
    <a href="profile.php">Profiili</a>
    <a href="settings.php">Seurattavat</a>
    <a href="logout.php">Kirjaudu ulos</a>
</div>

<div id="postForm">
    <h3>Luo uusi postaus</h3>
    <textarea id="newPost" maxlength="144" placeholder="Mitä kuuluu? (144 merkkiä max)"></textarea>
    <br><br>
    <button onclick="sendPost()">Julkaise</button>
</div>


<div id="posts"></div>


<script>
function loadFeed() {
    fetch("../api/posts/feed.php")
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById("posts");
            container.innerHTML = "";

            data.forEach(post => {
                const div = document.createElement("div");
                div.className = "post";

                let content = post.content
                    .replace(/#(\w+)/g, '<a href="hashtag.php?tag=$1">#$1</a>')
                    .replace(/@(\w+)/g, '<a href="profile.php?user=$1">@$1</a>');

                div.innerHTML = `
                    <strong><a href="profile.php?user=${post.username}">@${post.username}</a></strong><br>
                    ${content}<br><br>
                    <small>${post.created_at}</small>
                `;
                container.appendChild(div);
            });
        });
}

function sendPost() {
    const text = document.getElementById("newPost").value;

    if (text.trim() === "") {
        alert("Postaus ei voi olla tyhjä!");
        return;
    }

    fetch("../api/posts/create_post.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({content: text})
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "ok") {
            document.getElementById("newPost").value = "";
            loadFeed();
        } else {
            alert("Virhe postauksen lähetyksessä.");
        }
    });
}

loadFeed();
</script>

</body>
</html>
