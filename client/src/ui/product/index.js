import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

let ProductView = {
  html: function (data) {
    // Ne pas ajouter de <div> grid ici !
    let htmlString = '';
    for (let obj of data) {
      htmlString  += genericRenderer(template, obj);
    }
    return htmlString;
  },

  dom: function (data) {
    return htmlToFragment( ProductView.html(data) );
  }
};

export { ProductView };
