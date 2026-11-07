
/* ==========================================
   RIZA AI - MAIN JAVASCRIPT
   RIZA BOY & RIZA GIRL
========================================== */

"use strict";

let currentAssistant = "RIZA BOY";
let chatHistory = [];

// GET HTML ELEMENTS
const messageInput = document.getElementById("messageInput");
const sendButton = document.getElementById("sendButton");
const messagesContainer = document.getElementById("messages");
const newChatButton = document.getElementById("newChatButton");
const themeButton = document.getElementById("themeButton");
const assistantButtons = document.querySelectorAll(".assistant");
const assistantName = document.getElementById("assistantName");

// SEND MESSAGE
function sendMessage() {
    if (!messageInput || !messagesContainer) return;

    const message = messageInput.value.trim();

    if (!message) return;

    addMessage(message, "user");

    messageInput.value = "";

    // Temporary response until AI API is connected
    setTimeout(function () {
        addMessage(
            "Murakoze kubaza RIZA AI! " +
            "Ubu turimo gutegura AI connection kugira ngo ngusubize neza.",
            "ai"
        );
    }, 700);
}

// ADD MESSAGE TO CHAT
function addMessage(text, sender) {
    const message = document.createElement("div");

    message.className = "message " + sender;
    message.textContent = text;

    messagesContainer.appendChild(message);

    messagesContainer.scrollTop =
        messagesContainer.scrollHeight;

    chatHistory.push({
        text: text,
        sender: sender,
        time: new Date().toISOString()
    });
}

// SEND BUTTON
if (sendButton) {
    sendButton.addEventListener("click", sendMessage);
}

// ENTER KEY
if (messageInput) {
    messageInput.addEventListener("keydown", function (event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });
}

// NEW CHAT
if (newChatButton) {
    newChatButton.addEventListener("click", function () {
        if (messagesContainer) {
            messagesContainer.innerHTML = "";
        }

        chatHistory = [];

        addMessage(
            "Hello! I am " + currentAssistant +
            ". How can I help you today?",
            "ai"
        );
    });
}

// SELECT RIZA BOY OR RIZA GIRL
assistantButtons.forEach(function (button) {
    button.addEventListener("click", function () {
        assistantButtons.forEach(function (item) {
            item.classList.remove("selected");
        });

        button.classList.add("selected");

        currentAssistant =
            button.dataset.assistant || "RIZA BOY";

        if (assistantName) {
            assistantName.textContent = currentAssistant;
        }

        if (messagesContainer) {
            messagesContainer.innerHTML = "";
        }

        addMessage(
            "Hello! I am " + currentAssistant +
            ". How can I help you?",
            "ai"
        );
    });
});

// DARK AND LIGHT MODE
if (themeButton) {
    themeButton.addEventListener("click", function () {
        document.body.classList.toggle("dark");

        const isDark =
            document.body.classList.contains("dark");

        themeButton.textContent =
            isDark ? "☀️ Light Mode" : "🌙 Dark Mode";
    });
}

// INITIAL MESSAGE
if (messagesContainer && messagesContainer.children.length === 0) {
    addMessage(
        "Hello! Welcome to RIZA AI. How can I help you today?",
        "ai"
    );
}

console.log("RIZA AI JavaScript loaded successfully.");
