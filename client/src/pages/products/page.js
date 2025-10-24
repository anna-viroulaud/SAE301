import { ProductData } from "../../data/product.js";
import { CategoryData } from "../../data/category.js";
import { ProductView } from "../../ui/product/index.js";
import { htmlToFragment } from "../../lib/utils.js";
import template from "./template.html?raw";

let M = {
  products: [],
  currentCategory: null,
  categoryId: null
};

let C = {};

C.handler_clickOnProduct = function (ev) {
  if (ev.target.dataset.buy !== undefined) {
    let id = ev.target.dataset.buy;
    alert(`Le produit d'identifiant ${id} ? Excellent choix !`);
  }
};

C.init = async function (params) {
  // Récupérer les produits selon la catégorie ou tous
  if (params?.id) {
    M.categoryId = params.id; // Peut être un ID numérique ou un slug
    M.currentCategory = await CategoryData.fetch(params.id);
    M.products = await CategoryData.fetchByCategory(params.id);
  } else {
    M.categoryId = null;
    M.currentCategory = null;
    M.products = await ProductData.fetchAll();
  }
  return V.init(M.products, M.currentCategory, M.categoryId);
};

let V = {};

V.init = function (data, currentCategory, categoryId) {
  let fragment = V.createPageFragment(data, currentCategory, categoryId);
  V.attachEvents(fragment);
  return fragment;
};

V.createPageFragment = function (data, currentCategory, categoryId) {
  // Créer le fragment depuis le template
  let pageFragment = htmlToFragment(template);

  // Mettre à jour le titre et le breadcrumb
  const titleElement = pageFragment.querySelector('h1');
  const breadcrumbElement = pageFragment.querySelector('[data-breadcrumb]');
  const countElement = pageFragment.querySelector('[data-product-count]');
  
  if (currentCategory) {
    // Mode catégorie
    titleElement.textContent = currentCategory.name || 'Catégorie';
    if (breadcrumbElement) {
      breadcrumbElement.innerHTML = `
        <a href="/products" data-link class="text-stone-600 hover:text-stone-900">Catalogue</a>
        <span class="mx-2 text-stone-400">/</span>
        <span class="text-stone-900 font-semibold">${currentCategory.name || 'Catégorie'}</span>
      `;
    }
  } else {
    // Mode tous les produits
    titleElement.textContent = 'Notre Catalogue';
    if (breadcrumbElement) {
      breadcrumbElement.innerHTML = `
        <span class="text-stone-900 font-semibold">Catalogue</span>
      `;
    }
  }

  // Afficher le nombre de produits
  if (countElement) {
    const count = Array.isArray(data) ? data.length : 0;
    countElement.textContent = `${count} produit${count > 1 ? 's' : ''}`;
  }

  // Générer les produits
  let productsDOM = ProductView.dom(data);

  // Remplacer le slot par les produits
  pageFragment.querySelector('slot[name="products"]').replaceWith(productsDOM);

  return pageFragment;
};

V.attachEvents = function (pageFragment) {
  let root = pageFragment.firstElementChild;
  root.addEventListener("click", C.handler_clickOnProduct);
  return pageFragment;
};

export function ProductsPage(params) {
  console.log("ProductsPage", params);
  return C.init(params); 
}
