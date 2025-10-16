import { ProductData } from "../../data/product.js";
import { CardView } from "../../ui/card/index.js";

import { htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";



let M = {
    products: []
};



let C = {};

C.handler_clickOnProduct = function(ev){
    if (ev.target.dataset.buy!==undefined){
        let id = ev.target.dataset.buy;
        alert(`Le produit d'identifiant ${id} ? Excellent choix !`);
    }
}

C.init = async function(){
    M.products = await ProductData.fetchAll(); 
    return V.init( M.products );
}


let V = {};

V.init = function(data){
    let fragment = V.createPageFragment(data);
    V.attachEvents(fragment);
    return fragment;
}

V.createPageFragment = function( data ){
   // Créer le fragment depuis le template
   let pageFragment = htmlToFragment(template);
   
   // Générer les produits
   let productsDOM = CardView.dom(data);
   
   // Remplacer le slot par les produits
   pageFragment.querySelector('slot[name="card"]').replaceWith(productsDOM);

   
   return pageFragment;
}

V.attachEvents = function(pageFragment) {
    let root = pageFragment.firstElementChild;
    root.addEventListener("click", C.handler_clickOnProduct);
    return pageFragment;
}

export function ProductsPage(params) {
    console.log("ProductsPage", params);
    return C.init();
}
