<?php
include 'config.php';

echo "<h2>Hugging Face Inference Test</h2>";

// Test a simple inference request
function test_huggingface_inference($message, $model, $api_key = "") {
    $url = "https://api-inference.huggingface.co/models/" . $model;
    
    $data = [
        'inputs' => $message,
        'parameters' => [
            'max_length' => 100,
            'temperature' => 0.7,
            'top_p' => 0.9,
            'do_sample' => true,
            'return_full_text' => false
        ]
    ];
    
    $headers = [
        'Content-Type: application/json',
    ];
    
    if (!empty($api_key)) {
        $headers[] = 'Authorization: Bearer ' . $api_key;
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $http_code,
        'response' => $response,
        'error' => $curl_error
    ];
}

$test_messages = [
    "Hello, how are you?",
    "What is the meaning of life?",
    "Tell me a joke",
    "How to make coffee?"
];

foreach ($test_messages as $message) {
    echo "<h3>Testing: '" . htmlspecialchars($message) . "'</h3>";
    
    $result = test_huggingface_inference($message, $HUGGINGFACE_MODEL, $HUGGINGFACE_API_KEY);
    
    echo "HTTP Code: " . $result['http_code'] . "<br>";
    
    if ($result['error']) {
        echo "Error: " . $result['error'] . "<br>";
    }
    
    if ($result['http_code'] === 200) {
        $response_data = json_decode($result['response'], true);
        echo "Response: <pre>" . print_r($response_data, true) . "</pre><br>";
        
        // Extract the generated text
        if (isset($response_data[0]['generated_text'])) {
            echo "Generated Text: " . htmlspecialchars($response_data[0]['generated_text']) . "<br>";
        } elseif (isset($response_data['generated_text'])) {
            echo "Generated Text: " . htmlspecialchars($response_data['generated_text']) . "<br>";
        }
    } elseif ($result['http_code'] === 503) {
        echo "Model is loading. This is normal for the first request.<br>";
        echo "Response: " . htmlspecialchars($result['response']) . "<br>";
    } else {
        echo "Response: " . htmlspecialchars($result['response']) . "<br>";
    }
    
    echo "<hr>";
}

echo "<a href='test_setup.php'>Back to Setup Test</a> | ";
echo "<a href='test_medical.php'>Test Medical Responses</a>";
?>