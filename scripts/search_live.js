document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("searchInput");
  const dropdown = document.getElementById("searchResultsDropdown");
  let results = [];

  searchInput.addEventListener("input", function () {
    const query = searchInput.value.trim();
    if (query.length === 0) {
      dropdown.style.display = "none";
      dropdown.innerHTML = "";
      return;
    }
    fetch(`backend/search_suggest.php?q=${encodeURIComponent(query)}`)
      .then((res) => res.json())
      .then((data) => {
        results = data;
        if (results.users.length === 0 && results.posts.length === 0) {
          dropdown.innerHTML = '<div class="dropdown-item">No results</div>';
        } else {
          let html = "";
          if (results.users.length > 0) {
            html += '<div class="src-dropdown-section">Users</div>';
            results.users.forEach((user) => {
              html += `<div class="src-dropdown-item" data-url="userpage.php?id=${user.user_id}">
                <img src="${user.avatar}" style="width:24px;height:24px;border-radius:50%;vertical-align:middle;margin-right:8px;">
                ${user.username}
              </div>`;
            });
          }
          if (results.posts.length > 0) {
            html += '<div class="src-dropdown-section">Posts</div>';
            results.posts.forEach((post) => {
              html += `<div class="src-dropdown-item" data-url="search.php?q=${encodeURIComponent(
                query
              )}">
                <span>${
                  post.text_content.length > 50
                    ? post.text_content.slice(0, 50) + "..."
                    : post.text_content
                }</span>
              </div>`;
            });
          }
          dropdown.innerHTML = html;
        }
        dropdown.style.display = "block";
      });
  });

  // Handle click on dropdown item
  dropdown.addEventListener("click", function (e) {
    const item = e.target.closest(".src-dropdown-item");
    if (item && item.dataset.url) {
      window.location.href = item.dataset.url;
    }
  });

  // Hide dropdown on click outside
  document.addEventListener("click", function (e) {
    if (!dropdown.contains(e.target) && e.target !== searchInput) {
      dropdown.style.display = "none";
    }
  });

  // On Enter, go to search page
  searchInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      window.location.href = `search.php?q=${encodeURIComponent(
        searchInput.value.trim()
      )}`;
      e.preventDefault();
    }
  });
});
