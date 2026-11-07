
/* ==================================================
   RIZA AI V2 - PREMIUM CHAT SYSTEM
   RIZA BOY & RIZA GIRL
   ENGLISH + KINYARWANDA
   CHAT HISTORY + VOICE + IMAGE
================================================== */

"use strict";

/* ===============================
   1. GLOBAL SETTINGS
================================ */

const RIZA = {
    assistant: "RIZA BOY",
    language: localStorage.getItem("riza_language") || "en",
    theme: localStorage.getItem("riza_theme") || "light",
    chats: JSON.parse(localStorage.getItem("riza_chats") || "[]"),
    currentChat: null,
    selectedImage: null
};

const $ = id => document.getElementById(id);

/* ===============================
   2. ELEMENTS
================================ */

const messageInput = $("messageInput");
const sendButton = $("sendButton");
const messages = $("messages");
const newChatButton = $("newChatButton");
const themeButton = $("themeButton");
const languageButton = $("languageButton");
const assistantName = $("assistantName");
const historyList = $("historyList");
const imageInput = $("imageInput");
const imageButton = $("imageButton");
const imagePreview = $("imagePreview");
const voiceButton = $("voiceButton");
const menuButton = $("menuButton");

/* ===============================
   3. TRANSLATIONS
================================ */

const translations = {
    en: {
        welcome: "WELCOME TO",
        subtitle: "Your intelligent assistant. Ask anything, learn anything.",
        placeholder: "Ask RIZA anything...",
        hello: "Hello! Welcome to RIZA AI. How can I help you today?",
        boy: "Your smart and professional AI assistant.",
        girl: "Your friendly and creative AI assistant.",
        newChat: "New Chat",
        empty: "Your conversations will appear here",
        copied: "Copied!",
        listening: "Listening...",
        notSupported: "Voice recognition is not supported in this browser."
    },

    rw: {
        welcome: "IKAZE KURI",
        subtitle: "Umufasha wawe w'ubwenge. Baza icyo ushaka, wige icyo ushaka.",
        placeholder: "Baza RIZA icyo ushaka...",
        hello: "Muraho! Murakaza neza kuri RIZA AI. Nagufasha iki?",
        boy: "Umufasha wawe w'ubwenge kandi w'umwuga.",
        girl: "Umufasha wawe w'inshuti kandi uzi guhanga udushya.",
        newChat: "Ikiganiro gishya",
        empty: "Ibiganiro byawe bizagaragara hano",
        copied: "Byakoporowe!",
        listening: "Ndumva...",
        notSupported: "Iyi browser ntishyigikira kumva ijwi."
    }
};

function t(key) {
    return translations[RIZA.language][key] || key;
}

/* ===============================
   4. SAVE CHAT HISTORY
================================ */

function saveChats() {
    localStorage.setItem("riza_chats", JSON.stringify(RIZA.chats));
}

function createChat() {
    const chat = {
        id: Date.now().toString(),
        title: RIZA.language === "rw" ? "Ikiganiro gishya" : "New Chat",
        assistant: RIZA.assistant,
        messages: [],
        createdAt: new Date().toISOString()
    };

    RIZA.chats.unshift(chat);
    RIZA.currentChat = chat.id;

    saveChats();
    renderHistory();
    renderMessages();

    return chat;
}

function getCurrentChat() {
    return RIZA.chats.find(chat => chat.id === RIZA.currentChat);
}

/* ===============================
   5. RENDER CHAT HISTORY
================================ */

function renderHistory() {
    if (!historyList) return;

    historyList.innerHTML = "";

    if (RIZA.chats.length === 0) {
        const empty = document.createElement("div");
        empty.className = "history-item";
        empty.textContent = t("empty");
        historyList.appendChild(empty);
        return;
    }

    RIZA.chats.forEach(chat => {
        const item = document.createElement("div");

        item.className = "history-item" +
            (chat.id === RIZA.currentChat ? " active" : "");

        item.textContent = chat.title;

        item.addEventListener("click", () => {
            RIZA.currentChat = chat.id;
            RIZA.assistant = chat.assistant;
            updateAssistantUI();
            renderMessages();
            renderHistory();
        });

        historyList.appendChild(item);
    });
}

/* ===============================
   6. DISPLAY MESSAGES
================================ */

function renderMessages() {
    if (!messages) return;

    messages.innerHTML = "";

    const chat = getCurrentChat();

    if (!chat) {
        addMessage(t("hello"), "ai", false);
        return;
    }

    chat.messages.forEach((item, index) => {
        addMessage(item.text, item.sender, false, index);
    });

    messages.scrollTop = messages.scrollHeight;
}

function addMessage(text, sender, save = true, index = null) {
    if (!messages) return;

    const wrapper = document.createElement("div");
    wrapper.className = "message " + sender;

    const content = document.createElement("div");
    content.className = "message-content";
    content.textContent = text;

    wrapper.appendChild(content);

    if (sender === "ai" || sender === "user") {
        const actions = document.createElement("div");
        actions.className = "message-actions";

        actions.appendChild(createAction("Copy", () => {
            navigator.clipboard.writeText(text)
                .then(() => alert(t("copied")))
                .catch(() => alert("Unable to copy message."));
        }));

        if (sender === "ai") {
            actions.appendChild(createAction("🔊", () => speak(text)));
        }

        if (sender === "user") {
            actions.appendChild(createAction("Edit", () => {
                messageInput.value = text;
                messageInput.focus();
                if (save) {
                    const chat = getCurrentChat();
                    if (chat) {
                        chat.messages.splice(index, 1);
                        saveChats();
                        renderMessages();
                    }
                }
            }));
        }

        actions.appendChild(createAction("👍", button => {
            button.classList.toggle("active");
        }));

        actions.appendChild(createAction("👎", button => {
            button.classList.toggle("active");
        }));

        wrapper.appendChild(actions);
    }

    messages.appendChild(wrapper);

    if (save) {
        const chat = getCurrentChat();

        if (chat) {
            chat.messages.push({
                text,
                sender,
                time: new Date().toISOString()
            });

            if (chat.messages.length === 1 && sender === "user") {
                chat.title = text.slice(0, 35);
            }

            saveChats();
            renderHistory();
        }
    }

    messages.scrollTop = messages.scrollHeight;
}

function createAction(label, callback) {
    const button = document.createElement("button");

    button.type = "button";
    button.textContent = label;
    button.addEventListener("click", () => callback(button));

    return button;
}

/* ===============================
   7. SEND MESSAGE
================================ */

function sendMessage() {
    if (!messageInput) return;

    const text = messageInput.value.trim();

    if (!text && !RIZA.selectedImage) return;

    if (!RIZA.currentChat) createChat();

    if (text) {
        addMessage(text, "user");
    }

    messageInput.value = "";

    // Image preview is currently local only.
    // Real AI image analysis will be connected later.

    if (RIZA.selectedImage) {
        addMessage(
            "Image selected: " + RIZA.selectedImage.name,
            "user"
        );

        clearImage();
    }

    showTemporaryResponse();
}

function showTemporaryResponse() {
    const chat = getCurrentChat();

    if (!chat) return;

    const reply = RIZA.language === "rw"
        ? "Murakoze gukoresha RIZA AI! Ubu turimo gutegura uburyo bwo guhuza AI nyayo. Igisubizo cya AI kizajya kiza nyuma yo guhuza API."
        : "Thank you for using RIZA AI! We are preparing the real AI connection. Intelligent responses will be available after connecting the AI API.";

    setTimeout(() => {
        if (getCurrentChat()?.id === chat.id) {
            addMessage(reply, "ai");
        }
    }, 600);
}

if (sendButton) {
    sendButton.addEventListener("click", sendMessage);
}

if (messageInput) {
    messageInput.addEventListener("keydown", event => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });
}

/* ===============================
   8. NEW CHAT
================================ */

if (newChatButton) {
    newChatButton.addEventListener("click", () => {
        RIZA.currentChat = null;
        createChat();
        addMessage(t("hello"), "ai");
    });
}

/* ===============================
   9. SELECT ASSISTANT
================================ */

function updateAssistantUI() {
    if (assistantName) {
        assistantName.textContent = RIZA.assistant;
    }

    document.querySelectorAll(".assistant").forEach(card => {
        card.classList.toggle(
            "selected",
            card.dataset.assistant === RIZA.assistant
        );
    });
}

document.querySelectorAll(".assistant").forEach(card => {
    card.addEventListener("click", () => {
        RIZA.assistant = card.dataset.assistant || "RIZA BOY";

        updateAssistantUI();

        RIZA.currentChat = null;
        createChat();

        addMessage(
            RIZA.language === "rw"
                ? "Muraho! Ndi " + RIZA.assistant + ". Nagufasha iki?"
                : "Hello! I am " + RIZA.assistant + ". How can I help you?",
            "ai"
        );
    });
});

/* ===============================
   10. DARK / LIGHT MODE
================================ */

function applyTheme() {
    document.body.classList.toggle("dark", RIZA.theme === "dark");

    if (themeButton) {
        themeButton.textContent =
            RIZA.theme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
    }
}

if (themeButton) {
    themeButton.addEventListener("click", () => {
        RIZA.theme = RIZA.theme === "dark" ? "light" : "dark";

        localStorage.setItem("riza_theme", RIZA.theme);
        applyTheme();
    });
}

/* ===============================
   11. LANGUAGE EN / RW
================================ */

function applyLanguage() {
    if (messageInput) {
        messageInput.placeholder = t("placeholder");
    }

    if (languageButton) {
        languageButton.textContent =
            RIZA.language === "en" ? "🇷🇼 Kinyarwanda" : "🇬🇧 English";
    }

    const subtitle = document.querySelector(".welcome p");
    if (subtitle) subtitle.textContent = t("subtitle");

    renderHistory();
}

if (languageButton) {
    languageButton.addEventListener("click", () => {
        RIZA.language = RIZA.language === "en" ? "rw" : "en";

        localStorage.setItem("riza_language", RIZA.language);
        applyLanguage();
    });
}

/* ===============================
   12. IMAGE UPLOAD PREVIEW
================================ */

if (imageButton && imageInput) {
    imageButton.addEventListener("click", () => imageInput.click());
}

if (imageInput) {
    imageInput.addEventListener("change", event => {
        const file = event.target.files[0];

        if (!file) return;

        if (!file.type.startsWith("image/")) {
            alert("Please select an image.");
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert("Image must be smaller than 10 MB.");
            return;
        }

        RIZA.selectedImage = file;

        if (imagePreview) {
            imagePreview.innerHTML = "";

            const image = document.createElement("img");
            image.src = URL.createObjectURL(file);
            image.alt = "Selected image preview";

            imagePreview.appendChild(image);
        }
    });
}

function clearImage() {
    RIZA.selectedImage = null;

    if (imagePreview) {
        imagePreview.innerHTML = "";
    }

    if (imageInput) {
        imageInput.value = "";
    }
}

/* ===============================
   13. VOICE INPUT
================================ */

if (voiceButton) {
    voiceButton.addEventListener("click", () => {
        const SpeechRecognition =
            window.SpeechRecognition ||
            window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            alert(t("notSupported"));
            return;
        }

        const recognition = new SpeechRecognition();

        recognition.lang =
            RIZA.language === "rw" ? "rw-RW" : "en-US";

        recognition.interimResults = false;

        voiceButton.textContent = "🔴";

        recognition.onresult = event => {
            const result = event.results[0][0].transcript;

            messageInput.value = result;
            messageInput.focus();
        };

        recognition.onerror = () => {
            voiceButton.textContent = "🎤";
        };

        recognition.onend = () => {
            voiceButton.textContent = "🎤";
        };

        recognition.start();
    });
}

/* ===============================
   14. VOICE OUTPUT
================================ */

function speak(text) {
    if (!("speechSynthesis" in window)) {
        alert("Voice output is not supported.");
        return;
    }

    window.speechSynthesis.cancel();

    const speech = new SpeechSynthesisUtterance(text);

    speech.lang =
        RIZA.language === "rw" ? "rw-RW" : "en-US";

    speech.rate = 1;
    speech.pitch = 1;

    window.speechSynthesis.speak(speech);
}

/* ===============================
   15. SIDEBAR MENU
================================ */

if (menuButton) {
    menuButton.addEventListener("click", () => {
        document.body.classList.toggle("sidebar-collapsed");
    });
}

/* ===============================
   16. SETTINGS PLACEHOLDER
================================ */

const settingsButton = $("settingsButton");

if (settingsButton) {
    settingsButton.addEventListener("click", () => {
        alert("RIZA AI Settings will be available soon.");
    });
}

const loginLink = $("loginLink");

if (loginLink) {
    loginLink.addEventListener("click", () => {
        alert("Login and registration will be added in the next stage.");
    });
}

/* ===============================
   17. START RIZA AI
================================ */

applyTheme();
applyLanguage();
updateAssistantUI();

if (RIZA.chats.length > 0) {
    RIZA.currentChat = RIZA.chats[0].id;
    renderMessages();
} else {
    createChat();
    addMessage(t("hello"), "ai");
}

console.log("RIZA AI V2 initialized successfully.");
