import apiClient from "./apiClient.js";
import utils from "./utils.js";
const { __ } = window.wp.i18n;

document.addEventListener("DOMContentLoaded", () => {
  // Select the API key validation button
  const validateKey = document.querySelector("#validate-api");

  validateKey.addEventListener("click", async () => {
    //Slect the API key input field
    const apiInput = document.querySelector("#api-input");
    const apiKey = apiInput.value;
    // Check if the API key is empty
    const result = await apiClient.validateApiKey(apiKey);

    // If the API key is empty, show an error message & change input css class
    if (result) {
      utils.showAdminNotice(
        __(hges.messages.apiKeyValidation.apiKeySuccess),
        document.querySelector("#api-input").parentElement,
        "success"
      );
      apiInput.classList.remove("invalid");
      apiInput.classList.add("valid");
    } else {
      // If the API key is invalid, show an error message & change input css class
      utils.showAdminNotice(
        __(hges.messages.apiKeyValidation.apiKeyError),
        document.querySelector("#api-input").parentElement,
        "error"
      );
      apiInput.classList.remove("valid");
      apiInput.classList.add("invalid");
    }
  });
});
