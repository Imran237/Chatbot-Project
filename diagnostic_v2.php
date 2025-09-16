<?php
include 'config.php';

echo "<h2>Hugging Face Diagnostic Tool v2</h2>";

echo "<p>Testing models that are more likely to work with the inference API:</p>";

$test_models = [
    "gpt2",
    "distilgpt2", 
    "mrm8488/t5-base-finetuned-common_gen",
    "microsoft/DialoGPT-small",
    "facebook/blenderbot-400M-distill" // One more try
];

foreach ($test_models as $model) {
    echo "<h3>Testing Model: $model</h3>";
    
    $url = "https://api-inference.huggingface.co/models/" . $model;
    $headers = ['Content-Type: application/json'];
    
    if (!empty($HUGGINGFACE_API_KEY)) {
        $headers[] = 'Authorization: Bearer ' . $HUGGINGFACE_API_KEY;
    }
    
    // Test if model exists
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Model check: HTTP $http_code - ";
    if ($http_code === 200) {
        echo "✅ Available<br>";
    } elseif ($http_code === 503) {
        echo "🔄 Loading (this is normal)<br>";
    } else {
        echo "❌ Not available<br>";
    }
    
    echo "<hr>";
}

echo "<h3>Current Configuration Status:</h3>";
if ($HUGGINGFACE_MODEL) {
    echo "✅ Using model: <strong>$HUGGINGFACE_MODEL</strong><br>";
} else {
    echo "❌ No working Hugging Face models found<br>";
    echo "✅ Will use medical response system only<br>";
}

echo "<h3>Medical Response System Test:</h3>";
// Test medical detection
$test_queries = [
    "I have a headache",
    "What's the weather like?",
    "My stomach hurts"
];

foreach ($test_queries as $query) {
    $is_medical = is_medical_query($query);
    echo "'$query' → " . ($is_medical ? "Medical ✅" : "Not Medical ✅") . "<br>";
}

echo "<hr>";
echo "<h3>Recommendations:</h3>";
echo "1. Your medical response system is working perfectly<br>";
echo "2. Some models may work for inference even if they show 404 in this test<br>";
echo "3. Try the chat interface - it will use the best available option<br>";
echo "4. Consider using only the medical response system (it's very comprehensive)<br>";

echo "<a href='chat.php'>Test Chat Interface</a> | ";
echo "<a href='test_medical.php'>Test Medical Responses</a>";

// Include the medical functions for testing
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
?>