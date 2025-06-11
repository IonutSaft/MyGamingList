const profileUserId = window.profileUserId;

function openModal(title, type, userId, page = 1) {
  document.getElementById("modal-title").innerHTML = title;
  document.getElementById("follow-modal").style.display = "flex";
  loadModalContent(type, userId, page);
}
function closeModal() {
  document.getElementById("follow-modal").style.display = "none";
}
document.getElementById("close-modal").onclick = closeModal;
window.onclick = function (event) {
  if (event.target == document.getElementById("follow-modal")) closeModal();
};

function loadModalContent(type, userId, page = 1) {
  document.getElementById("modal-body").innerHTML = "<em>Loading...</em>";
  fetch(`backend/fetch_follows.php?type=${type}&user_id=${userId}&page=${page}`)
    .then((res) => res.text())
    .then((html) => {
      const [listHtml, pagiationHtml] = html.split("\n\n");
      document.getElementById("modal-body").innerHTML = listHtml;
      document.getElementById("modal-pagination").innerHTML =
        pagiationHtml || "";
    });
}

document.getElementById("show-followers").onclick = function () {
  openModal("Followers", "followers", profileUserId);
};
document.getElementById("show-following").onclick = function () {
  openModal("Following", "following", profileUserId);
};

document.getElementById("modal-pagination").onclick = function (event) {
  if (event.target.classList.contains("page-link")) {
    event.preventDefault();
    const page = event.target.getAttribute("data-page");
    const type = document.getElementById("modal-title").innerText.toLowerCase();
    loadModalContent(type, profileUserId, page);
  }
};
