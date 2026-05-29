# AI Chatbot with Login, History, and NVIDIA Model Integration

A secure, session-based AI chatbot built with PHP, MySQL, and OpenRouter. Users can sign up, log in, view conversation history in a sidebar, and chat with an NVIDIA Nemotron-powered assistant.

## 🔥 Project Idea

This project is a full-stack AI chat application designed to deliver a modern experience with:

- user authentication
- database-backed chat history
- previous conversation sidebar
- persistent message storage
- session management
- a clean UI with responsive design
- placeholder Google authentication button
- NVIDIA free model integration via OpenRouter

The goal is to provide a polished chatbot interface with account-based history and reusable conversations.

## ✨ Key Features

- Login and signup workflow
- Password confirmation validation
- MySQL-backed user and chat persistence
- Secure PHP sessions
- Sidebar conversation history, sorted by most recent
- Auto-load conversations after login
- Chatbot backend integrated with NVIDIA model placeholder
- Google auth UI placeholder
- Clean responsive design for desktop and mobile

## 🧱 Technology Stack

- PHP 8+ (backend)
- MySQL / MariaDB (data storage)
- HTML5 / CSS3 / JavaScript (frontend)
- OpenRouter API for the model endpoint
- XAMPP / Apache local development environment

## 📁 Project Structure

```
ai-chatbot/
├── auth.php          # Signup / login API
├── chatbot.php       # Chat API and OpenRouter integration
├── db.php            # MySQL connection helper
├── history.php       # Conversation history API
├── index.php         # Main UI with auth and chat pages
├── logout.php        # Logout and session cleanup
├── app.js            # Frontend interaction logic
├── style.css         # Application styling
├── database.sql      # Database schema setup
├── README.md         # Documentation
└── .gitignore        # Git ignore rules
```

## 🚀 Setup Instructions

1. Start your local server (XAMPP / Apache / PHP).
2. Create the database using `database.sql`.
3. Update database settings in `db.php` if needed.
4. Set your OpenRouter API key in `chatbot.php`.
5. Open the `index.php` page in your browser.

### Database setup

- Use phpMyAdmin or MySQL CLI to run `database.sql`.
- Default database name: `ai_chatbot`
- Default user table: `ai_users`
- Default conversation tables: `ai_conversations`, `ai_messages`

### Update database config

Open `db.php` and adjust if your database credentials are different:

```php
$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "ai_chatbot";
```

### Update AI integration key

Open `chatbot.php` and set the OpenRouter key:

```php
$api_key = "---------------------------------------";
```

## 🛠️ Usage

1. Open the app in the browser.
2. Sign up or log in.
3. Start a new chat or select a previous conversation from the sidebar.
4. Type your message and press **Send**.
5. Responses will be stored in the database automatically.

## 📌 Notes

- The Google authentication button is a UI placeholder only.
- Chat history is stored per user.
- The sidebar shows the first user message preview for each history entry.
- New conversations can be started with the **New Chat** button.

## 🎓 Interview Q&A

### Q: What problem does this project solve?

A: It provides a user-based chatbot experience with persistent history, allowing users to save and revisit past conversations instead of starting from scratch.

### Q: What are the main components?

A: Frontend UI (`index.php`, `style.css`, `app.js`), authentication backend (`auth.php`), chat backend (`chatbot.php`), database helper (`db.php`), and history API (`history.php`).

### Q: How does user authentication work?

A: Users sign up with email, username, and password. Passwords are hashed using `password_hash` and stored in MySQL. Sessions are created on successful login.

### Q: How is chat history stored?

A: Each chat belongs to a conversation record in `ai_conversations`, and every message is stored in `ai_messages`. The app loads history for the logged-in user.

### Q: How are conversations displayed?

A: The left sidebar shows the latest conversations sorted by `updated_at`, with the first user query shown as a short preview.

### Q: What model is used?

A: The app uses the NVIDIA Nemotron free model placeholder via OpenRouter's chat completion API.

### Q: Is Google login implemented?

A: Not yet. The button is a UI placeholder only.

### Q: What security measures are included?

A: Session-based login, password hashing, request validation, and user-specific history access.

### Q: What are the next improvements?

A:

- Add real Google OAuth
- Add password reset flow
- Add conversation titles and tags
- Add message streaming
- Add admin user management

## 📊 Project Report

### Purpose

Build a polished chat application with authentication, message persistence, and history features using PHP and MySQL.

### Requirements

- Login / signup flow
- Password validation
- Session handling
- Chat storage
- History sidebar
- Responsive UI
- OpenRouter model integration

### Implementation

- Built UI with `index.php`, `style.css`, and `app.js`.
- Created authentication API in `auth.php`.
- Connected to MySQL via `db.php`.
- Stored chats in `ai_conversations` and `ai_messages`.
- Integrated OpenRouter to send and receive assistant messages.

### Outcome

A working local AI chatbot that supports multiple users, session-based login, saved conversations, and history display.

### Future work

- Add full OAuth support
- Improve chat preview labels
- Add conversation search and tagging
- Enhance security with CSRF protection

## ✅ Contribution

If you want to extend this project, start by improving the authentication flow and adding a proper OAuth provider.

---

**Project status:** Working prototype with login, chat persistence, and sidebar history.
