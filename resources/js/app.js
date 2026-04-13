import "../css/app.css";
import Alpine from 'alpinejs';
import './forms/donate-form.js';

window.Alpine = Alpine;
Alpine.start();

if (document.getElementById('payment-form')) {
	import('./forms/cart-form.js');
}
