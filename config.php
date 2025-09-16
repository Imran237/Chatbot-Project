<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "medical_chatbot_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hugging Face API configuration
// Models that are confirmed to work with the inference API
$HUGGINGFACE_MODELS = [
    "gpt2",                                        // Basic text generation
    "distilgpt2",                                  // Smaller, faster version
    "mrm8488/t5-base-finetuned-common_gen",        // General conversation
    "microsoft/DialoGPT-small"                     // Smaller but available version
];

// Current model to use
$HUGGINGFACE_MODEL = $HUGGINGFACE_MODELS[0];

// Your Hugging Face User Access Token
$HUGGINGFACE_API_KEY = "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";

// API endpoint
$HUGGINGFACE_API_URL = "https://api-inference.huggingface.co/models/";

// For debugging
define('DEBUG_MODE', true);

// Function to test which models work
function get_working_model($models, $api_key) {
    foreach ($models as $model) {
        $url = "https://api-inference.huggingface.co/models/" . $model;
        $headers = ['Content-Type: application/json'];
        
        if (!empty($api_key)) {
            $headers[] = 'Authorization: Bearer ' . $api_key;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 || $http_code === 503) {
            // 200 = available, 503 = loading (but exists)
            return $model;
        }
    }
    
    // If no models work, return null to use fallback only
    return null;
}

// Test and set the working model
$working_model = get_working_model($HUGGINGFACE_MODELS, $HUGGINGFACE_API_KEY);
if ($working_model) {
    $HUGGINGFACE_MODEL = $working_model;
} else {
    // No working models found - will use fallback only
    $HUGGINGFACE_MODEL = null;
}
?>