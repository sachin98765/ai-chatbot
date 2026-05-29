const authMessage = document.getElementById("auth-message")
const authMessageSignup = document.getElementById("auth-message-signup")
let currentConversationId = null
let isNewConversation = false

window.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("auth-root")) {
    initAuthPage()
  }
  if (document.getElementById("chat-root")) {
    initChatPage()
  }
})

function initAuthPage() {
  switchAuthTab("login")
  document
    .getElementById("login-tab")
    .addEventListener("click", () => switchAuthTab("login"))
  document
    .getElementById("signup-tab")
    .addEventListener("click", () => switchAuthTab("signup"))
}

function switchAuthTab(tab) {
  const loginPanel = document.getElementById("login-panel")
  const signupPanel = document.getElementById("signup-panel")
  const loginTab = document.getElementById("login-tab")
  const signupTab = document.getElementById("signup-tab")

  if (tab === "login") {
    loginPanel.classList.add("active")
    signupPanel.classList.remove("active")
    loginTab.classList.add("active")
    signupTab.classList.remove("active")
    authMessageSignup.textContent = ""
  } else {
    signupPanel.classList.add("active")
    loginPanel.classList.remove("active")
    signupTab.classList.add("active")
    loginTab.classList.remove("active")
    authMessage.textContent = ""
  }
}

function submitAuth(action) {
  clearAuthMessages()

  const payload = { action }
  if (action === "login") {
    payload.email = document.getElementById("login-email").value.trim()
    payload.password = document.getElementById("login-password").value
  } else {
    payload.username = document.getElementById("signup-username").value.trim()
    payload.email = document.getElementById("signup-email").value.trim()
    payload.password = document.getElementById("signup-password").value
    payload.confirmPassword = document.getElementById(
      "signup-confirm-password",
    ).value
  }

  fetch("auth.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        window.location.reload()
      } else {
        showAuthMessage(
          data.error || "Something went wrong. Please try again.",
          action === "login" ? "login" : "signup",
        )
      }
    })
    .catch(() => {
      showAuthMessage(
        "Unable to connect. Please try again.",
        action === "login" ? "login" : "signup",
      )
    })
}

function clearAuthMessages() {
  if (authMessage) authMessage.textContent = ""
  if (authMessageSignup) authMessageSignup.textContent = ""
}

function showAuthMessage(message, action) {
  if (action === "signup" && authMessageSignup) {
    authMessageSignup.textContent = message
  } else if (authMessage) {
    authMessage.textContent = message
  }
}

function handleGoogleAuth() {
  showAuthMessage("Google authentication is a placeholder UI only.", "login")
}

function initChatPage() {
  const input = document.getElementById("user-input")
  const sendBtn = document.getElementById("send-btn")
  const newChatBtn = document.getElementById("new-chat-btn")

  if (input) {
    input.addEventListener("keypress", handleKeyPress)
  }

  if (sendBtn) {
    sendBtn.addEventListener("click", sendMessage)
  }

  if (newChatBtn) {
    newChatBtn.addEventListener("click", createNewConversation)
  }

  loadHistory()
}

function handleKeyPress(event) {
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault()
    sendMessage()
  }
}

function createNewConversation() {
  currentConversationId = null
  isNewConversation = true
  clearChatBox()
  setChatSubtitle("Starting a new conversation...")
  updateActiveConversation(null)
}

function loadHistory() {
  fetch("history.php")
    .then((res) => res.json())
    .then((data) => {
      if (data.success && Array.isArray(data.conversations)) {
        renderConversationList(data.conversations)
        if (data.conversations.length > 0) {
          const firstConversationId = data.conversations[0].id
          loadConversation(firstConversationId, data.conversations[0].title)
        } else {
          showEmptyChat()
        }
      } else {
        showEmptyChat()
      }
    })
    .catch(() => {
      showEmptyChat("Unable to load previous chats. Please refresh the page.")
    })
}

function renderConversationList(conversations) {
  const list = document.getElementById("conversation-list")
  if (!list) return

  list.innerHTML = ""

  if (conversations.length === 0) {
    list.innerHTML =
      '<div class="placeholder-text">No prior chats yet. Start a new conversation.</div>'
    return
  }

  conversations.forEach((conversation) => {
    const item = document.createElement("button")
    item.type = "button"
    item.className = "conversation-item"
    item.dataset.conversationId = conversation.id
    const preview = conversation.first_user_message
      ? conversation.first_user_message.substring(0, 40)
      : "No messages yet"
    item.innerHTML = `<span>${escapeHtml(preview)}</span>`
    item.onclick = () => loadConversation(conversation.id, conversation.title)
    if (conversation.id === currentConversationId) {
      item.classList.add("active")
    }
    list.appendChild(item)
  })
}

function loadConversation(conversationId, title = "") {
  fetch(`history.php?conversation_id=${escapeURIComponent(conversationId)}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success && Array.isArray(data.messages)) {
        currentConversationId = conversationId
        isNewConversation = false
        setChatSubtitle(title || "Previous conversation")
        renderMessages(data.messages)
        updateActiveConversation(conversationId)
      } else {
        showChatError("Could not load conversation.")
      }
    })
    .catch(() => {
      showChatError("Unable to load conversation history.")
    })
}

function setChatSubtitle(text) {
  const subtitle = document.getElementById("chat-subtitle")
  if (subtitle) {
    subtitle.textContent = text
  }
}

function updateActiveConversation(conversationId) {
  const items = document.querySelectorAll(".conversation-item")
  items.forEach((item) => {
    const id = item.dataset.conversationId
    item.classList.toggle("active", id === String(conversationId))
  })
}

function sendMessage() {
  const input = document.getElementById("user-input")
  const message = input?.value.trim()
  const sendBtn = document.getElementById("send-btn")

  if (!message || !sendBtn) return

  if (currentConversationId === null && !isNewConversation) {
    setChatSubtitle("Loading your latest chat...")
  }

  const chatBox = document.getElementById("chat-box")
  if (chatBox && chatBox.querySelector(".placeholder-text")) {
    chatBox.innerHTML = ""
  }

  displayMessage(message, "user")
  input.value = ""
  input.focus()

  sendBtn.disabled = true
  sendBtn.textContent = "Thinking..."

  fetch("chatbot.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      message,
      conversation_id: currentConversationId,
      new_conversation: isNewConversation,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        currentConversationId = data.conversation_id || currentConversationId
        isNewConversation = false
        displayMessage(data.response, "bot")
        loadHistory()
      } else {
        displayMessage(`❌ ${data.error || "Unknown error"}`, "error")
      }
    })
    .catch(() => {
      displayMessage("❌ Connection error. Please try again.", "error")
    })
    .finally(() => {
      sendBtn.disabled = false
      sendBtn.textContent = "Send"
    })
}

function renderMessages(messages) {
  const chatBox = document.getElementById("chat-box")
  if (!chatBox) return

  chatBox.innerHTML = ""

  if (messages.length === 0) {
    showEmptyChat("This conversation is empty. Send a message to begin.")
    return
  }

  messages.forEach((message) => {
    displayMessage(
      message.content,
      message.role === "assistant" ? "bot" : "user",
    )
  })
}

function displayMessage(text, sender) {
  const chatBox = document.getElementById("chat-box")
  if (!chatBox) return

  const messageDiv = document.createElement("div")
  messageDiv.className = `message ${sender}`
  messageDiv.innerHTML = escapeHtml(text).replace(/\n/g, "<br>")
  chatBox.appendChild(messageDiv)
  chatBox.scrollTop = chatBox.scrollHeight
}

function clearChatBox() {
  const chatBox = document.getElementById("chat-box")
  if (chatBox) {
    chatBox.innerHTML =
      '<div class="placeholder-text">Send a message to start a new conversation.</div>'
  }
}

function showEmptyChat(
  message = "No conversations available. Begin by asking a question.",
) {
  const chatBox = document.getElementById("chat-box")
  if (chatBox) {
    chatBox.innerHTML = `<div class="placeholder-text">${escapeHtml(message)}</div>`
  }
}

function showChatError(message) {
  const chatBox = document.getElementById("chat-box")
  if (chatBox) {
    chatBox.innerHTML = `<div class="message error">${escapeHtml(message)}</div>`
  }
}

function escapeHtml(unsafe) {
  return String(unsafe)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;")
}

function escapeURIComponent(value) {
  return encodeURIComponent(value)
}
