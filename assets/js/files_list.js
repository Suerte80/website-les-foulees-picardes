import {createFlashMessage} from './flash.js';

console.log('Test2');

document.addEventListener('DOMContentLoaded', () => {

    const formCreateDir = document.querySelector('#create-form-dir');

    const turboFrame = document.querySelector('#files-frame');

    if(turboFrame){

        turboFrame.addEventListener('click', (e) => {
            if(e.target.matches('#create-dir-button')){

                e.preventDefault();

                const createDirBtn = document.getElementById('create-dir-button');

                createDirBtn?.setAttribute('disabled', 'disabled');

                const input = prompt('Nom du répertoire :');

                formCreateDir.querySelector('input[name="name"]').value = input;

                formCreateDir.requestSubmit();

                createDirBtn?.removeAttribute('disabled');
            }
        })
    }

    if(formCreateDir){

        // Partie pour la reception de l'event de submit du formulaire de création de dossier
        formCreateDir.addEventListener('submit', async (e) => {

            if(!formCreateDir.matches('form[data-ajax]'))
                return;

            e.preventDefault();

            const btn = formCreateDir.querySelector('[type="submit"]');
            btn?.setAttribute('disabled', 'disabled');

            try{
                const body = new FormData(formCreateDir);

                const res = await fetch(formCreateDir.action, {
                    method: (formCreateDir.method || 'POST').toUpperCase(),
                    body,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                });

                // si la session a expiré, fetch suit le 302 -> page HTML
                if (res.redirected) throw new Error('Session expirée, reconnecte-toi.');

                const ct = res.headers.get('content-type');
                const raw = await res.text();

                const data = ct.includes('application/json') ? JSON.parse(raw) : { ok: false, error: raw };

                if(!res.ok || data.ok === false){
                    throw new Error(data.error || `Erreur HTML ${res.status}`);
                }

                // Succés
                createFlashMessage('Dossier créer', 'info');
                formCreateDir.reset();

                reloadFilesFrame();

            } catch (err){
                createFlashMessage('Impossible de créer le dossier.', 'error');
                console.error(err);
            } finally {
                btn?.removeAttribute('disabled');
            }
        })
    }
});

function reloadFilesFrame() {
    const frame = document.getElementById('files-frame');
    if (!frame) return;

    // Toujours repartir de l’URL canonique prévue pour l’update
    const base = frame.dataset.reloadUrl || frame.getAttribute('src');
    if (!base) return; // rien à recharger proprement

    const url = new URL(base, window.location.origin);
    url.searchParams.set('_ts', Date.now().toString()); // anti-cache
    frame.src = url.pathname + url.search;              // ⇦ déclenche le reload
}
