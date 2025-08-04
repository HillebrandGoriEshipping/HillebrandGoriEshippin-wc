import apiClient from "./apiClient.js";
import utils from "./utils.js";
const { __ } = window.wp.i18n;

const productMetaModule = {
  alcoholPercentageField: null,
  appellationField: null,
  appellationSelect: null,
  capacityField: null,
  colorField: null,
  countrySelect: null,
  drinkTypeSelect: null,
  existingNotice: null,
  hsCodeField: null,
  productTypeSelect: null,
  publishButton: null,
  tabs: null,
  target: null,
  variationCheckbox: null,
  wineFields: null,
  init() {
    this.countrySelect = document.querySelector("#_producing_country");
    this.appellationSelect = document.querySelector("#appellation-select-field");
    this.appellationField = document.querySelector("#_appellation");
    this.capacityField = document.querySelector("#_capacity");
    this.alcoholPercentageField = document.querySelector("#_alcohol_percentage");
    this.colorField = document.querySelector("#_color");
    this.target = document.querySelector("#product_attributes");
    this.publishButton = document.querySelector("#publish");
    this.hsCodeField = document.querySelector("#_hs_code");
    this.productTypeSelect = document.querySelector("#product-type");
    this.drinkTypeSelect = document.querySelector("#_type");
    this.wineFields = document.querySelectorAll(".wine-form-field input, .wine-form-field select, .wine-form-field textarea");
    this.productTypeSelect = document.querySelector("#product-type");
    this.tabs = document.querySelectorAll(".attribute_tab");
    this.existingNotice = document.querySelector("#hges-custom-notice");
    this.variationCheckbox = document.querySelectorAll(".enable_variation");
    this.capacityTypeSelect = document.querySelector("#_capacity_type");
    this.customCapacityWrapper = document.querySelector("#custom-capacity-wrapper");
    this.initEventListeners();

    hges.pricableProductTypes.forEach((productType) => {
      document.querySelector('.options_group.pricing').classList.add('show_if_' + productType);
    });

    this.evalProductPublishable();
    this.evalWineFormEnabled();
    this.loadAppellationInSelect();
  },
  initEventListeners() {
    if (this.countrySelect) {
      this.countrySelect.addEventListener("change", this.loadAppellationInSelect.bind(this));
    }

    if (this.appellationSelect) {
      console.log("appellationSelect", this.appellationSelect);
      this.appellationSelect.addEventListener("change", this.checkHsCode.bind(this));
    }

    if (this.hsCodeField && this.productTypeSelect) {
      this.hsCodeField.addEventListener("change", this.evalProductPublishable.bind(this));
      this.productTypeSelect.addEventListener("change", this.evalProductPublishable.bind(this));
    }

    if (this.drinkTypeSelect) {
      this.drinkTypeSelect.addEventListener("change", this.evalWineFormEnabled.bind(this));
    }

    this.evalUseInVariationCheckboxDisplay();

    const mutationObserver = new MutationObserver((mutationsList, observer) => {
      for (const mutation of mutationsList) {
        if (mutation.type === "childList" || mutation.type === "attributes") {
          this.evalUseInVariationCheckboxDisplay();
        }
      }
    });

    mutationObserver.observe(this.target, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["class", "style"]
    });

    this.tabs.forEach((tab) => {
      tab.addEventListener("click", function () {
        this.evalUseInVariationCheckboxDisplay();
      }.bind(this));
    });

    if (this.capacityTypeSelect) {
      this.capacityTypeSelect.addEventListener("change", this.handleCapacityTypeChange.bind(this));
      this.handleCapacityTypeChange();
    }
  },
  evalUseInVariationCheckboxDisplay() {
    if (hges.variableProductTypes.includes(this.productTypeSelect.value)) {
      setTimeout(() => {
        for (const checkbox of this.variationCheckbox) {
          checkbox.style.display = "block";
          checkbox.querySelector('input').checked = true;
        }
      }, 500);
    } else {
      for (const checkbox of this.variationCheckbox) {
        checkbox.style.display = "none";
        checkbox.querySelector('input').checked = false;
      }
    }
  },
  async loadAppellationInSelect() {
    const selectedCountry = this.countrySelect.value;
    if (selectedCountry) {
      const result = await apiClient.get("/get-appellations", {
        producingCountry: selectedCountry,
      });

      if (result) {
        const appellations = result;
        const currentValue = this.appellationSelect.dataset.savedValue || this.appellationSelect.value;
        this.appellationSelect.innerHTML = "";

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = __("Select an appellation");
        this.appellationSelect.appendChild(defaultOption);

        appellations.forEach((appellation) => {
          const option = document.createElement("option");
          option.value = appellation;
          option.textContent = appellation;
          if (appellation === currentValue) {
            option.selected = true;
          }
          this.appellationSelect.appendChild(option);
        });

        this.appellationSelect.disabled = false;
      }
    }
  },
  isBottleProduct() {
    if (!this.productTypeSelect) return false;

    const bottleTypes = ["bottle-simple", "bottle-variable"];
    return bottleTypes.includes(this.productTypeSelect.value);
  },
  evalProductPublishable() {
    if (!this.hsCodeField || !this.publishButton) {
      return;
    }

    this.removeCustomPublishError();

    if (!this.isBottleProduct()) {
      this.setPublishButtonEnabled(false);
      return;
    }

    this.setPublishButtonEnabled(this.hsCodeField.value.trim() !== "");
  },
  evalWineFormEnabled() {
    const isWineProduct = (['still', 'sparkling']).includes(this.drinkTypeSelect.value);
    this.setAppellationFieldsEnabled(!isWineProduct);
    this.wineFields.forEach((field) => {
      if (isWineProduct) {
        field.removeAttribute("disabled");
      } else {
        field.setAttribute("disabled", "disabled");
      }
    });
  },
  setAppellationFieldsEnabled(enabled) {
    if (enabled) {
      this.appellationField.closest(".form-field").style.display = "block";
      this.hsCodeField.closest(".form-field").style.display = "block";
    } else {
      this.appellationField.closest(".form-field").style.display = "none";
      this.hsCodeField.closest(".form-field").style.display = "none";
    }
  },
  async checkHsCode() {
    
    const currentCapacity = this.capacityField.value;
    const currentAlcoholPercentage = this.alcoholPercentageField.value;
    const currentColor = this.colorField.value;
    const selectedAppellation = this.appellationSelect.value;
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

      if (hsCode) {
        utils.showAdminNotice(
          __(hges.messages.productMeta.settingsSuccess),
          errorContainer,
          "success"
        );
        this.hsCodeField.value = hsCode;
        this.appellationField.value = selectedAppellation;
      } else {
        utils.showAdminNotice(
          __(hges.messages.productMeta.settingsError),
          errorContainer,
          "error"
        );
        this.hsCodeField.value = "";
      }

      // Recheck button state
      this.evalProductPublishable();
    }
  },
  showCustomPublishError(message) {

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
  },
  removeCustomPublishError() {
    if (this.existingNotice) {
      this.existingNotice.remove();
      this.existingNotice = null;
    }
  },
  setPublishButtonEnabled(enabled) {
    if (this.publishButton) {
      if (!enabled) {
        this.publishButton.disabled = true;
        this.publishButton.classList.add("button-disabled");
        this.showCustomPublishError(__(hges.messages.productMeta.preventPublish));
      } else {
        this.publishButton.disabled = false;
        this.publishButton.classList.remove("button-disabled");
        this.publishButton.removeAttribute("title");
      }
    }
  },
  handleCapacityTypeChange() {
    const selected = this.capacityTypeSelect.value;

    if (!this.customCapacityWrapper || !this.capacityField) return;

    if (selected === "standard") {
      this.customCapacityWrapper.style.display = "none";
      this.capacityField.value = "750";
      this.capacityField.dispatchEvent(new Event("change", { bubbles: true }));
    } else if (selected === "magnum") {
      this.customCapacityWrapper.style.display = "none";
      this.capacityField.value = "1500";
      this.capacityField.dispatchEvent(new Event("change", { bubbles: true }));
    } else if (selected === "other") {
      this.customCapacityWrapper.style.display = "";
 
      if (["750", "1500"].includes(this.capacityField.value)) {
        this.capacityField.value = "";
        this.capacityField.dispatchEvent(new Event("change", { bubbles: true }));
      }
    }
  }
}

document.addEventListener("DOMContentLoaded", productMetaModule.init.bind(productMetaModule));