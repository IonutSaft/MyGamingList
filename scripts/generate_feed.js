document.addEventListener("DOMContentLoaded", function () {
  const confit = {
    postsPerPage: 10,
    scrollThreshold: 100,
  };

  const state = {
    page: 1,
    isLoading: false,
    hasMore: true,
  };

  const elements = {
    container: document.getElementById("feed-container"),
    loading: document.getElementById("loading"),
  };

  loadPosts();

  window.addEventListener("scroll", handleScroll);

  async function loadPosts() {
    if (state.isLoading || !state.hasMore) return;

    state.isLoading = true;
    elements.loading.style.display = "block";

    try {
      const response = await fetch(`backend/load_posts.php?page=${state.page}`);
      const data = await response.json();

      if (!data.success) {
        throw new Error(data.error || "Failed to load posts");
      }

      if (data.posts.length === 0) {
        state.hasMore = false;
        if (state.page === 1) {
          showNoPostsMessage();
        }
        return;
      }

      renderPosts(data.posts);
      state.page++;
    } catch (error) {
      console.error("Post loading error:", error);
      showError(error.message);
    } finally {
      state.isLoading = false;
      elements.loading.style.display = "none";
    }
  }
  function renderPosts(posts) {
    posts.forEach((post) => {
      const postElement = createPostElement(post);
      elements.container.appendChild(postElement);
    });
  }

  function createPostElement(post) {
    const postElement = document.createElement("div");
    postElement.className = "feed-item";
    postElement.dataset.postId = post.post_id;

    postElement.innerHTML = `
      <div class="post-header">
        <img src="${post.avatar}"
          onerror="this.src='/mygamelist/default/default_avatar.png'"
          alt="${post.username}">
        <div>
          <div class="post-author">${post.username}</div>
          <div class="post-time">${
            post.time_ago
          } • <i class="fas fa-globe-americas"></i></div>
        </div>
        <div class="post-menu">
          <i class="fas fa-ellipsis-h"></i>
        </div>
      </div>
      <div class="post-content">
        <p class="post-test">${post.text_content}</p>
        ${renderMedia(post.media_content)}
      </div>
      <div class="post-stats">
        <div></div>
        <div>${post.like_count || 1} <i class="fas fa-thumbs-up"></i>
        ${post.comment_count || 1} comments</div>
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
      </div>`;

    return postElement;
  }

  function renderMedia(mediaArray) {
    if (!mediaArray || mediaArray.length === 0) return "";

    return mediaArray
      .map((media) => {
        if (media.match(/\.(mp4)$/i)) {
          return `
          <video controls class="post-media">
            <source src="${media}" type="video/mp4">
            Your browser doesn't support videdos
          </video>  
          `;
        } else {
          return `
          <img src = "${media}" class="post-media" loading="lazy" onerror="this.style.display='none';">
        `;
        }
      })
      .join("");
  }

  function handleScroll() {
    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    if (scrollTop + clientHeight >= scrollHeight - confit.scrollThreshold) {
      loadPosts();
    }
  }

  function showNoPostsMessage() {
    elements.container.innerHTML = `
      <div class="no-posts">
        <i class="fas fa-newspaper"></i>
        <p>No posts yet. Be the first to share something!</p>
      </div>
    `;
  }

  function showError(message) {
    const errorEl = document.createElement("div");
    errorEl.className = "error-message";
    errorEl.innerHTML = `
      <i class="fas fa-exclamation-circle"></i>
      <p>${message}</p>
    `;
    elements.container.appendChild(errorEl);
  }
});
