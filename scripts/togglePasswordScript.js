function togglePassword(iconElement) {
  iconElement.classList.toggle("fa-eye");
  iconElement.classList.toggle("fa-eye-slash");

  const passwordInput = iconElement.previousElementSibling;
  passwordInput.type = passwordInput.type === "password" ? "test" : "password";
}
