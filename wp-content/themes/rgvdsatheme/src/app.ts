import "./styles/tailwind.css";
import "./styles/app.scss";

import { mountIslands } from "@/lib/islands.ts";

function boot() {
  mountIslands();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", boot);
} else {
  boot();
}
