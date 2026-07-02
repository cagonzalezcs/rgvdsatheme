/** @type {import("stylelint").Config} */
export default {
  extends: ["stylelint-config-standard-scss"],
  rules: {
    // Allow BEM class names (block__element--modifier).
    "selector-class-pattern": [
      "^[a-z][a-z0-9]*(-[a-z0-9]+)*(__[a-z0-9]+(-[a-z0-9]+)*)?(--[a-z0-9]+(-[a-z0-9]+)*)?$",
      { message: "Expected class selector to follow BEM naming" },
    ],
    // Allow WP core custom properties (e.g. --wp-admin--admin-bar--height).
    "custom-property-pattern": "^[a-z][a-z0-9]*(-{1,2}[a-z0-9]+)*$",
  },
};
