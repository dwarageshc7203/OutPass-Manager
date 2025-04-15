function redirect(){
    window.location.href="homepage.html";
};

console.log("hello");

function redirect_signup(){
    window.location.href="signup.html";
};

window.addEventListener("DOMContentLoaded", function () {
    const contacts = document.getElementById("contacts");
    const footer = document.querySelector("footer");
  
    contacts.addEventListener("click", function () {
      footer.scrollIntoView({ behavior: "smooth" });
    });
  });
  
window.addEventListener("DOMContentLoaded", function () {
    const contacts = document.getElementById("contacts");
    const footer = document.querySelector("footer");
  
    contacts.addEventListener("click", function () {
      footer.scrollIntoView({ behavior: "smooth" });
    });
  });

login=document.getElementById("login_form");
login.addEventListener("submit", function (event){
    event.preventDefault();
    const username=document.getElementById("username").value.trim();
    const uid=document.getElementById("uid").value.trim();
    const password=document.getElementById("password").value.trim();
    const role=document.getElementById("role").value.trim();


    if(username&&uid&&password&&role){
        window.location.href="";
    }
    else{
        alert("Fill in all the detials");
    }
});