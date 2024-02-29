<?php
// Get the movie name from the URL parameter
$movie = isset($_REQUEST["film"]) ? $_REQUEST["film"] : null;

// Define paths to the info, overview, reviews files, and poster directory
$infoPath = "./film/" . $movie . "/info.txt";
$overviewPath = "./film/" . $movie . "/overview.txt";
$reviewsPath = "./film/" . $movie . "/reviews.txt";
$posterDirPath = "./film/" . $movie . "/movie_poster"; // Updated path for movie posters

// Initialize variables to store movie details, reviews, and YouTube preview URL
$title = $year = $rating = $numReviews = $youtubeURL = "";
$overviewDetails = [];
$reviewQuotes = [];
$posterImages = [];

// Load movie information if the movie name is provided and the file exists
if ($movie && file_exists($infoPath) && file_exists($overviewPath) && file_exists($reviewsPath)) {
    // Process the info.txt file
    $info = file($infoPath, FILE_IGNORE_NEW_LINES);
    if (count($info) >= 4) {
        list($title, $year, $rating, $numReviews) = $info;
    }

    // Process the overview.txt file and extract YouTube preview URL
    $overviewLines = file($overviewPath, FILE_IGNORE_NEW_LINES);
    foreach ($overviewLines as $line) {
        list($key, $value) = explode(":", $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key == "YOUTUBE_PREVIEW_URL") {
            $youtubeURL = $value;
        } elseif ($key == "AWARD_WINS_TOTAL") {
            $awardWinsTotal = $value;
        } elseif ($key == "NOMINATIONS_TOTAL") {
            $nominationsTotal = $value;
        } elseif ($key == "OSCAR_WINS") {
            $oscarWins = array_map('trim', explode(',', $value)); // Split by comma and trim each entry
        } else {
            $overviewDetails[$key] = $value;
        }
    }
    
    
    // Process the reviews.txt file
    $reviewsContent = file_get_contents($reviewsPath);
    $reviewQuotes = explode('","', trim($reviewsContent, 'QUOTES: "'));
} else {
    echo "Movie not found or invalid movie name.";
    exit; // Stop script execution if movie data is not found
}

// Fetch up to two movie poster images from the movie_poster directory
if (is_dir($posterDirPath)) {
    $files = array_slice(scandir($posterDirPath), 2); // Exclude '.' and '..'
    foreach ($files as $file) {
        if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            $posterImages[] = $posterDirPath . "/" . $file;
            if (count($posterImages) == 2) break; // Limit to two posters
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Movie Review</title>
    <link rel="stylesheet" href="./css/movie.css">
    <!-- Google Materials -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<header>
    <h1>Movie Reviews <span class="material-symbols-outlined">movie</span></h1>
</header>

<main>
    <section class="movie-highlight">
        <!-- Dynamically display movie posters if available -->
        <?php foreach ($posterImages as $index => $posterImagePath): ?>
            <img src="<?php echo htmlspecialchars($posterImagePath); ?>" alt="<?php echo htmlspecialchars($title); ?> Movie Poster <?php echo $index + 1; ?>" class="movie-poster">
        <?php endforeach; ?>

        <h2><?php echo htmlspecialchars($title); ?></h2>
        
        <!-- Display movie details -->
        <?php foreach ($overviewDetails as $key => $value): ?>
            <p><strong><?php echo htmlspecialchars($key); ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
        <?php endforeach; ?>

        <!-- Display Awards Information -->
        <!-- Check for empty data before displaying -->
                <hr>
        <h3>Awards</h3>
        <?php if (!empty($awardWinsTotal)): ?>
            <p><strong>Total Award Wins:</strong> <?php echo htmlspecialchars($awardWinsTotal); ?></p>
        <?php endif; ?>
        <?php if (!empty($nominationsTotal)): ?>
            <p><strong>Total Nominations:</strong> <?php echo htmlspecialchars($nominationsTotal); ?></p>
        <?php endif; ?>
        <?php if (!empty($oscarWins)): ?>
            <p><strong>Oscar Wins:</strong> <?php echo htmlspecialchars(implode(', ', $oscarWins)); ?></p>
        <?php endif; ?>

        <!-- YouTube Preview -->
        <?php if (!empty($youtubeURL)): ?>
            <h3><span class="material-symbols-outlined">play_circle</span> Trailer</h3>
            <iframe width="560" height="315" src="<?php echo htmlspecialchars($youtubeURL); ?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        <?php endif; ?>

        <hr>
        <h3>Reviews</h3>
        <?php foreach ($reviewQuotes as $quote): ?>
            <p>"<?php echo htmlspecialchars($quote); ?>"</p>
        <?php endforeach; ?>

        <!-- Share Section -->
        <h3>Share Movie</h3>
        <img src="./img/facebook_icon.png" alt="Share on Facebook" class="social-media-icon">
        <img src="./img/twitter_icon.png" alt="Share on Twitter" class="social-media-icon">
        <img src="./img/linkedin_icon.png" alt="Share on LinkedIn" class="social-media-icon">
        <img src="./img/email.png" alt="Share via Email" class="social-media-icon">
    </section>

    <!-- Sidebar Content -->
    <aside class="sidebar">
        <h3><span class="material-symbols-outlined">local_movies</span> Latest Movie Reviews</h3>
        <h3><span class="material-symbols-outlined">trending_up</span> Trending Now</h3>
        <p><a href="#">The latest hit movies...</a></p>
        <h3>You Might Also Like</h3>
        <p><a href="#">Other animated adventures...</a></p>
    </aside>
</main>

<footer>
    <p>© 2024 Dynamic Movie Reviews page by Scott. All rights reserved.</p>
</footer>
</body>
</html>
