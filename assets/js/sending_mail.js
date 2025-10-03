document.addEventListener('DOMContentLoaded', function() {

    const formMailSend = document.getElementById('form-mail-send');

    if(formMailSend) {
        formMailSend.addEventListener('submit', (e) => {
            const res = confirm('Êtes vous sur de vouloir les mails a TOUT les adherents?');

            if(!res){
                e.preventDefault();
            }
        });

        document.addEventListener('turbo:submit-start', (e) => {
            if(e.target === formMailSend) {
                const loadingFrame = document.getElementById('loading-frame');
                if(loadingFrame) {
                    loadingFrame.classList.remove('hidden');
                    loadingFrame.classList.add('block');
                }
            }
        });

        document.addEventListener('turbo:submit-end', (e) => {
            if(e.target === formMailSend) {
                const loadingFrame = document.getElementById('loading-frame');
                if(loadingFrame) {
                    loadingFrame.classList.remove('block');
                    loadingFrame.classList.add('hidden');
                }
            }
        })
    }

})
