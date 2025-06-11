const currentUserId = window.currentUserId;

if (currentUserId === profileUserId) {
  document.getElementById("add-game-btn").onclick = () => {
    document.getElementById("add-game-modal").style.display = "flex";
    document.getElementById("game-search-input").value = "";
    document.getElementById("game-search-results").innerHTML = "";
  };
}
document.getElementById("close-add-game-modal").onclick = () => {
  document.getElementById("add-game-modal").style.display = "none";
};

document.getElementById("game-search-input").oninput = function () {
  const query = this.value;
  if (query.length < 2) {
    document.getElementById("game-search-results").innerHTML = "";
    return;
  }
  fetch("backend/search_games.php?q=" + encodeURIComponent(query))
    .then((res) => res.json())
    .then((data) => {
      let html = "";
      if (data.length === 0) {
        html = '<div class="search-result">No games found</div>';
      } else {
        data.forEach((game) => {
          html += `<div class="search-result" data-game-id="${game.game_id}" data-title="${game.title}">${game.title}</div>`;
        });
      }
      document.getElementById("game-search-results").innerHTML = html;
    });
};

document.getElementById("game-search-results").onclick = function (e) {
  if (e.target.classList.contains("search-result") && e.target.dataset.gameId) {
    const gameId = e.target.getAttribute("data-game-id");
    fetch("backend/add_games.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "profile_user_id=" +
        encodeURIComponent(profileUserId) +
        "&game_id=" +
        encodeURIComponent(gameId),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          document.getElementById("add-game-modal").style.display = "none";
          loadUserGameList();
        } else {
          alert(data.error || "Failed to add game.");
        }
      });
  }
};

document.getElementById("user-game-list").onclick = function (e) {
  if (e.target.classList.contains("remove-game-btn")) {
    if (!confirm("Remove this game from your list?")) return;
    const gameListid = e.target.getAttribute("data-game-list-id");
    fetch("backend/remove_game_from_list.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "game_list_id=" + encodeURIComponent(gameListid),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) loadUserGameList();
        else alert("Could not remove game");
      });
  }

  if (e.target.classList.contains("game-status-dropdown")) {
    const gameListId = e.target.getAttribute("data-game-list-id");
    const newStatus = e.target.value;
    fetch("backend/update_game_status.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body:
        "game_list_id=" +
        encodeURIComponent(gameListId) +
        "&status=" +
        encodeURIComponent(newStatus),
    });
  }
};

function loadUserGameList() {
  fetch(
    "backend/fetch_user_game_list.php?profile_user_id=" +
      encodeURIComponent(profileUserId)
  )
    .then((res) => res.text())
    .then((html) => {
      document.getElementById("user-game-list").innerHTML = html;
    });
}

loadUserGameList();
