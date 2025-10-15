import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

let CardView = {
  html: function (data) {
    let htmlString = '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">';
    for (let obj of data) {
      htmlString  += genericRenderer(template, obj);
    }
    return htmlString + '</div>';
  },

  dom: function (data) {
    return htmlToFragment( ProductView.html(data) );
  }

};

export { CardView };
