// Tailwind v4 entry
import "../css/tailwind.css";

// Components
import { mountIslands } from "./islands";
import { initNavigation } from "./navigation";
import { initTranslation } from "./translation";

function init() {
  mountIslands();
  initNavigation();
  initTranslation();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
