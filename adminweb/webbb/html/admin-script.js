


function initAdminUsers() {
  if (!localStorage.getItem("adminUsers")) {
    localStorage.setItem(
      "adminUsers",
      JSON.stringify([
        {
          id: 1,
          username: "admin",
          password: "admin123",
          name: "Quản trị viên",
        },
      ]),
    );
  }
}


function handleAdminLogin() {
  const loginForm = document.getElementById("adminLoginForm");
  if (!loginForm) return;

  loginForm.addEventListener("submit", function (e) {
    e.preventDefault();
    const username = document.getElementById("adminUsername").value;
    const password = document.getElementById("adminPassword").value;
    const errorDiv = document.getElementById("loginError");

    const adminUsers = JSON.parse(localStorage.getItem("adminUsers") || "[]");
    const admin = adminUsers.find(
      (u) => u.username === username && u.password === password,
    );

    if (admin) {
      sessionStorage.setItem("adminLoggedIn", "true");
      sessionStorage.setItem("adminUser", JSON.stringify(admin));
      window.location.href = "index.html";
    } else {
      errorDiv.textContent = "Tên đăng nhập hoặc mật khẩu không đúng!";
      errorDiv.style.display = "block";
    }
  });
}


document.addEventListener("DOMContentLoaded", function () {
  initAdminUsers();
  handleAdminLogin();
});
