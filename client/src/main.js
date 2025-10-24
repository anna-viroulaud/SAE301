
import { Router } from "./lib/router.js";
import { AboutPage } from "./pages/about/page.js";
import { HomePage } from "./pages/home/page.js";
import { ProductsPage } from "./pages/products/page.js";
import { ProductDetailPage } from "./pages/productDetail/page.js";

import { LoginPage } from "./pages/login/page.js";
import { SignupPage } from "./pages/signup/page.js";
import { ProfilePage } from "./pages/profil/page.js";
import { OrdersPage } from "./pages/orders/page.js";

import { AccountPage } from "./pages/account/page.js";
import { CartPage } from "./pages/cart/page.js";
import { OrderConfirmationPage } from "./pages/orderConfirmation/page.js";

import { RootLayout } from "./layouts/root/layout.js";
import { The404Page } from "./pages/404/page.js";

// Exemple d'utilisation avec authentification

// ...
const router = new Router('app');
window.router = router;

try {
  const user = sessionStorage.getItem('user');
  window.router.setAuth(!!user);
} catch (e) {
  window.router.setAuth(false);
}

router.addLayout("/", RootLayout);

router.addRoute("/", HomePage);
router.addRoute("/about", AboutPage);

router.addRoute("/products", ProductsPage);
router.addRoute("/products/:id/:slug", ProductDetailPage);

router.addRoute("/products", ProductsPage);

router.addRoute("/categories/:id", ProductsPage);


router.addRoute("/login", LoginPage, { useLayout: false });
router.addRoute("/signup", SignupPage, { useLayout: false });

router.addRoute("/profile", ProfilePage, { requireAuth: true });
router.addRoute("/orders", OrdersPage, { requireAuth: true });
// router.addRoute("/account", AccountPage, { requireAuth: true });
router.addRoute("/cart", CartPage, { requireAuth: true });
router.addRoute("/order-confirmation/:id", OrderConfirmationPage, { requireAuth: true });

router.addRoute("*", The404Page);

// Démarrer le routeur
router.start();

