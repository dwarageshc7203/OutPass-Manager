const password=document.getElementById("password");
const confirm_password=document.getElementById("confirm_password");
const password_checker=document.getElementById("password_checker");
const form=document.getElementById("login_form");

confirm_password.addEventListener("input", function checkPassword(){
    if(password.value!== confirm_password.value){
        password_checker.innerHTML="Password does not match, enter again";
    }
    else{
        password_checker.innerHTML="";
    }
});

window.addEventListener("DOMContentLoaded", function () {
    const contacts = document.getElementById("contacts");
    const footer = document.querySelector("footer");
  
    contacts.addEventListener("click", function () {
      footer.scrollIntoView({ behavior: "smooth" });
    });
  });
  
function removeShake(event){
    Element.addEventListener('animationed', () => {Element.classList.remove("shake");},{once: true});
}

form.addEventListener("submit", function resist(event){
    if(password.value!==confirm_password.value){
        event.preventDefault(); 
        password.classList.add("shake");
        confirm_password.classList.add("shake");
    }
});
