<?php
/**
 * Statement of Authorship : I, Nishtha Chaudhari, 000930353, certify that this material is my original work. No other person's work has been used without due acknowledgment and I have not made my work available to anyone else.
 * 
 * @author: Nishtha Chaudhari
 * @version: 3.0
 * @package: Assignment 4
 */

/*
 * Purpose of the Script:
 * This PHP script manages a quotes system where users can log in, log out, view quotes, and mark favorites. 
 * It interacts with a database to fetch quotes, handle user sessions, and store favorite quote selections. 
 * The script also generates a user-friendly interface using Bootstrap cards for quote display.
 */

// Database credentials - Update these to connect to your own database
$host = 'localhost'; // Server where the database is hosted
$dbname = 'sa000930353'; // Name of the database
$username = 'sa000930353'; // Database username
$password = 'Sa_20051009'; // Password for the database user

// Ensure responses are sent as JSON for frontend compatibility
header('Content-Type: application/json');

// Set up the connection to the database using PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error reporting
} catch (PDOException $e) {
    // Send an error response if the database connection fails
    echo json_encode(["error" => "Could not connect to the database: " . $e->getMessage()]);
    exit; // Stop further script execution
}

// Start the session to track user authentication
session_start();

// Handle login requests
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    // Sanitize input to remove unwanted characters
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Check if both username and password are provided
    if ($username && $password) {
        // Query the database to find the user
        $stmt = $pdo->prepare("SELECT user_id, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify the provided password against the stored hash
        if ($user && password_verify($password, $user['password'])) {
            // Save the user ID in the session for later use
            $_SESSION['validated'] = $user['user_id'];
            echo json_encode(["status" => "success", "message" => "Welcome! You are now logged in."]);
        } else {
            // Inform the user if credentials are incorrect
            echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
        }
    } else {
        // Prompt the user to provide both fields
        echo json_encode(["status" => "error", "message" => "Please enter both username and password."]);
    }
    exit; // End the login request handling
}

// Handle logout requests
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_unset(); // Clear all session variables
    session_destroy(); // Terminate the session completely
    echo json_encode(["status" => "success", "message" => "You have successfully logged out."]);
    exit; // Stop further processing
}

// Handle marking or unmarking a quote as a favorite
if (isset($_POST['action']) && $_POST['action'] === 'favourite' && isset($_SESSION['validated'])) {
    // Get and validate the quote ID from the request
    $quote_id = filter_input(INPUT_POST, 'quote', FILTER_VALIDATE_INT);
    // Check if the favorite checkbox is marked or unmarked
    $is_favorite = filter_input(INPUT_POST, 'check', FILTER_VALIDATE_BOOLEAN);

    // Process only if the quote ID is valid
    if ($quote_id) {
        if ($is_favorite) {
            // Add the quote to the user's favorites
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, quote_id) VALUES (:user_id, :quote_id)");
            $stmt->bindParam(':user_id', $_SESSION['validated'], PDO::PARAM_INT);
            $stmt->bindParam(':quote_id', $quote_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            // Remove the quote from the user's favorites
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = :user_id AND quote_id = :quote_id");
            $stmt->bindParam(':user_id', $_SESSION['validated'], PDO::PARAM_INT);
            $stmt->bindParam(':quote_id', $quote_id, PDO::PARAM_INT);
            $stmt->execute();
        }
        echo json_encode(["status" => "success", "message" => "Favorite status updated."]);
    }
    exit; // End the favorite update process
}

// Fetch and display all quotes
try {
    // Retrieve all quotes and their respective authors
    $stmt = $pdo->prepare("
        SELECT quotes.quote_id, quotes.quote_text, authors.author_name 
        FROM quotes 
        LEFT JOIN authors ON quotes.author_id = authors.author_id
    ");
    $stmt->execute();
    $quotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If the user is logged in, get their list of favorite quotes
    if (isset($_SESSION['validated'])) {
        $stmt = $pdo->prepare("SELECT quote_id FROM favorites WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['validated'], PDO::PARAM_INT);
        $stmt->execute();
        $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch favorite quote IDs
    } else {
        $favorites = []; // No favorites for unauthenticated users
    }

    // Generate Bootstrap cards for each quote
    $formattedQuotes = array_map(function($quote) use ($favorites) {
        // Check if the quote is a favorite for the current user
        $isFavorite = in_array($quote['quote_id'], $favorites) ? 'checked' : '';
        return generateCard($quote['quote_text'], $quote['author_name'], $quote['quote_id'], $isFavorite);
    }, $quotes);

    // Send the formatted cards as a JSON response
    echo json_encode($formattedQuotes);
} catch (PDOException $e) {
    // Handle errors during the fetching of quotes
    echo json_encode(["error" => "Failed to fetch quotes: " . $e->getMessage()]);
    exit; // Stop further processing
}

// Function to create a Bootstrap card for each quote
function generateCard($quoteText, $authorName, $quoteId, $isFavorite) {
    // Create a favorite toggle switch only if the user is logged in
    $favoriteToggle = isset($_SESSION['validated']) ? 
        '<div class="form-check form-switch">' .
        '<input type="checkbox" class="form-check-input" id="fav' . $quoteId . '" onclick="buttonFav(\'fav' . $quoteId . '\', document.getElementById(\'fav' . $quoteId . '\').checked);" ' . $isFavorite . '>' .
        '</div>' : '';

    // Return the card's HTML structure
    return '<div class="card mb-3 a4card w-100">' .
        '<div class="card-header">' . htmlspecialchars($authorName) . '</div>' .
        '<div class="card-body d-flex align-items-center">' .
            '<p class="card-text w-100">' . htmlspecialchars($quoteText) . '</p>' .
            $favoriteToggle .
        '</div>' .
    '</div>';
}

?>
