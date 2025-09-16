<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not authenticated"]);
    exit;
}

if (!isset($_POST['message'])) {
    echo json_encode(["error" => "No message received"]);
    exit;
}

include 'config.php';

$message = trim($_POST['message']);
$user_id = $_SESSION['user_id'];

// Save message to database
$stmt = $conn->prepare("INSERT INTO chat_messages (user_id, user_message, bot_response) VALUES (?, ?, '')");
$stmt->bind_param("is", $user_id, $message);
$stmt->execute();
$message_id = $stmt->insert_id;
$stmt->close();

// Get response 
$reply = get_huggingface_response($message, $HUGGINGFACE_API_URL, $HUGGINGFACE_MODEL, $HUGGINGFACE_API_KEY);

// Update with bot response
$stmt = $conn->prepare("UPDATE chat_messages SET bot_response = ? WHERE id = ?");
$stmt->bind_param("si", $reply, $message_id);
$stmt->execute();
$stmt->close();

// Return JSON
echo json_encode([
    "user" => htmlspecialchars($message),
    "bot"  => htmlspecialchars($reply)
]);

function get_huggingface_response($message, $api_url, $model, $api_key = "") {
    // For medical queries, use our specialized response system
    if (is_medical_query($message)) {
        return get_medical_response($message);
    }
    
    $url = $api_url . $model;
    
    // Prepare request data based on model type
    if (strpos($model, "blenderbot") !== false) {
        // BlenderBot format
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
    } else {
        // Default format for other models
        $data = [
            'inputs' => $message,
            'parameters' => [
                'max_length' => 100,
                'temperature' => 0.7,
            ]
        ];
    }
    
    $headers = [
        'Content-Type: application/json',
    ];
    
    // Add authorization header
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
    
    // Log the request for debugging
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("HF Request to: " . $url);
        error_log("HF Status: " . $http_code);
        error_log("HF Response: " . $response);
    }
    
    // Check for CURL errors
    if ($curl_error) {
        error_log("Hugging Face CURL Error: " . $curl_error);
        return get_fallback_response($message);
    }
    
    // Handle different HTTP responses
    if ($http_code === 200) {
        $result = json_decode($response, true);
        
        // Extract response text based on model type
        if (isset($result[0]['generated_text'])) {
            return $result[0]['generated_text'];
        } elseif (isset($result['generated_text'])) {
            return $result['generated_text'];
        } elseif (isset($result[0]['summary_text'])) {
            return $result[0]['summary_text'];
        } elseif (isset($result['conversation']['generated_responses'][0])) {
            // BlenderBot format
            return $result['conversation']['generated_responses'][0];
        } else {
            error_log("Hugging Face API Unknown Format: " . json_encode($result));
            return get_fallback_response($message);
        }
    } 
    elseif ($http_code === 503) {
        // Model is loading
        return "The AI model is loading. This can take 20-30 seconds for the first request. Please try again in a moment.";
    }
    elseif ($http_code === 404) {
        // Model not found - try an alternative
        error_log("Hugging Face Model Not Found: " . $model);
        return get_fallback_response($message);
    }
    else {
        error_log("Hugging Face API Error: HTTP " . $http_code . " - " . $response);
        return get_fallback_response($message);
    }
}

// ADD THE MISSING FUNCTIONS HERE:

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

function get_medical_response($message) {
    $responses = [
        'headache' => "For headaches: 1) Rest in a quiet, dark room. 2) Apply a cool compress to your forehead. 3) Stay hydrated by drinking water. 4) Consider over-the-counter pain relievers like acetaminophen or ibuprofen if appropriate for you. If your headache is severe, persistent, or accompanied by other symptoms like vision changes, fever, or confusion, please consult a healthcare professional promptly.",
        'fever' => "For fever: 1) Rest and stay hydrated with water, broth, or electrolyte solutions. 2) Use a damp cloth to cool your skin. 3) Dress in lightweight clothing. 4) Consider fever reducers like acetaminophen if appropriate. If fever is above 103°F (39.4°C), lasts more than 3 days, or is accompanied by severe symptoms like difficulty breathing, stiff neck, rash, or confusion, please seek medical attention.",
        'cough' => "For cough: 1) Stay hydrated with warm liquids like tea with honey. 2) Use a humidifier to add moisture to the air. 3) Consider cough drops or lozenges. 4) Avoid irritants like smoke. 5) Try steam inhalation. If you're coughing up blood, having difficulty breathing, experiencing chest pain, or your cough persists for more than 3 weeks, please consult a healthcare provider.",
        'cold' => "For cold symptoms: 1) Get plenty of rest. 2) Drink fluids like water, juice, and clear broth. 3) Use saline nasal drops or spray. 4) Gargle with salt water to soothe a sore throat. 5) Consider over-the-counter cold medications if appropriate. Most colds improve in 7-10 days. If symptoms worsen, you develop difficulty breathing, or have a high fever, consult a healthcare professional.",
        'stomach' => "For stomach issues: 1) Stay hydrated with clear fluids. 2) Avoid dairy, fatty, or spicy foods. 3) Try the BRAT diet (bananas, rice, applesauce, toast). 4) Rest and allow your digestive system to recover. If you experience severe pain, persistent vomiting, dehydration signs (like dark urine or dizziness), blood in stool, or symptoms lasting more than 48 hours, please see a doctor.",
        'covid' => "For COVID-19 concerns: 1) Isolate yourself if you have symptoms. 2) Get tested if possible. 3) Rest and hydrate well. 4) Monitor your symptoms and oxygen levels if available. 5) Use over-the-counter medications to manage symptoms if appropriate. Seek emergency care for difficulty breathing, persistent chest pain, confusion, inability to stay awake, or pale/gray/blue-colored skin, lips, or nail beds.",
        'allergy' => "For allergies: 1) Avoid known triggers when possible. 2) Consider antihistamines as directed. 3) Use a saline nasal rinse to clear allergens. 4) Keep windows closed during high pollen seasons. 5) Shower after being outdoors to remove allergens. 6) Use air purifiers indoors. If symptoms are severe, affecting breathing, or not relieved by over-the-counter medications, consult a healthcare provider.",
        'back pain' => "For back pain: 1) Apply heat or ice to the painful area. 2) Try over-the-counter pain relievers. 3) Maintain good posture. 4) Avoid heavy lifting. If pain is severe, radiates down your leg, or is accompanied by fever or loss of bladder/bowel control, seek immediate medical attention.",
        
        'sore throat' => "For sore throat: 1) Gargle with warm salt water. 2) Drink warm liquids like tea with honey. 3) Use throat lozenges. 4) Rest your voice. 5) Use a humidifier. If you have difficulty breathing, swallowing, or if your sore throat lasts more than a week, consult a healthcare provider.",
        
        'burn' => "For minor burns: 1) Cool the burn under cool running water for 10-15 minutes. 2) Apply aloe vera or moisturizer. 3) Cover with a sterile gauze bandage. 4) Take over-the-counter pain relievers if needed. For severe burns, electrical burns, or chemical burns, seek emergency medical care immediately.",
        
        'cut' => "For minor cuts: 1) Apply gentle pressure with a clean cloth to stop bleeding. 2) Clean with water. 3) Apply antibiotic ointment. 4) Cover with a bandage. For deep cuts, cuts that won't stop bleeding, or signs of infection (redness, swelling, pus), seek medical attention.",
        
        'sprain' => "For sprains: Follow the RICE method - 1) Rest the injured area. 2) Ice for 20 minutes at a time. 3) Compress with an elastic bandage. 4) Elevate above heart level. If you heard a popping sound, can't bear weight, or have severe pain, see a doctor to rule out fractures.",
        
        'anxiety' => "For anxiety: 1) Practice deep breathing exercises. 2) Try mindfulness or meditation. 3) Get regular exercise. 4) Limit caffeine and alcohol. 5) Ensure adequate sleep. If anxiety is interfering with your daily life or causing panic attacks, please consult a mental health professional.",
        
        'depression' => "For depression: 1) Maintain a regular routine. 2) Stay connected with supportive people. 3) Get regular exercise. 4) Eat a balanced diet. 5) Consider talking to a therapist or counselor. If you're having thoughts of self-harm or suicide, please contact emergency services or a crisis hotline immediately."
    ];
    
    $message = strtolower($message);
    
    foreach ($responses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response . " [Note: This is general information. For personalized advice, please consult a healthcare professional.]";
        }
    }
    
    return "I understand you have a health concern. While I can provide general information, it's important to consult with a healthcare professional for proper medical advice, diagnosis, or treatment. Could you please describe your symptoms in more detail? Remember: For emergencies, please contact emergency services immediately.";
}

function get_fallback_response($message) {
    if (is_medical_query($message)) {
        return get_medical_response($message);
    }
    
    return "I'm here to help with health-related questions. Could you tell me more about what you're experiencing? If this is not health-related, I might not be the best resource for your question.";
}
?>