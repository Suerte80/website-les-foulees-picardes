const menuHamburger = document.querySelector('#hamburger-menu');
const hamburger = document.querySelector('#hamburger');
const panel = document.querySelector('#panel');

const closeBtn = document.querySelector('.close-btn');

if(
    menuHamburger && hamburger
){
    const olMenu = menuHamburger.getElementsByTagName('ol').item(0);

    hamburger.onclick = function(){
        toggleMenu();
    }

    closeBtn.onclick = function(){
        toggleMenu();
    }

    document.onclick = function(e){
        if(e.target === panel){
            toggleMenu();
        }
    }
}

function toggleMenu(){
    const toggled = menuHamburger.classList.toggle('hidden');
    (!toggled)?menuHamburger.focus():menuHamburger.blur();

    document.body.classList.toggle('overflow-hidden');
}
