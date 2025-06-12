// Like functionality
document.querySelectorAll("a.like-btn").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    const postId = this.getAttribute("data-post-id");
    if (!postId) return;
    fetch("backend/toggle_like.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "post_id=" + encodeURIComponent(postId),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const feedItem = this.closest(".feed-item");
          const statsDiv = feedItem.querySelector(
            ".post-stats .like-comment-count"
          );
          // Update like and comment counts from AJAX response
          statsDiv.innerHTML = `${data.like_count} <i class="fas fa-thumbs-up"></i> ${data.comment_count} comments`;
          statsDiv.setAttribute("data-like-count", data.like_count);
          statsDiv.setAttribute("data-comment-count", data.comment_count);
          // Toggle liked visual state and icon
          if (data.liked) {
            this.classList.add("liked");
            this.querySelector("i").classList.remove("far");
            this.querySelector("i").classList.add("fas");
          } else {
            this.classList.remove("liked");
            this.querySelector("i").classList.remove("fas");
            this.querySelector("i").classList.add("far");
          }
        } else {
          console.error("Like AJAX failed:", data);
        }
      })
      .catch((err) => {
        console.error("Fetch error on like:", err);
      });
  });
});

// Comment functionality
document.querySelectorAll(".comment-form").forEach((form) => {
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    const postId = this.querySelector('input[name="post_id"]').value;
    const textarea = this.querySelector('textarea[name="comment_content"]');
    const content = textarea.value.trim();
    if (!postId || !content) return;
    fetch("backend/add_comment.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "post_id=" +
        encodeURIComponent(postId) +
        "&comment_content=" +
        encodeURIComponent(content),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const feedItem = this.closest(".feed-item");
          const commentsList = feedItem.querySelector(".comments-list");
          commentsList.innerHTML = data.comments
            .map(
              (comment) => `
          <div class="comment">
            <div class="comment-avatar">
              <a href="userpage.php?id=${comment.user_id}">
                <img src="${comment.avatar}" alt="Profile">
              </a>
            </div>
            <div class="comment-body">
              <div class="comment-header">
                <span class="post-author">${comment.username}</span>
                <span class="post-time">${comment.comment_date}</span>
              </div>
              <div class="comment-text">${comment.content}</div>
            </div>
          </div>
        `
            )
            .join("");
          textarea.value = "";
          // Update like and comment counts from AJAX response
          const statsDiv = feedItem.querySelector(
            ".post-stats .like-comment-count"
          );
          statsDiv.innerHTML = `${data.like_count} <i class="fas fa-thumbs-up"></i> ${data.comment_count} comments`;
          statsDiv.setAttribute("data-like-count", data.like_count);
          statsDiv.setAttribute("data-comment-count", data.comment_count);
        } else {
          console.error("Comment AJAX failed:", data);
        }
      })
      .catch((err) => {
        console.error("Fetch error on comment:", err);
      });
  });
});
