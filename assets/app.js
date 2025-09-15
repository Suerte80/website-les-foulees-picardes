import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', function(){
    const btnEditMember = document.getElementById('btn-edit');
    if(btnEditMember){
        const sectionReadable = document.getElementById('section-readable');
        const sectionEditable = document.getElementById('section-editable');
        if(sectionReadable && sectionEditable){
            btnEditMember.addEventListener('click', function(){
                sectionReadable.style.display = (sectionReadable.style.display === 'none' ? 'block' : 'none');
                sectionEditable.style.display = (sectionEditable.style.display === 'none' ? 'block' : 'none');
            });
        }
    }
});
