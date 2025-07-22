import apiClient from "./apiClient.js";
import utils from "./utils.js";
const { __ } = window.wp.i18n;

document.addEventListener("DOMContentLoaded", function () {
  const countrySelect = document.querySelector("#_producing_country");
  const appellationSelect = document.querySelector("#appellation-select-field");
  const appellationField = document.querySelector("#_appellation");
  const capacityField = document.querySelector("#_capacity");
  const alcoholPercentageField = document.querySelector("#_alcohol_percentage");
  const colorField = document.querySelector("#_color");
  const target = document.querySelector("#product_attributes");
  const publishButton = document.querySelector("#publish");
  const hsCodeField = document.querySelector("#_hs_code");
  const selectType = document.querySelector("#product-type");
  
  loadAppellationInSelect();

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

  function isBottleProduct() {
    const productTypeSelect = document.querySelector("#product-type");
    if (!productTypeSelect) return false;

    const bottleTypes = ["bottle-simple", "bottle-variable"];
    return bottleTypes.includes(productTypeSelect.value);
  }


  function togglePublishButton() {
    if (!hsCodeField || !publishButton) return;

      removeCustomPublishError();

    // Block the button if the product is not a bottle type
    if (!isBottleProduct()) {
      publishButton.disabled = false;
      publishButton.classList.remove("button-disabled");
      publishButton.removeAttribute("title");
      return;
    }

    if (hsCodeField.value.trim() === "") {
      publishButton.disabled = true;
      publishButton.classList.add("button-disabled");
      showCustomPublishError(__(
        hges.messages.productMeta.preventPublish,
      ));
    } else {
      publishButton.disabled = false;
      publishButton.classList.remove("button-disabled");
      publishButton.removeAttribute("title");
    }
  }

  // Block publish/update button if HS code is empty
  togglePublishButton();

  // Check every time the HS code field changes
  hsCodeField.addEventListener("change", togglePublishButton);
  selectType.addEventListener("change", togglePublishButton);

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
      const hsCode = await apiClient.get("/get-hscode", {
        appellationName: selectedAppellation,
        capacity: currentCapacity,
        alcoholDegree: currentAlcoholPercentage,
        color: currentColor,
      });

      const hsCodeField = document.querySelector("#_hs_code");

      if (hsCode) {
        utils.showAdminNotice(
          __(hges.messages.productMeta.settingsSuccess),
          errorContainer,
          "success"
        );
        hsCodeField.value = hsCode;
        appellationField.value = selectedAppellation;
      } else {
        utils.showAdminNotice(
          __(hges.messages.productMeta.settingsError),
          errorContainer,
          "error"
        );
        hsCodeField.value = "";
      }

      // Recheck button state
      togglePublishButton();
    }
  }

  function showCustomPublishError(message) {
    let existingNotice = document.querySelector("#hges-custom-notice");

    if (!existingNotice) {
      const notice = document.createElement("div");
      notice.id = "hges-custom-notice";
      notice.className = "notice notice-error";
      notice.style.marginTop = "20px";
      notice.innerHTML = `<p><strong>${message}</strong></p>`;

      const publishBox = document.querySelector("#submitdiv");
      if (publishBox) {
        const button = publishBox.querySelector("#publish");
        button.parentNode.insertBefore(notice, button);
      }
    }
  }

  function removeCustomPublishError() {
    const existingNotice = document.querySelector("#hges-custom-notice");
    if (existingNotice) {
      existingNotice.remove();
    }
  }
});
