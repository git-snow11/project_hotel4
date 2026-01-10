import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
function dynamicDropdown(listIndex) {
    document.getElementById("rooms").length = 0;

    for (let i = 1; i < Number(listIndex) + 1; i++) {
        document.getElementById("rooms").options[i - 1] = new Option(i.toString(), i);
    }
}
