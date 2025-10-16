import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

let CardView = {
  html: function (data) {
    // Création d'une grille responsive
    let htmlString =
      '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">';
    for (let obj of data) {
      // On remplace les placeholders {{…}} par les valeurs du produit
      // Si price ou image manquent, on met des valeurs par défaut
      const productData = {
        ...obj,
        price:
          obj.price !== null && obj.price !== undefined && !isNaN(obj.price)
            ? Number(obj.price).toFixed(2)
            : "N/A",
        image: obj.image || "placeholder.webp",
      };

      htmlString += genericRenderer(template, productData);
    }
    htmlString += "</div>";
    return htmlString;
  },

  dom: function (data) {
    return htmlToFragment(CardView.html(data));
  },
};

export { CardView };
