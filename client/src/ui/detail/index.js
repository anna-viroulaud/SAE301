import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

let DetailView = {
  html: function (data) {
    return genericRenderer(template, data);
  },

  dom: function (data) {
    // Crée le fragment DOM
    let fragment = htmlToFragment(DetailView.html(data));

    // Injecte les images dans la galerie
    let gallery = fragment.querySelector("[data-gallery]");
    if (gallery && Array.isArray(data.images)) {
      gallery.innerHTML = data.images.map(url =>
        `<img src="/${url}" alt="${data.name}" class="rounded-lg w-full h-auto object-cover" loading="lazy" />`
      ).join("");
    }

    return fragment;
  }
};

export { DetailView };