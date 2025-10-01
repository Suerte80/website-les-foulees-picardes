document.addEventListener('DOMContentLoaded', function() {
    const closeBtn = document.querySelectorAll('.flash img');

    closeBtn.forEach(flashe => {
        flashe.addEventListener('click', function() {
            const parentElement = flashe.parentElement;

            console.log(parentElement);

            parentElement.style.transition = 'opacity 0.5s ease';
            parentElement.style.opacity = '0';

            setTimeout(() => {
                parentElement.remove();
                console.log('coucou');
            }, 500)
        });
    });
});

export function createFlashMessage(message, level = 'info')
{
    // Récupération de la template du message flash.
    const flashTemplate = document.getElementById('flash-message');

    // Clone du contenu de la template
    const flashMessage = flashTemplate.content.firstElementChild.cloneNode(true);

    // Ajout des éléments dans du message flash.
    flashMessage.classList.add('bg-flash-' + level);
    flashMessage.querySelector('p').innerText = message;

    // Ajout des events sur les messages flash.
    const closeBtn = flashMessage.querySelector('img');

    closeBtn.addEventListener('click', function() {
        flashMessage.remove();
    })

    setTimeout(() => {
        if(flashMessage)
            flashMessage.remove();
    }, 500);

    // Ajout de du message flash dans le conteneur.
    const flashContainer = document.getElementById('flash-container');
    flashContainer.appendChild(flashMessage);
}
