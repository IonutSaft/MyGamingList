// Post deletion
document.querySelectorAll(".delete-post").forEach((btn) => {
  btn.addEventListener("click", async function () {
    if (!confirm("Delete this post permanently?")) return;

    const postId = this.dataset.postId;
    try {
      const response = await fetch("backend/delete_post.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ post_id: postId }),
      });

      const result = await response.json();
      if (result.success) {
        this.closest(".feed-item").remove();
      } else {
        alert(result.error || "Failed to delete post");
      }
    } catch (error) {
      console.error("Error:", error);
      alert("Network error");
    }
  });
});
