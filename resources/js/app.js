import '../css/app.css';
import './bootstrap';

import { createApp } from 'vue';
import { initAuth } from './stores/auth';

// Initialize auth state from Blade-embedded JSON before Vue mounts
initAuth();

import PremiumLayout from './Components/PremiumLayout.vue';
import Header from './Components/Header.vue';
import Footer from './Components/Footer.vue';
import LiaAssistant from './Components/LiaAssistant.vue';
import PageHero from './Components/PageHero.vue';
import AdminLayout from './Components/AdminLayout.vue';

// Page Components
import Home from './Pages/Home.vue';
import Shop from './Pages/Shop.vue';
import ProductDetail from './Pages/ProductDetail.vue';
import Services from './Pages/Services.vue';
import Contact from './Pages/Contact.vue';
import Checkout from './Pages/Checkout.vue';
import Cart from './Pages/Cart.vue';
import Reservation from './Pages/Reservation.vue';
import Packs from './Pages/Packs.vue';
import PackDetail from './Pages/PackDetail.vue';
import Consultation from './Pages/Consultation.vue';
import AdminDashboard from './Pages/Admin/Dashboard.vue';
import OrderIndex from './Pages/Admin/Orders/Index.vue';
import OrderDetail from './Pages/Admin/Orders/Show.vue';
import AdminUsers from './Pages/Admin/Users.vue';
import AdminSettings from './Pages/Admin/Settings.vue';

const app = createApp({});

app.component('PremiumLayout', PremiumLayout);
app.component('Header', Header);
app.component('Footer', Footer);
app.component('LiaAssistant', LiaAssistant);
app.component('PageHero', PageHero);
app.component('AdminLayout', AdminLayout);

app.component('Home', Home);
app.component('Shop', Shop);
app.component('ProductDetail', ProductDetail);
app.component('Services', Services);
app.component('Contact', Contact);
app.component('Checkout', Checkout);
app.component('Cart', Cart);
app.component('Reservation', Reservation);
app.component('Packs', Packs);
app.component('PackDetail', PackDetail);
app.component('Consultation', Consultation);
app.component('AdminDashboard', AdminDashboard);
app.component('OrderIndex', OrderIndex);
app.component('OrderDetail', OrderDetail);
app.component('AdminUsers', AdminUsers);
app.component('AdminSettings', AdminSettings);

// Only mount Vue on pages that have the #app container (auth pages don't)
if (document.getElementById('app')) {
    app.mount('#app');
}
