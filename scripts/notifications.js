function updateNotificationBadge() {
  fetch("backend/get_unread_notifications_count.php")
    .then((res) => res.json())
    .then((data) => {
      const badge = document.querySelector(
        '.nav-icon[title="Notifications"] .notification-badge'
      );
      if (badge) {
        if (data.unread_count > 0) {
          badge.textContent = data.unread_count;
          badge.style.display = "";
        } else {
          badge.style.display = "none";
        }
      }
    });
}

function loadNotificationsDropdown() {
  fetch("backend/get_notifications.php")
    .then((res) => res.json())
    .then((notifications) => {
      let dropdown = document.getElementById("notifications-dropdown");
      if (!dropdown) {
        dropdown = document.createElement("div");
        dropdown.id = "notifications-dropdown";
        dropdown.className = "dropdown-menu";
        document
          .querySelector('.nav-icon[title="Notifications"]')
          .appendChild(dropdown);
      }
      dropdown.innerHTML = "";
      if (notifications.length === 0) {
        dropdown.innerHTML =
          '<div class="dropdown-item">No notifications</div>';
      } else {
        notifications.forEach((n) => {
          dropdown.innerHTML += `
            <div class="dropdown-item${
              n.is_read ? "" : " unread"
            }" style="display: flex; align-items: center;">
              <a href="userpage.php?id=${
                n.actor_id
              }" style="margin-right: 8px;">
                <img src="${n.actor_avatar}" alt="${
            n.actor_username
          }" class="notif-avatar" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
              </a>
              <div>
                <a href="userpage.php?id=${
                  n.actor_id
                }" class="notif-username" style="font-weight:bold;">${
            n.actor_username
          }</a>
                <span class="notif-content"> ${n.content}</span>
                <div>
                  <span class="notif-date" style="font-size:0.85em;color:gray;">${new Date(
                    n.created_at
                  ).toLocaleString()}</span>
                </div>
              </div>
            </div>
          `;
        });
      }
      dropdown.style.display = "block";
    });
  // Mark as read
  fetch("backend/mark_notifications_read.php", { method: "POST" }).then(() =>
    updateNotificationBadge()
  );
}

document.addEventListener("DOMContentLoaded", function () {
  updateNotificationBadge();
  setInterval(updateNotificationBadge, 30000); // Update every 30s
  const notifBtn = document.querySelector('.nav-icon[title="Notifications"]');
  if (notifBtn) {
    notifBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      loadNotificationsDropdown();
    });
    document.addEventListener("click", function (e) {
      const dropdown = document.getElementById("notifications-dropdown");
      if (dropdown && !notifBtn.contains(e.target)) {
        dropdown.style.display = "none";
      }
    });
  }
});
