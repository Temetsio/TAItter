# TAItter

Taitter on Twitter-tyyppinen sosiaalisen median sovellus TAI:n opiskelijoille, joka on rakennettu PHP:lla ja MariaDB-tietokannalla.


## TIIMI
- Jenni: Tietokantasuunnittelu ja -toteutus, php, readme.md
- Vesku: Backend-arkkitehtuuri ja tietokantayhteys, php
- Meleqe: UI/UX-suunnittelu, HTML/CSS, powerpoint
- Teemu: Github reposition luominen

## Ominaisuudet

- **Viestit (Posts)**: Käyttäjät voivat julkaista enintään 144 merkin pituisia viestejä
- **Seuraaminen**: Käyttäjät voivat seurata toisiaan
- **Hashtagit**: Viesteihin voi lisätä hashtageja ja käyttäjät voivat seurata hashtageja
- **Maininnat**: Käyttäjät voivat mainita toisia käyttäjiä viesteissään
- **Tykkäykset**: Viestejä voi tykätä
- **Uudelleenjako (Reposts)**: Viestejä voi jakaa eteenpäin
- **Kommentit**: Viesteihin voi kommentoida
- **Käyttäjäprofiilit**: Käyttäjillä on profiili, jossa bio ja profiilikuva
- **Muokkaus**: Omia viestejä ja kommentteja voi muokata ja poistaa

## Testidataa

Tietokannassa on valmiina 5 testikäyttäjää:
- Matti (koodari)
- Liisa (musiikin ystävä)
- Pekka (ruoka ja matkailu)
- Vesku
- Jenni (kahvia, koodausta ja testausta)

## Tietokantarakenne

### TAULUT  

### Käyttäjät (users)

- `user_id`: (PK, INT, AUTO_INCREMENT)
- `username`: (VARCHAR 50, UNIQUE)
- `email`: (VARCHAR 100, UNIQUE)
- `password_hash`: (VARCHAR 255)
- `bio`: (TEXT)
- `profile_picture_url`: (VARCHAR 255)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `last_seen_mentions`: (DATETIME)
- `last_seen_shares`: (DATETIME)
- `last_seen_likes`: (DATETIME)

### Viestit (posts)

- `post_id`: (PK, INT, AUTO_INCREMENT)
- `user_id`: (FK viittaa `käyttäjät`, INT)
- `content`: (VARCHAR 144)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `edited_at`: (DATETIME)

### Seuraaminen (follows)

- `follow_id`: (PK, INT, AUTO_INCREMENT)
- `follower_id`: (FK viittaa `käyttäjät`, INT)
- `following_id`: (FK viittaa `käyttäjät`, INT)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `UNIQUE constraint`: (follower_id, following_id)

### Hashtagit (hashtags)

- `hashtag_id`: (PK, INT, AUTO_INCREMENT)
- `tag_name`: (VARCHAR 50, UNIQUE)

### Viestien hashtagit (post_hashtags)

- `id`: (PK, INT, AUTO_INCREMENT)
- `post_id`: (FK viittaa `viestit`, INT)
- `hashtag_id`: (FK viittaa `hashtags`, INT)
- `UNIQUE constraint`: (post_id, hashtag_id)

### Seuratut hashtagit (followed_hashtags)

- `id`: (PK, INT, AUTO_INCREMENT)
- `user_id`: (FK viittaa `käyttäjät`, INT)
- `hashtag_id`: (FK viittaa `hashtags`, INT)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `UNIQUE constraint`: (user_id, hashtag_id)

### Tykkäykset (likes)

- `like_id`: (PK, INT, AUTO_INCREMENT)
- `user_id`: (FK viittaa `käyttäjät`, INT)
- `post_id`: (FK viittaa `viestit`, INT)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `UNIQUE constraint`: (user_id, post_id)

### Uudelleenjaot (reposts)

- `repost_id`: (PK, INT, AUTO_INCREMENT)
- `user_id`: (FK viittaa `käyttäjät`, INT)
- `post_id`: (FK viittaa `viestit`, INT)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

### Kommentit (comments)

- `comment_id`: (PK, INT, AUTO_INCREMENT)
- `post_id`: (FK viittaa `viestit`, INT)
- `user_id`: (FK viittaa `käyttäjät`, INT)
- `content`: (TEXT)
- `created_at`: (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
- `edited_at`: (DATETIME)

### Maininnat (mentions)

- `mention_id`: (PK, INT, AUTO_INCREMENT)
- `post_id`: (FK viittaa `viestit`, INT)
- `mentioned_user_id`: (FK viittaa `käyttäjät`, INT)

---

### RELAATIOT

- `users`  (1) → (n) `posts`
- `users` (1) → (n) `comments`
- `users` (1) → (n) `likes`
- `users` (1) → (n) `reposts`
- `users` (n) ↔ (n) `users` (`follows`-välitaulu, follower_id ↔ following_id)
- `users` (n) ↔ (n) `hashtags` (`followed_hashtags`-välitaulu)
- `posts` (1) → (n) `comments`
- `posts` (1) → (n) `likes`
- `posts` (1) → (n) `reposts`
- `posts` (1) → (n) `mentions`
- `posts` (n) ↔ (n) `hashtags` (`post_hashtags`-välitaulu)

## Tekninen toteutus

- **Tietokanta**: MariaDB 10.4.32
- **PHP**: 8.2.12
- **Merkistö**: UTF-8 (utf8mb4)
- **Indeksointi**: Optimoitu kyselyille (käyttäjä- ja hashtaghaut)

## Asennus

### TIETOKANNAN TUONTI(XAMPP)

1. Käynnistä XAMPP (Apache + MySQL)  
2. Avaa phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)  
3. Luo uusi tietokanta: `taitter`  
4. Valitse tietokanta  
5. Klikkaa **Import**  
6. Valitse tiedosto: `taitter.sql`  
7. Klikkaa **Go**  

---

### TIETOKANTAYHTEYDEN KONFIGUROINTI

Luo tiedosto `config/db_connect.php`:

```php
<?php
$host = 'localhost';
$db   = 'taitter';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Tietokantayhteys epäonnistui: " . $e->getMessage());
}
?>
```

### TEKNOLOGIAT

```
Tietokanta: MySQL / MariaDB
Backend: PHP 8.x
Frontend: HTML5, CSS3, JavaScript
Palvelin: Apache (XAMPP)
```

### PROJEKTIRAKENNE
`````
taitter/
├──interation/
│   ├── comment.php/
│   ├── config.php/
│   ├── debug_edit_comment.php/
│   ├── delete_comment.php/
│   ├── delete_post.php/
│   ├── edit_comment.php/
│   ├── edit_post.php/
│   ├── edit_profile/
│   ├── fetch_like.php/
│   ├── fetch_posts.php/
│   ├── follow.php/
│   ├── follow_hashtag.php/
│   ├── get_comments.php/
│   ├── index.php/
│   ├── like.php/
│   ├── login.php/
│   ├── logout.php/
│   ├── mark_seen.php/
│   ├── post.php/
│   ├── profile.php/
│   ├── register.css/
│   ├── repost.php/
├── README.md
├── taitter.sql
`````

### KÄYNNISTYS
```
1. Kopioi projektikansio: C:\xampp\htdocs\taitter\
2. Tuo tietokanta (ks. Asennus)
3. Avaa selaimessa: http://localhost/taitter/
```


## Yhteystiedot

Projekti tehty osana Juhannuskukkulan tietokantakurssia.

**Tiimi**: Meleqe, Teemu, Vesku ja Jenni

**Päivämäärä**: Lokakuu 2025

## Lisenssi

Projekti on opiskelijaprojekti.
