import template from "./template.html?raw";
import { UserData } from "../../data/user.js";
import { htmlToFragment } from "../../lib/utils.js";

export function SignupPage() {
  const frag = htmlToFragment(template);
  const form = frag.querySelector("#signupForm");
  const errorDiv = frag.querySelector("#signupError");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorDiv.textContent = "";

    const username = form.username.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value;

    if (!username || !email || !password) {
      errorDiv.textContent = "Veuillez remplir tous les champs.";
      return;
    }

    const res = await UserData.signup({ username, email, password });

    if (res && !res.error) {
      sessionStorage.setItem("user", JSON.stringify(res));
      if (window.router && typeof window.router.setAuth === "function") {
        window.router.setAuth(true);
        window.router.navigate("/profile");
      } else {
        window.location.href = "/profile";
      }
    } else {
      errorDiv.textContent = res?.message || "Erreur lors de la création du compte.";
    }
  });

  return frag;
}

