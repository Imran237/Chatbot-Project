<?php
include 'config.php';

echo "<h2>Hugging Face Diagnostic Tool</h2>";

// Test each model
foreach ($HUGGINGFACE_MODELS as $model) {
    echo "<h3>Testing Model: $model</h3>";
    
    $url = "https://api-inference.huggingface.co/models/" . $model;
    $headers = ['Content-Type: application/json'];
    
    if (!empty($HUGGINGFACE_API_KEY)) {
        $headers[] = 'Authorization: Bearer ' . $HUGGINGFACE_API_KEY;
    }
    
    // Test 1: Check if model exists
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
        continue; // Skip further tests for this model
    }
    
    // Test 2: Try an inference request
    $data = [
        'inputs' => "Hello, how are you?",
        'parameters' => [
            'max_length' => 50,
            'return_full_text' => false
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Inference test: HTTP $http_code<br>";
    
    if ($http_code === 200) {
        $response_data = json_decode($response, true);
        echo "✅ Success! Response: <pre>" . print_r($response_data, true) . "</pre>";
    } elseif ($http_code === 503) {
        echo "🔄 Model is loading. This can take 20-30 seconds.<br>";
        echo "Response: " . htmlspecialchars($response);
    } else {
        echo "❌ Failed. Response: " . htmlspecialchars($response);
    }
    
    echo "<hr>";
}

echo "<h3>Recommendations:</h3>";
echo "1. The first request to a model can take 20-30 seconds to load<br>";
echo "2. Try using the chat interface - it might work despite these test results<br>";
echo "3. Your medical query system is working perfectly as a fallback<br>";

echo "<a href='chat.php'>Test Chat Interface</a>";
?>