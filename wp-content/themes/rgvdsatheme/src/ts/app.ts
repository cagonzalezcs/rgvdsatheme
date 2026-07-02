// Tailwind v4 entry
import "../css/tailwind.css";

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
