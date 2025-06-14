function updateEnvelopeUnreadCount() {
  fetch("backend/get_unread_count.php")
    .then((res) => res.json())
    .then((data) => {
      if (typeof data.unread_count !== "undefined") {
        const badge = document.querySelector(
          '.nav-icon[title="Mail"] .notification-badge'
        );
        if (badge) {
          if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.style.display = "";
          } else {
            badge.style.display = "none";
          }
        }
      }
    })
    .catch(() => {
      console.error("Error updating envelope unread count.");
    });
}

updateEnvelopeUnreadCount();
setInterval(updateEnvelopeUnreadCount, 30000);
