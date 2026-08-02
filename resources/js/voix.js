/* ==========================================================================
   MÉRIDIEN — lecture vocale
   --------------------------------------------------------------------------
   Suivre un cours à l'oreille suppose trois choses que la synthèse brute ne
   donne pas : pouvoir s'arrêter au milieu d'une phrase pour prendre une note,
   savoir en permanence où en est la voix, et entendre le code et les tableaux
   plutôt que se les entendre annoncer.

   Le mécanisme central est le plan de lecture. Chaque mot du bloc visé est
   enveloppé dans un <span>, et l'on note à quel endroit du texte prononcé il
   commence. L'événement `boundary` de l'API Web Speech donne, pendant la
   lecture, la position du mot en cours : une recherche dichotomique retrouve
   le span correspondant et le surligne.

   S'appuie sur l'API Web Speech, présente nativement dans Chrome et Edge —
   aucune dépendance, aucun réseau.
   ========================================================================== */

const synth = window.speechSynthesis;

/* Au-delà de quelques centaines de caractères, Chrome interrompt la synthèse
   sans prévenir. On découpe, mais jamais au milieu d'un mot. */
const TAILLE_MORCEAU = 320;

/* Éléments dont le contenu ne doit pas être prononcé. */
const IGNORES = new Set(['script', 'style', 'noscript', 'svg', 'button', 'select', 'textarea', 'input']);

/* Éléments après lesquels on marque une pause. */
const BLOCS = new Set(['p', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'div', 'section', 'tr']);

let voixFr = null;
let lecture = null; // état de la lecture en cours
let entretien = null; // parade au minuteur interne de Chrome
const plans = new WeakMap();

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

/* --------------------------------------------------------------------------
   Prononciation
   -------------------------------------------------------------------------- */

/** Symboles du texte courant : logique, complexité, ponctuation typographique. */
const SYMBOLES = [
    [/⟺|<=>|<->/g, ' équivaut à '],
    [/⇒|→|->/g, ' implique '],
    [/∀/g, ' pour tout '],
    [/∃/g, ' il existe '],
    [/¬/g, ' non '],
    [/∧|\/\\/g, ' et '],
    [/∨|\\\//g, ' ou '],
    [/∉/g, " n'appartient pas à "],
    [/∈/g, ' appartient à '],
    [/⊆|⊂/g, ' inclus dans '],
    [/∪/g, ' union '],
    [/∩/g, ' inter '],
    [/∅/g, ' ensemble vide '],
    [/≤|<=/g, ' inférieur ou égal à '],
    [/≥|>=/g, ' supérieur ou égal à '],
    [/≠|!=|<>/g, ' différent de '],
    [/≈/g, ' environ '],
    [/Θ/g, ' thêta '],
    [/Ω/g, ' oméga '],
    [/Σ/g, ' somme '],
    [/√/g, ' racine de '],
    [/∞/g, ' infini '],
    [/O\(n²\)/g, ' O de n carré '],
    [/O\(n\^?2\)/g, ' O de n carré '],
    [/O\(n log n\)/gi, ' O de n log n '],
    [/O\(log n\)/gi, ' O de log n '],
    [/O\(1\)/g, ' O de 1 '],
    [/O\(n\)/g, ' O de n '],
    [/²/g, ' carré '],
    [/³/g, ' cube '],
    [/·|×/g, ' fois '],
    [/…/g, '. '],
    [/[«»""„‹›]/g, ''],
    [/[—–]/g, ', '],
    [/’/g, "'"],
];

/** Rend un fragment de texte courant prononçable. */
export function prononcer(mot) {
    let sortie = mot;
    SYMBOLES.forEach(([motif, remplacement]) => {
        sortie = sortie.replace(motif, remplacement);
    });

    return sortie.replace(/\s+/g, ' ').trim();
}

/**
 * Symboles du code. Plus littéral que le texte courant : c'est précisément
 * la ponctuation d'un langage qu'on n'arrive pas à retenir quand on débute,
 * et une accolade qu'on n'entend pas est une accolade qu'on oublie d'écrire.
 */
const SYMBOLES_CODE = [
    [/\{/g, ' accolade ouvrante '],
    [/\}/g, ' accolade fermante '],
    [/\(/g, ' parenthèse ouvrante '],
    [/\)/g, ' parenthèse fermante '],
    [/\[/g, ' crochet ouvrant '],
    [/\]/g, ' crochet fermant '],
    [/<->|<=>/g, ' équivaut à '],
    // L'affectation avant la comparaison : `i <- i + 1` n'a rien d'un « inférieur ».
    [/<-|:=/g, ' reçoit '],
    [/->|=>/g, ' flèche '],
    [/<=/g, ' inférieur ou égal à '],
    [/>=/g, ' supérieur ou égal à '],
    [/!=|<>/g, ' différent de '],
    [/==|=/g, ' égale '],
    [/\/\\|&&/g, ' et '],
    [/\\\/|\|\|/g, ' ou '],
    [/\|/g, ' cas '], // séparateur de filtrage : `| Cons x l -> …`
    [/@/g, ' concaténé à '],
    [/</g, ' inférieur à '],
    [/>/g, ' supérieur à '],
    [/::/g, ' cons '],
    [/:/g, ' deux-points '],
    [/;/g, ' point-virgule '],
    [/,/g, ' virgule '],
    [/"/g, ' guillemet '],
    [/\+/g, ' plus '],
    [/\*/g, ' fois '],
    [/%/g, ' modulo '],
    [/\//g, ' divisé par '],
    [/\bnot\b/g, ' non '],
    [/\bforall\b/g, ' pour tout '],
    [/\bexists\b/g, ' il existe '],
];

/**
 * Rend un jeton de code prononçable.
 *
 * Le souligné entouré de lettres marque un mot composé : `ease_factor` se dit
 * « ease factor », pas « ease souligné factor ». Seul, il garde son nom.
 */
export function prononcerCode(jeton) {
    let sortie = jeton
        .replace(/([A-Za-z0-9])_([A-Za-z0-9])/g, '$1 $2')
        .replace(/(?<=\d)\.(?=\d)/g, ' virgule ') // 2.5 → deux virgule cinq
        .replace(/(?<=[A-Za-z])\.(?=[A-Za-z])/g, ' point '); // int.Int

    SYMBOLES_CODE.forEach(([motif, remplacement]) => {
        sortie = sortie.replace(motif, remplacement);
    });

    return sortie
        .replace(/\.$/, ' point ')
        .replace(/_/g, ' souligné ')
        .replace(/\s+/g, ' ')
        .trim();
}

/* --------------------------------------------------------------------------
   Construction du plan de lecture
   -------------------------------------------------------------------------- */

/**
 * Parcourt le bloc et construit, en une passe, le texte à prononcer et la
 * table des mots.
 *
 * @returns {{texte: string, mots: Array<{el: HTMLElement, bloc: ?Element, debut: number}>}}
 */
export function construirePlan(racine) {
    const mots = [];
    let texte = '';

    /**
     * Ajoute du texte sans cible à surligner : numéros, en-têtes, pauses.
     *
     * Le texte ne peut plus être retouché une fois construit — les positions
     * des mots y renvoient. Les points en trop sont donc écartés ici, à
     * l'écriture, et non par un nettoyage final qui décalerait tout.
     */
    const dire = (fragment) => {
        if (!fragment) return;

        const finiParPoint = /[.:]\s*$/.test(texte);
        if (finiParPoint) {
            if (/^\.\s*$/.test(fragment)) return;
            if (fragment.startsWith('. ')) fragment = fragment.slice(2);
        }

        if (texte && !/\s$/.test(texte)) texte += ' ';
        texte += fragment;
    };

    /** Ajoute un mot et retient le span qui le porte à l'écran. */
    const ajouter = (el, parle, bloc) => {
        if (!parle) return;
        if (texte && !/\s$/.test(texte)) texte += ' ';
        mots.push({ el, bloc, debut: texte.length });
        texte += parle;
    };

    /** Enveloppe chaque mot d'un nœud texte dans un span surlignable. */
    const decouper = (noeud, bloc, traduire) => {
        const brut = noeud.nodeValue;
        if (!brut.trim()) return;

        const frag = document.createDocumentFragment();
        const nouveaux = [];

        brut.split(/(\s+)/).forEach((part) => {
            if (!part) return;

            if (!part.trim()) {
                frag.appendChild(document.createTextNode(part));
                return;
            }

            const span = document.createElement('span');
            span.className = 'voix-mot';
            span.textContent = part;
            frag.appendChild(span);
            nouveaux.push([span, traduire(part)]);
        });

        noeud.parentNode.replaceChild(frag, noeud);
        nouveaux.forEach(([span, parle]) => ajouter(span, parle, bloc));
    };

    /* ---- Blocs de code -------------------------------------------------- */

    /**
     * Le code est reconstruit ligne par ligne : c'est le seul moyen de
     * numéroter les lignes à la lecture, ce qui rend un bloc de dix lignes
     * suivable à l'oreille. Un `pre` ne contient rien d'interactif, on peut
     * en réécrire le contenu sans rien casser.
     */
    const lireCode = (pre) => {
        const source = (pre.querySelector('code') || pre).textContent.replace(/\s+$/, '');
        const lignes = source.split('\n');
        const numeroter = lignes.filter((l) => l.trim()).length >= 4;

        const cible = pre.querySelector('code') || pre;
        cible.textContent = '';
        dire('. Bloc de code. ');

        lignes.forEach((ligne, i) => {
            if (i > 0) cible.appendChild(document.createTextNode('\n'));

            if (!ligne.trim()) return;

            if (numeroter) dire(`. Ligne ${i + 1}. `);
            else dire('. ');

            ligne.split(/(\s+)/).forEach((part) => {
                if (!part) return;

                if (!part.trim()) {
                    cible.appendChild(document.createTextNode(part));
                    return;
                }

                const span = document.createElement('span');
                span.className = 'voix-mot';
                span.textContent = part;
                cible.appendChild(span);
                ajouter(span, prononcerCode(part), pre);
            });
        });

        dire('. Fin du bloc de code. ');
    };

    /* ---- Tableaux -------------------------------------------------------- */

    /**
     * Un tableau lu de gauche à droite sans repères est incompréhensible : on
     * perd la colonne au deuxième mot. Chaque cellule est donc annoncée par
     * son en-tête, et chaque ligne numérotée.
     */
    const lireTableau = (table) => {
        const entetes = [...table.querySelectorAll('thead th, thead td')]
            .map((c) => prononcer(c.textContent));
        const lignes = [...table.querySelectorAll('tbody tr')];
        const rangs = lignes.length ? lignes : [...table.querySelectorAll('tr')].slice(entetes.length ? 1 : 0);

        dire(`. Tableau de ${rangs.length} ligne${rangs.length > 1 ? 's' : ''}. `);

        if (entetes.length) {
            dire(`Colonnes : ${entetes.filter(Boolean).join(', ')}. `);
        }

        rangs.forEach((tr, i) => {
            dire(`. Ligne ${i + 1}. `);

            [...tr.children].forEach((cellule, j) => {
                const entete = entetes[j];
                if (entete && entetes.length > 1) dire(`${entete} : `);

                parcourir(cellule, tr, prononcer);
                dire('. ');
            });
        });

        dire('. Fin du tableau. ');
    };

    /* ---- Parcours -------------------------------------------------------- */

    function parcourir(noeud, bloc, traduire) {
        if (noeud.nodeType === Node.TEXT_NODE) {
            decouper(noeud, bloc, traduire);
            return;
        }

        if (noeud.nodeType !== Node.ELEMENT_NODE) return;

        const nom = noeud.tagName.toLowerCase();
        if (IGNORES.has(nom) || noeud.hasAttribute('data-voix-ignorer')) return;
        if (noeud.getAttribute('aria-hidden') === 'true') return;

        if (nom === 'pre') {
            lireCode(noeud);
            return;
        }

        if (nom === 'table') {
            lireTableau(noeud);
            return;
        }

        const propre = BLOCS.has(nom) ? noeud : bloc;

        // Une liste ordonnée perd tout son sens si l'on n'entend pas les rangs.
        if (nom === 'li' && noeud.parentElement?.tagName === 'OL') {
            dire(`. ${[...noeud.parentElement.children].indexOf(noeud) + 1}. `);
        }

        [...noeud.childNodes].forEach((enfant) => parcourir(enfant, propre, traduire));

        if (BLOCS.has(nom)) dire('. ');
    }

    parcourir(racine, null, prononcer);

    // Surtout pas de nettoyage global ici : les positions enregistrées dans
    // `mots` pointent dans cette chaîne. Seule la fin peut être élaguée.
    return { texte: texte.replace(/\s+$/, ''), mots };
}

/**
 * Le plan est calculé une fois par bloc et conservé.
 *
 * Les spans posés restent en place : ils sont inertes tant qu'ils ne portent
 * pas la classe active. Les retirer à chaque arrêt ferait perdre les
 * écouteurs des éléments voisins pour rien.
 */
function planDe(element) {
    if (!plans.has(element)) {
        const plan = construirePlan(element);
        plan.debuts = plan.mots.map((m) => m.debut);
        plans.set(element, plan);
    }

    return plans.get(element);
}

/* --------------------------------------------------------------------------
   Découpage et surlignement
   -------------------------------------------------------------------------- */

/** Découpe le texte en morceaux courts, aux frontières de phrase de préférence. */
export function morceler(texte) {
    const morceaux = [];
    let position = 0;

    while (position < texte.length) {
        let fin = Math.min(position + TAILLE_MORCEAU, texte.length);

        if (fin < texte.length) {
            const tranche = texte.slice(position, fin);
            const phrase = Math.max(tranche.lastIndexOf('. '), tranche.lastIndexOf('? '), tranche.lastIndexOf('! '));
            const coupe = phrase > TAILLE_MORCEAU * 0.4 ? phrase + 1 : tranche.lastIndexOf(' ');
            if (coupe > 0) fin = position + coupe;
        }

        morceaux.push({ texte: texte.slice(position, fin), debut: position });
        position = fin;
    }

    return morceaux;
}

/** Retrouve, par dichotomie, le dernier mot commençant avant la position lue. */
export function motA(debuts, position) {
    let bas = 0;
    let haut = debuts.length - 1;
    let trouve = -1;

    while (bas <= haut) {
        const milieu = (bas + haut) >> 1;
        if (debuts[milieu] <= position) {
            trouve = milieu;
            bas = milieu + 1;
        } else {
            haut = milieu - 1;
        }
    }

    return trouve;
}

function surligner(index) {
    if (!lecture || index < 0 || index === lecture.index) return;

    const { plan } = lecture;
    const precedent = plan.mots[lecture.index];
    const courant = plan.mots[index];
    if (!courant) return;

    precedent?.el.classList.remove('voix-mot-actif');
    if (precedent?.bloc && precedent.bloc !== courant.bloc) {
        precedent.bloc.classList.remove('voix-bloc-actif');
    }

    courant.el.classList.add('voix-mot-actif');
    courant.bloc?.classList.add('voix-bloc-actif');

    lecture.index = index;
    majProgression();
    suivreDuRegard(courant.el);
}

/** Ramène le mot lu à l'écran, sans secouer la page à chaque syllabe. */
function suivreDuRegard(el) {
    const boite = el.getBoundingClientRect();
    const haut = window.innerHeight * 0.15;
    const bas = window.innerHeight * 0.75;

    if (boite.top < haut || boite.bottom > bas) {
        el.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
}

function nettoyerSurlignage(plan) {
    plan.mots.forEach((m) => {
        m.el.classList.remove('voix-mot-actif');
        m.bloc?.classList.remove('voix-bloc-actif');
    });
}

/* --------------------------------------------------------------------------
   Barre de commande flottante
   -------------------------------------------------------------------------- */

let barre = null;

function construireBarre() {
    if (barre) return barre;

    barre = document.createElement('div');
    barre.className = 'voix-barre';
    barre.setAttribute('role', 'toolbar');
    barre.setAttribute('aria-label', 'Lecture vocale');
    barre.innerHTML = `
        <button type="button" data-voix-bascule class="voix-btn voix-btn-primaire" title="Pause (P)">
            <span data-voix-icone></span><span data-voix-etat>Pause</span>
        </button>
        <button type="button" data-voix-recul class="voix-btn" title="Phrase précédente">◀◀</button>
        <button type="button" data-voix-avance class="voix-btn" title="Phrase suivante">▶▶</button>
        <div class="voix-jauge"><div data-voix-jauge></div></div>
        <span class="voix-compte" data-voix-compte>0 %</span>
        <select data-voix-vitesse-barre class="voix-select" title="Vitesse">
            <option value="0.8">0,8×</option>
            <option value="1">1×</option>
            <option value="1.25">1,25×</option>
            <option value="1.5">1,5×</option>
            <option value="1.75">1,75×</option>
            <option value="2">2×</option>
        </select>
        <button type="button" data-voix-stop class="voix-btn" title="Arrêter (Échap)">✕</button>
    `;
    document.body.appendChild(barre);

    barre.querySelector('[data-voix-bascule]').addEventListener('click', basculerPause);
    barre.querySelector('[data-voix-stop]').addEventListener('click', arreter);
    barre.querySelector('[data-voix-recul]').addEventListener('click', () => sauter(-1));
    barre.querySelector('[data-voix-avance]').addEventListener('click', () => sauter(1));

    const vitesse = barre.querySelector('[data-voix-vitesse-barre]');
    vitesse.value = vitesseChoisie();
    vitesse.addEventListener('change', () => {
        localStorage.setItem('meridien-voix-vitesse', vitesse.value);
        document.querySelectorAll('[data-voix-vitesse]').forEach((s) => { s.value = vitesse.value; });
        // La vitesse ne se change pas en vol : on reprend au morceau courant.
        if (lecture) relancer(lecture.morceau);
    });

    return barre;
}

function majProgression() {
    if (!barre || !lecture) return;

    const total = lecture.plan.mots.length || 1;
    const part = Math.min(100, Math.round(((lecture.index + 1) / total) * 100));

    barre.querySelector('[data-voix-jauge]').style.width = `${part}%`;
    barre.querySelector('[data-voix-compte]').textContent = `${part} %`;
}

function majEtatBarre() {
    if (!barre) return;

    const enPause = !!lecture?.enPause;
    barre.querySelector('[data-voix-etat]').textContent = enPause ? 'Reprendre' : 'Pause';
    barre.querySelector('[data-voix-icone]').textContent = enPause ? '▶' : '❚❚';
    barre.classList.toggle('voix-barre-pause', enPause);
    barre.querySelector('[data-voix-bascule]').title = enPause ? 'Reprendre (P)' : 'Pause (P)';
}

/* --------------------------------------------------------------------------
   Pilotage de la lecture
   -------------------------------------------------------------------------- */

function vitesseChoisie() {
    return localStorage.getItem('meridien-voix-vitesse') || '1';
}

/**
 * Lance la synthèse à partir d'un morceau donné.
 *
 * Deux précautions imposées par Chrome. `cancel()` déclenche `onend` sur tous
 * les énoncés en attente : sans le numéro de série, la file qu'on vient
 * d'abandonner ferait tomber celle qu'on met en route. Et un `speak()` collé
 * à un `cancel()` est parfois avalé sans un souffle de répit.
 */
function relancer(depuis) {
    if (!lecture) return;

    const serie = ++lecture.serie;
    lecture.morceau = depuis;
    lecture.enPause = false;
    synth.cancel();

    const debit = Number(vitesseChoisie());
    const encoreAJour = () => lecture && lecture.serie === serie;

    setTimeout(() => {
        if (!encoreAJour()) return;

        lecture.morceaux.slice(depuis).forEach((morceau, decalage) => {
            const u = new SpeechSynthesisUtterance(morceau.texte);
            u.lang = 'fr-FR';
            u.rate = debit;
            u.pitch = 1;
            if (voixFr) u.voice = voixFr;

            const rang = depuis + decalage;

            u.onstart = () => { if (encoreAJour()) lecture.morceau = rang; };

            // `boundary` donne la position du mot attaqué, relative au morceau.
            u.onboundary = (e) => {
                if (e.name === 'sentence' || !encoreAJour()) return;
                surligner(motA(lecture.plan.debuts, morceau.debut + e.charIndex));
            };

            if (rang === lecture.morceaux.length - 1) {
                u.onend = () => { if (encoreAJour() && !lecture.enPause) arreter(); };
            }

            synth.speak(u);
        });
    }, 60);

    majEtatBarre();
}

function basculerPause() {
    if (!lecture) return;

    if (lecture.enPause) {
        lecture.enPause = false;
        synth.resume();
    } else {
        lecture.enPause = true;
        synth.pause();
    }

    majEtatBarre();
}

/** Recule ou avance d'un morceau — en pratique, d'une phrase ou deux. */
function sauter(pas) {
    if (!lecture) return;

    const cible = Math.max(0, Math.min(lecture.morceaux.length - 1, lecture.morceau + pas));
    relancer(cible);
}

function arreter() {
    if (entretien) {
        clearInterval(entretien);
        entretien = null;
    }

    synth?.cancel();

    if (lecture) {
        nettoyerSurlignage(lecture.plan);
        lecture.cible.classList.remove('en-lecture');
        lecture.bouton.dataset.lireActif = '0';
        lecture = null;
    }

    barre?.classList.remove('voix-barre-visible');
    document.querySelectorAll('[data-lire]').forEach(majBouton);
}

function majBouton(bouton) {
    const actif = bouton.dataset.lireActif === '1';
    const libelle = bouton.querySelector('[data-lire-libelle]');

    if (libelle) libelle.textContent = actif ? 'Arrêter' : (bouton.dataset.lireLabel || 'Écouter');
    bouton.classList.toggle('lecture-active', actif);
}

function lire(bouton) {
    if (!synth) {
        alert("La lecture vocale n'est pas disponible dans ce navigateur. Chrome ou Edge la prennent en charge.");
        return;
    }

    const relance = bouton.dataset.lireActif === '1';
    arreter();
    if (relance) return;

    const cible = document.getElementById(bouton.dataset.lire);
    if (!cible) return;

    const plan = planDe(cible);
    if (!plan.texte) return;

    cible.classList.add('en-lecture');
    bouton.dataset.lireActif = '1';
    majBouton(bouton);

    lecture = {
        bouton,
        cible,
        plan,
        morceaux: morceler(plan.texte),
        morceau: 0,
        index: -1,
        enPause: false,
        serie: 0,
    };

    construireBarre().classList.add('voix-barre-visible');
    majProgression();
    relancer(0);

    /* Chrome coupe la synthèse au bout d'une quinzaine de secondes lorsque la
       page n'a pas la main. Un `resume()` périodique — sans effet quand la
       voix tourne — suffit à la maintenir. */
    entretien = setInterval(() => {
        if (lecture && !lecture.enPause) synth.resume();
    }, 8000);
}

/** Réglage de vitesse dans l'en-tête, mémorisé d'une session à l'autre. */
function initVitesse() {
    document.querySelectorAll('[data-voix-vitesse]').forEach((select) => {
        select.value = vitesseChoisie();

        select.addEventListener('change', () => {
            localStorage.setItem('meridien-voix-vitesse', select.value);
            if (barre) barre.querySelector('[data-voix-vitesse-barre]').value = select.value;
            if (lecture) relancer(lecture.morceau);
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

    document.addEventListener('keydown', (e) => {
        if (!lecture) return;
        if (e.target.matches('input, textarea, select')) return;

        if (e.key === 'Escape') {
            arreter();
        } else if (e.key === 'p' || e.key === 'P') {
            e.preventDefault();
            basculerPause();
        }
    });

    // Quitter la page ne doit pas laisser une voix tourner.
    window.addEventListener('beforeunload', () => synth.cancel());

    initVitesse();
}