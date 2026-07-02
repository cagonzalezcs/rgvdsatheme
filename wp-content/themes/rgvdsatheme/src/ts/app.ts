// Tailwind v4 entry (must precede legacy SCSS so legacy rules win during migration)
import "../css/tailwind.css";

// Web Awesome Imports
import "@awesome.me/webawesome/dist/styles/webawesome.css";
import "@awesome.me/webawesome/dist/components/button/button.js";
import "@awesome.me/webawesome/dist/components/input/input.js";

// Custom Styles
import "../scss/app.scss";

// Components
import { initSiteHeader } from "./components/SiteHeader";
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initSiteHeader);
} else {
  initSiteHeader();
}
