let currentUserId = null; // The user you are chatting with
let pollInterval = null; // For polling new messages
let lastMessageId = 0;

// Load all mutuals with last message preview and unread count
function loadUsers() {
  fetch("backend/get_mutuals_with_last_message.php")
    .then((res) => res.json())
    .then((users) => {
      const usersDiv = document.getElementById("users");
      usersDiv.innerHTML = "<h4>Chats</h4>";
      if (!users.length) {
        usersDiv.innerHTML += "<em>No mutual friends yet.</em>";
        return;
      }
      users.forEach((u) => {
        const btn = document.createElement("button");
        btn.className = "user-list-item";
        btn.onclick = () => openChat(u.user_id, u.username);

        // Avatar (optional, only if you store avatars)
        if (u.avatar) {
          const avatar = document.createElement("img");
          avatar.src = u.avatar;
          avatar.alt = "";
          avatar.className = "user-avatar";
          btn.appendChild(avatar);
        }

        // Username and preview
        const label = document.createElement("div");
        label.className = "user-label";
        label.innerHTML = `
                <div class="user-name">${u.username}</div>
                <div class="last-message">
                    ${
                      u.last_message
                        ? (u.last_message_sender_id == window.loggedInUserId
                            ? "You: "
                            : "") +
                          (u.last_message.length > 30
                            ? u.last_message.slice(0, 30) + "…"
                            : u.last_message) +
                          (u.last_message_time
                            ? ' <span class="msg-time">· ' +
                              formatTimeAgo(u.last_message_time) +
                              "</span>"
                            : "")
                        : "<em>No messages</em>"
                    }
                </div>
            `;
        btn.appendChild(label);

        // Unread badge
        if (u.unread_count > 0) {
          const badge = document.createElement("span");
          badge.className = "unread-badge";
          badge.textContent = u.unread_count;
          btn.appendChild(badge);
        }

        usersDiv.appendChild(btn);
      });
    })
    .catch(() => {
      document.getElementById("users").innerHTML = "Could not load users.";
    });
}

// Open a chat with a user
function openChat(userId, username) {
  currentUserId = userId;
  lastMessageId = 0;
  document.getElementById("sendForm").style.display = "";
  document.getElementById("chatbox").innerHTML = "<em>Loading messages...</em>";

  loadMessages(true);

  // clear previous polling
  if (pollInterval) clearInterval(pollInterval);
  pollInterval = setInterval(() => loadMessages(false), 3000);
}

// Load all messages with currentUserId
function loadMessages(scrollToBottom) {
  if (!currentUserId) return;
  fetch("backend/get_conversation.php?user_id=" + currentUserId)
    .then((res) => res.json())
    .then((msgs) => {
      let chatbox = document.getElementById("chatbox");
      chatbox.innerHTML = "";
      let lastSender = null;
      msgs.forEach((m) => {
        // Only show avatar+name if sender changed or first message
        if (lastSender !== m.sender_id) {
          const header = document.createElement("div");
          header.className = "msg-sender-header";
          header.innerHTML = `
                    <img src="${m.sender_avatar}" class="chat-avatar" alt="">
                    <span class="chat-username">${m.sender_name}</span>
                `;
          chatbox.appendChild(header);
          lastSender = m.sender_id;
        }
        const div = document.createElement("div");
        div.className =
          m.sender_id == window.loggedInUserId
            ? "me msg-bubble"
            : "them msg-bubble";
        div.innerHTML = `
                <span class="msg-content">${escapeHTML(m.content)}</span>
                <span class="msg-time">${formatTimeAgo(m.sent_at)}</span>
                ${
                  m.sender_id == window.loggedInUserId && m.read_at
                    ? '<span class="msg-seen" title="Read">✔</span>'
                    : ""
                }
            `;
        chatbox.appendChild(div);
      });
      if (msgs.length === 0) {
        chatbox.innerHTML = "<em>No messages yet.</em>";
      }
      if (scrollToBottom !== false) chatbox.scrollTop = chatbox.scrollHeight;
      fetch("backend/mark_messages_read.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "user_id=" + encodeURIComponent(currentUserId),
      }).then(() => loadUsers());
    });
}

// Handle sending a message
document.getElementById("sendForm").onsubmit = function (e) {
  e.preventDefault();
  const msgInput = document.getElementById("msg");
  const msg = msgInput.value.trim();
  if (!msg || !currentUserId) return;
  msgInput.value = "";

  // Instantly display the message for sender
  const chatbox = document.getElementById("chatbox");
  const div = document.createElement("div");
  div.className = "me";
  const now = new Date();
  div.innerHTML = `<span class="msg-content">${escapeHTML(msg)}</span>
                     <span class="msg-time">${formatTimeAgo(
                       now.toISOString().slice(0, 19).replace("T", " ")
                     )}</span>`;
  chatbox.appendChild(div);
  chatbox.scrollTop = chatbox.scrollHeight;

  // Send to server
  fetch("backend/send_message.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body:
      "receiver_id=" +
      encodeURIComponent(currentUserId) +
      "&content=" +
      encodeURIComponent(msg),
  })
    .then((res) => res.json())
    .then((r) => {
      if (r.success) {
        loadMessages(true); // sync in case of missed/delayed messages
        loadUsers(); // update unread preview
      } else {
        alert(r.error || "Failed to send message.");
        chatbox.removeChild(chatbox.lastChild);
      }
    })
    .catch(() => {
      alert("Failed to send message (network error).");
      chatbox.removeChild(chatbox.lastChild);
    });
};

function formatTimeAgo(dateString) {
  const date = new Date(dateString.replace(" ", "T"));
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);
  if (diff < 60) return "Just now";
  if (diff < 3600) return Math.floor(diff / 60) + " min ago";
  if (diff < 86400) return Math.floor(diff / 3600) + "h ago";
  return date.toLocaleDateString();
}

// Utility to escape HTML
function escapeHTML(str) {
  return String(str).replace(/[<>&"']/g, function (c) {
    return {
      "<": "&lt;",
      ">": "&gt;",
      "&": "&amp;",
      '"': "&quot;",
      "'": "&#39;",
    }[c];
  });
}

window.onload = function () {
  window.loggedInUserId = parseInt('<?php echo $_SESSION["user_id"] ?? 0; ?>');
  loadUsers();
};
