import { genericRenderer, htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";


let UserInfoView = {
  html: function (data) {
    return genericRenderer(template, data);
  },

  dom: function (data) {
    return htmlToFragment(UserInfoView.html(data));
  }
};



export { UserInfoView };