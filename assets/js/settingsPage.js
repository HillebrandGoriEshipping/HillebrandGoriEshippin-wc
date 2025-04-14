import apiClient from "./apiClient.js";

document.addEventListener("DOMContentLoaded", () => {
  const validateKey = document.querySelector("#validate-api");

  validateKey.addEventListener("click", async () => {
    console.log("Validating API Key...");

    const apiKey = document.querySelector("#api-input").value;

    const result = await apiClient.validateApiKey(apiKey);
    if (result) {
      alert("API Key is valid!");
    } else {
      alert("API Key is invalid!");
    }
  });
});
