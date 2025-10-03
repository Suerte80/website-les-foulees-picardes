import { Controller } from "@hotwired/stimulus";

/*
 * Stimulus controller pour gérer l'ouverture/fermeture du menu mobile.
 * Usage : data-controller="hamburger" sur l'élément <nav> ou un parent commun.
 */
export default class extends Controller {
    static targets = ["openButton", "closeButton", "backdrop", "drawer"];

    connect() {
        this.overlayVisible = false;
    }

    toggle(event) {
        event.preventDefault();
        this.overlayVisible ? this.close() : this.open();
    }

    open(event) {
        if (event) event.preventDefault();
        this.drawerTarget.classList.remove("hidden");
        this.drawerTarget.setAttribute("aria-hidden", "false");
        this.openButtonTarget.setAttribute("aria-expanded", "true");
        this.overlayVisible = true;
        document.body.classList.add("overflow-hidden");
    }

    close(event) {
        if (event) event.preventDefault();
        this.drawerTarget.classList.add("hidden");
        this.drawerTarget.setAttribute("aria-hidden", "true");
        this.openButtonTarget.setAttribute("aria-expanded", "false");
        this.overlayVisible = false;
        document.body.classList.remove("overflow-hidden");
    }

    // Ferme le menu quand on clique sur un lien interne
    closeFromLink() {
        this.close();
    }
}

