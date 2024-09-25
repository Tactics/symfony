# CSRF protection

## Enabling CSRF protection

Add the [sfCsrfProtectionFilter](../filter/sfCsrfProtectionFilter.php) filter to your /app/\<yourapp\>/config/filters.yml.

When added, every action that is requested will be scanned for the [CsrfProtected](./CsrfProtected.php) attribute.

The attribute can be added to the validate\<ActionName\> or execute\<ActionName\> method.

If this attribute is found, a CSRF token is required and verified.

The form_tag helper function (see [FormHelper](../helper/FormHelper.php)), will automatically
inject a crsf token as hidden input parameter.

If no token is found or it is invalid, the user will be forwarded to the 404 error module/action as defined in
the /app/\<yourapp\>/config/settings.yml file.
