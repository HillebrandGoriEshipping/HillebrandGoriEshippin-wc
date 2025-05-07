document.addEventListener("DOMContentLoaded", function () {
  const countrySelect = document.getElementById("_producing_country");
  const appellationSelect = document.getElementById("_appellation");

  countrySelect.addEventListener("change", function () {
    console.log("Selected country:", this.value);

    const country = this.value;

    if (country) {
      console.log("Fetching appellations for country:", country);

      fetch(
        `${
          HGES_Ajax.ajax_url
        }?action=get_appellations_by_country&country=${encodeURIComponent(
          country
        )}`
      )
        .then((response) => response.json())
        .then((result) => {
          if (result.success) {
            const appellations = result.data;
            console.log("Appellations:", appellations);
          }
        })
        .catch((error) => {
          console.log("Erreur réseau ou serveur :", error);
        });
    }
  });
});
