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

    const firstName = form.firstName.value.trim();
    const lastName = form.lastName.value.trim();
    const email = form.email.value.trim();
    const dateOfBirth = form.dateOfBirth.value;
    const password = form.password.value;

    if (!firstName || !lastName || !email || !dateOfBirth || !password) {
      errorDiv.textContent = "Veuillez remplir tous les champs.";
      return;
    }

    const res = await UserData.signup({ 
      firstName, 
      lastName, 
      email, 
      dateOfBirth,
      password 
    });

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

