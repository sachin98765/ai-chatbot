# AI Chatbot - HuggingFace & Ollama Edition

Choose your preferred setup:

## ⚡ Option 1: HuggingFace API (Recommended)

### Step 1: Get Free HuggingFace Token

1. Go to: **https://huggingface.co/join**
2. Sign up (free)
3. Go to: **https://huggingface.co/settings/tokens**
4. Click "New token"
5. Name: `chatbot`
6. Copy the token

### Step 2: Update Chatbot

Open `chatbot.php` line 3:

```php
$api_key = "hf_YOUR_TOKEN_HERE";
```

### Step 3: Run

```
http://localhost/ai-chatbot/
```

---

## 🚀 Option 2: Ollama (Best - Local, No API Key!)

**Completely local AI - No internet needed, No rate limits!**

### Step 1: Download Ollama

1. Go to: **https://ollama.ai**
2. Download for your OS (Windows, Mac, Linux)
3. Install and run

### Step 2: Download a Model

Open terminal/cmd:

```bash
ollama pull mistral
```

(Or: `ollama pull llama2`)

### Step 3: Update Chatbot

Replace entire `chatbot.php` with this:

```php
<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['message'])) {
    echo json_encode(['error' => 'No message']);
    exit;
}

$message = trim($input['message']);

// Call local Ollama
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:11434/api/generate");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "mistral",
    "prompt" => $message,
    "stream" => false
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['response'])) {
    echo json_encode([
        'success' => true,
        'response' => trim($data['response'])
    ]);
} else {
    echo json_encode(['error' => 'Failed to get response']);
}
?>
```

### Step 4: Run

```
http://localhost/ai-chatbot/
```

---

## 📊 Comparison

| Feature | HuggingFace | Ollama |
|---------|------------|--------|
| Setup Time | 5 min | 10 min |
| Internet | Required | Not needed |
| Cost | Free | Free |
| Speed | Medium | Fast |
| Rate Limits | No | No |
| API Key | Yes | No |
| Best For | Online | Offline/Privacy |

---

## 🎯 Quick Setup Choice

**New user?** → Use HuggingFace (easier)  
**Want privacy?** → Use Ollama (local)  
**Have issues?** → Try the other one

---

## 📁 Files

- `index.php` - HTML  
- `chatbot.php` - Backend (choose API)
- `app.js` - Frontend
- `style.css` - Styling

---

## ✅ Test Your Setup

After setup, go to: `http://localhost/ai-chatbot/`

Try these messages:
- "Hello"
- "What is 2+2?"
- "Tell me a joke"

---

**Choose your setup and let me know if you need help!** 🚀
