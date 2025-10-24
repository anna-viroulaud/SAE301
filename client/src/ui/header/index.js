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

    // Side menu (off-canvas) for mobile
    const burger = frag.querySelector("#burgerMenu");
    const sideMenu = frag.querySelector("#sideMenu");
    const overlay = frag.querySelector("#mobileNavOverlay");
    const sideClose = frag.querySelector("#sideClose");

    if (burger && sideMenu && overlay) {
      let lastFocus = null;

      const openSide = () => {
        lastFocus = document.activeElement;
        sideMenu.classList.remove("-translate-x-full");
        sideMenu.classList.add("translate-x-0");
        sideMenu.setAttribute("aria-hidden", "false");
        overlay.classList.remove("hidden");
        overlay.setAttribute("aria-hidden", "false");
        burger.setAttribute("aria-expanded", "true");
        document.documentElement.classList.add("nav-open");
        sideMenu.focus(); // move focus into menu
      };

      const closeSide = (returnFocus = true) => {
        sideMenu.classList.add("-translate-x-full");
        sideMenu.classList.remove("translate-x-0");
        sideMenu.setAttribute("aria-hidden", "true");
        overlay.classList.add("hidden");
        overlay.setAttribute("aria-hidden", "true");
        burger.setAttribute("aria-expanded", "false");
        document.documentElement.classList.remove("nav-open");
        if (returnFocus && lastFocus) lastFocus.focus();
      };

      const cartBtn = frag.querySelector("#btn-cart");
      if (cartBtn) {
        cartBtn.addEventListener("click", (e) => {
          e.preventDefault();
          // utilise le router global si présent, sinon fallback vers location
          if (window.router && typeof window.router.navigate === "function") {
            window.router.navigate("/cart");
          } else {
            window.location.href = "/cart";
          }
        });
      }

      burger.addEventListener("click", (e) => {
        e.stopPropagation();
        const open = burger.getAttribute("aria-expanded") === "true";
        if (open) closeSide();
        else openSide();
      });

      overlay.addEventListener("click", () => closeSide());
      if (sideClose) sideClose.addEventListener("click", () => closeSide());

      // close on link click
      sideMenu.querySelectorAll("a").forEach((a) => {
        a.addEventListener("click", () => closeSide());
      });

      // ESC to close
      const escHandler = (e) => {
        if (e.key === "Escape") {
          // only if menu is open
          if (sideMenu.getAttribute("aria-hidden") === "false") {
            closeSide();
          }
        }
      };
      document.addEventListener("keydown", escHandler);

      // close when clicking outside (in case overlay absent)
      document.addEventListener("click", (e) => {
        if (sideMenu.getAttribute("aria-hidden") === "false") {
          if (
            !sideMenu.contains(e.target) &&
            !burger.contains(e.target) &&
            !overlay.contains(e.target)
          ) {
            closeSide();
          }
        }
      });
    }

    return frag;
  },
};

export { HeaderView };
