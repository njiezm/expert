/* ==========================================================================
   MÉRIDIEN — comportements d'interface
   Pas de framework : quelques comportements attachés par attribut de données.
   ========================================================================== */

import { initVoix } from './voix.js';
import { initSchemas } from './schema.js';

/* -------- Bascule de thème --------------------------------------------
   Le clair est la valeur par défaut ; le sombre s'active par la classe
   `theme-sombre` sur <html> et se mémorise en local.                      */
function libelleTheme() {
    const sombre = document.documentElement.classList.contains('theme-sombre');
    document.querySelectorAll('[data-libelle-theme]').forEach((el) => {
        el.textContent = sombre ? 'Thème clair' : 'Thème sombre';
    });
}

document.addEventListener('click', (e) => {
    const bouton = e.target.closest('[data-bascule-theme]');
    if (!bouton) return;

    const sombre = document.documentElement.classList.toggle('theme-sombre');
    localStorage.setItem('meridien-theme', sombre ? 'sombre' : 'clair');
    libelleTheme();
});

/* -------- Dévoilement progressif (indice → méthode → solution) ---------
   L'exercice ne livre pas sa solution d'un bloc : chaque palier est un choix
   conscient, et il est enregistré pour pondérer le score de maîtrise.        */
document.addEventListener('click', (e) => {
    const bouton = e.target.closest('[data-devoiler]');
    if (!bouton) return;

    const cible = document.getElementById(bouton.dataset.devoiler);
    if (!cible) return;

    cible.hidden = false;
    bouton.hidden = true;

    const niveau = bouton.dataset.niveau;
    const champ = document.querySelector('[data-niveau-devoile]');
    if (champ && niveau && Number(niveau) > Number(champ.value)) {
        champ.value = niveau;
    }
});

/* -------- Chronomètre d'examen -----------------------------------------
   Le temps restant est calculé depuis l'échéance serveur, pas depuis un
   compteur local : recharger la page ne rend pas de temps.                 */
function demarrerChrono(el) {
    const finMs = Number(el.dataset.chronoFin) * 1000;
    const formulaire = el.dataset.chronoFormulaire
        ? document.getElementById(el.dataset.chronoFormulaire)
        : null;

    const tick = () => {
        const reste = Math.max(0, Math.round((finMs - Date.now()) / 1000));
        const h = String(Math.floor(reste / 3600)).padStart(2, '0');
        const m = String(Math.floor((reste % 3600) / 60)).padStart(2, '0');
        const s = String(reste % 60).padStart(2, '0');
        el.textContent = `${h}:${m}:${s}`;

        // Sous 5 minutes : alerte visuelle, comme la fin d'épreuve annoncée.
        el.classList.toggle('alerte-temps', reste > 0 && reste <= 300);
        if (reste <= 300) el.style.color = 'var(--color-lacune-fort)';

        if (reste === 0) {
            clearInterval(minuteur);
            if (formulaire) formulaire.submit();
        }
    };

    tick();
    const minuteur = setInterval(tick, 1000);
}

/* -------- Sauvegarde locale des réponses d'examen -----------------------
   Filet de sécurité : une coupure à 3 h du matin ne doit pas coûter trois
   heures de composition.                                                   */
function activerBrouillon(zone) {
    const cle = `meridien-brouillon-${zone.dataset.brouillon}`;
    const sauvegarde = localStorage.getItem(cle);

    if (sauvegarde && !zone.value.trim()) {
        zone.value = sauvegarde;
    }

    zone.addEventListener('input', () => localStorage.setItem(cle, zone.value));
    zone.form?.addEventListener('submit', () => localStorage.removeItem(cle));
}

/* -------- Auto-agrandissement des zones de rédaction -------------------- */
function autoHauteur(zone) {
    const ajuster = () => {
        zone.style.height = 'auto';
        zone.style.height = `${zone.scrollHeight + 2}px`;
    };
    zone.addEventListener('input', ajuster);
    ajuster();
}

/* -------- Carte de drill : raccourcis clavier --------------------------- */
document.addEventListener('keydown', (e) => {
    if (!document.querySelector('[data-carte-drill]') || e.target !== document.body) return;

    if (e.code === 'Space') {
        e.preventDefault();
        document.querySelector('[data-retourner]')?.click();
        return;
    }

    // 1 à 4 : notation de la carte, comme dans un logiciel de répétition espacée.
    if (/^Digit[1-4]$/.test(e.code)) {
        e.preventDefault();
        document.querySelector(`[data-note="${e.code.slice(-1)}"]`)?.click();
    }
});

/* -------- Initialisation ------------------------------------------------ */
document.addEventListener('DOMContentLoaded', () => {
    libelleTheme();
    document.querySelectorAll('[data-chrono-fin]').forEach(demarrerChrono);
    document.querySelectorAll('[data-brouillon]').forEach(activerBrouillon);
    document.querySelectorAll('[data-auto-hauteur]').forEach(autoHauteur);
    initVoix();
    initSchemas();
});