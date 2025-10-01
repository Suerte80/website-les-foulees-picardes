const rejectButtons = document.querySelectorAll('.btn-cancel-request');

const validateButtons = document.querySelectorAll('.btn-validate-request');

if(validateButtons.length > 0){
    sendFormRequest(validateButtons)
}

if(rejectButtons.length > 0) {
    sendFormRequest(rejectButtons, true);
}

function sendFormRequest(buttons, isReject = false)
{
    buttons.forEach((button) => {

        button.addEventListener('click', async (e) => {
            e.preventDefault();

            // Récupération des éléments dans le dataset du bouton.
            const url = button.dataset.url;
            const id = button.dataset.id;
            const token = button.dataset.token;
            const rowSel = button.dataset.rowsel;

            // Création de la FormData
            const body = new FormData();
            body.append('_token', token);

            // Demande à l'utilsateur s'il veut vraiment rejeter la demande
            if(isReject){
                const comfirmationRejection = confirm('Voulez-vous rejeter la demande ?');
                if(!comfirmationRejection){
                    return;
                }
            }

            // désactivation du bouton
            button.disabled = true;

            try{

                // Envoi de la requête au serveur.
                const res = await fetch(url, {
                    method: 'POST',
                    body: body,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                // Vérification du bon envoi de la requête.
                if(!res.ok) throw new Error(`HTTP ${res.status}`);

                // Vérification du résultat de la requête.
                const data = await res.json();
                if(!data.ok) throw new Error(data.error || 'Erreur serveur');

                // Suppression de la ligne si elle existe.
                console.log('Rowsel dtaset: ', button.dataset.rowsel);
                if(rowSel){
                    const row = document.querySelector(rowSel);

                    // Vérification s'il y a encore des rows
                    const tbody = row.closest('tbody');

                    const tbodyChildrenLength = tbody?.children.length;
                    console.log(tbodyChildrenLength);
                    if(tbodyChildrenLength <= 1){
                        // Recherche + création de la template
                        const emptyTableTemplateClone = document.querySelector('#empty-table-template')?.cloneNode(true);

                        console.log(emptyTableTemplateClone);

                        // Ajout de la ligne vide dans le tableau.
                        console.log(emptyTableTemplateClone.content.cloneNode(true));
                        tbody.appendChild(emptyTableTemplateClone.content.cloneNode(true));
                    }

                    // Suppression de la ligne (après avoir mis la template s'il y a besoin.)
                    row?.remove();
                }

            } catch (err){
                alert('Echec de l\'opération : ' + err.message);
            } finally {
                button.disabled = false;
            }
        });
    });
}
