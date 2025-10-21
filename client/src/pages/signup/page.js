import template from "./template.html?raw";
import { postRequest } from "../../lib/api-request.js";
import { htmlToFragment } from "../../lib/utils.js";

export function SignupPage() {
  let frag = htmlToFragment(template);
  let form = frag.querySelector("#signupForm");
  let errorDiv = frag.querySelector("#signupError");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorDiv.textContent = "";
    let username = form.username.value.trim();
    let email = form.email.value.trim();
    let password = form.password.value;

    if (!username || !email || !password) {
      errorDiv.textContent = "Veuillez remplir tous les champs.";
      return;
    }

    let formData = new FormData();
    formData.append("username", username);
    formData.append("email", email);
    formData.append("password", password);

let res = await postRequest("users", formData);

    if (res && !res.error) {
      sessionStorage.setItem("user", JSON.stringify(res));
      window.router.setAuth(true);
      window.router.navigate("/profile");
    } else {
      errorDiv.textContent = res?.message || "Erreur lors de la création du compte.";
    }
  });

  return frag;
}

