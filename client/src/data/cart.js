let CartData = {};
const STORAGE_KEY = 'cart'; // conserve votre clé existante

function loadRaw() {
  const raw = localStorage.getItem(STORAGE_KEY);
  return raw ? JSON.parse(raw) : [];
}

function saveRaw(arr) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
  // notifie l'app (header, pages...)
  try {
    window.dispatchEvent(new CustomEvent('cart:updated', { detail: arr }));
    // pour multi-onglets
    localStorage.setItem(STORAGE_KEY + '_lastUpdate', Date.now());
  } catch (e) { /* ignore */ }
}

CartData.addToCart = async function(product){
    let cartArray = loadRaw();

    const pid = Number(product.id);
    if (!pid) return cartArray;

    const existingProductIndex = cartArray.findIndex(item => Number(item.id) === pid);
    
    if (existingProductIndex !== -1) {
        cartArray[existingProductIndex].quantity = (Number(cartArray[existingProductIndex].quantity)||0) + (Number(product.quantity)||1);
    } else {
        cartArray.push({
            id: pid,
            quantity: Number(product.quantity) || 1,
            price: product.price ?? null,
            name: product.name ?? null,
            image: product.image ?? null
        });
    }
    
    saveRaw(cartArray);
    console.log('Panier actuel:', cartArray);
    
    return cartArray;
}

CartData.getCart = function() {
    return loadRaw();
}

CartData.getTotalItems = function() {
    return loadRaw().reduce((s, it) => s + (Number(it.quantity) || 0), 0);
}

CartData.clearCart = function() {
    saveRaw([]);
    return [];
}

CartData.removeFromCart = function(productId) {
    const pid = Number(productId);
    let cartArray = loadRaw();
    cartArray = cartArray.filter(item => Number(item.id) !== pid);
    saveRaw(cartArray);
    return cartArray;
}

CartData.updateQuantity = function(productId, quantity) {
    const pid = Number(productId);
    let cartArray = loadRaw();
    const productIndex = cartArray.findIndex(item => Number(item.id) === pid);
    
    if (productIndex !== -1) {
        const qty = Math.max(0, Number(quantity) || 0);
        if (qty <= 0) {
            cartArray.splice(productIndex, 1);
        } else {
            cartArray[productIndex].quantity = qty;
        }
        saveRaw(cartArray);
    }
    
    return cartArray;
}

export {CartData};