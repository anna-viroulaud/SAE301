import { htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

// HeaderView est un composant statique
// on ne fait que charger le template HTML
// en donnant la possibilité de l'avoir sous forme html ou bien de dom
let HeaderView = {
  html: function () {
    return template;
  },

  dom: function () {
    let frag = htmlToFragment(template);

    // attache le listener sur le bouton profil (compatible avec ton routeur)
    const btn = frag.querySelector("#btn-profile");
    if (btn) {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        if (window.router && typeof window.router.navigate === "function") {
          window.router.navigate("/profile");
        } else {
          // fallback si window.router non défini
          window.location.href = "/profile";
        }
      });
    }

    return frag;
  }
};


export { HeaderView };
