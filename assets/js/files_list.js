import {createFlashMessage} from './flash.js';

console.log('Test2');

document.addEventListener('DOMContentLoaded', () => {

    const createDirBtn = document.querySelector('#create-dir-button');

    const formCreateDir = document.querySelector('#create-form-dir');

    if(createDirBtn){
        createDirBtn.addEventListener('click', (e) => {
            debugger;

            e.preventDefault();

            const createDirForm = document.querySelector('#create-form-dir');

            createDirBtn?.setAttribute('disabled', 'disabled');

            const input = prompt('Nom du répertoire :');

            createDirForm.querySelector('input[name="name"]').value = input;

            createDirForm.requestSubmit();

            createDirBtn?.removeAttribute('disabled');
        });
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

                const frame = document.querySelector('turbo-frame#files-frame');
                if(frame){
                    const url = new URL(frame.src, window.location.origin);
                    url.searchParams.set('_ts', Date.now().toString());
                    frame.src = url.pathname + url.search;
                }

            } catch (err){
                createFlashMessage('Impossible de créer le dossier.', 'error');
                console.error(err);
            } finally {
                btn?.removeAttribute('disabled');
            }
        })
    }
});
