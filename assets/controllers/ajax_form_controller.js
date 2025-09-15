import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["form"];
    static values = {
        successRedirect: String // ex: "/member"
    };

    async submit(event) {
        event.preventDefault();
        const form = this.formTarget || event.target;

        // Désactive le bouton pour éviter les doubles submit
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: form.method || "POST",
                body: new FormData(form),
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            // 201 Created -> succès
            if (res.status === 201) {
                if (this.hasSuccessRedirectValue) {
                    window.location.href = this.successRedirectValue;
                } else {
                    // Par défaut: recharger
                    window.location.reload();
                }
                return;
            }

            // 422 Unprocessable Entity -> on reçoit le fragment HTML du form avec erreurs
            if (res.status === 422) {
                const html = await res.text();
                // Remplace le conteneur (this.element) par le form ré-rendu (avec erreurs)
                this.element.innerHTML = html;
                return;
            }

            // Autres codes => log / feedback utilisateur
            console.error("Réponse inattendue:", res.status, await res.text());
            alert("Une erreur est survenue. Réessayez plus tard.");

        } catch (e) {
            console.error("Erreur réseau/fetch:", e);
            alert("Impossible de contacter le serveur.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }
}
