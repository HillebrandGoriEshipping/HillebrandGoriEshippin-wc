document.addEventListener("DOMContentLoaded", function () {
  const countrySelect = document.getElementById("_producing_country");
  const appellationSelect = document.getElementById("_appellation");

  countrySelect.addEventListener("change", function () {
    loadAppellationInSelect();
  });

  function loadAppellationInSelect() {
    const selectedCountry = countrySelect.value;
    if (selectedCountry) {
      fetch(
        `${
          HGES_Ajax.ajax_url
        }?action=get_appellations_by_country&country=${encodeURIComponent(
          selectedCountry
        )}`
      )
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            const appellations = result.data;
            // Clear the existing options
            appellationSelect.innerHTML = "";
            // Populate the appellation select with new options
            appellations.forEach((appellation) => {
              const option = document.createElement("option");
              // encode the appelation value to be URL safe
              const encodedAppellation = encodeURIComponent(appellation);
              option.value = encodedAppellation;
              option.textContent = appellation;
              appellationSelect.appendChild(option);
            });
            // Enable the appellation select
            appellationSelect.disabled = false;
          }
        })
        .catch((error) => {
          console.log("Erreur réseau ou serveur :", error);
        });
    }
  }
});
