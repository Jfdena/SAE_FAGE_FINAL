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


// JS DE JF

// Gestion du defilement de la video
// Gestion du changement de vidéo avec animations et navigation au clavier
document.addEventListener("DOMContentLoaded", function () {
  const videoContainer = document.querySelector(
    ".Actualite-Ressource-container2"
  );
  const videos = document.querySelectorAll(".background-video");
  const videoIndicators = document.querySelectorAll(".video-indicator");
  const changeVideoBtn = document.getElementById("changeVideoBtn");
  const videoTitle = document.getElementById("video-title");
  const videoDescription = document.getElementById("video-description");
  const videoContent = document.querySelector(".video-content");
  // Index de la vidéo actuellement affichée
  let currentVideoIndex = 0;
  const totalVideos = videos.length;

  // Données pour chaque vidéo
  const videoData = [
    {
      title: "AGORAE 2024",
      description: "Le grand rassemblement étudiant de la FAGE",
      buttonText: "Découvrir l'événement",
      buttonLink: "#evenements",
    },
    {
      title: "FEEL FESTIVAL",
      description: "Le festival étudiant pour l'engagement et la solidarité",
      buttonText: "Voir le programme",
      buttonLink: "#evenements",
    },
  ];

  // Fonction pour changer de vidéo
  function changeVideo(index) {
    // Animation de transition
    videoContent.classList.add("changing");

    setTimeout(() => {
      // Mettre à jour les vidéos
      videos.forEach((video, i) => {
        if (i === index) {
          video.classList.add("active");
          if (video.paused) {
            video.play();
          }
        } else {
          video.classList.remove("active");
          video.pause();
        }
      });

      // Mettre à jour les indicateurs
      videoIndicators.forEach((indicator, i) => {
        indicator.classList.toggle("active", i === index);
      });

      // Mettre à jour le contenu texte
      const data = videoData[index];
      videoTitle.textContent = data.title;
      videoDescription.textContent = data.description;

      const btn = document.querySelector(".btn-video");
      btn.textContent = data.buttonText;
      btn.href = data.buttonLink;

      // Fin de l'animation
      videoContent.classList.remove("changing");

      currentVideoIndex = index;
    }, 300);
  }

  // Bouton de changement de vidéo
  changeVideoBtn.addEventListener("click", function () {
    const nextIndex = (currentVideoIndex + 1) % totalVideos;
    changeVideo(nextIndex);
  });

  // Indicateurs de vidéo
  videoIndicators.forEach((indicator, index) => {
    indicator.addEventListener("click", function () {
      changeVideo(index);
    });
  });

  // Navigation au clavier
  document.addEventListener("keydown", function (e) {
    if (e.key === "ArrowLeft") {
      const prevIndex = (currentVideoIndex - 1 + totalVideos) % totalVideos;
      changeVideo(prevIndex);
    } else if (e.key === "ArrowRight") {
      const nextIndex = (currentVideoIndex + 1) % totalVideos;
      changeVideo(nextIndex);
    }
  });

  // Auto-rotation des vidéos
  let autoRotateInterval = setInterval(() => {
    const nextIndex = (currentVideoIndex + 1) % totalVideos;
    changeVideo(nextIndex);
  }, 5000); // Change toutes les 5 secondes

  // Arrêter l'auto-rotation quand la souris est sur la section
  videoContainer.addEventListener("mouseenter", function () {
    clearInterval(autoRotateInterval);
  });

  // Redémarrer l'auto-rotation quand la souris quitte la section
  videoContainer.addEventListener("mouseleave", function () {
    autoRotateInterval = setInterval(() => {
      const nextIndex = (currentVideoIndex + 1) % totalVideos;
      changeVideo(nextIndex);
    }, 5000);
  });
});

// Gestion du carrousel des événements FAGE
document.addEventListener("DOMContentLoaded", function () {
  // Sélection du conteneur du carrousel
  const carouselContainer = document.querySelector(".carousel-container");
  // Vérification de l'existence du carrousel
  if (!carouselContainer) return;
  // Sélection des éléments du carrousel
  const track = carouselContainer.querySelector(".carousel-track");
  const slides = Array.from(
    carouselContainer.querySelectorAll(".carousel-slide")
  );
  const prevBtn = carouselContainer.querySelector(".carousel-prev");
  const nextBtn = carouselContainer.querySelector(".carousel-next");
  const indicators = Array.from(
    carouselContainer.querySelectorAll(".indicator")
  );
  // État initial du carrousel
  let currentSlide = 0;
  const slideCount = slides.length;
  let visibleSlides = [...slides]; // Slides visibles après filtrage
  let currentFilter = "all"; // Filtre actuel

  // Fonction pour mettre à jour le carrousel
  function updateCarousel() {
    // Déplacer la piste
    track.style.transform = `translateX(-${currentSlide * 100}%)`;

    // Mettre à jour les indicateurs
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle("active", index === currentSlide);
      // Cacher les indicateurs des slides non visibles
      indicator.style.display = visibleSlides.includes(slides[index])
        ? "block"
        : "none";
    });

    // Mettre à jour les slides
    slides.forEach((slide, index) => {
      slide.classList.toggle("active", index === currentSlide);
    });
  }

  // Fonction pour filtrer les slides
  function filterSlides(category) {
    currentFilter = category;
    // Réinitialiser les slides visibles
    if (category === "all") {
      // Afficher toutes les slides
      slides.forEach((slide) => {
        slide.style.display = "flex";
      });
      visibleSlides = [...slides];
    } else {
      // Filtrer par catégorie
      slides.forEach((slide) => {
        const slideCategory = slide.dataset.category;
        if (slideCategory === category) {
          slide.style.display = "flex";
        } else {
          slide.style.display = "none";
        }
      });

      // Mettre à jour les slides visibles
      visibleSlides = slides.filter(
        (slide) => slide.dataset.category === category
      );
    }

    // Réinitialiser à la première slide visible
    currentSlide = 0;
    updateCarousel();
  }

  // Navigation suivante
  function nextSlide() {
    // Vérifier s'il y a des slides visibles
    if (visibleSlides.length === 0) return;
    // Avancer à la slide suivante
    currentSlide = (currentSlide + 1) % slideCount;

    // Si la slide actuelle n'est pas visible, trouver la suivante visible
    if (!visibleSlides.includes(slides[currentSlide])) {
      for (let i = 1; i < slideCount; i++) {
        const nextIndex = (currentSlide + i) % slideCount;
        if (visibleSlides.includes(slides[nextIndex])) {
          currentSlide = nextIndex;
          break;
        }
      }
    }
    // Mettre à jour le carrousel
    updateCarousel();
  }

  // Navigation précédente -$
  function prevSlide() {
    // Vérifier s'il y a des slides visibles
    if (visibleSlides.length === 0) return;
    // Reculer à la slide précédente
    currentSlide = (currentSlide - 1 + slideCount) % slideCount;

    // Si la slide actuelle n'est pas visible, trouver la précédente visible
    if (!visibleSlides.includes(slides[currentSlide])) {
      for (let i = 1; i < slideCount; i++) {
        const prevIndex = (currentSlide - i + slideCount) % slideCount;
        if (visibleSlides.includes(slides[prevIndex])) {
          currentSlide = prevIndex;
          break;
        }
      }
    }
    // Mettre à jour le carrousel
    updateCarousel();
  }

  // Aller à une slide spécifique
  function goToSlide(index) {
    // Vérifier s'il y a des slides visibles
    if (
      index >= 0 &&
      index < slideCount &&
      visibleSlides.includes(slides[index])
    ) {
      currentSlide = index;
      updateCarousel();
    }
  }

  // Événements des boutons
  if (prevBtn) {
    // Bouton précédent
    prevBtn.addEventListener("click", function (e) {
      e.preventDefault();
      prevSlide();
    });
  }
  // Événements du bouton suivant
  if (nextBtn) {
    // Bouton suivant
    nextBtn.addEventListener("click", function (e) {
      e.preventDefault();
      nextSlide();
    });
  }

  // Événements des indicateurs
  indicators.forEach((indicator, index) => {
    // Clic sur un indicateur
    indicator.addEventListener("click", function (e) {
      e.preventDefault();
      goToSlide(index);
    });
  });

  // Auto-play
  let autoPlayInterval;
  // Fonctions pour démarrer et arrêter l'auto-play
  function startAutoPlay() {
    // Arrêter l'auto-play existant
    autoPlayInterval = setInterval(() => {
      if (visibleSlides.length > 1) {
        // Seulement si plus d'une slide visible
        nextSlide();
      }
    }, 5000);
  }
  // Fonction pour arrêter l'auto-play
  function stopAutoPlay() {
    // Arrêter l'intervalle d'auto-play
    clearInterval(autoPlayInterval);
  }

  // Démarrer l'auto-play
  startAutoPlay();

  // Arrêter l'auto-play au survol
  carouselContainer.addEventListener("mouseenter", stopAutoPlay);
  carouselContainer.addEventListener("mouseleave", startAutoPlay);

  // Gestion des filtres d'événements
  const filtreBtns = document.querySelectorAll(".filtre-btn");
  // Événements des boutons de filtre
  filtreBtns.forEach((btn) => {
    // Clic sur un bouton de filtre
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      // Retirer la classe active de tous les boutons
      filtreBtns.forEach((b) => b.classList.remove("active"));

      // Ajouter la classe active au bouton cliqué
      this.classList.add("active");

      const filtre = this.dataset.filtre;

      // Redémarrer l'auto-play
      stopAutoPlay();

      // Appliquer le filtre
      filterSlides(filtre);

      // Redémarrer l'auto-play après un délai
      setTimeout(startAutoPlay, 100);
    });
  });

  // Initialiser le carrousel
  updateCarousel();
});

// Gesstion Filtre Actualités
// Fonctions de filtrage des actualités
document.addEventListener("DOMContentLoaded", function () {
  // Éléments du formulaire
  const newsTypesSelect = document.getElementById("news-types");
  const themesSelect = document.getElementById("themes");
  const actorsSelect = document.getElementById("actors");
  const monthsSelect = document.getElementById("months");

  // Données complètes des actualités
  const actualitesData = [
    {
      element: document.querySelector(
        '.actu-card[title*="Indicateur du coût de la rentrée étudiante 2025"]'
      ),
      date: "2025-09",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Résultats des élections CNESER 2025"]'
      ),
      date: "2025-06",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector('.actu-card[title*="Etats Généraux"]'),
      date: "2025-05",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Baromètre de la précarité étudiante"]'
      ),
      date: "2025-02",
      types: ["2", "3"],
      themes: ["1", "2", "3"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Un arrondi en caisse"]'
      ),
      date: "2024-09",
      types: ["2", "3"],
      themes: ["1", "2", "3"],
      actors: ["1", "2", "3", "4"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Indicateur du coût de la rentrée étudiante 2024"]'
      ),
      date: "2024-09",
      types: ["2", "3"],
      themes: ["1", "2", "3"],
      actors: ["1", "2", "3", "4"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="20 ans après le 21 avril"]'
      ),
      date: "2024-07",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3", "4"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Dégel des frais universitaires"]'
      ),
      date: "2024-05",
      types: ["2", "3"],
      themes: ["1", "3", "5"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector('.actu-card[title*="FEEL Festival"]'),
      date: "2024-06",
      types: ["2"],
      themes: ["5"],
      actors: ["2"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Repensons l\'éducation"]'
      ),
      date: "2024-03",
      types: ["1"],
      themes: ["5"],
      actors: ["1"],
    },
  ];

  // Fonction pour vérifier si un tableau contient une valeur
  function arrayContains(array, value) {
    return array.includes(value);
  }

  // Fonction pour réinitialiser TOUTES les bordures
  function resetAllHighlights() {
    actualitesData.forEach((actualite) => {
      actualite.element.classList.remove("filter-highlight");
    });
  }

  // Fonction pour appliquer les bordures selon TOUS les filtres ACTUELS
  function highlightActualites() {
    const selectedType = newsTypesSelect.value;
    const selectedTheme = themesSelect.value;
    const selectedActor = actorsSelect.value;
    const selectedDate = monthsSelect.value;

    console.log("Filtres actuels:", {
      type: selectedType,
      theme: selectedTheme,
      actor: selectedActor,
      date: selectedDate,
    });

    // D'abord, réinitialiser TOUTES les bordures
    resetAllHighlights();

    // Si aucun filtre n'est sélectionné, on s'arrête là
    if (!selectedType && !selectedTheme && !selectedActor && !selectedDate) {
      console.log("Aucun filtre sélectionné - bordures réinitialisées");
      return;
    }

    // Ensuite, appliquer les bordures rouges aux éléments correspondants à TOUS les filtres
    actualitesData.forEach((actualite) => {
      let matchesAllSelectedFilters = true;

      // Vérifier le type - seulement si un type est sélectionné
      if (selectedType) {
        if (!arrayContains(actualite.types, selectedType)) {
          matchesAllSelectedFilters = false;
        }
      }

      // Vérifier le thème - seulement si un thème est sélectionné
      if (selectedTheme) {
        if (!arrayContains(actualite.themes, selectedTheme)) {
          matchesAllSelectedFilters = false;
        }
      }

      // Vérifier l'acteur - seulement si un acteur est sélectionné
      if (selectedActor) {
        if (!arrayContains(actualite.actors, selectedActor)) {
          matchesAllSelectedFilters = false;
        }
      }

      // Vérifier la date - seulement si une date est sélectionnée
      if (selectedDate) {
        if (actualite.date !== selectedDate) {
          matchesAllSelectedFilters = false;
        }
      }

      // Appliquer la bordure rouge seulement si l'élément correspond à TOUS les filtres sélectionnés
      if (matchesAllSelectedFilters) {
        actualite.element.classList.add("filter-highlight");
        console.log(
          "Bordure ajoutée à:",
          actualite.element.querySelector("h4").textContent
        );
      }
    });

    console.log("Mise à jour des bordures terminée");
  }

  // Fonction pour réinitialiser les filtres
  function resetFilters() {
    newsTypesSelect.value = "";
    themesSelect.value = "";
    actorsSelect.value = "";
    monthsSelect.value = "";
    resetAllHighlights();
    console.log("Tous les filtres réinitialisés");
  }

  // Mise à jour automatique quand on change un filtre
  [newsTypesSelect, themesSelect, actorsSelect, monthsSelect].forEach(
    (select) => {
      select.addEventListener("change", function () {
        console.log("Changement détecté dans:", select.id);
        highlightActualites();
      });
    }
  );

  // Ajouter un bouton de réinitialisation
  if (!document.querySelector(".btn-reset-container")) {
    const resetContainer = document.createElement("div");
    resetContainer.className = "Conteneur-btn-filtre btn-reset-container";
    resetContainer.innerHTML =
      '<button class="btn-reset-filters" onclick="resetFilters()">Réinitialiser les filtres</button>';
    document.querySelector(".filtreTotal").appendChild(resetContainer);
  }

  // Exposer les fonctions globalement
  window.resetFilters = resetFilters;

  // Initialisation
  console.log("Système de filtrage corrigé initialisé");
});
// Gestion du modal des actualités
// Données détaillées des actualités
const actualitesDetails = {
  "Indicateur du coût de la rentrée étudiante 2025": {
    date: "03/09/2025",
    image: "../assets/img/fageActuImg0.png",
    content: `
            <p><strong>Cette nouvelle rentrée se présente comme un coup de massue supplémentaire pour le public étudiant.</strong> Les constats de ce 23ème indicateur du coût de la rentrée étudiante sont dramatiques et intimement liés à un contexte évident d'instabilité politique.</p>
            <p>Via cet indicateur à la méthode fiable et complète, la FAGE tient à mettre en avant les chiffres qui reflètent la situation de pauvreté sans appel connue par des milliers de jeunes.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Innovation sociale, Fage, Fédés territoriales, Fédés de filière</em></p>
        `,
  },
  "Résultats des élections CNESER 2025": {
    date: "06/06/2025",
    image: "../assets/img/fageActuImg1.png",
    content: `
            <p><strong>Pour le 5ème scrutin consécutif, la FAGE confirme largement sa place de 1ère ORE de France.</strong></p>
            <p>Cette victoire démontre la confiance que les étudiants accordent à notre organisation pour les représenter et défendre leurs droits.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Innovation sociale, Fage, Fédés territoriales, Fédés de filière</em></p>
        `,
  },
  "Etats Généraux": {
    date: "07/05/2025",
    image: "../assets/img/fageActuImg2.png",
    content: `
            <p><strong>Près de 3000 jeunes se sont expriméEs sur leur rapport à la démocratie et à l'engagement en France !</strong></p>
            <p>Nous publions aujourd'hui le dossier de presse des résultats des États Généraux de la démocratie et de l'engagement des jeunes : une consultation nationale menée sur plusieurs mois auprès de près de 3000 jeunes de 16 à 30 ans.</p>
            <p>Cette consultation unique a permis de recueillir la parole des jeunes sur leur vision de la démocratie, leur engagement citoyen et leurs attentes pour l'avenir.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Innovation sociale, Fage, Fédés territoriales, Fédés de filière</em></p>
        `,
  },
  "Baromètre de la précarité étudiante": {
    date: "19/02/2025",
    image: "../assets/img/fageActuImg3.png",
    content: `
            <p><strong>Enquête réalisée auprès des bénéficiaires des AGORAé, épiceries sociales et solidaires étudiantes de la FAGE.</strong></p>
            <p>Le baromètre de la précarité étudiante met en lumière la situation critique de cette population, touchée de plein fouet par une précarité grandissante.</p>
            <p>En 2024, l'indicateur du coût de la rentrée de la FAGE dépasse le montant alarmant des 3000€, poussée par une hausse des frais de vie courante. Pour la majorité des étudiantEs, étudier dans des conditions de vie dignes devient un luxe.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Fage, Fédés territoriales, Fédés de filière</em></p>
        `,
  },
  "Un arrondi en caisse": {
    date: "19/02/2025",
    image: "../assets/img/fageActuImg4.png",
    content: `
            <p><strong>Une initiative solidaire pour lutter contre la précarité étudiante.</strong></p>
            <p>Ce projet innovant permet de collecter des fonds via l'arrondi en caisse dans les commerces partenaires, afin de financer des milliers de repas pour les étudiants en situation de précarité.</p>
            <p>Chaque centime compte et contribue à améliorer le quotidien des étudiants les plus démunis.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Fage, Fédés territoriales, Fédés de filière, Membres associés</em></p>
        `,
  },
  "Indicateur du coût de la rentrée étudiante 2024": {
    date: "04/09/2024",
    image: "../assets/img/fageActuImg5.png",
    content: `
            <p><strong>Vingt-et-un ans après sa toute première édition, la FAGE publie une nouvelle édition de son indicateur du coût de la rentrée et du coût de la vie pour un·e étudiant·e.</strong></p>
            <p>Pour la 21ème année consécutive, notre indicateur se base sur la même méthodologie et les mêmes critères, faisant de lui l'unique outil fiable venant recenser l'évolution des différentes dépenses auxquelles un·e étudiant·e doit faire face, tout en mettant en avant les conséquences que cela induit mais également les solutions qu'il est essentiel d'apporter.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Fage, Fédés territoriales, Fédés de filière, Membres associés</em></p>
        `,
  },
  "20 ans après le 21 avril": {
    date: "01/07/2024",
    image: "../assets/img/fageActuImg6.png",
    content: `
            <p><strong>Il y 20 ans, l'extrême droite arrivait pour la 1ère fois au 2nd tour de la présidentielle.</strong></p>
            <p>20 ans après, les étudiant·e·s restent mobilisé·e·s contre l'extrême droite. La FAGE, aux côtés d'autres organisations, appelle les étudiant·e·s à se rassembler Place du Panthéon jeudi 21 avril à 17h30 pour faire barrage à Marine Le Pen.</p>
            <p>Notre engagement pour les valeurs républicaines et démocratiques reste plus que jamais d'actualité.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Social, Jeunesse, Innovation sociale, Fage, Fédés territoriales, Fédés de filière, Membres associés</em></p>
        `,
  },
  "Dégel des frais universitaires": {
    date: "28/05/2024",
    image: "../assets/img/fageActuImg7.png",
    content: `
            <p><strong>Pour la rentrée 2024-2025, les frais universitaires en France ne seront plus gelés.</strong></p>
            <p>Cette décision aura un impact néfaste sur l'accès à l'enseignement supérieur pour des centaines de milliers d'étudiantEs qui devront s'acquitter de 175€ pour une inscription en licence (+5€), 250€ en master (+7€) et 391€ en doctorat (+11€).</p>
            <p>Cette mesure représente un frein supplémentaire à l'accès aux études supérieures pour les étudiants les plus précaires.</p>
            <p><em>Actualité, CP et DP, Enseignement supérieur, Jeunesse, Formations, Fage, Fédés territoriales, Fédés de filière</em></p>
        `,
  },
  "FEEL Festival - 2 juin 2024": {
    date: "02/06/2024",
    image: "../assets/img/fageActuImg8.png",
    content: `
            <p><strong>Le FEEL Festival, événement phare de la vie étudiante, revient pour une nouvelle édition.</strong></p>
            <p>Au programme : concerts, ateliers, débats et rencontres autour des thématiques de l'engagement étudiant et de la solidarité.</p>
            <p>Un moment unique de partage et de convivialité pour tous les étudiants engagés dans la vie associative.</p>
            <p><em>Actualité, Formations, Fédés territoriales</em></p>
        `,
  },
  "Repensons l'éducation": {
    date: "22/05/2024",
    image: "../assets/img/fageActuImg9.png",
    content: `
            <p><strong>Repensons l'éducation à la citoyenneté pour relever les défis de la crise démocratique.</strong></p>
            <p>Le conseil supérieur de l'éducation était consulté ce mercredi 22 mai sur les nouveaux programmes de l'enseignement moral et civique, du niveau école au niveau lycée, une mesure annoncée à la rentrée 2023.</p>
            <p>La FAGE participe activement à cette réflexion pour promouvoir une éducation à la citoyenneté plus inclusive et adaptée aux enjeux contemporains.</p>
            <p><em>Interviews, Formations, Fage</em></p>
        `,
  },
};

// Gestion du filtrage des actualités
document.addEventListener("DOMContentLoaded", function () {
  // Éléments du formulaire
  const newsTypesSelect = document.getElementById("news-types");
  const themesSelect = document.getElementById("themes");
  const actorsSelect = document.getElementById("actors");
  const monthsSelect = document.getElementById("months");

  // Données complètes des actualités pour le filtrage
  const actualitesData = [
    {
      element: document.querySelector(
        '.actu-card[title*="Indicateur du coût de la rentrée étudiante 2025"]'
      ),
      date: "2025-09",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Résultats des élections CNESER 2025"]'
      ),
      date: "2025-06",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector('.actu-card[title*="Etats Généraux"]'),
      date: "2025-05",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Baromètre de la précarité étudiante"]'
      ),
      date: "2025-02",
      types: ["2", "3"],
      themes: ["1", "2", "3"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Un arrondi en caisse"]'
      ),
      date: "2024-09",
      types: ["2", "3"],
      themes: ["1", "2", "3"],
      actors: ["1", "2", "3", "4"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Indicateur du coût de la rentrée étudiante 2024"]'
      ),
      date: "2024-09",
      types: ["2", "3"],
      themes: ["1", "2", "3"],
      actors: ["1", "2", "3", "4"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="20 ans après le 21 avril"]'
      ),
      date: "2024-07",
      types: ["2", "3"],
      themes: ["1", "2", "3", "4"],
      actors: ["1", "2", "3", "4"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Dégel des frais universitaires"]'
      ),
      date: "2024-05",
      types: ["2", "3"],
      themes: ["1", "3", "5"],
      actors: ["1", "2", "3"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="FEEL Festival - 2 juin 2024"]'
      ),
      date: "2024-06",
      types: ["2"],
      themes: ["5"],
      actors: ["2"],
    },
    {
      element: document.querySelector(
        '.actu-card[title*="Repensons l\'éducation"]'
      ),
      date: "2024-03",
      types: ["1"],
      themes: ["5"],
      actors: ["1"],
    },
  ];

  // Fonction pour vérifier si un tableau contient une valeur
  function arrayContains(array, value) {
    return array.includes(value);
  }

  // Fonction pour réinitialiser TOUTES les bordures
  function resetAllHighlights() {
    actualitesData.forEach((actualite) => {
      actualite.element.classList.remove("filter-highlight");
    });
  }

  // Fonction pour appliquer les bordures selon TOUS les filtres ACTUELS
  function highlightActualites() {
    const selectedType = newsTypesSelect.value;
    const selectedTheme = themesSelect.value;
    const selectedActor = actorsSelect.value;
    const selectedDate = monthsSelect.value;

    // D'abord, réinitialiser TOUTES les bordures
    resetAllHighlights();

    // Si aucun filtre n'est sélectionné, on s'arrête là
    if (!selectedType && !selectedTheme && !selectedActor && !selectedDate) {
      return;
    }

    // Ensuite, appliquer les bordures rouges aux éléments correspondants à TOUS les filtres
    actualitesData.forEach((actualite) => {
      let matchesAllSelectedFilters = true;

      // Vérifier le type - seulement si un type est sélectionné
      if (selectedType) {
        if (!arrayContains(actualite.types, selectedType)) {
          matchesAllSelectedFilters = false;
        }
      }

      // Vérifier le thème - seulement si un thème est sélectionné
      if (selectedTheme) {
        if (!arrayContains(actualite.themes, selectedTheme)) {
          matchesAllSelectedFilters = false;
        }
      }

      // Vérifier l'acteur - seulement si un acteur est sélectionné
      if (selectedActor) {
        if (!arrayContains(actualite.actors, selectedActor)) {
          matchesAllSelectedFilters = false;
        }
      }

      // Vérifier la date - seulement si une date est sélectionnée
      if (selectedDate) {
        if (actualite.date !== selectedDate) {
          matchesAllSelectedFilters = false;
        }
      }

      // Appliquer la bordure rouge seulement si l'élément correspond à TOUS les filtres sélectionnés
      if (matchesAllSelectedFilters) {
        actualite.element.classList.add("filter-highlight");
      }
    });
  }

  // Fonction pour réinitialiser les filtres
  function resetFilters() {
    newsTypesSelect.value = "";
    themesSelect.value = "";
    actorsSelect.value = "";
    monthsSelect.value = "";
    resetAllHighlights();
  }

  // Mise à jour automatique quand on change un filtre
  [newsTypesSelect, themesSelect, actorsSelect, monthsSelect].forEach(
    (select) => {
      select.addEventListener("change", highlightActualites);
    }
  );

  // Gestion des clics sur les cartes d'actualités
  const actuModal = document.getElementById("actuModal");
  const closeModalBtn = document.getElementById("closeActuModal");
  const actuCards = document.querySelectorAll(".actu-card");

  // Fonction pour ouvrir la modal
  function openActuModal(title) {
    const details = actualitesDetails[title];
    if (!details) return;

    // Remplir la modal avec les données
    document.getElementById("modal-title").textContent = title;
    document.getElementById("modal-date").textContent = details.date;
    document.getElementById("modal-image").src = details.image;
    document.getElementById("modal-image").alt = title;
    document.getElementById("modal-text").innerHTML = details.content;

    // Afficher la modal
    actuModal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  // Fonction pour fermer la modal
  function closeActuModal() {
    actuModal.classList.remove("active");
    document.body.style.overflow = "";
  }

  // Ajouter les événements de clic sur chaque carte
  actuCards.forEach((card) => {
    card.style.cursor = "pointer";
    card.addEventListener("click", function () {
      const title = this.getAttribute("title");
      openActuModal(title);
    });
  });

  // Fermer la modal avec le bouton
  closeModalBtn.addEventListener("click", closeActuModal);

  // Fermer en cliquant en dehors
  actuModal.addEventListener("click", function (e) {
    if (e.target === actuModal) {
      closeActuModal();
    }
  });

  // Fermer avec la touche Échap
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && actuModal.classList.contains("active")) {
      closeActuModal();
    }
  });

  // Exposer les fonctions globalement
  window.resetFilters = resetFilters;
  window.closeActuModal = closeActuModal;

  // Initialisation
  console.log("Système des actualités initialisé");
});
// Gestion de la modal du blog
//  Blog Modal
document.addEventListener("DOMContentLoaded", function () {
  const blogButton = document.querySelector('a[href="#blog-fage"]');
  const closeBlogModal = document.getElementById("closeBlogModal");
  const blogModal = document.getElementById("blogModal");
  const articles = document.querySelectorAll(".blog-article");
  const indicators = document.querySelectorAll(".blog-indicator-simple");
  const prevBtn = document.querySelector(".blog-prev");
  const nextBtn = document.querySelector(".blog-next");

  let currentArticle = 0;

  // Ouvrir la modal
  if (blogButton) {
    blogButton.addEventListener("click", function (e) {
      e.preventDefault();
      blogModal.classList.add("active");
      document.body.style.overflow = "hidden";
      showArticle(currentArticle);
    });
  }

  // Fermer la modal
  closeBlogModal.addEventListener("click", function () {
    blogModal.classList.remove("active");
    document.body.style.overflow = "";
  });

  // Fermer en cliquant en dehors
  blogModal.addEventListener("click", function (e) {
    if (e.target === blogModal) {
      blogModal.classList.remove("active");
      document.body.style.overflow = "";
    }
  });

  // Navigation
  function showArticle(index) {
    // Mettre à jour l'affichage de l'article
    articles.forEach((article) => article.classList.remove("active"));
    indicators.forEach((indicator) => indicator.classList.remove("active"));

    articles[index].classList.add("active");
    indicators[index].classList.add("active");
    currentArticle = index;
  }

  // Boutons navigation
  if (prevBtn)
    prevBtn.addEventListener("click", () => {
      currentArticle = (currentArticle - 1 + articles.length) % articles.length;
      showArticle(currentArticle);
    });
  // Bouton suivant
  if (nextBtn)
    nextBtn.addEventListener("click", () => {
      currentArticle = (currentArticle + 1) % articles.length;
      showArticle(currentArticle);
    });

  // Indicateurs
  indicators.forEach((indicator, index) => {
    indicator.addEventListener("click", () => showArticle(index));
  });

  // Clavier
  document.addEventListener("keydown", function (e) {
    // Vérifier si la modal est ouverte
    if (!blogModal.classList.contains("active")) return;
    // Navigation avec les flèches
    if (e.key === "ArrowLeft") {
      currentArticle = (currentArticle - 1 + articles.length) % articles.length;
      showArticle(currentArticle);
    } else if (e.key === "ArrowRight") {
      currentArticle = (currentArticle + 1) % articles.length;
      showArticle(currentArticle);
    } else if (e.key === "Escape") {
      blogModal.classList.remove("active");
      document.body.style.overflow = "";
    }
  });
});
// Contact Modal
document.addEventListener("DOMContentLoaded", function () {
  // Sélection des éléments
  const contactButtons = document.querySelectorAll(
    'a[href="#contact"], .btn-contact-faq, .btn-contact'
  );
  const closeContactModal = document.getElementById("closeContactModal");
  const contactModal = document.getElementById("contactModal");

  // Ouvrir la modal
  contactButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      contactModal.classList.add("active");
      document.body.style.overflow = "hidden";
    });
  });

  // Fermer la modal
  closeContactModal.addEventListener("click", function () {
    contactModal.classList.remove("active");
    document.body.style.overflow = "";
  });

  // Fermer en cliquant en dehors
  contactModal.addEventListener("click", function (e) {
    if (e.target === contactModal) {
      contactModal.classList.remove("active");
      document.body.style.overflow = "";
    }
  });

  // Fermer avec Échap
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && contactModal.classList.contains("active")) {
      contactModal.classList.remove("active");
      document.body.style.overflow = "";
    }
  });
});











