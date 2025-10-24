import template from "./template.html?raw";
import { CartData } from "../../data/cart.js";
import { htmlToFragment } from "../../lib/utils.js";
import { ProductCartView } from "../../ui/productCart/index.js";
import { jsonpostRequest } from "../../lib/api-request.js";


let C = {};

C.init = function(){
    let cartItems = CartData.getCart();
    return V.init(cartItems);
}

C.handlerRemoveItem = function(event){
    const productId = Number(event.currentTarget.dataset.id);
    CartData.removeFromCart(productId);
    
    // Recharger la page du panier
    const newFragment = C.init();
    const appContainer = document.querySelector('#app');
    appContainer.innerHTML = '';
    appContainer.appendChild(newFragment);
}

C.handlerDecreaseQuantity = function(event){
    const productId = Number(event.currentTarget.dataset.id);
    const element = event.currentTarget.closest('#product-cart-item');
    const currentQty = Number(element.querySelector('#quantity').textContent);
    
    if (currentQty > 1) {
        CartData.updateQuantity(productId, currentQty - 1);
        element.querySelector('#quantity').textContent = currentQty - 1;
        V.updateProductPrice(element, currentQty - 1);
        V.updateTotalPrice();
    }
}

C.handlerIncreaseQuantity = function(event){
    const productId = Number(event.currentTarget.dataset.id);
    const element = event.currentTarget.closest('#product-cart-item');
    const currentQty = Number(element.querySelector('#quantity').textContent);
    
    CartData.updateQuantity(productId, currentQty + 1);
    element.querySelector('#quantity').textContent = currentQty + 1;
    V.updateProductPrice(element, currentQty + 1);
    V.updateTotalPrice();
}

// US007 : Handler pour valider la commande
C.handlerCheckout = async function(event){
    event.preventDefault();
    
    const btn = event.currentTarget;
    const cartItems = CartData.getCart();
    
    if (cartItems.length === 0) {
        alert("Votre panier est vide");
        return;
    }
    
    // Vérifier l'authentification
    const user = sessionStorage.getItem('user');
    if (!user) {
        alert("Vous devez être connecté pour passer commande");
        window.router?.navigate('/login');
        return;
    }
    
    // Désactiver le bouton pendant le traitement
    btn.disabled = true;
    btn.textContent = "Traitement en cours...";
    
    try {
        const userData = JSON.parse(user);
        const userId = userData.id;
        
        console.log("Synchronisation du panier avec le serveur...");
        await CartData.syncWithServer(userId);
        
        console.log("Validation de la commande...");
        // Appel API pour valider la commande
        const result = await jsonpostRequest('carts', {
            action: 'validate'
        });
        
        console.log("Résultat de la validation:", result);
        
        if (result && result.orderNumber) {
            // Vider le panier local
            CartData.clearCart();
            
            // Rediriger vers la page de confirmation
            window.router?.navigate(`/order-confirmation/${result.id}`);
        } else {
            alert(result?.error || "Erreur lors de la validation de la commande");
            btn.disabled = false;
            btn.textContent = "COMMANDER";
        }
    } catch (error) {
        console.error("Erreur lors de la validation:", error);
        console.error("Détails de l'erreur:", error.message);
        alert(`Une erreur s'est produite: ${error.message || error}`);
        btn.disabled = false;
        btn.textContent = "COMMANDER";
    }
}

let V = {};

// Fonction utilitaire pour convertir le prix string en number
V.parsePrice = function parsePrice(priceValue) {
    if (priceValue == null) return 0;
    if (typeof priceValue === 'number') return priceValue;
    const s = String(priceValue);
    // garder chiffres, séparateurs décimaux (.,) et signe -
    const cleaned = s.replace(/[^0-9\.,-]/g, '').replace(',', '.');
    const n = parseFloat(cleaned);
    return Number.isFinite(n) ? n : 0;
}


// Fonction utilitaire pour formater un nombre en prix
V.formatPrice = function(number){
    // Convertir 2400.00 en "2 400,00"
    return number.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

V.attachEvents = function(fragment){
    let products = fragment.querySelectorAll('#product-cart-item');
    products.forEach(element => {
        element.querySelector('#removeItemCart').addEventListener('click', C.handlerRemoveItem);
        element.querySelector('#decreaseQuantity').addEventListener('click', C.handlerDecreaseQuantity);
        element.querySelector('#increaseQuantity').addEventListener('click', C.handlerIncreaseQuantity);
    });
    
    // US007 : Ajouter l'événement pour le bouton de validation
    const checkoutBtn = fragment.querySelector('#checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', C.handlerCheckout);
    }
}

V.nbItems = function(fragment){
    let nbItems = CartData.getCart().length;
    let cartCounter = fragment.querySelector('#nbItemsCart');
    if(cartCounter){
        cartCounter.textContent = nbItems;
    }
}

V.updateProductPrice = function(element, quantity){
    const productId = Number(element.dataset.id);
    const cartItems = CartData.getCart();
    const product = cartItems.find(item => item.id === productId);
    
    if(product){
        const priceNumber = V.parsePrice(product.price);
        const totalPrice = priceNumber * quantity;
        element.querySelector('#productPrice').textContent = V.formatPrice(totalPrice) + '€';
    }
}

V.updateAllProductsPrices = function(fragment){
    const products = fragment.querySelectorAll('#product-cart-item');
    products.forEach(element => {
        const quantity = Number(element.querySelector('#quantity').textContent);
        V.updateProductPrice(element, quantity);
    });
}

V.updateTotalPrice = function(fragment){
    const cartItems = CartData.getCart();
    const total = cartItems.reduce((sum, item) => {
        const priceNumber = V.parsePrice(item.price);
        return sum + (priceNumber * item.quantity);
    }, 0);
    if (fragment) {
        let totalElement = fragment.querySelector('#totalPrice');
                console.log("Total price updated:", total);
        if(totalElement){
            totalElement.textContent = V.formatPrice(total) + '€';
        }
    }else{
        let totalElement = document.querySelector('#totalPrice');
        console.log("Total price updated:", total);
        if(totalElement){
            totalElement.textContent = V.formatPrice(total) + '€';
        }
    }

}

V.init = function(cartItems){
    let fragment = V.createPageFragment(cartItems);
    V.nbItems(fragment);
    V.updateAllProductsPrices(fragment); // Met à jour tous les prix des produits
    V.updateTotalPrice(fragment); // Met à jour le total
    V.attachEvents(fragment);
    return fragment;
}

V.createPageFragment = function(cartItems){
    let pageFragment = htmlToFragment(template);

    let productCartDOM = ProductCartView.dom(cartItems);

    pageFragment.querySelector('slot[name="listproducts"]').replaceWith(productCartDOM);
    return pageFragment;
}



export function CartPage(){
    const cartItems = CartData.getCart();
    console.log("Cart items:", cartItems);
    return C.init();
}
