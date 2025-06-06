document.addEventListener("DOMContentLoaded", function () {
  let page = 1;
  let isLoading = false;
  const feedContainer = document.getElementById("feed-container");
  const loadingElement = document.getElementById("loading");

  loadPosts();

  window.addEventListener("scroll", function () {
    if (isLoading) return;

    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    if (scrollTop + clientHeight >= scrollHeight - 100) {
      page++;
      loadPosts();
    }
  });

  function loadPosts() {
    isLoading = true;
    loadingElement.style.display = "block";

    fetch("../mygamelist/backend/load_posts.php?page=${page}")
      .then((response) => {
        if (!response.ok) throw new Error("Network error");
        return response.json();
      })
      .then((data) => {
        if (!data.success) throw new Error(data.error);

        const posts = data.posts;

        if (posts.length === 0 && page === 1) {
          feedContainer.innerHTML =
            '<div class="no-posts">No posts yet. Be the first to post!</div>';
          return;
        }
        posts.forEach((post) => {
          feedContainer.appendChild(createPostElement(post));
        });
      })
      .catch((error) => {
        console.error("Error loading posts:", error);
      })
      .finally(() => {
        isLoading = false;
        loadingElement.style.display = "none";
      });
  }

  function createPostElement(post) {
    const postElement = document.createElement("div");
    postElement.className = "feed-item";
    postElement.dataset.postId = post.post_id;

    let postHTML = `
      <div class="post-header">
        <img src="${post.avatar} || 'default/default_avatar.png" alt="${post.username}">
        <div>
          <div class="post-author">${post.username}</div>
          <div class="post-time">${post.time_ago} • <i class="fas fa-globe-americas"></i></div>
        </div>
        <div class="post-menu">
          <i class="fas fa-ellipsis-h"></i>
        </div>
      </div>
      <div class="post-content">
        <p class="post-test">${post.text_content}</p>`;

    if (post.media_conte && post.media_content.length > 0) {
      post.media_content.forEach((media) => {
        if (media.match(/\.(mp4)$/i)) {
          postHTML += `
            <video controls class="post-media">
              <source src="${media}" type="video/mp4">
              Your browser does not support the video tag.
            </video>`;
        } else {
          postHTML += `<img src="${media}" class="post-media" loading="lazy">`;
        }
      });
    }

    postHTML += `
      </div>
      <div class="post-stats">
        <div>${post.like_count || 0} <i class="fas fa-thumbs-up"></i></div>
        <div>${post.comment_count || 0} comments • ${
      post.shares_count || 0
    } shares</div>
      </div>
      <div class="post-actions">
        <div class="post-action like-btn" data-post-id="${post.post_id}">
          <i class="fas fa-thumbs-up"></i>
          <span>Like</span>
        </div>
        <div class="post-action comment-btn" data-post-id="${post.post_id}">
          <i class="fas fa-comment"></i>
          <span>Comment</span>
        </div>
        <div class="post-action share-btn" data-post-id="${post.post_id}">
          <i class="fas fa-share"></i>
          <span>Share</span>
        </div>
      </div>`;

    postElement.innerHTML = postHTML;
    return postElement;
  }
});
