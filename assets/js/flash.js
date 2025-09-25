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
