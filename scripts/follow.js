document.querySelectorAll(".follow-btn").forEach((btn) => {
  btn.addEventListener("click", async function () {
    const userId = this.dataset.userId;
    const isFollowing = this.classList.contains("following");

    try {
      const response = await fetch("backend/follow_action.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: userId,
          action: isFollowing ? "unfollow" : "follow",
        }),
      });

      const result = await response.json();
      if (result.success) {
        this.classList.toggle("following");
        this.textContent = isFollowing ? "Follow" : "Following";
        // Update follower count
        document.querySelector(".stat:nth-child(2) .stat-number").textContent =
          parseInt(
            document.querySelector(".stat:nth-child(2) .stat-number")
              .textContent
          ) + (isFollowing ? -1 : 1);
      }
    } catch (error) {
      console.error("Error:", error);
    }
  });
});
