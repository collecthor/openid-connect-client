# Simple OpenID Connect Client

Many OIDC client implementation built on top of existing OAuth 2 libraries.
This means they often still use OAuth 2 mechanics and don't benefit from the simplicity of the ID token.

This library is purposely a very simple client:
- Only support the `code` flow, PHP is a backend language after all
- Always request the `openid` scope
- Ignore the `access_token` when exchanging the `authorization_code` for an `access_token` and `id_token`.
- Cryptographically verify the `id_token`, even though even this could be skipped in the `code` flow.


The goal is to be spec compliant in the sense that any scenario that we implement we want to implement correctly.


## Configuration discovery & caching
OpenID Connect uses configuration discovery via a well known URL. This is intentionally the only way to configure this library.
Instead of having support for caching inside this library, if you want caching, you must implement it as an adapter for the HTTP client.
Several adapters for this purpose exist and there is no need to cache any non-HTTP related stuff in this library.
