import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";

let C = {};

C.handler_submit = async function(ev) {
  ev.preventDefault();

  const form = ev.target;
  const errorDiv = form.querySelector("#loginError");
  errorDiv.textContent = "";

  const email = form.email.value.trim();
  const password = form.password.value;

  if (!email || !password) {
    errorDiv.textContent = "Veuillez remplir tous les champs.";
    return;
  }

  // Préparer les données à envoyer
  const formData = new FormData();
  formData.append("email", email);
  formData.append("password", password);

  const res = await postRequest("users/login", formData);

  if (res && !res.error) {
    sessionStorage.setItem("user", JSON.stringify(res));

    if (window.router && typeof window.router.setAuth === "function") {
      window.router.setAuth(true);
      window.router.navigate("/profile");
    } else {
      window.location.href = "/profile";
    }
  } else {
    errorDiv.textContent = res?.message || "Erreur lors de la connexion.";
  }
}

let V = {};

V.createPageFragment = function() {
  let frag = htmlToFragment(template);
  return frag;
}

V.attachEvents = function(fragment) {
  const form = fragment.querySelector("#loginForm");
  form.addEventListener("submit", C.handler_submit);
  return fragment;
}

C.init = function() {
  const fragment = V.createPageFragment();
  V.attachEvents(fragment);
  return fragment;
}

export function LoginPage(params) {
  return C.init(params);
}
