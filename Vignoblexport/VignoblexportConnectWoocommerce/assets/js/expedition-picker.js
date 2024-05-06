var finshedLoad = false;
var count;
var i = false;
(function ($) {
  $(document).ready(function () {
    i = false;
    $("input[name*=shipping_mettype]").bind("change", function () {
      if ($("input[name*=shipping_mettype]").is(":checked")) {
        // if value is true, enable button
        $("#place_order").prop("disabled", false);
        $(".checkout-button").removeClass("disabled-button");
      } else {
        $(".checkout-button").addClass("disabled-button");
        $("#place_order").prop("disabled", true); // if false disable button
      }
    });
    $("input[name*=shipping_mettype]").trigger("change");

    $("input[name*=offer]").on("click", function () {
      $("#place_order").prop("disabled", true);
    });

    if (
      $("input[id*=vignoblexport_connect]").eq(0).is(":checked") ||
      $("input[id*=vignoblexport_connect]").eq(0).attr("type") === "hidden"
    ) {
      doneTyping();
    }

    //disable validate offer button
    var getUrl = window.location;
    const baseurl =
      getUrl.protocol +
      "//" +
      getUrl.host +
      "/" +
      getUrl.pathname.split("/")[1];
    // console.log(baseurl);
    // baseurl = ajaxurl.substring(0, ajaxurl.indexOf('/wp-admin') + 1);
    loadscript();

    $("input[name='offer[]']").on("change", function () {
      updatcart();
      //activate validate command(panier)
      $(".wc-proceed-to-checkout *").removeClass("disable-div");
      i = true;
    });
    $("#validateColis").on("click", function () {
      if ($("#exp-details").val()) {
        $("#exp-details").val(" ");
      }

      updatcolis();
    });
    if ($("#exp-details").val() === "") {
      $("#place_order").prop("disabled", true);
    }

    $("form.checkout").on(
      "change",
      "#billing_city, #billing_address_1, #select2-billing_country-container, #billing_postcode",
      function () {
        $("#place_order").prop("disabled", true);
        console.log("Billing changed");
      }
    );
    $("form.checkout").on(
      "change",
      "#shipping_city, #shipping_address_1, #select2-shipping_country-container, #shipping_postcode",
      function () {
        $("#place_order").prop("disabled", true);
        console.log("Shipping changed");
      }
    );
    if ($("#exp-details").val() === "") {
      $("#place_order").prop("disabled", true);
    }
  });

  $(document.body).on("update_order_review", function () {
    console.log("updating");
    $("input[name*=offer]").on("change", function () {
      $("#place_order").prop("disabled", true);
      console.log("order updated");
    });
    if ($("#exp-details").val() === "") {
      $("#place_order").prop("disabled", true);
    }

    loadscript();

    i = false;

    if (
      $("input[id*=vignoblexport_connect]").eq(0).is(":checked") ||
      $("input[id*=vignoblexport_connect]").eq(0).attr("type") === "hidden"
    ) {
      doneTyping();
    }

    $("input[name='offer[]']").on("change", function () {
      updatcart();
      $("#place_order").prop("disabled", false);
      i = true;
    });

    // $("#validateColis").on("click", function () {
    //   updatcolis();
    // });
    if (i == true) {
      $(".checkout-button").removeClass("disabled-button");
    }
    if ($("#exp-details").val() === "") {
      $("#place_order").prop("disabled", true);
    }
  });

  $(document.body).on("updated_checkout", function () {
    loadscript();
    doneTyping();

    $("input[name*=offer]").on("change", function () {
      $("#place_order").prop("disabled", true);
    });

    $("input[name='offer[]']").on("click", function () {
      updatcart();
      $("#place_order").prop("disabled", false);
    });

    if ($("#exp-details").val() === "") {
      $("#place_order").prop("disabled", true);
    }

    if (!$("#shipping_method").find(".amount").length) {
      $(".appointment-link").before(': <span class="amount"></span>');
    } else {
      $("#shipping_method").find(".amount").text("");
    }
  });

  function loadscript() {
    console.log("loadscript");
    updatCarrier();
    if ($("#shipping_method_0_Vignoblexport_connect_pr").length == 0) {
      $("#relayaction").hide();
      $(".VINW-0-client").hide();
    }
    $(
      "#shipping_method_0_Vignoblexport_connect_dom, #shipping_method_0_Vignoblexport_connect_pr"
    ).on("click", function () {
      if (wc_add_to_cart_params.is_cart !== "1") {
        if (finshedLoad) {
          $("#place_order").prop("disabled", true);
        }
      }
    });

    $(
      "#shipping_method_0_Vignoblexport_connect_dom, #shipping_method_0_Vignoblexport_connect_pr"
    ).on("change", function () {
      if ($(this).val() == "domicile") {
        $("#offer1").show();
        $("#offer2").hide();
        $("#relayaction").hide();
        $(".VINW-parcel-client").hide();
      } else {
        $("#offer1").hide();
        $("#offer2").show();
        $("#relayaction").show();
        $(".VINW-parcel-client").show();
      }
    });
    if ($("#exp-details").length !== 0) {
      const expDetails = $("#exp-details").val().split(";");
      $("input[name*=shipping_mettype]")
        .eq(parseInt(expDetails[5]))
        .prop("checked", true)
        .change();
    }
  }

  function doneTyping() {
    console.log("done typing");
    $("#validateColis").prop("disabled", true);
    nbBottles = $("#form_nb_bouteilles").val();
    nbMagnums = $("#form_nb_magnums").val();

    if ($("#exp-details").length !== 0) {
      let expDetails = $("#exp-details").val().split(";");
      if (expDetails[3]) {
        $("input[name*=choix]")
          .eq(parseInt(expDetails[3]))
          .prop("checked", true)
          .change();
      }
      if (expDetails[1]) {
        $("input[name*=offer]")
          .eq(parseInt(expDetails[1]))
          .prop("checked", true)
          .change();
        count++;
        if (count === 1) {
          finshedLoad = true;
        }
      }
    }
    if ($("#exp-colis").length !== 0) {
      let colisDetails = $("#exp-colis").val().split(";");
      if (colisDetails[2]) {
        $("input[name*=choix]")
          .eq(parseInt(colisDetails[2]))
          .prop("checked", true)
          .change();
      }
    }
    $("input[name='offer[]']").on("click", function () {
      $("#place_order").prop("disabled", true);
      if ($("#exp-details").val()) {
        $("#exp-details").val(" ");
      }
    });
    if ($("input[name='offer[]']:checked").length > 0) {
      const offerInput2 = $("input[name*=offer]");
      indexcheked = offerInput2.index(offerInput2.filter(":checked"));
      $(`#offer-cont_${indexcheked}`).css("font-weight", "bold");
    }

    $("input[name='shipping_mettype[]'").on("change", function () {
      $("#place_order").prop("disabled", true);
    });

    if ($("#exp-details").val() === "") {
      if (wc_add_to_cart_params.is_cart !== "1") {
        if (finshedLoad) {
          $("#place_order").prop("disabled", true);
        }
      }
    }
  }

  function updatcolis() {
    console.log("updatcolis");
    const choixInput = $("input[name*=choix]");
    const choixcolis = choixInput.filter(":checked").val();
    const choixIndexcolis = choixInput.index(choixInput.filter(":checked"));

    $.get(
      baseurl +
        "/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/updatecolis.php?choix=" +
        choixcolis +
        "&choixIndex=" +
        choixIndexcolis,
      function (data) {
        $.get(
          baseurl +
            "/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/get-colis.php",
          function (data) {
            $("#exp-colis").val(data[0].value);
            if ($("#exp-details").val()) {
              $("#exp-details").val(" ");
            }

            if (wc_add_to_cart_params.is_cart !== "1") {
              location.reload();
              return false;
            } else {
              $("[name='update_cart']").prop("disabled", false);
              $("[name='update_cart']").trigger("click");
              i = false;
              $(".checkout-button").addClass("disabled-button");
            }
          }
        );
      }
    );
    if ($("#exp-details").val() === "") {
      $("#place_order").prop("disabled", true);
    }
  }

  function updatCarrier() {
    console.log("updatCarrier");
    const selectedOffer = $("input[name*=offer]:checked");
    const currentOfferName = selectedOffer.data("name");

    let testInput = selectedOffer.closest("form").find("input[name=carrier]");

    if (testInput.length == 0) {
      selectedOffer
        .closest("form")
        .append(
          '<input type="hidden" name="carrier" value="' +
            currentOfferName +
            '"/>'
        );
    } else {
      testInput.val(currentOfferName);
    }
  }

  function updatcart() {
    console.log("updatcart");
    const choixInput = $("input[name*=choix]");
    const offerInput = $("input[name*=offer]");
    const methodInput = $("input[name*=shipping_mettype]");
    const selectedOffer = $("input[name*=offer]:checked");

    const currentOfferName = selectedOffer.data("name");
    const offer = offerInput.filter(":checked").val();
    const offerIndex = offerInput.index(offerInput.filter(":checked"));

    const methode = methodInput.filter(":checked").val();
    const methodeIndex = methodInput.index(methodInput.filter(":checked"));

    const priceOffre = selectedOffer.next().val();
    const nb_magnums = $("#form_nb_magnums").val();
    const nbr_bottles = $("#form_nb_bouteilles").val();

    updatCarrier();
    sendTaxAndDuties();
    $.get(
      baseurl +
        "/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/updateexpidition.php?methode=" +
        methode +
        "&methodIndex=" +
        methodeIndex +
        "&offer=" +
        offer +
        "&offerIndex=" +
        offerIndex +
        "&priceOffre=" +
        priceOffre +
        "&nb_magnums=" +
        nb_magnums +
        "&nbr_bottles=" +
        nbr_bottles,
      function (data) {
        $.get(
          baseurl +
            "/wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/get-expidition.php",
          function (data) {
            $("#exp-details").val(data[0].value);
            if (wc_add_to_cart_params.is_cart !== "1") {
              // location.reload();
              updateCartPrices();
              updatePickupRelay();
              return false;
            } else {
              $("[name='update_cart']").prop("disabled", false);
              $("[name='update_cart']").trigger("click");
              i = true;
              $(".checkout-button").removeClass("disabled-button");
            }
          }
        );
        i = true;
      }
    );
  }

  function updateCartPrices() {
    console.log("updateCartPrices");
    const offerInput = $("input[name*=offer]");
    const selectedOffer = $("input[name*=offer]:checked");
    const fullOffer = selectedOffer.val();
    const offerIndex = offerInput.index(offerInput.filter(":checked"));
    const methodInput = $("input[name*=shipping_mettype]");
    const methode = methodInput.filter(":checked").val();
    const taxAmount = offerInput.filter(":checked").data("taxamount");
    const insurance = offerInput.filter(":checked").data("insurance");

    var ajaxscript = { ajax_url: baseurl + "/wp-admin/admin-ajax.php" };
    $.ajax({
      url: ajaxscript.ajax_url,
      data: {
        action: "update_cart_prices",
        id: offerIndex,
        offer: fullOffer,
        tax: taxAmount,
        insurance: insurance,
      },
      dataType: "json",
      method: "GET",
      success: function (data) {
        console.log("data :", data);
        $("#shipping_method").find(".amount").first().text(data.shippingTotal);
        $(".order-total").find(".amount").first().text(data.total);
        $(".includes_tax").find(".amount").first().text(data.totalVat);
        if ($("#tax-and-duties-amount")) {
          $("#tax-and-duties-amount").text(data.totalVat);
        }
        if (methode == "domicile" && selectedOffer.length) {
          $("#place_order").prop("disabled", false);
        }
        $("#place_order").prop("disabled", false);
      },
      error: function (error) {
        console.log("error :", error);
      },
    });
  }

  function sendTaxAndDuties() {
    console.log("sendTaxAndDuties");
    const selectedOffer = $("input[name*=offer]:checked");
    const selectedOfferValue = selectedOffer.attr("data-name");
    console.log(typeof selectedOfferValue);

    var ajaxscript = { ajax_url: baseurl + "/wp-admin/admin-ajax.php" };
    $.ajax({
      url: ajaxscript.ajax_url,
      method: "GET",
      data: {
        action: "calculate_tax_duties",
        selected_offer_value: selectedOfferValue,
      },
      success: function (data) {
        let TaxAndDuties = $(".tax-duties");
        if (TaxAndDuties) {
          processTaxAndDuties(data);
        }
      },
      error: function (error) {
        console.log("error :", error);
      },
    });
  }

  function processTaxAndDuties(data) {
    console.log("processTaxAndDuties");
    let TaxAndDutiesElement = $(".tax-amount");
    TaxAndDutiesElement.text(data.price + " " + data.currency);
  }

  function updatePickupRelay() {
    const methodInput = $("input[name*=shipping_mettype]");
    const methode = methodInput.filter(":checked").val();

    var ajaxscript = { ajax_url: baseurl + "/wp-admin/admin-ajax.php" };
    $.ajax({
      url: ajaxscript.ajax_url,
      data: {
        action: "update_pickup_relay",
      },
      dataType: "json",
      method: "POST",
      success: function (data) {
        if (methode == "pointRelais") {
          $(".relay").remove();
          $(".appointment-link").append(data);
          if (!$("#point_relais").length) {
            $(".woocommerce-billing-fields").append(
              '<input type="hidden" class="input-hidden " name="point_relais" id="point_relais" value="">'
            );
          }
        } else {
          $(".relay").remove();
        }
      },
      error: function (error) {
        console.log("error :", error);
      },
    });
  }
})(jQuery);
