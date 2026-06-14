<?php
/**
 * AI Mentor Chat API
 * Proxies requests to Groq API (OpenAI-compatible)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
requireLogin();
require_once __DIR__ . '/../config/ai-config.php';

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$history = $input['history'] ?? [];

if (!$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

if (!AI_API_KEY) {
    http_response_code(503);
    echo json_encode(['error' => 'AI API key not configured. Set GROQ_API_KEY in .env or ai-config.php']);
    exit;
}

// Build messages array
$messages = [
    ['role' => 'system', 'content' => AI_SYSTEM_PROMPT]
];

// Add conversation history (last 10 messages for context)
$recent = array_slice($history, -10);
foreach ($recent as $msg) {
    if (in_array($msg['role'], ['user', 'assistant'])) {
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }
}

$messages[] = ['role' => 'user', 'content' => $message];

$payload = [
    'model' => AI_MODEL,
    'messages' => $messages,
    'temperature' => 0.3,
    'max_tokens' => 2048,
    'top_p' => 0.9,
];

$ch = curl_init(AI_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . AI_API_KEY,
    ],
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'API request failed: ' . $error]);
    exit;
}

$data = json_decode($response, true);

if ($http_code !== 200) {
    http_response_code(500);
    $err_msg = $data['error']['message'] ?? 'Unknown API error';
    echo json_encode(['error' => $err_msg]);
    exit;
}

$reply = $data['choices'][0]['message']['content'] ?? '';
$usage = $data['usage'] ?? [];

if (!$reply) {
    http_response_code(500);
    echo json_encode(['error' => 'Empty response from AI']);
    exit;
}

echo json_encode([
    'reply' => $reply,
    'usage' => $usage,
]);
