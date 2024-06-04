// prevent double trigger of script
let i = false;
jQuery(document).ready(function () {
  baseurl = ajaxurl.substring(0, ajaxurl.indexOf("/wp-admin") + 1);
  jQuery("#Expvalidate").on("click", function () {
    updatstatus();
  });
  jQuery("#ExpvalidatePallet").on("click", function () {
    updatstatusPallet();
  });

  jQuery("#type_colissage").on("change", function () {
    if (jQuery("#type_colissage").val() == "choisir") {
      jQuery("#offredispo").css("display", "none");
      jQuery("#editColis").css("display", "none");
      jQuery("#editPallet").css("display", "none");
      jQuery("#offredispoPallet").css("display", "none");
      jQuery("#packaging-body").css("display", "none");
    }

    if (jQuery("#type_colissage").val() == "pallet") {
      jQuery("#offredispo").css("display", "none");
      jQuery("#editColis").css("display", "none");
      jQuery("#editPallet").css("display", "contents");
      jQuery("#offredispoPallet").css("display", "contents");
      jQuery("#packaging-body").css("display", "none");
    }

    if (jQuery("#type_colissage").val() == "colis") {
      jQuery("#offredispo").css("display", "contents");
      jQuery("#editColis").css("display", "contents");
      jQuery("#editPallet").css("display", "none");
      jQuery("#offredispoPallet").css("display", "none");
      jQuery("#packaging-body").css("display", "contents");
    }
  });

  jQuery("#editoffre").click(function () {
    jQuery("#editColisage").css("display", "contents");
    jQuery("#Expvalidate").prop("disabled", true);
  });

  jQuery("#validEdit").on("click", function () {
    if (jQuery("input[name='offer[]']:checked").val()) {
      updatparm();
      jQuery("#ExpvalidatePallet").prop("disabled", false);
    } else alert("you need to select an offer and type of packing");
  });

  jQuery("#validEditPallet").on("click", function () {
    if (jQuery("input[name='offer[]']:checked").val()) updatparm();
    else alert("you need to select an type of packing");
  });

  jQuery("#validColi").on("click", function () {
    updatpackage();
  });

  jQuery("#validColis").on("click", function () {
    if (jQuery("input[name='choix']:checked").val()) {
      updatpackage();

      jQuery("#ExpvalidatePallet").prop("disabled", true);
    } else alert("you need to select an type of packing");
  });

  jQuery("#validPallet").on("click", function () {
    if (jQuery("input[name='choix']:checked").val()) {
      updatpackage();
      jQuery("#ExpvalidatePallet").prop("disabled", true);
    } else {
      alert("you need to select an type of packing");
    }
  });
});

jQuery(document).ready(function () {
  //setup before functions
  let typingTimer; //timer identifier
  let doneTypingInterval = 800; //time in ms, 10 second for example
  let nb_pack = jQuery("#form_nb_pack");

  //on keyup, start the countdown
  nb_pack.on("change", function () {
    clearTimeout(typingTimer);
    jQuery("#form_colis").empty();
    typingTimer = setTimeout(doneTyping, doneTypingInterval);
  });

  //user is "finished typing," do something
  function doneTyping() {
    nbBottles = jQuery("#form_nb_bouteilles").val();
    nbMagnums = jQuery("#form_nb_magnums").val();
    nbPack = jQuery("#form_nb_pack").val();
    packages = getSizesPack(nbBottles, nbMagnums, nbPack);
  }

  function getSizesPack(nbBottles, nbMagnums, nbPack) {
    document.getElementById("choices").innerHTML = "";
    jQuery.ajax({
      type: "GET",
      url:
        location.origin +
        baseurl +
        "wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/get_pack.php?nbBottles=" +
        nbBottles +
        "&nbMagnums=" +
        nbMagnums +
        "&nbPack=" +
        nbPack,
      success: function (data) {
        if (data.packages) {
          data.packages.forEach((package, index) => {
            jQuery("#choices").append(
              `<input type="radio" id="choix${
                index + 1
              }" name="choix" value="${encodeURIComponent(
                JSON.stringify(package.choice)
              )}" >
                        <label for="choix"><strong>` +
                vig_js_strings.choix_colissage +
                `</strong></label>`
            );
            package.choice.map((choice) => {
              if (choice.nbMagnums) {
                jQuery("#choices").append(
                  `<li>${choice.nbPackages} colis de ${choice.nbMagnums} magnums (${choice.sizes.width}x${choice.sizes.height}cm )</li>`
                );
              } else {
                jQuery("#choices").append(
                  `<li>${choice.nbPackages} colis de ${choice.nbBottles} bouteilles (${choice.sizes.width}x${choice.sizes.height}cm )</li>`
                );
              }
            });
          });

          if (!jQuery("input[name=choix").is("checked")) {
            jQuery("#choix1").prop("checked", true);
            jQuery("#form_colis").empty();
            //i index choice from php
            let i = 0;
            data.packages.forEach((package, index) => {
              if (i == 0) {
                package.choice.map((choice, ind) => {
                  if (choice.nbBottles == undefined) {
                    var nbBottles = 0;
                  } else {
                    var nbBottles = choice.nbBottles;
                  }
                  if (choice.nbMagnums == undefined) {
                    var nbMagnums = 0;
                  } else {
                    var nbMagnums = choice.nbMagnums;
                  }

                  jQuery("#form_colis").append(
                    `<tr><th>` +
                      vig_js_strings.nbr_colis +
                      `</th><th>` +
                      vig_js_strings.poids_colis +
                      `</th><th> Dimensions<th></tr>`
                  );
                  jQuery("#form_colis").append(`
                    <tr class="packshipping" id="shipping${ind}">
                      <td>
                        <input type="hidden"
                        id="form_colis_colis${ind}_nb_bouteilles"
                        name="form[colis][colis${ind}][nb_bouteilles]"
                        value="${nbBottles}" >
                        <input type="hidden"
                        id="form_colis_colis${ind}_nb_magnums"
                        name="form[colis][colis${ind}][nb_magnums]"
                        value="${nbMagnums}" >
                        <input type ="text"
                        id="form_colis_colis${ind}_nb"
                        name="form[colis][colis${ind}][nb]"
                        require ="required"
                        class="disabled form-control"
                        readonly="readonly"
                        value="${choice.nbPackages}" > 
                        <input type="hidden"
                        id="form_colis_colis${ind}_poids_vol"
                        name="form[colis][colis${ind}][poids_vol]"
                        class="poids_vol" value="${choice.sizes.weightVE}" > 
                      </td>
                      <td>
                        <input type="text" id="form_colis_colis${ind}_poids" name="form[colis][colis${ind}][poids]" required="required" class="poids form-control" value="${choice.sizes.weightStill}"></td>
                        <td colspan="4">
                        <input type="text" id="form_colis_colis${ind}_largeur" name="form[colis][colis${ind}][largeur]" required="required" class="littleInput fleft center largeur form-control" value="${choice.sizes.width}" style="width: 95px;"> 
                        <label class="fleft">x</label>
                        <input type="text" id="form_colis_colis${ind}_hauteur" name="form[colis][colis${ind}][hauteur]" required="required" class="littleInput fleft center hauteur form-control" value="${choice.sizes.height}" style="width: 95px;">
                        <label class="fleft">x</label>
                        <input type="text" id="form_colis_colis${ind}_profondeur" name="form[colis][colis${ind}][profondeur]" required="required" class="littleInput fleft center profondeur form-control" value="${choice.sizes.length}" style="width: 95px;">
                      </td>
                    </tr>
                  `);
                });
              }
              i++;
              jQuery("#choices").append("<br>");
            });

            jQuery("#form_colis").append(`
                <tr>
                  <td colspan="4"></td><td colspan="2">
                    <input type="button" class="button" value="Valider colis" id="validColi">
                  </td>
                </tr>
              `);
          }

          jQuery("#validColi").on("click", function () {
            updatpackage();
          });

          jQuery("input[name*=choix]").on("click", function () {
            //index checkbox
            let index = jQuery("input[name*=choix]").index(this);
            jQuery("#form_colis").empty();
            //i index choice from php
            let i = 0;
            data.packages.forEach((package, index) => {
              if (i == index) {
                package.choice.map((choice, ind) => {
                  if (choice.nbBottles == undefined) {
                    var nbBottles = 0;
                  } else {
                    var nbBottles = choice.nbBottles;
                  }
                  if (choice.nbMagnums == undefined) {
                    var nbMagnums = 0;
                  } else {
                    var nbMagnums = choice.nbMagnums;
                  }
                  jQuery("#form_colis").append(
                    `<tr><th>` +
                      vig_js_strings.nbr_colis +
                      `</th><th>` +
                      vig_js_strings.poids_colis +
                      `</th><th> Dimensions<th></tr>`
                  );
                  jQuery("#form_colis").append(`
                    <tr class="packshipping" id="shipping${ind}">
                      <td>
                        <input type="hidden"
                          id="form_colis_colis${ind}_nb_bouteilles"
                          name="form[colis][colis${ind}][nb_bouteilles]"
                          value="${nbBottles}" >
                        <input type="hidden"
                          id="form_colis_colis${ind}_nb_magnums"
                          name="form[colis][colis${ind}][nb_magnums]"
                          value="${nbMagnums}" >
                        <input type ="text"
                          id="form_colis_colis${ind}_nb"
                          name="form[colis][colis${ind}][nb]"
                          require ="required"
                          class="disabled form-control"
                          readonly="readonly"
                          value="${choice.nbPackages}" > 
                        <input type="hidden"
                          id="form_colis_colis${ind}_poids_vol"
                          name="form[colis][colis${ind}][poids_vol]"
                          class="poids_vol"> 
                      </td>
                      <td>
                        <input type="text" id="form_colis_colis${ind}_poids" name="form[colis][colis${ind}][poids]" required="required" class="poids form-control" value="${choice.sizes.weightStill}"></td>
                      <td colspan="4">  <input type="text" id="form_colis_colis${ind}_largeur" name="form[colis][colis${ind}][largeur]" required="required" class="littleInput fleft center largeur form-control" value="${choice.sizes.width}" style="width: 90px;">> 
                        <label class="fleft">x</label>
                        <input type="text" id="form_colis_colis${ind}_hauteur" name="form[colis][colis${ind}][hauteur]" required="required" class="littleInput fleft center hauteur form-control" value="${choice.sizes.height}" style="width: 90px;">
                        <label class="fleft">x</label>
                        <input type="text" id="form_colis_colis${ind}_profondeur" name="form[colis][colis${ind}][profondeur]" required="required" class="littleInput fleft center profondeur form-control" value="${choice.sizes.length}" style="width: 90px;">
                      </td>
                    </tr>
                  `);
                });
              }
              i++;
              jQuery("#form_colis")
                .append(`<tr><td colspan="4"></td><td colspan="2">
                            <input type="button" class="button" value="Valider colis" id="validColi"></td></tr>`);
            });
          });
        }
      },
    });
  }
});

function updatstatusPallet() {
  const order_id = jQuery("#order_id").val();
  jQuery.ajax({
    type: "GET",
    url:
      location.origin +
      baseurl +
      "wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/updatestatusPalletexpidition.php?order_id=" +
      order_id,
    success: function (xml, textStatus, xhr) {
      if (xhr.status == "200") {
        alert("Success update : Pallet");
        // console.log(xhr.responseText);
        location.reload();
        return false;
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      // let { fullerrors } = " ";
      // let obj = JSON.parse(xhr.responseText);

      // let { errors } = obj[0].error;

      // for (let key in Object.values(errors)[0]) {
      //   fullerrors += `${key}:${Object.values(errors)[0][key]}`;
      // }
      // alert(fullerrors);
      console.log(xhr.responseText);
      console.log(ajaxOptions);
      console.log(thrownError);
    },
  });
}

function updatstatus() {
  const order_id = jQuery("#order_id").val();
  jQuery.ajax({
    type: "GET",
    url:
      location.origin +
      baseurl +
      "wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/updatestatusexpidition.php?order_id=" +
      order_id,
    success: function (xml, textStatus, xhr) {
      if (xhr.status == "200" || (data.message = "OK")) {
        // alert("Success update : Parcel");
        // console.log(xhr.responseText);

        // location.reload();
        jQuery.blockUI({ message: null });
        return false;
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      // let { fullerrors } = " ";
      // let obj = JSON.parse(xhr.responseText);
      // let { errors } = obj[0].error;
      // for (let key in Object.values(errors)[0]) {
      //   fullerrors += `${key}:${Object.values(errors)[0][key]}`;
      // }
      // alert(fullerrors);
      console.log(xhr.responseText);
      console.log(ajaxOptions);
      console.log(thrownError);
    },
  });
}

function updatparm() {
  const offerInput = jQuery("input[name*=offer]");
  const offer = offerInput.filter(":checked").val();
  const offerIndex = offerInput.index(offerInput.filter(":checked"));

  const methode = jQuery("#shipping_method_type").val();
  const methodeIndex = 1;

  const priceOffre = jQuery("input[name*=offer]:checked").next().val();
  const taxAmount = jQuery("input[name*=offer]:checked")
    .next()
    .data("tax_amount");
  const insuranceAmount = jQuery("input[name*=offer]:checked")
    .next()
    .data("insurance");
  const order_id = jQuery("#order_id").val();
  const package_type = jQuery("#type_colissage").val();
  const nb_magnums = jQuery("#form_nb_magnums").val();
  const nbr_bottles = jQuery("#form_nb_bouteilles").val();

  jQuery.get(
    location.origin +
      baseurl +
      "wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/expedition_param_update.php?order_id=" +
      order_id +
      "&offer=" +
      offer +
      "&offerIndex=" +
      offerIndex +
      "&taxAmount=" +
      taxAmount +
      "&priceOffre=" +
      priceOffre +
      "&insurance=" +
      insuranceAmount,
    function (data) {
      if ((data.message = "OK")) {
        // alert("Success update parameters");
        console.log(data);
        location.reload();
        jQuery.blockUI({ message: null });
      } else {
        alert("Failed update");
      }
    }
  );
}

function updatpackage() {
  var k = false;
  const package_type = jQuery("#type_colissage").val();

  const choixInput = jQuery("input[name*=choix]");
  const choix = choixInput.filter(":checked").val();
  const choixIndex = choixInput.index(choixInput.filter(":checked"));

  const order_id = jQuery("#order_id").val();

  const nbr_bottles = jQuery("#form_nb_bouteilles").val();
  if (package_type == "pallet") {
    var values = [];
    jQuery("input[name='length']").each(function () {
      if (jQuery(this).length > 0 && jQuery(this).val() != "")
        values.push(jQuery(this).val());
    });
    const length = values;
    jQuery.get(
      location.origin +
        baseurl +
        "wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/package_param_update.php?order_id=" +
        order_id +
        "&choix=" +
        choix +
        "&choixIndex=" +
        choixIndex +
        "&package_type=" +
        package_type +
        "&length=" +
        length,
      function (data) {
        if ((data.message = "OK")) {
          // alert("Success update");
          location.reload();
          jQuery.blockUI({ message: null });
        } else {
          alert("Failed update");
        }
      }
    );
  } else {
    var val = [];
    var k = false;

    jQuery(".packshipping").each(function (pack, i) {
      varindput = "colis" + pack;
      arrVar = [];
      var chaine1 = "";
      jQuery(`input[name^='form[colis][${varindput}]'`).each(function (
        indval,
        element
      ) {
        if (jQuery(this).length > 0 && jQuery(this).val() != "") {
          if (indval == 7) chaine1 = chaine1.concat(jQuery(this).val());
          else chaine1 = chaine1.concat(jQuery(this).val() + ",");
        } else {
          alert("Insert parametre  from " + varindput);
          k = true;
          return false;
        }
      });
      val.push(chaine1);
      if (k == true) {
        return false;
      }
    });
    if (k == false) {
      let arrayToString = JSON.stringify(val);
      jQuery.get(
        location.origin +
          baseurl +
          "wp-content/plugins/Vignoblexport/Vignoblexport/VignoblexportPhp/package_param_update.php?order_id=" +
          order_id +
          "&choix=" +
          choix +
          "&choixIndex=" +
          choixIndex +
          "&package_type=" +
          package_type +
          "&val=" +
          arrayToString,
        function (data) {
          if ((data.message = "OK")) {
            // alert("Success update package");
            location.reload();
            jQuery.blockUI({ message: null });
          } else {
            alert("Failed update");
          }
        }
      );

      return false;
    }
  }
}
