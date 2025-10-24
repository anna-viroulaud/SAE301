import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

let ProductCartView = {
  html: function (data) {
    let htmlString = '<section id="product-cart" class="lg:col-span-2 space-y-6">';
    for (let obj of data) {
        htmlString  += genericRenderer(template, obj);

    }
    return htmlString + '</section>';
  },

  dom: function (data) {
    return htmlToFragment( ProductCartView.html(data) );
  }

};

export { ProductCartView };