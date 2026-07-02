// Tailwind v4 entry (must precede legacy SCSS so legacy rules win during migration)
import "../css/tailwind.css";

// Custom Styles
import "../scss/app.scss";

// Components
import { mountIslands } from "./islands";

function init() {
  mountIslands();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
