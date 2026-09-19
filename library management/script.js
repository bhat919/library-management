// ================= REGISTER VALIDATION =================

function validateRegisterForm() {
  const password = document.getElementById("password");

  const confirmPassword = document.getElementById("confirm_password");

  if (password && confirmPassword) {
    if (password.value.length < 6) {
      alert("Password must contain at least 6 characters.");

      return false;
    }

    if (password.value !== confirmPassword.value) {
      alert("Passwords do not match.");

      return false;
    }
  }

  return true;
}

// ================= BUTTON ANIMATION =================

document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(
    ".feature-card, .stat-card, .action-card",
  );

  cards.forEach(function (card) {
    card.addEventListener("mouseenter", function () {
      this.style.transition = "0.3s ease";
    });
  });
});
