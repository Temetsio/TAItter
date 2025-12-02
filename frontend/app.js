function getMe(callback) {
    fetch("../api/auth/me.php")
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                console.log("Käyttäjä ei ole kirjautunut.");
                window.location = "login.php";
                return;
            }
            callback(data);
        })
        .catch(err => console.error("Virhe me(): ", err));
}




function sendPost() {
    const textarea = document.getElementById("newPost");
    const content = textarea.value;

    if (!content || content.trim() === "") {
        alert("Postaus ei voi olla tyhjä!");
        return;
    }

    fetch("../api/posts/create_post.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ content })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "ok") {
            textarea.value = "";
            if (typeof loadFeed === "function") {
                loadFeed();
            }
        } else {
            alert("Postauksen lähetys epäonnistui.");
        }
    })
    .catch(err => console.error("Virhe sendPost(): ", err));
}




function loadFeed() {
    const postsContainer = document.getElementById("posts");

    if (!postsContainer) return; 

    fetch("../api/posts/feed.php")
        .then(r => r.json())
        .then(posts => {
            postsContainer.innerHTML = "";

            if (posts.length === 0) {
                postsContainer.innerHTML = "<i>Ei näytettäviä postauksia.</i>";
                return;
            }

            posts.forEach(post => {
                const div = document.createElement("div");
                div.className = "post";

                const formatted = post.content
                    .replace(/#(\w+)/g, `<a href="hashtag.php?tag=$1">#$1</a>`)
                    .replace(/@(\w+)/g, `<a href="profile.php?user=$1">@$1</a>`);

                div.innerHTML = `
                    <strong><a href="profile.php?user=${post.username}">@${post.username}</a></strong>
                    <p>${formatted}</p>
                    <small>${post.created_at}</small>
                `;

                postsContainer.appendChild(div);
            });
        })
        .catch(err => console.error("Virhe loadFeed(): ", err));
}




function followTag(tag) {
    fetch("../api/hashtags/follow_tag.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ tag })
    })
    .then(r => r.json())
    .then(() => {
        if (typeof loadTagPosts === "function") {
            loadTagPosts();
        }
    });
}



function likeUser(username) {
    fetch("../api/users/like_user.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({ username })
    })
    .then(r => r.json())
    .then(() => {
        if (typeof loadProfile === "function") {
            loadProfile();
        }
    });
}




function api(url, method = "GET", data = null) {
    return fetch(url, {
        method,
        headers: {"Content-Type": "application/json"},
        body: data ? JSON.stringify(data) : null
    }).then(r => r.json());
}
