document.addEventListener("DOMContentLoaded", () => {
  const postForm = document.getElementById("post-form");
  const postContent = document.getElementById("post-content");
  const mediaUpload = document.getElementById("media-upload");
  const mediaPreview = document.getElementById("media-preview");
  const tagButton = document.getElementById("tag-button");
  const tagSuggestions = document.getElementById("tag-suggestions");

  //Handle media preview
  mediaUpload.addEventListener("change", (e) => {
    mediaPreview.innerHTML = "";
    Array.from(e.target.files).forEach((file) => {
      const url = URL.createObjectURL(file);
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = url;
        img.alt = "Preview";
        img.style.maxWidth = "200px";
        img.style.maxHeight = "150px";
        mediaPreview.appendChild(img);
      } else if (file.type.startsWith("video/")) {
        const video = document.createElement("video");
        video.src = url;
        video.controls = true;
        video.style.maxWidth = "200px";
        video.style.maxHeight = "150px";
        mediaPreview.appendChild(video);
      }
    });
  });

  //Handle tag insertion
  tagButton.addEventListener("click", () => {
    const cursorPos = postContent.selectionStart;
    const text = postContent.value;
    postContent.value =
      text.substring(0, cursorPos) + " #" + text.substring(cursorPos);
    postContent.focus();
    postContent.setSelectionRange(cursorPos + 2, cursorPos + 2);
  });

  //Handle tag suggestions
  postContent.addEventListener("input", () => {
    const cursorPos = postContent.selectionStart;
    const text = postContent.value;

    if (
      text[cursorPos - 1] === "#" ||
      (text[cursorPos - 1] === " " && text[cursorPos - 2] === "#")
    ) {
      const tagStart = text.lastIndexOf(" ", cursorPos) + 1;
      const partialTag = text.substring(tagStart + 1, cursorPos);

      if (partialTag.length > 0) {
        fetch(
          "http://localhost/mygamelist/backend/get_tags.php?query=${encodeURIComponent(partialTag)}"
        )
          .then((response) => response.json())
          .then((tags) => {
            if (tags.length > 0) {
              tagSuggestions.innerHTML = tags
                .map(
                  (tag) =>
                    "<div class='tag-suggestion' data-tag='${tag}'>${tag}</div>"
                )
                .join("");

              const rect = postContent.getBoundingClientRect();
              tagSuggestions.style.top = "${rect.bottom}px";
              tagSuggestions.style.left = "${rect.left}px";
              tagSuggestions.style.display = "block";

              document
                .querySelectorAll(".tag-suggestion")
                .forEach((suggestion) => {
                  suggestion.addEventListener("click", () => {
                    const fullTag = suggestion.dataset.tag;
                    postContent.value =
                      text.substring(0, tagStart + 1) +
                      fullTag +
                      text.substring(cursorPos);
                    tagSuggestions.style.display = "none";
                    postContent.focus();
                    postContent.setSelectionRange(
                      tagStart + 1 + fullTag.length,
                      tagStart + 1 + fullTag.length
                    );
                  });
                });
            } else {
              tagSuggestions.style.display = "none";
            }
          });
        return;
      }
    }
    tagSuggestions.style.display = "none";
  });

  //Handle form submission
  postForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const formData = new FormData(postForm);
    //formData.append("post_content", postContent.value);

    //add media files
    for (let i = 0; i < mediaUpload.files.length; i++) {
      formData.append("media[]", mediaUpload.files[i]);
    }

    fetch("http://localhost/mygamelist/backend/create_post.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
          return response.json();
        }

        return response.text().then((text) => {
          throw new Error("Server returned non-JSON: $text");
        });
        //console.log("Response status:", response.status);
        //return response.json();
      })
      //.then((response) => response.json())
      .then((data) => {
        if (data.success) {
          postContent.value = "";
          mediaPreview.innerHTML = "";
          mediaUpload.value = "";
          alert("Post created successfully!");
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
      });
  });
});
