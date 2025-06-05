document
  .getElementById("media-upload")
  .addEventListener("change", function (e) {
    const preview = document.getElementById("media-preview");
    preview.innerHTML = "";

    Array.from(e.target.files).forEach((file) => {
      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.src = URL.createObjectURL(file);
        img.style.height = "100px";
        preview.appendChild(img);
      } else if (file.type.startsWith("video/")) {
        const vid = document.createElement("video");
        vid.src = URL.createObjectURL(file);
        vid.style.height = "200px";
        vid.style.width = "100%";
        vid.controls = true;
        preview.appendChild(vid);
      }
    });
  });
