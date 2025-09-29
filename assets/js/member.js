document.addEventListener('DOMContentLoaded', function(){
    const sectionReadable = document.querySelector('#section-readable');
    const sectionEditable = document.querySelector('#section-editable');
    const btnEdit = document.querySelector('#btn-edit');
    const btnDeleteAvatar = document.querySelector('#btn-delete-avatar');

    if( sectionReadable && sectionEditable && btnEdit && btnDeleteAvatar){
        // Initialisation de l'interface
        btnEdit.checked = false;
        toggleBlock(sectionReadable, sectionEditable, btnDeleteAvatar, btnEdit.checked);

        // Ajout de l'événement du click sur le toggle
        btnEdit.addEventListener('change', function(){
           toggleBlock(sectionReadable, sectionEditable, btnDeleteAvatar, btnEdit.checked);
        });
    }
});

function toggleBlock(sectionReadable, sectionEditable, btnDeleteAvatar, state) {
    if(sectionReadable && sectionEditable){
        if(!state) {
            sectionReadable.classList.remove('hidden');
            sectionReadable.classList.add('block');
            sectionEditable.classList.add('hidden');
            sectionEditable.classList.remove('block');
            btnDeleteAvatar.classList.add('hidden');
        } else{
            sectionReadable.classList.remove('block');
            sectionReadable.classList.add('hidden');
            sectionEditable.classList.add('block');
            sectionEditable.classList.remove('hidden');
            btnDeleteAvatar.classList.remove('hidden');
        }
    }
}
