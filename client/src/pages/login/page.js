import template from "./template.html?raw";

import { postRequest } from "../../lib/api-request.js";
import { htmlToFragment } from "../../lib/utils.js";

export function LoginPage() {
  let frag = htmlToFragment(template);
  let form = frag.querySelector("#loginForm");
  let errorDiv = frag.querySelector("#loginError");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorDiv.textContent = "";
    let email = form.email.value.trim();
    let password = form.password.value;

    if (!email || !password) {
      errorDiv.textContent = "Veuillez remplir tous les champs.";
      return;
    }

    let res = await postRequest("users/login", { email, password });
    if (res && !res.error) {
      sessionStorage.setItem("user", JSON.stringify(res));
      window.router.setAuth(true);
      window.router.navigate("/profile");
    } else {
      errorDiv.textContent = res?.message || "Identifiants incorrects.";
    }
  });

  return frag;
}

