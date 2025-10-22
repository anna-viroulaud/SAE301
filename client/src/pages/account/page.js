import template from "./template.html?raw";
import { htmlToFragment } from "../../lib/utils.js";
import { NavView } from "../../ui/nav/index.js";
import { UserData } from "../../data/user.js";

export function AccountPage(){
  const frag = htmlToFragment(template);
  // si template contient un emplacement #navRoot/#mainRoot, on l'utilise
  const navRoot = frag.querySelector("#navRoot");
  const mainRoot = frag.querySelector("#mainRoot");
  const cached = JSON.parse(sessionStorage.getItem("user") || "null");

  if (navRoot) navRoot.appendChild(NavView.dom());
  if (mainRoot) {
    mainRoot.innerHTML = `<h1 class="text-2xl font-bold mb-4">Bienvenue${cached ? ", " + (cached.username || cached.email) : ""}</h1>
      <p>Accédez à votre profil, vos commandes et paramètres via la navigation.</p>`;
  } else {
    // fallback simple si template minimal
    const wrapper = htmlToFragment(`<div class="max-w-4xl mx-auto p-6"><h1>Bienvenue${cached ? ", " + (cached.username || cached.email) : ""}</h1></div>`);
    frag.appendChild(wrapper);
  }

  // refresh serveur optionnel
  (async () => {
    const p = await UserData.getProfile();
    if (p && !p.error) sessionStorage.setItem("user", JSON.stringify(p));
  })();

  return frag;
}