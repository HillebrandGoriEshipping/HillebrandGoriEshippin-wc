(function ($) {
  $(document).ready(function () {
    if ($("#_custom_producing_country").val() == null) {
      $("._custom_appelation_field").css("display", "none");
    }
  });

  // CHECK APPELLATION
  $("#_custom_producing_country").on("change", function () {
    var ajaxscript = { ajax_url: baseurl + "wp-admin/admin-ajax.php" };
    $.ajax({
      url: ajaxscript.ajax_url,
      data: {
        action: "check_country_has_appellations",
        country: $("#_custom_producing_country").val(),
      },
      dataType: "json",
      method: "GET",
      success: function (data) {
        if (data) {
          pushAppellationsInSelection(data);
          displayProducingCountrySelect();
        } else {
          displayErrorMessage();
        }
      },
      error: function (error) {
        // console.log("error :", error);
      },
    });
  });

  //
  function displayProducingCountrySelect() {
    $("._custom_appelation_field").css("display", "block");
    $("#product-error").css("display", "none");
  }

  function displayErrorMessage() {
    $("._custom_appelation_field").css("display", "none");
    $("#product-error").css("display", "block");
    $("#product-error>p").html(product_translation.no_appellation);
  }

  function pushAppellationsInSelection(data) {
    if (data) {
      $("#_custom_appelation").empty();
      $("#_custom_appelation").append();
      for (const item in data) {
        $("#_custom_appelation").append(
          $("<option>", {
            value: item,
            text: data[item],
          })
        );
      }
    }
  }

  // VERIFY HS CODE
  $("#_custom_appelation").on("change", function () {
    var ajaxscript = { ajax_url: baseurl + "wp-admin/admin-ajax.php" }; //repeated code
    $.ajax({
      url: ajaxscript.ajax_url,
      data: {
        action: "is_product_info_correct",
        appellation_name: $("#_custom_appelation").val(),
        capacity: $("#_custom_capacity").val(),
        alcohol_degree: $("#_custom_alcohol_degree").val(),
        color: $("#_custom_color").val(),
      },
      dataType: "json",
      method: "GET",
      success: function (data) {
        if (data != "") {
          displayInfoCorrect();
        } else {
          displayInfoError();
        }
      },
      error: function (error) {
        // console.log("error :", error);
      },
    });
  });

  function displayInfoCorrect() {
    $("#product-error").css("display", "block");
    $("#product-error>p").html(product_translation.hs_code_ok);
  }
  function displayInfoError() {
    $("#product-error").css("display", "block");
    $("#product-error>p").html(product_translation.error_hscode);
  }
})(jQuery);
