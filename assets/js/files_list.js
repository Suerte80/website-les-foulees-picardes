import {createFlashMessage} from './flash.js';

const createDirBtn = document.querySelector('#create-dir');

document.addEventListener('DOMContentLoaded', () => {
    if(createDirBtn){
        createDirBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const createDirForm = document.querySelector('#create-form-dir');

            createDirBtn.disabled = true;

            const input = prompt('Nom du répertoire :');

            createDirForm.querySelector('input[name="name"]').value = input;

            createDirForm.submit();

            createDirBtn.disabled = false;
        });
    }
});
