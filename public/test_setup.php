<?php
session_start();
include 'config.php';

echo "<h2>Medical Chatbot Setup Test</h2>";

// Test database connection
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error . "<br>";
} else {
    echo "✅ Database connection successful.<br>";
}

// Test Hugging Face configuration
echo "<h3>Hugging Face Configuration</h3>";
echo "API URL: " . $HUGGINGFACE_API_URL . "<br>";
echo "Model: " . $HUGGINGFACE_MODEL . "<br>";

if (empty($HUGGINGFACE_API_KEY)) {
    echo "API Key: Not configured (using anonymous access)<br>";
} else {
    echo "API Key: " . substr($HUGGINGFACE_API_KEY, 0, 6) . "..." . "<br>";
}

// Test if we can connect to Hugging Face
$url = $HUGGINGFACE_API_URL . $HUGGINGFACE_MODEL;
$headers = ['Content-Type: application/json'];

if (!empty($HUGGINGFACE_API_KEY)) {
    $headers[] = 'Authorization: Bearer ' . $HUGGINGFACE_API_KEY;
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Connection test to Hugging Face: ";
if ($http_code === 200) {
    echo "✅ Successful<br>";
} elseif ($http_code === 401) {
    echo "❌ Authentication failed<br>";
} elseif ($http_code === 404) {
    echo "❌ Model not found<br>";
} else {
    echo "⚠️ HTTP Code: " . $http_code . "<br>";
    if ($curl_error) {
        echo "CURL Error: " . $curl_error . "<br>";
    }
}

// Test medical query detection function
echo "<h3>Medical Query Detection Test</h3>";

function is_medical_query($message) {
    $medical_keywords = [
        'pain', 'hurt', 'symptom', 'fever', 'headache', 'cough', 'cold',
        'nausea', 'vomit', 'dizzy', 'dizziness', 'rash', 'allergy', 'breath',
        'chest', 'heart', 'stomach', 'abdominal', 'infection', 'virus',
        'bacteria', 'medicine', 'medication', 'pill', 'tablet', 'dose',
        'diagnose', 'treatment', 'therapy', 'doctor', 'hospital', 'emergency',
        'blood', 'pressure', 'sugar', 'diabetes', 'asthma', 'arthritis',
        'cancer', 'covid', 'corona', 'flu', 'influenza', 'headache', 'migraine'
    ];
    
    $message = strtolower($message);
    
    foreach ($medical_keywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

$test_queries = [
    "I have a headache" => true,
    "What's the weather like?" => false,
    "My stomach hurts" => true,
    "Tell me a joke" => false,
    "I have fever and cough" => true,
    "How to make pizza?" => false,
    "I need pain medication" => true
];

foreach ($test_queries as $query => $expected) {
    $result = is_medical_query($query);
    $status = $result === $expected ? "✅" : "❌";
    echo $status . " '" . htmlspecialchars($query) . "' -> " . 
         ($result ? "Medical" : "Not Medical") . 
         " (Expected: " . ($expected ? "Medical" : "Not Medical") . ")<br>";
}

// Test session
echo "<h3>Session Test</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User ID in session: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "⚠️ No user ID in session (this is normal if not logged in)<br>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "1. <a href='test_huggingface.php'>Test Hugging Face Inference</a><br>";
echo "2. <a href='test_medical.php'>Test Medical Responses</a><br>";
echo "3. <a href='register.php'>Register a test account</a><br>";
echo "4. <a href='login.php'>Login and test the full chat</a><br>";
?>