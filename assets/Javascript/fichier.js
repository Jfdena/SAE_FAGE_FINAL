// Attendre que le DOM soit chargé avant d'exécuter tout le code
window.addEventListener('DOMContentLoaded', () => {

    // --- Apparition du titre et du sous-titre ---
    const titre = document.getElementById('Titre-Principal'); // Récupère l'élément <h1> par son ID//
    const soustitre = document.getElementById('Sous-Titre'); // Récupère l'élément <h2> par son ID

    if (titre) titre.classList.add('visible'); // Si le titre existe, on lui ajoute la classe 'visible' -> déclenche la transition CSS

    // Si le sous-titre existe, on lui ajoute la classe 'visible' avec un délai
    // Cela crée un effet d'apparition décalée entre le titre et le sous-titre
    if (soustitre) {
        setTimeout(() => {
            soustitre.classList.add('visible');
        }, 800);
    }

    // --- Boutons d'inscription et de don ---
    const btnInscription = document.getElementById('btn-inscription');//Bouton d'incription
    const btnDon = document.getElementById('btn-don'); //Bouton de don

    if (btnInscription) {
        // Quand on clique sur le bouton "Inscription", on redirige vers la page d'inscription
        btnInscription.addEventListener('click', function() {
            window.location.href = 'mettre lien page inscription'; // Remplace par ton vrai lien que wallid te donne
        });
    }

    if (btnDon) {
        //Pareil mais pour don
        btnDon.addEventListener('click', function() {
            window.location.href = 'Dons_Engagement.html'; // Remplace par ton vrai lien
        });
    }

    // --- Bandeau Cookies ---
    const afficher = document.getElementById('afficher-banniere'); //bouton afficher la banniere
    const banniere = document.getElementById('banniere-cookies'); //bloc de la banniere cookies
    const accepter = document.getElementById('accepter'); //bouton accepter
    const refus = document.getElementById('refus'); //bouton refuser

    afficher.addEventListener('click', () => {
        // bouton la visibilité
        banniere.classList.toggle('hidden'); // Ajoute ou retire la classe 'hidden'
    });

    // Si on clique sur "Accepter", on masque la bannière et le bouton
    if (accepter) {
        accepter.addEventListener('click', () => {
            banniere.classList.add('hidden'); // Cache la bannière
            afficher.style.display = 'none'; // Cache le bouton pour ne pas le revoir
        });
    }
    // Si on clique sur "Refuser", même comportement que "Accepter" ici
    if (refus) {
        refus.addEventListener('click', () => {
            banniere.classList.add('hidden');
            afficher.style.display = 'none'; // On cache le bouton
        });

        const counters = document.querySelectorAll('.Nombre');
        let started = false; // empêche que l'animation se rejoue

        function animateCounters() {
            if (!started && window.scrollY + window.innerHeight >= document.querySelector('#Chiffres').offsetTop) {
                started = true;

                counters.forEach(counter => {
                    const updateCount = () => {
                        const target = +counter.getAttribute('data-target');
                        const count = +counter.innerText;
                        const speed = 200; // plus grand = plus lent
                        const increment = target / speed;

                        if (count < target) {
                            counter.innerText = Math.ceil(count + increment);
                            setTimeout(updateCount, 10);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };
                    updateCount();
                });
            }
        }

        window.addEventListener('scroll', animateCounters);

    }})

document.addEventListener('DOMContentLoaded', function() {
    let currentSlide = 0;

    // Sélectionner TOUTES les cartes (y compris la première)
    const premiereCarte = document.querySelector('.temoignage-carte_premiere');
    const autresCartes = document.querySelectorAll('.temoignage-carte');

    // Combiner toutes les cartes dans un seul tableau
    const cards = [premiereCarte, ...autresCartes];

    const indicators = document.querySelectorAll('.indicateur');
    const totalSlides = cards.length;

    function showSlide(n) {
        if (n >= totalSlides) {
            currentSlide = 0;
        } else if (n < 0) {
            currentSlide = totalSlides - 1;
        } else {
            currentSlide = n;
        }

        // Cacher toutes les cartes
        cards.forEach(card => {
            card.style.display = 'none';
            card.classList.remove('active');
        });

        // Désactiver tous les indicateurs
        indicators.forEach(ind => ind.classList.remove('active'));

        // Afficher la carte actuelle
        cards[currentSlide].style.display = 'block';
        cards[currentSlide].classList.add('active');

        // Activer l'indicateur correspondant
        indicators[currentSlide].classList.add('active');
    }

    // Exposer les fonctions globalement pour les onclick
    window.changementSlide = function(direction) {
        currentSlide += direction;
        showSlide(currentSlide);
    }

    window.goToSlide = function(n) {
        currentSlide = n;
        showSlide(currentSlide);
    }

    // Initialiser l'affichage
    showSlide(0);
});











