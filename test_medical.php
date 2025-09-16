<?php
include 'config.php';

echo "<h2>Medical Response Test</h2>";

// Include the functions from sendmessage.php
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
        'allergy' => "For allergies: 1) Avoid known triggers when possible. 2) Consider antihistamines as directed. 3) Use a saline nasal rinse to clear allergens. 4) Keep windows closed during high pollen seasons. 5) Shower after being outdoors to remove allergens. 6) Use air purifiers indoors. If symptoms are severe, affecting breathing, or not relieved by over-the-counter medications, consult a healthcare provider."
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

// Test medical queries
$medical_test_cases = [
    "I have a headache",
    "My stomach hurts badly",
    "I'm running a fever of 101 degrees",
    "I can't stop coughing",
    "I think I have a cold",
    "I have covid symptoms",
    "My allergies are acting up"
];

foreach ($medical_test_cases as $query) {
    echo "<h3>Query: '" . htmlspecialchars($query) . "'</h3>";
    
    $is_medical = is_medical_query($query);
    echo "Detected as medical: " . ($is_medical ? "✅ Yes" : "❌ No") . "<br>";
    
    if ($is_medical) {
        $response = get_medical_response($query);
        echo "Response: " . nl2br(htmlspecialchars($response)) . "<br>";
    } else {
        $response = get_fallback_response($query);
        echo "Fallback Response: " . nl2br(htmlspecialchars($response)) . "<br>";
    }
    
    echo "<hr>";
}

// Test non-medical queries
$non_medical_test_cases = [
    "What's the weather like?",
    "Tell me a joke",
    "How to make pizza?",
    "What time is it?"
];

echo "<h2>Non-Medical Query Test</h2>";

foreach ($non_medical_test_cases as $query) {
    echo "<h3>Query: '" . htmlspecialchars($query) . "'</h3>";
    
    $is_medical = is_medical_query($query);
    echo "Detected as medical: " . ($is_medical ? "✅ Yes" : "❌ No") . "<br>";
    
    $response = get_fallback_response($query);
    echo "Response: " . nl2br(htmlspecialchars($response)) . "<br>";
    
    echo "<hr>";
}

echo "<a href='test_setup.php'>Back to Setup Test</a> | ";
echo "<a href='test_huggingface.php'>Test Hugging Face</a>";
?>