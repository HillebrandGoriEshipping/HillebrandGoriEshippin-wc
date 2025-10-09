import apiClient from "./apiClient.js";
import utils from "./utils.js";
const { translate } = window.hges.i18n;

document.addEventListener("DOMContentLoaded", () => {

  const form = document.querySelector("#configuration_form");

  if (form) {
    window.hges.validator.attachForm(
      form,
      'settings'
    );
  }

  // Select the API key validation button
  const validateKey = document.querySelector("#validate-api");

  validateKey.addEventListener("click", async () => {
    //Slect the API key input field
    const apiInput = document.querySelector("#api-input");
    const apiKey = apiInput.value;
    // Select the submit button
    const validationButton = document.querySelector("#submitkey");
    // Check if the API key is empty
    const result = await apiClient.validateApiKey(apiKey);

    // If the API key is valid, show a success message & change input css class
    if (result) {
      utils.showAdminNotice(
        translate(hges.messages.apiKeyValidation.apiKeySuccess),
        document.querySelector("#api-input").parentElement,
        "success"
      );
      apiInput.classList.remove("invalid");
      apiInput.classList.add("valid");
      // enable the submit button
      validationButton.disabled = false;
    } else {
      // If the API key is invalid, show an error message & change input css class
      utils.showAdminNotice(
        translate(hges.messages.apiKeyValidation.apiKeyError),
        document.querySelector("#api-input").parentElement,
        "error"
      );
      apiInput.classList.remove("valid");
      apiInput.classList.add("invalid");
      // disable the submit button
      validationButton.disabled = true;
    }
  });
});
