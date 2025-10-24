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
    if (gallery) {
      let imagesToDisplay = [];
      
      // Si on a un tableau d'images avec du contenu
      if (Array.isArray(data.images) && data.images.length > 0) {
        imagesToDisplay = data.images;
      } 
      // Sinon, utiliser au moins l'image principale si elle existe
      else if (data.image) {
        imagesToDisplay = [data.image];
      }
      
      // Générer le HTML des images
      if (imagesToDisplay.length > 0) {
        // Adapter la grille selon le nombre d'images
        if (imagesToDisplay.length === 1) {
          // Une seule image : pleine largeur
          gallery.classList.remove('grid-cols-2');
          gallery.classList.add('grid-cols-1');
        } else {
          // Plusieurs images : grille 2 colonnes
          gallery.classList.add('grid-cols-2');
        }
        
        gallery.innerHTML = imagesToDisplay.map(url =>
          `<img src="/${url}" alt="${data.name}" class="rounded-lg w-full h-auto object-cover" loading="lazy" />`
        ).join("");
      } else {
        // Pas d'image disponible : afficher un placeholder
        gallery.classList.remove('grid-cols-2');
        gallery.classList.add('grid-cols-1');
        gallery.innerHTML = `
          <div class="flex items-center justify-center bg-gray-100 rounded-lg h-64">
            <p class="text-gray-400">Aucune image disponible</p>
          </div>
        `;
      }
    }

    return fragment;
  }
};

export { DetailView };