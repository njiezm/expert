/* ==========================================================================
   MÉRIDIEN — éditeur de diagrammes de classes
   --------------------------------------------------------------------------
   Construit pour une raison précise : l'épreuve d'ALO de janvier a été notée 0
   parce que les trois questions de conception ont reçu du pseudo-code là où un
   schéma était demandé. On ne s'entraîne pas à décrire un diagramme, on
   s'entraîne à le dessiner.

   L'éditeur produit un JSON { nodes, links, labels } déposé dans un champ caché
   du formulaire, et rendu à l'identique à la relecture.
   ========================================================================== */

const TYPES_LIEN = {
    heritage: { libelle: 'Héritage', trait: 'plein', tete: 'triangle-creux' },
    implementation: { libelle: 'Implémentation', trait: 'pointille', tete: 'triangle-creux' },
    agregation: { libelle: 'Agrégation', trait: 'plein', tete: 'losange-creux' },
    composition: { libelle: 'Composition', trait: 'plein', tete: 'losange-plein' },
    association: { libelle: 'Association', trait: 'plein', tete: 'aucune' },
};

const STEREOTYPES = {
    classe: '',
    interface: '«interface»',
    abstraite: '«abstract»',
};

class EditeurSchema {
    constructor(racine) {
        this.racine = racine;
        this.champ = document.getElementById(racine.dataset.schemaChamp);
        this.lectureSeule = racine.hasAttribute('data-schema-lecture');

        this.etat = this.charger();
        this.compteur = this.prochainId();

        this.selection = null;
        this.lienDepuis = null;
        this.typeLienCourant = 'heritage';
        this.deplacement = null;

        this.construire();
        this.dessiner();
    }

    /* ---------------- État ---------------- */

    charger() {
        const brut = this.champ?.value || this.racine.dataset.schemaValeur || '';

        try {
            const donnees = JSON.parse(brut);
            return {
                nodes: donnees.nodes || [],
                links: donnees.links || [],
                labels: donnees.labels || [],
            };
        } catch {
            return { nodes: [], links: [], labels: [] };
        }
    }

    prochainId() {
        const ids = [
            ...this.etat.nodes.map((n) => n.id),
            ...this.etat.labels.map((l) => l.id),
        ].filter((id) => typeof id === 'number');

        return ids.length ? Math.max(...ids) + 1 : 1;
    }

    enregistrer() {
        if (this.champ) this.champ.value = JSON.stringify(this.etat);
    }

    /* ---------------- Construction de l'interface ---------------- */

    construire() {
        this.racine.classList.add('editeur-schema');

        if (!this.lectureSeule) {
            this.racine.appendChild(this.barreOutils());
        }

        this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        this.svg.setAttribute('class', 'schema-toile');
        this.svg.setAttribute('viewBox', '0 0 1200 760');
        this.svg.setAttribute('preserveAspectRatio', 'xMidYMin meet');
        this.racine.appendChild(this.svg);

        if (!this.lectureSeule) {
            this.aide = document.createElement('p');
            this.aide.className = 'schema-aide';
            this.racine.appendChild(this.aide);
            this.majAide();

            this.svg.addEventListener('mousedown', (e) => this.souris(e, 'down'));
            window.addEventListener('mousemove', (e) => this.souris(e, 'move'));
            window.addEventListener('mouseup', () => this.souris(null, 'up'));
            this.svg.addEventListener('dblclick', (e) => this.doubleClic(e));

            document.addEventListener('keydown', (e) => {
                if (this.lectureSeule || e.target.matches('input, textarea')) return;
                if ((e.key === 'Delete' || e.key === 'Backspace') && this.selection) {
                    e.preventDefault();
                    this.supprimerSelection();
                }
                if (e.key === 'Escape') {
                    this.lienDepuis = null;
                    this.selection = null;
                    this.dessiner();
                    this.majAide();
                }
            });
        }
    }

    barreOutils() {
        const barre = document.createElement('div');
        barre.className = 'schema-outils';

        const groupe = (titre) => {
            const g = document.createElement('div');
            g.className = 'schema-groupe';
            const t = document.createElement('span');
            t.className = 'schema-groupe-titre';
            t.textContent = titre;
            g.appendChild(t);
            return g;
        };

        const bouton = (texte, action, titre = '') => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'schema-btn';
            b.textContent = texte;
            if (titre) b.title = titre;
            b.addEventListener('click', action);
            return b;
        };

        /* Boîtes */
        const gBoites = groupe('Ajouter');
        gBoites.appendChild(bouton('Classe', () => this.ajouterNoeud('classe')));
        gBoites.appendChild(bouton('Interface', () => this.ajouterNoeud('interface')));
        gBoites.appendChild(bouton('Abstraite', () => this.ajouterNoeud('abstraite')));
        gBoites.appendChild(bouton('Étiquette patron', () => this.ajouterEtiquette(),
            'Le nom du patron, à poser à côté de la zone concernée'));
        barre.appendChild(gBoites);

        /* Liens */
        const gLiens = groupe('Relier');
        this.boutonsLien = {};

        Object.entries(TYPES_LIEN).forEach(([cle, def]) => {
            const b = bouton(def.libelle, () => {
                this.typeLienCourant = cle;
                this.lienDepuis = null;
                this.majBoutonsLien();
                this.majAide();
            });
            this.boutonsLien[cle] = b;
            gLiens.appendChild(b);
        });

        barre.appendChild(gLiens);
        this.majBoutonsLien();

        /* Actions */
        const gActions = groupe('Actions');
        gActions.appendChild(bouton('Supprimer', () => this.supprimerSelection(),
            'Supprime l’élément sélectionné (touche Suppr)'));
        gActions.appendChild(bouton('Tout effacer', () => {
            if (!confirm('Effacer tout le diagramme ?')) return;
            this.etat = { nodes: [], links: [], labels: [] };
            this.selection = null;
            this.enregistrer();
            this.dessiner();
        }));
        barre.appendChild(gActions);

        return barre;
    }

    majBoutonsLien() {
        Object.entries(this.boutonsLien || {}).forEach(([cle, b]) => {
            b.classList.toggle('actif', cle === this.typeLienCourant);
        });
    }

    majAide() {
        if (!this.aide) return;

        if (this.lienDepuis) {
            const n = this.etat.nodes.find((x) => x.id === this.lienDepuis);
            this.aide.textContent =
                `Relier depuis « ${n?.name || '?'} » — cliquez la boîte d'arrivée. Échap pour annuler.`;
            return;
        }

        this.aide.textContent =
            'Glissez pour déplacer · double-cliquez une boîte pour l’éditer · '
            + `cliquez deux boîtes pour les relier en ${TYPES_LIEN[this.typeLienCourant].libelle.toLowerCase()} · Suppr pour retirer.`;
    }

    /* ---------------- Créations ---------------- */

    ajouterNoeud(stereotype) {
        const n = this.etat.nodes.length;

        this.etat.nodes.push({
            id: this.compteur++,
            x: 60 + (n % 4) * 280,
            y: 60 + Math.floor(n / 4) * 200,
            stereotype,
            name: stereotype === 'interface' ? 'NouvelleInterface' : 'NouvelleClasse',
            attributes: [],
            methods: [],
        });

        this.enregistrer();
        this.dessiner();
    }

    ajouterEtiquette() {
        const texte = prompt('Nom du patron (Composite, État, Stratégie, Observateur, Décorateur, Visiteur…)');
        if (!texte) return;

        this.etat.labels.push({
            id: this.compteur++,
            x: 60,
            y: 700,
            text: `◄── ${texte}`,
        });

        this.enregistrer();
        this.dessiner();
    }

    supprimerSelection() {
        if (!this.selection) return;

        const { type, id } = this.selection;

        if (type === 'node') {
            this.etat.nodes = this.etat.nodes.filter((n) => n.id !== id);
            this.etat.links = this.etat.links.filter((l) => l.from !== id && l.to !== id);
        } else if (type === 'label') {
            this.etat.labels = this.etat.labels.filter((l) => l.id !== id);
        } else if (type === 'link') {
            this.etat.links.splice(id, 1);
        }

        this.selection = null;
        this.enregistrer();
        this.dessiner();
    }

    /* ---------------- Interaction ---------------- */

    point(e) {
        const r = this.svg.getBoundingClientRect();
        const vb = this.svg.viewBox.baseVal;

        return {
            x: ((e.clientX - r.left) / r.width) * vb.width,
            y: ((e.clientY - r.top) / r.height) * vb.height,
        };
    }

    souris(e, phase) {
        if (this.lectureSeule) return;

        if (phase === 'up') {
            this.deplacement = null;
            return;
        }

        if (phase === 'move') {
            if (!this.deplacement) return;

            const p = this.point(e);
            const cible = this.deplacement.cible;
            cible.x = Math.max(0, Math.round(p.x - this.deplacement.dx));
            cible.y = Math.max(0, Math.round(p.y - this.deplacement.dy));
            this.enregistrer();
            this.dessiner();
            return;
        }

        /* mousedown */
        const groupe = e.target.closest('[data-node-id], [data-label-id], [data-link-index]');

        if (!groupe) {
            this.selection = null;
            this.lienDepuis = null;
            this.dessiner();
            this.majAide();
            return;
        }

        if (groupe.dataset.linkIndex !== undefined) {
            this.selection = { type: 'link', id: Number(groupe.dataset.linkIndex) };
            this.dessiner();
            return;
        }

        if (groupe.dataset.labelId !== undefined) {
            const id = Number(groupe.dataset.labelId);
            const cible = this.etat.labels.find((l) => l.id === id);
            const p = this.point(e);
            this.selection = { type: 'label', id };
            this.deplacement = { cible, dx: p.x - cible.x, dy: p.y - cible.y };
            this.dessiner();
            return;
        }

        const id = Number(groupe.dataset.nodeId);
        const noeud = this.etat.nodes.find((n) => n.id === id);

        /* Second clic d'une mise en relation */
        if (this.lienDepuis !== null && this.lienDepuis !== id) {
            this.etat.links.push({
                from: this.lienDepuis,
                to: id,
                type: this.typeLienCourant,
                fromMult: '',
                toMult: '',
            });
            this.lienDepuis = null;
            this.enregistrer();
            this.dessiner();
            this.majAide();
            return;
        }

        const p = this.point(e);
        this.selection = { type: 'node', id };
        this.lienDepuis = id;
        this.deplacement = { cible: noeud, dx: p.x - noeud.x, dy: p.y - noeud.y };
        this.dessiner();
        this.majAide();
    }

    doubleClic(e) {
        const groupe = e.target.closest('[data-node-id], [data-link-index]');
        if (!groupe) return;

        e.preventDefault();
        this.lienDepuis = null;

        if (groupe.dataset.linkIndex !== undefined) {
            const lien = this.etat.links[Number(groupe.dataset.linkIndex)];
            const from = prompt('Multiplicité côté départ (1, *, 0..1, 1..*) — vide pour aucune', lien.fromMult);
            if (from !== null) lien.fromMult = from.trim();
            const to = prompt('Multiplicité côté arrivée', lien.toMult);
            if (to !== null) lien.toMult = to.trim();
            this.enregistrer();
            this.dessiner();
            return;
        }

        const noeud = this.etat.nodes.find((n) => n.id === Number(groupe.dataset.nodeId));

        const nom = prompt('Nom de la classe', noeud.name);
        if (nom === null) return;
        noeud.name = nom.trim() || noeud.name;

        const attrs = prompt(
            'Attributs, un par ligne\nExemple : - id : String',
            noeud.attributes.join('\n')
        );
        if (attrs !== null) {
            noeud.attributes = attrs.split('\n').map((s) => s.trim()).filter(Boolean);
        }

        const meths = prompt(
            'Méthodes, une par ligne\nExemple : + calculer() : int',
            noeud.methods.join('\n')
        );
        if (meths !== null) {
            noeud.methods = meths.split('\n').map((s) => s.trim()).filter(Boolean);
        }

        this.enregistrer();
        this.dessiner();
    }

    /* ---------------- Rendu ---------------- */

    dimensions(noeud) {
        const lignes = [
            ...(STEREOTYPES[noeud.stereotype] ? [1] : []),
            1,
            ...noeud.attributes,
            ...noeud.methods,
        ];

        const largeurTexte = Math.max(
            noeud.name.length,
            ...noeud.attributes.map((a) => a.length),
            ...noeud.methods.map((m) => m.length),
            12
        );

        return {
            w: Math.min(340, Math.max(150, largeurTexte * 7.2 + 24)),
            h: 12 + lignes.length * 18 + 12,
        };
    }

    dessiner() {
        const ns = 'http://www.w3.org/2000/svg';
        this.svg.innerHTML = '';

        /* Marqueurs de tête de flèche */
        const defs = document.createElementNS(ns, 'defs');
        defs.innerHTML = `
            <marker id="triangle-creux" viewBox="0 0 12 12" refX="11" refY="6"
                    markerWidth="11" markerHeight="11" orient="auto-start-reverse">
                <path d="M1 1 L11 6 L1 11 z" fill="var(--surface)" stroke="var(--texte-doux)" stroke-width="1.2"/>
            </marker>
            <marker id="losange-creux" viewBox="0 0 16 12" refX="15" refY="6"
                    markerWidth="14" markerHeight="12" orient="auto-start-reverse">
                <path d="M1 6 L8 1 L15 6 L8 11 z" fill="var(--surface)" stroke="var(--texte-doux)" stroke-width="1.2"/>
            </marker>
            <marker id="losange-plein" viewBox="0 0 16 12" refX="15" refY="6"
                    markerWidth="14" markerHeight="12" orient="auto-start-reverse">
                <path d="M1 6 L8 1 L15 6 L8 11 z" fill="var(--texte-doux)"/>
            </marker>`;
        this.svg.appendChild(defs);

        const boites = new Map();
        this.etat.nodes.forEach((n) => boites.set(n.id, { ...n, ...this.dimensions(n) }));

        /* Liens d'abord, sous les boîtes */
        this.etat.links.forEach((lien, index) => {
            const a = boites.get(lien.from);
            const b = boites.get(lien.to);
            if (!a || !b) return;

            const ca = { x: a.x + a.w / 2, y: a.y + a.h / 2 };
            const cb = { x: b.x + b.w / 2, y: b.y + b.h / 2 };
            const p1 = this.bordure(a, ca, cb);
            const p2 = this.bordure(b, cb, ca);
            const def = TYPES_LIEN[lien.type] || TYPES_LIEN.association;

            const g = document.createElementNS(ns, 'g');
            g.dataset.linkIndex = index;
            g.setAttribute('class', 'schema-lien' + (this.estSelectionne('link', index) ? ' selection' : ''));

            /* Zone de clic élargie */
            const zone = document.createElementNS(ns, 'line');
            zone.setAttribute('x1', p1.x); zone.setAttribute('y1', p1.y);
            zone.setAttribute('x2', p2.x); zone.setAttribute('y2', p2.y);
            zone.setAttribute('stroke', 'transparent');
            zone.setAttribute('stroke-width', '14');
            g.appendChild(zone);

            const trait = document.createElementNS(ns, 'line');
            trait.setAttribute('x1', p1.x); trait.setAttribute('y1', p1.y);
            trait.setAttribute('x2', p2.x); trait.setAttribute('y2', p2.y);
            trait.setAttribute('stroke', 'var(--texte-doux)');
            trait.setAttribute('stroke-width', '1.5');
            if (def.trait === 'pointille') trait.setAttribute('stroke-dasharray', '7 5');
            if (def.tete !== 'aucune') trait.setAttribute('marker-end', `url(#${def.tete})`);
            g.appendChild(trait);

            /* Multiplicités */
            [[lien.fromMult, p1, p2], [lien.toMult, p2, p1]].forEach(([mult, pres, loin]) => {
                if (!mult) return;
                const t = document.createElementNS(ns, 'text');
                t.setAttribute('x', pres.x + (loin.x - pres.x) * 0.13);
                t.setAttribute('y', pres.y + (loin.y - pres.y) * 0.13 - 6);
                t.setAttribute('class', 'schema-mult');
                t.textContent = mult;
                g.appendChild(t);
            });

            this.svg.appendChild(g);
        });

        /* Boîtes */
        boites.forEach((n) => {
            const g = document.createElementNS(ns, 'g');
            g.dataset.nodeId = n.id;
            g.setAttribute('class', 'schema-noeud'
                + (this.estSelectionne('node', n.id) ? ' selection' : '')
                + (this.lienDepuis === n.id ? ' depart' : ''));

            const rect = document.createElementNS(ns, 'rect');
            rect.setAttribute('x', n.x); rect.setAttribute('y', n.y);
            rect.setAttribute('width', n.w); rect.setAttribute('height', n.h);
            rect.setAttribute('rx', '3');
            g.appendChild(rect);

            let y = n.y + 18;

            if (STEREOTYPES[n.stereotype]) {
                g.appendChild(this.texte(n.x + n.w / 2, y, STEREOTYPES[n.stereotype], 'schema-stereo', 'middle'));
                y += 18;
            }

            g.appendChild(this.texte(
                n.x + n.w / 2, y, n.name,
                'schema-nom' + (n.stereotype === 'abstraite' ? ' italique' : ''), 'middle'
            ));
            y += 10;

            /* Compartiment attributs */
            g.appendChild(this.separateur(n.x, y, n.w));
            y += 16;
            n.attributes.forEach((a) => {
                g.appendChild(this.texte(n.x + 10, y, a, 'schema-membre'));
                y += 18;
            });
            if (!n.attributes.length) y += 2;

            /* Compartiment méthodes */
            g.appendChild(this.separateur(n.x, y - 12, n.w));
            y += 4;
            n.methods.forEach((m) => {
                g.appendChild(this.texte(n.x + 10, y, m, 'schema-membre'));
                y += 18;
            });

            this.svg.appendChild(g);
        });

        /* Étiquettes de patron */
        this.etat.labels.forEach((l) => {
            const g = document.createElementNS(ns, 'g');
            g.dataset.labelId = l.id;
            g.setAttribute('class', 'schema-etiquette'
                + (this.estSelectionne('label', l.id) ? ' selection' : ''));

            const largeur = l.text.length * 7.6 + 18;
            const rect = document.createElementNS(ns, 'rect');
            rect.setAttribute('x', l.x); rect.setAttribute('y', l.y - 16);
            rect.setAttribute('width', largeur); rect.setAttribute('height', 26);
            rect.setAttribute('rx', '13');
            g.appendChild(rect);
            g.appendChild(this.texte(l.x + 10, l.y + 2, l.text, 'schema-etiquette-texte'));

            this.svg.appendChild(g);
        });

        /* Message d'accueil */
        if (!this.etat.nodes.length && !this.etat.labels.length) {
            const t = this.texte(600, 340,
                this.lectureSeule ? 'Aucun schéma rendu.' : 'Commencez par « Classe » ou « Interface ».',
                'schema-vide', 'middle');
            this.svg.appendChild(t);
        }
    }

    estSelectionne(type, id) {
        return this.selection && this.selection.type === type && this.selection.id === id;
    }

    texte(x, y, contenu, classe, ancre = 'start') {
        const t = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        t.setAttribute('x', x);
        t.setAttribute('y', y);
        t.setAttribute('class', classe);
        t.setAttribute('text-anchor', ancre);
        t.textContent = contenu;
        return t;
    }

    separateur(x, y, w) {
        const l = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        l.setAttribute('x1', x); l.setAttribute('y1', y);
        l.setAttribute('x2', x + w); l.setAttribute('y2', y);
        l.setAttribute('class', 'schema-separateur');
        return l;
    }

    /** Point où le segment centre-à-centre coupe le bord de la boîte. */
    bordure(boite, depuis, vers) {
        const dx = vers.x - depuis.x;
        const dy = vers.y - depuis.y;

        if (dx === 0 && dy === 0) return depuis;

        const demiL = boite.w / 2;
        const demiH = boite.h / 2;
        const echelle = Math.min(
            dx === 0 ? Infinity : demiL / Math.abs(dx),
            dy === 0 ? Infinity : demiH / Math.abs(dy)
        );

        return { x: depuis.x + dx * echelle, y: depuis.y + dy * echelle };
    }
}

export function initSchemas() {
    document.querySelectorAll('[data-schema]').forEach((el) => {
        if (!el.dataset.schemaPret) {
            el.dataset.schemaPret = '1';
            new EditeurSchema(el);
        }
    });
}