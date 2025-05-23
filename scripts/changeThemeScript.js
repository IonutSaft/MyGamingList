const themeToggle = document.getElementById("theme-toggle");
const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");

const currentTheme = localStorage.getItem("theme");
if (currentTheme === "light" || (!currentTheme && !prefersDarkScheme.matches)) {
  document.body.classList.add("light-mode");
  toggleIcons(true);
}

themeToggle.addEventListener("click", () => {
  const isLight = document.body.classList.toggle("light-mode");
  localStorage.setItem("theme", isLight ? "light" : "dark");
  toggleIcons(isLight);
});

function toggleIcons(isLight) {
  const sunIcon = themeToggle.querySelector(".fas.fa-sun");
  const moonIcon = themeToggle.querySelector(".fas.fa-moon");

  sunIcon.style.display = isLight ? "none" : "block";
  moonIcon.style.display = isLight ? "block" : "none";
}

prefersDarkScheme.addEventListener("change", (e) => {
  if (!localStorage.getItem("theme")) {
    document.body.classList.toggle("light-mode", !e.matches);
    toggleIcons(!e.matches);
  }
});
