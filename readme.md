# PHP Contact Handler
This project processes AJAX contact form submits using Mailgun and (optionally) ReCaptcha.

## Installation

Download this repo and run `composer install`

Serve the app from `public/index.php` (you can also move this file; just update `require('../config.php')` to reflect the new path)

## Configuration

Copy `.env.sample` to `.env` and fill in the values appropriately. The variables are as follows:
- **MAILGUN_API_KEY**: Your API key generated from Mailgun (https://www.mailgun.com/)
- **MAILGUN_DOMAIN**: The domain name set on your Mailgun account
- **CONTACT_RECIPIENT**: The email address that will receive contact form submissions
- **CONTACT_SUBJECT**: Subject of the email containing the contact form submissions
- **FORM_DOMAIN**: The domain where the contact form will be hosted. This is to allow cross-domain AJAX requests. If you are hosting this handler and the form on the same domain, you can comment this line out
  - Note: subdomains count as separate domains. Make sure you include the http(s)://
  - Separate multiple domains with commas
- **BLOCKED_EMAIL_DOMAINS**: Email domains that should be blocked (separated by commas). The form will not send the message if it is filled out with an email from this domain. 
  - If all the other form fields are filled out correctly, the server will still return a `200 OK` header to make it more difficult for spammers to detect/circumvent the block
- **RECAPTCHA_SECRET**: Your secret code for the ReCaptcha API (https://www.google.com/recaptcha/). Comment this line out if you aren't using ReCaptcha.

## Usage

The form handler looks for POST fields called `name`, `email`, and `message`. All three are required. Optionally, you can add a field for ReCaptcha.

When the form processed, all responses are returned in JSON format. If the form submits successfully and the email is sent, a `200 OK` header is returned. If the email is not sent (but the form fields are OK), a `500 Internal Server Error` header is returned. If the fields are not filled out correctly (e.g. something is missing, the email address is not valid, the captcha is not filled out, etc.) a `400 Bad Request` response is returned with a JSON array of items to fix.

You can use your favorite combination of HTML/JavaScript frameworks to submit the form and handle the errors. Here is a simple example with vanilla HTML (with Bootstrap classes) and jQuery:

### HTML

```html
<div id='alert-container'></div>
<form action="FORM HANDLER URL" accept-charset="UTF-8" method="post" id="contact_form">
  <fieldset class="form-group">
    <label for="name" class="control-label">Name: </label>
    <input type="text" name="name" class="form-control" id="name">
  </fieldset>
  <fieldset class="form-group">
    <label for="email" class="control-label">Email: </label>
    <input type="email" name="email" class="form-control" id="email">
  </fieldset>
  <fieldset class="form-group">
    <label for="message" class="control-label">Message: </label>
    <textarea name="message" class="form-control" id="message"></textarea>
  </fieldset>
  <fieldset class="form-group">
    <label for="human_confirmation" class="control-label">Human confirmation: </label>
    <!-- uncomment to enable ReCaptcha
    <div class="g-recaptcha" data-sitekey="YOUR SITE KEY (note: this is NOT the same as the secret key in .env)"></div>
    -->
  </fieldset>
  <div class="form-group">
    <input type="submit" value="send" class="btn btn-primary">
  </div>
</form>
```

### Javascript

```js
$(function(){
    $('#contact_form').submit(function(e){
      e.preventDefault();
      $.ajax({
        method:$(this).attr('method'),
        url:$(this).attr('action'),
        data:$(this).serialize(),
        success:function(data){
          $('#alert-container').html("<div class='alert alert-success'><p>Message sent!</p></div>");
          $('#name').val('');
          $('#email').val('');
          $('#message').val('');
          window.scrollTo(0,0);
        },
        error:function(data){
          var errors=$('<ul></ul>');
          var response = JSON.parse(data.responseText);
          var item;

          for(var i=0; i<response.length; i++){
            item=response[i];
            var error = $('<li>'+item+'</li>');
            errors.append(error);
          }
          var errorAlert=$('<div class="alert alert-danger"></div>');

          errorAlert.append("<p>Please correct the following errors:</p>");
          errorAlert.append(errors);
          $('#alert-container').html(errorAlert);
        },
        /* uncomment to enable ReCaptcha
        complete:function(){
          grecaptcha.reset();
        }
        */
      });
    });
  });
```
