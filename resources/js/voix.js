/* ==========================================================================
   MÉRIDIEN — lecture vocale
   --------------------------------------------------------------------------
   Écouter une fiche pendant qu'on trace un schéma, ou réviser en marchant,
   change la façon dont on encaisse un chapitre. S'appuie sur l'API Web Speech,
   présente nativement dans Chrome et Edge — aucune dépendance, aucun réseau.
   ========================================================================== */

const synth = window.speechSynthesis;

let voixFr = null;
let blocCourant = null;

/** Choisit la meilleure voix française disponible. */
function choisirVoix() {
    if (!synth) return null;

    const voix = synth.getVoices();
    if (!voix.length) return null;

    return voix.find((v) => v.lang === 'fr-FR' && /Denise|Henri|Natural|Google/i.test(v.name))
        || voix.find((v) => v.lang === 'fr-FR')
        || voix.find((v) => v.lang.startsWith('fr'))
        || null;
}

/**
 * Prépare le texte pour l'oreille.
 *
 * Le contenu des fiches est du Markdown rendu : tableaux, code, symboles
 * logiques. Lu tel quel, cela donne une bouillie. On nettoie et on traduit
 * les symboles que le module emploie constamment.
 */
function texteLisible(element) {
    const copie = element.cloneNode(true);

    // Les blocs de code et les tableaux se lisent mal : on les annonce sans les épeler.
    copie.querySelectorAll('pre').forEach((el) => {
        el.replaceWith(document.createTextNode('. Bloc de code à lire à l’écran. '));
    });
    copie.querySelectorAll('table').forEach((el) => {
        el.replaceWith(document.createTextNode('. Tableau à consulter à l’écran. '));
    });

    let texte = copie.textContent || '';

    const symboles = [
        [/⇒|→|->/g, ' implique '],
        [/⟺|<->|<=>/g, ' équivaut à '],
        [/∀/g, ' pour tout '],
        [/∃/g, ' il existe '],
        [/¬|\\\+/g, ' non '],
        [/∧|\/\\/g, ' et '],
        [/∨|\\\//g, ' ou '],
        [/∈/g, ' appartient à '],
        [/⊆/g, ' inclus dans '],
        [/∅/g, ' ensemble vide '],
        [/≤|<=/g, ' inférieur ou égal à '],
        [/≥|>=/g, ' supérieur ou égal à '],
        [/≠/g, ' différent de '],
        [/O\(n²\)/g, ' O de n carré '],
        [/O\(n\)/g, ' O de n '],
        [/O\(log n\)/g, ' O de log n '],
        [/O\(n log n\)/g, ' O de n log n '],
        [/·/g, ' fois '],
        [/«\s*/g, ''],
        [/\s*»/g, ''],
        [/\s+/g, ' '],
    ];

    symboles.forEach(([motif, remplacement]) => {
        texte = texte.replace(motif, remplacement);
    });

    return texte.trim();
}

function arreter() {
    synth?.cancel();

    if (blocCourant) {
        blocCourant.classList.remove('en-lecture');
        blocCourant = null;
    }

    document.querySelectorAll('[data-lire]').forEach(majBouton);
}

function majBouton(bouton) {
    const actif = bouton.dataset.lireActif === '1';
    const libelle = bouton.querySelector('[data-lire-libelle]');
    const icone = bouton.querySelector('svg');

    if (libelle) libelle.textContent = actif ? 'Arrêter' : 'Écouter';
    bouton.classList.toggle('lecture-active', actif);
    if (icone) icone.style.color = actif ? 'var(--accent)' : '';
}

function lire(bouton) {
    if (!synth) {
        alert("La lecture vocale n'est pas disponible dans ce navigateur. Chrome ou Edge la prennent en charge.");
        return;
    }

    const dejaEnCours = bouton.dataset.lireActif === '1';
    arreter();
    document.querySelectorAll('[data-lire]').forEach((b) => { b.dataset.lireActif = '0'; });

    if (dejaEnCours) {
        majBouton(bouton);
        return;
    }

    const cible = document.getElementById(bouton.dataset.lire);
    if (!cible) return;

    const texte = texteLisible(cible);
    if (!texte) return;

    // Découpage en phrases : au-delà de quelques centaines de caractères,
    // Chrome interrompt la synthèse sans prévenir.
    const morceaux = texte.match(/[^.!?]+[.!?]*/g) || [texte];

    blocCourant = cible;
    cible.classList.add('en-lecture');
    bouton.dataset.lireActif = '1';
    majBouton(bouton);

    const vitesse = Number(localStorage.getItem('meridien-voix-vitesse') || '1');

    morceaux.forEach((morceau, i) => {
        const u = new SpeechSynthesisUtterance(morceau.trim());
        u.lang = 'fr-FR';
        u.rate = vitesse;
        u.pitch = 1;
        if (voixFr) u.voice = voixFr;

        if (i === morceaux.length - 1) {
            u.onend = () => {
                bouton.dataset.lireActif = '0';
                arreter();
            };
        }

        synth.speak(u);
    });
}

/** Réglage de vitesse, mémorisé d'une session à l'autre. */
function initVitesse() {
    document.querySelectorAll('[data-voix-vitesse]').forEach((select) => {
        select.value = localStorage.getItem('meridien-voix-vitesse') || '1';

        select.addEventListener('change', () => {
            localStorage.setItem('meridien-voix-vitesse', select.value);
            arreter();
        });
    });
}

export function initVoix() {
    if (!synth) {
        document.querySelectorAll('[data-lire], [data-voix-vitesse]').forEach((el) => {
            el.closest('[data-voix-bloc]')?.remove() ?? el.remove();
        });
        return;
    }

    voixFr = choisirVoix();

    // Chrome charge la liste des voix de façon asynchrone.
    synth.addEventListener('voiceschanged', () => { voixFr = choisirVoix(); });

    document.addEventListener('click', (e) => {
        const bouton = e.target.closest('[data-lire]');
        if (bouton) {
            e.preventDefault();
            lire(bouton);
        }
    });

    // Quitter la page ne doit pas laisser une voix tourner.
    window.addEventListener('beforeunload', () => synth.cancel());
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) arreter();
    });

    initVitesse();
}