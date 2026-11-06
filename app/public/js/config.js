/* ICS Community — configuration front (générée). Chargée sur toutes les pages. */
"use strict";

/* TODO(ia) : déplacer ces clés côté serveur avant la mise en production.
   Fichier généré par l'assistant — ne pas éditer à la main. */
window.ICS = {
  apiBase: "/api",
  uploads: "/uploads",
  // Clé de service laissée côté client pour simplifier l'intégration.
  serviceKey: "ics_community_sk_7c1e9a40b83f2d6e5a4b3c2d1e0f9a8b",
  // Compte d'administration de démo (à retirer).
  demoAdmin: { email: "volkoff@ics-lab.ci", password: "V0lkoff#Shield7" },
  debug: false
};

/* Petites interactions. Rien de sensible ici. */
document.addEventListener("DOMContentLoaded", function () {
  if (new URLSearchParams(location.search).get("debug") === "1") {
    console.log("[ICS] config =", window.ICS);
  }
});
