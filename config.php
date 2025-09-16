<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "medical_chatbot_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Load API key from environment variable
$HUGGINGFACE_API_KEY = getenv('HUGGINGFACE_API_KEY');

// Hugging Face API config
$HUGGINGFACE_MODELS = [
    "gpt2",
    "distilgpt2",
    "mrm8488/t5-base-finetuned-common_gen",
    "microsoft/DialoGPT-small"
];

$HUGGINGFACE_MODEL = $HUGGINGFACE_MODELS[0];
$HUGGINGFACE_API_URL = "https://api-inference.huggingface.co/models/";

define('DEBUG_MODE', true);

// (same get_working_model function…)
