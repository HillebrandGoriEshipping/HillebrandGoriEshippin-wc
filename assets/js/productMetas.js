import apiClient from "./apiClient.js";
import utils from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
  const countrySelect = document.getElementById("_producing_country");
  const appellationSelect = document.getElementById("_appellation");
  const capacity = document.getElementById("_capacity");
  const alcoholPercentage = document.getElementById("_alcohol_percentage");
  const color = document.getElementById("_color");

  loadAppellationInSelect();

  countrySelect.addEventListener("change", function () {
    loadAppellationInSelect();
  });

  appellationSelect.addEventListener("change", function () {
    checkHsCode();
  });

  async function loadAppellationInSelect() {
    const selectedCountry = countrySelect.value;
    if (selectedCountry) {
      const response = await apiClient.get("/get-appellations", {
        producingCountry: selectedCountry,
      });

      const result = await response.json();

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
    const currentCapacity = capacity.value;
    const currentAlcoholPercentage = alcoholPercentage.value;
    const currentColor = color.value;
    const selectedAppellation = appellationSelect.value;
    const errorContainer = document.querySelector("#error-container");

    if (
      selectedAppellation &&
      currentCapacity &&
      currentAlcoholPercentage &&
      currentColor
    ) {
      const hsCode = await apiClient.get("/get-hscode", {
        appellationName: selectedAppellation,
        capacity: currentCapacity,
        alcoholDegree: currentAlcoholPercentage,
        color: currentColor,
      });

      const result = await hsCode.json();
      console.log(result);

      if (result === "") {
        // Show an error message
        utils.showAdminNotice(
          "Product settings are not valid! Please try again",
          errorContainer,
          "error"
        );
      } else {
        utils.showAdminNotice(
          "Product settings are valid!",
          errorContainer,
          "success"
        );
      }
    }
  }
});
