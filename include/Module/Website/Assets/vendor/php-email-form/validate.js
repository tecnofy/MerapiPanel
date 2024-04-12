/**
 * Modify bg il4mb for recaptcha
 */

var onloadCallback = function () {
  grecaptcha.render('html_element', {
    'sitekey': '6LfNebIpAAAAACJkiqAMrZrzJ4eVMxT1khTEuYSs'
  });
};


var is_send = false;

(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach(function (e) {
    e.addEventListener('submit', function (event) {
      event.preventDefault();

      let thisForm = this;
      thisForm.querySelector('[type="submit"]').disabled = true;
      let action = thisForm.getAttribute('action');

      if (!action) {
        displayError(thisForm, 'The form action property is not set!');
        return;
      }

      thisForm.querySelector('.loading').classList.add('d-block');
      thisForm.querySelector('.error-message').classList.remove('d-block');
      thisForm.querySelector('.sent-message').classList.remove('d-block');

      let formData = new FormData(thisForm);
      php_email_form_submit(thisForm, action, formData);

    });
  });


  function php_email_form_submit(thisForm, action, formData) {
    if (is_send) return;
    is_send = true;

    fetch(action, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(response => {
        setTimeout(() => is_send = false, 400);
        if (response.ok) {
          return response.text();
        } else {
          throw new Error(`${response.status} ${response.statusText} ${response.url}`);
        }
      })
      .then(data => {
        setTimeout(() => is_send = false, 400);
        thisForm.querySelector('.loading').classList.remove('d-block');
        if (data.trim() == 'OK') {
          thisForm.querySelector('.sent-message').classList.add('d-block');
          thisForm.reset();
        } else {
          throw new Error(data ? data : 'Form submission failed and no error message returned from: ' + action);
        }
      })
      .catch((error) => {
        displayError(thisForm, error.message ? error.message : error);
      }).finally(() => {
        thisForm.querySelector('[type="submit"]').disabled = false;
        setTimeout(() => is_send = false, 400);
      });
  }

  function displayError(thisForm, error) {
    thisForm.querySelector('.loading').classList.remove('d-block');
    thisForm.querySelector('.error-message').innerHTML = error;
    thisForm.querySelector('.error-message').classList.add('d-block');
  }

})();
