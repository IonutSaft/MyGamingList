document.addEventListener("DOMContentLoaded", function () {
  // === COVER UPLOAD ===
  const coverForm = document.getElementById("cover-form");
  const coverInput = document.getElementById("cover-upload");
  const coverImg = document.getElementById("cover-image");
  if (coverForm && coverInput) {
    coverInput.addEventListener("change", function () {
      const file = coverInput.files[0];
      if (!file) return;
      const formData = new FormData();
      formData.append("cover", file);

      fetch("backend/update_cover.php", {
        method: "POST",
        body: formData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success && data.url) {
            coverImg.src = data.url;
          } else {
            alert("Cover update failed: the file might be too large");
          }
        });
    });
  }

  // === AVATAR UPLOAD ===
  const avatarForm = document.getElementById("avatar-form");
  const avatarInput = document.getElementById("avatar-upload");
  const avatarImg = document.getElementById("avatar-image");
  if (avatarForm && avatarInput) {
    avatarInput.addEventListener("change", function () {
      const file = avatarInput.files[0];
      if (!file) return;
      const formData = new FormData();
      formData.append("avatar", file);

      fetch("backend/update_avatar.php", {
        method: "POST",
        body: formData,
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success && data.url) {
            avatarImg.src = data.url;
          } else {
            alert("Avatar update failed: the file might be too large");
          }
        });
    });
  }

  // === BIO UPDATE ===
  const saveBioBtn = document.getElementById("save-bio");
  const bioTextarea = document.getElementById("profile-bio");
  if (saveBioBtn && bioTextarea) {
    saveBioBtn.addEventListener("click", function (e) {
      e.preventDefault();
      fetch("backend/update_bio.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "bio=" + encodeURIComponent(bioTextarea.value),
      })
        .then((r) => r.json())
        .then((data) => {
          if (data.success) {
            // Optionally show a message, or update the bio elsewhere on the page
          } else {
            alert("Bio update failed");
          }
        });
    });
  }
});
