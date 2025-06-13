document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".post-menu > i").forEach((menuIcon) => {
    menuIcon.addEventListener("click", function (e) {
      e.stopPropagation();
      const options = menuIcon.parentElement.querySelector(".post-options");
      options.style.display =
        options.style.display === "block" ? "none" : "block";
    });
  });

  document.addEventListener("click", function () {
    document.querySelectorAll(".post-options").forEach((options) => {
      options.style.display = "none";
    });
  });

  document.querySelectorAll(".hide-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const feedItem = btn.closest(".feed-item");
      if (feedItem) feedItem.style.display = "none";
    });
  });

  document.querySelectorAll(".report-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const feedItem = btn.closest(".feed-item");
      if (feedItem) feedItem.style.display = "none";
      alert("Post reported. Thank you for your feedback!");
    });
  });
});
