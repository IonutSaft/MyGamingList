let mutualsOffeset = 0;
const mutualsLimit = 10;
let loadingMutuals = false;
let allMutualsLoaded = false;

function loadMutuals(reset = false) {
  if (loadingMutuals || allMutualsLoaded) return;
  loadingMutuals = true;

  if (reset) {
    mutualsOffeset = 0;
    allMutualsLoaded = false;
    document.getElementById("mutualsList").innerHTML = "";
  }

  fetch(
    `backend/get_mutuals.php?offset=${mutualsOffeset}&limit=${mutualsLimit}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.length < mutualsLimit) allMutualsLoaded = true;
      mutualsOffeset += data.length;

      const container = document.getElementById("mutualsList");
      data.forEach((user) => {
        container.appendChild(makeMutualItem(user));
      });
    })
    .finally(() => (loadingMutuals = false));
}

function makeMutualItem(user) {
  const li = document.createElement("li");
  li.className = "mutual-user";
  li.dataset.userid = user.user_id;
  li.innerHTML = `
    <a class="user-card" href="#" onclick="openChatbox(${user.user_id}, '${
    user.username
  }', '${user.avatar}'); return false;">
      <img src="${user.avatar}" alt="">
      <span>${user.username}</span>
      <div class="last-message">${
        user.last_message ? user.last_message : "<i>No messages</i>"
      }</div>
    </a>
  `;
  return li;
}

//scroll
document.getElementById("mutualsList").addEventListener("scroll", function () {
  if (this.scrollTop + this.clientHeight >= this.scrollHeight - 10) {
    loadMutuals();
  }
});

//chatbox code
let activeChatUserId = null;
let messagesOffset = 0;
const messagesLimit = 30;
let loadingMessages = false;
let allMessagesLoaded = false;

// Open chatbox and load messages
function openChatbox(userId, username, avatar) {
  activeChatUserId = userId;
  messagesOffset = 0;
  allMessagesLoaded = false;
  document.getElementById("chatboxUser").innerHTML = `
    <img src="${avatar}" alt="">
    <span>${username}</span>
  `;
  document.getElementById("chatboxMessages").innerHTML = "";
  document.getElementById("chatbox").style.display = "block";
  loadMessages(true);
}

// Fetch messages with user
function loadMessages(reset = false) {
  if (loadingMessages || allMessagesLoaded || !activeChatUserId) return;
  loadingMessages = true;

  if (reset) {
    messagesOffset = 0;
    allMessagesLoaded = false;
    document.getElementById("chatboxMessages").innerHTML = "";
  }

  fetch(
    `backend/get_messages.php?user_id=${activeChatUserId}&offset=${messagesOffset}&limit=${messagesLimit}`
  )
    .then((res) => res.json())
    .then((data) => {
      if (data.length < messagesLimit) allMessagesLoaded = true;
      messagesOffset += data.length;

      const container = document.getElementById("chatboxMessages");
      data.forEach((msg) => {
        container.appendChild(makeMessageItem(msg));
      });
      // Optional: Scroll to bottom for new chats
      if (reset) container.scrollTop = container.scrollHeight;
    })
    .finally(() => (loadingMessages = false));
}

// Helper to make a message bubble
function makeMessageItem(msg) {
  const div = document.createElement("div");
  div.className =
    "message-bubble " +
    (msg.sender_id === window.loggedInUserId ? "outgoing" : "incoming");
  div.innerHTML = `
    <div class="message-content">${escapeHtml(msg.content)}</div>
    <div class="message-meta"><span>${formatTimeAgo(msg.sent_at)}</span></div>
  `;
  return div;
}

// Escape HTML utility
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text.replace(/[&<>"']/g, (m) => map[m]);
}

//sending message
document.getElementById("chatboxForm").addEventListener("submit", function (e) {
  e.preventDefault();
  const input = this.message;
  const content = input.value.trim();
  if (!content || !activeChatUserId) return;

  fetch("backend/send_message.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `receiver_id=${encodeURIComponent(
      activeChatUserId
    )}&content=${encodeURIComponent(content)}`,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // Add the new message to the chat
        const msg = {
          message_id: data.message_id,
          sender_id: window.loggedInUserId,
          receiver_id: activeChatUserId,
          content: data.content,
          sent_at: data.sent_at,
        };
        document
          .getElementById("chatboxMessages")
          .appendChild(makeMessageItem(msg));
        input.value = "";
        document.getElementById("chatboxMessages").scrollTop =
          document.getElementById("chatboxMessages").scrollHeight;
      }
    });
});

setInterval(function () {
  if (activeChatUserId) {
    loadMessages(true);
  }
}, 4000);

function formatTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);
  if (diff < 60) return "Just now";
  if (diff < 3600) return Math.floor(diff / 60) + " min ago";
  if (diff < 86400) return Math.floor(diff / 3600) + "h ago";
  return date.toLocaleDateString();
}
