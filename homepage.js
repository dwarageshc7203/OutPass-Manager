window.addEventListener("DOMContentLoaded", function () {
    const contacts = document.getElementById("contacts");
    const footer = document.querySelector("footer");
  
    contacts.addEventListener("click", function () {
      footer.scrollIntoView({ behavior: "smooth" });
    });
  });

function redirect_login(){
  window.location.href="login.html";
};
    