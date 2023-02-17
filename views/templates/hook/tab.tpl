<div class="card mt-2" id="resend_order_conf_email">
  <div class="card-header">
    <h3 class="card-header-title">
      Resend Order Confirmation Email
    </h3>
  </div>

  <div class="card-body">
    <div class="input-group">
      <input type="text" id="recipient_email" name="presta_resend_order_conf_email_email" aria-label="input" class="form-control" value="{$recipient_email}">
      <button class="btn btn-link pt-0 pb-0" onclick="resend_order_conf_email(`{$id_order}`);">Send</button>
    </div>
  </div>
</div>
<div id="presta_resend_order_conf_email_res"></div>

<script type="text/javascript">
  let presta_resend_conf_email_token = `{$admin_token}`;
  {literal}
  const resend_order_conf_email = (id_order) => {
    $.ajax({
      url: `index.php?controller=AdminPrestaResendOrderConfEmail`,
      type: 'POST',
      dataType: 'json',
      data: {
        ajax: true,
        controller: `AdminPrestaResendOrderConfEmail`,
        action: `resendOrderConfEmail`,
        id_order: id_order,
        token: presta_resend_conf_email_token,
        recipient_email: $(`#recipient_email`).val(),
      },
    })
    .done(function(res) {
      if (res.status == 'OK') {
        $("#presta_resend_order_conf_email_res").html(`<div class="alert alert-success" role="alert">${res.messages}</div>`);
      } else {
        let errors = ``;
        $.each(res.errors, function(index, val) {
           /* iterate through array or object */
          errors += `<div class="alert alert-danger" role="alert">${val}</div>`;
        });
        $("#presta_resend_order_conf_email_res").html(errors);
      }
    })
    .fail(function(xhr, textStatus, errorThrown) {
      $("#presta_resend_order_conf_email_res").html(`<div class="alert alert-danger" role="alert">${xhr.responseText}</div>`);
    })
    .always(function() {

    });
  }
  {/literal}

</script>