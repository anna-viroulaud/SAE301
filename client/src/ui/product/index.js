import { genericRenderer, htmlToFragment, slugify } from "../../lib/utils.js";
import template from "./template.html?raw";

let ProductView = {
  html: function (data) {
    // Ne pas ajouter de <div> grid ici !
    let htmlString = '';
    for (let obj of data) {
      // Ajouter un slug pour l'URL
      const dataWithSlug = { ...obj, slug: slugify(obj.name) };
      htmlString  += genericRenderer(template, dataWithSlug);
    }
    return htmlString;
  },

  dom: function (data) {
    const fragment = htmlToFragment( ProductView.html(data) );
    
    // Attacher les événements aux boutons "Ajouter au panier"
    const addToCartButtons = fragment.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation(); // Empêche la navigation vers la page détail
        
        const productId = btn.dataset.productId;
        const productName = btn.dataset.productName;
        const productPrice = btn.dataset.productPrice;
        const productImage = btn.dataset.productImage;
        
        // Importer CartData dynamiquement
        const { CartData } = await import('../../data/cart.js');
        
        // Ajouter au panier
        await CartData.addToCart({
          id: productId,
          name: productName,
          price: productPrice,
          image: productImage,
          quantity: 1
        });
        
        // Feedback visuel
        const originalText = btn.textContent;
        btn.textContent = '✓ Ajouté au panier';
        btn.classList.remove('bg-stone-800', 'hover:bg-stone-700');
        btn.classList.add('bg-green-600');
        btn.disabled = true;
        
        setTimeout(() => {
          btn.textContent = originalText;
          btn.classList.remove('bg-green-600');
          btn.classList.add('bg-stone-800', 'hover:bg-stone-700');
          btn.disabled = false;
        }, 2000);
      });
    });
    
    return fragment;
  }
};

export { ProductView };
