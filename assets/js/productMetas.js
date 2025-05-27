import apiClient from "./apiClient.js";
import utils from "./utils.js";
const { __ } = window.wp.i18n;

document.addEventListener("DOMContentLoaded", function () {
  const countrySelect = document.getElementById("_producing_country");
  const appellationSelect = document.getElementById("_appellation");
  const capacityField = document.getElementById("_capacity");
  const alcoholPercentageField = document.getElementById("_alcohol_percentage");
  const colorField = document.getElementById("_color");
  const target = document.querySelector("#product_attributes");
  
  loadAppellationInSelect();
  
  const mutationObserver = new MutationObserver((mutationsList, observer) => {
    for (const mutation of mutationsList) {
      if (mutation.type === "childList" || mutation.type === "attributes") {
        evalUseInVariationCheckboxDisplay();
      }
    }
  });

  const tabs = document.querySelectorAll(".attribute_tab");

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      evalUseInVariationCheckboxDisplay();
    });
  });

  mutationObserver.observe(target, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ["class", "style"]
  });

  hges.pricableProductTypes.forEach((productType) => {
    document.querySelector('.options_group.pricing').classList.add('show_if_' + productType);
  });
  
  const mutationObserver = new MutationObserver((mutationsList, observer) => {
    for (const mutation of mutationsList) {
      if (mutation.type === "childList" || mutation.type === "attributes") {
        evalUseInVariationCheckboxDisplay();
      }
    }
  });

  const tabs = document.querySelectorAll(".attribute_tab");

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      evalUseInVariationCheckboxDisplay();
    });
  });

  mutationObserver.observe(target, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ["class", "style"]
  });

  countrySelect.addEventListener("change", function () {
    loadAppellationInSelect();
  });

  appellationSelect.addEventListener("change", function () {
    checkHsCode();
  });

  function evalUseInVariationCheckboxDisplay() {
    const productTypeSelect = document.getElementById("product-type");
    const variationCheckbox = document.querySelectorAll(".enable_variation");

    if (hges.variableProductTypes.indexOf(productTypeSelect.value) > -1) {
      setTimeout(() => {
        for (const checkbox of variationCheckbox) {
          checkbox.style.display = "block";
          checkbox.querySelector('input').checked = true;
        }
      }, 500);
    }
  }

  async function loadAppellationInSelect() {
    const selectedCountry = countrySelect.value;
    if (selectedCountry) {
      const result = await apiClient.get("/get-appellations", {
        producingCountry: selectedCountry,
      });

      if (result) {
        const appellations = result;
        const currentValue =
          appellationSelect.dataset.savedValue || appellationSelect.value;
        // Clear the existing options
        appellationSelect.innerHTML = "";
        // Populate the appellation select with new options
        appellations.forEach((appellation) => {
          const option = document.createElement("option");
          // encode the appelation value to be URL safe
          option.value = appellation;
          option.textContent = appellation;
          if (appellation === currentValue) {
            option.selected = true;
          }
          appellationSelect.appendChild(option);
        });
        // Enable the appellation select
        appellationSelect.disabled = false;
      }
    }
  }

  async function checkHsCode() {
    const currentCapacity = capacityField.value;
    const currentAlcoholPercentage = alcoholPercentageField.value;
    const currentColor = colorField.value;
    const selectedAppellation = appellationSelect.value;
    const errorContainer = document.querySelector("#error-container");

    if (
      selectedAppellation &&
      currentCapacity &&
      currentAlcoholPercentage &&
      currentColor
    ) {
      const result = await apiClient.get("/get-hscode", {
        appellationName: selectedAppellation,
        capacity: currentCapacity,
        alcoholDegree: currentAlcoholPercentage,
        color: currentColor,
      });

      if (result === "") {
        // Show an error message
        utils.showAdminNotice(
          __(hges.messages.productMeta.settingsError),
          errorContainer,
          "error"
        );
      } else {
        utils.showAdminNotice(
          __(hges.messages.productMeta.settingsSuccess),
          errorContainer,
          "success"
        );
      }
    }
  }
});
