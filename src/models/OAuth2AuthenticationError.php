<?php
declare(strict_types=1);

namespace Collecthor\OpenidConnectClient\models;

enum OAuth2AuthenticationError: string
{
    case InvalidRequest = 'invalid_request';
    case UnauthorizedClient = 'unauthorized_client';

    case AccessDenied = 'access_denied';

    case UnsupportedResponseType = 'unsupported_response_type';

    case InvalidScope = 'invalid_scope';

    case ServerScope = 'server_error';

    case TemporarilyUnavailable = 'temporarily_unavailable';


    public function description(): string
    {
        return match($this) {
            self::InvalidRequest => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.',
            default => "Todo: copy texts from https://www.rfc-editor.org/rfc/rfc6749#section-4.1.2"
        };
    }
}
