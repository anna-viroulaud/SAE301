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

/**
 * Synchroniser le panier local avec le serveur
 * Convertit les items du localStorage au format API et les envoie
 */
CartData.syncWithServer = async function(userId) {
    const { jsonpostRequest } = await import('../lib/api-request.js');
    
    const cartArray = loadRaw();
    
    if (!userId || cartArray.length === 0) {
        console.log("Panier vide ou utilisateur non connecté, rien à synchroniser");
        return { success: true, message: "Rien à synchroniser" };
    }
    
    // Transformer les items du format localStorage au format API
    const items = cartArray.map(item => {
        const price = parseFloat(item.price) || 0;
        console.log(`Item ${item.name}: prix = ${item.price} → ${price}`);
        return {
            variantId: item.id,          // ID du produit/variante
            productId: item.id,          // Alias pour compatibilité
            quantity: item.quantity,     // Quantité
            unitPrice: price             // Prix unitaire converti en nombre
        };
    });
    
    console.log("Synchronisation du panier:", { userId, items });
    
    try {
        const result = await jsonpostRequest('carts', {
            clientId: userId,
            items: items
        });
        
        if (result && !result.error) {
            console.log("Panier synchronisé avec succès");
            return { success: true, data: result };
        } else {
            console.error("Erreur lors de la synchronisation:", result?.error);
            throw new Error(result?.error || "Erreur lors de la synchronisation");
        }
    } catch (error) {
        console.error("Échec de la synchronisation:", error);
        throw error;
    }
}

export {CartData};