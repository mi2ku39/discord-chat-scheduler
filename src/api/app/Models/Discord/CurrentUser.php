<?php


namespace App\Models\Discord;


use App\Functions\DiscordOAuthFunction;
use App\Models\OAuth\DiscordToken;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CurrentUser
{
    protected PendingRequest $http_client;
    protected DiscordToken $token;
    protected ?Response $_user = null;

    /**
     * CurrentUser constructor.
     * @param DiscordToken $token
     */
    public function __construct(DiscordToken $token)
    {
        $this->token = $token;
        $this->http_client = Http::baseUrl('https://discord.com/api')->withToken($this->token->access_token);
    }

    /**
     * {
     *     "id": "80351110224678912",
     *     "username": "Nelly",
     *     "discriminator": "1337",
     *     "avatar": "8342729096ea3675442027381ff50dfe",
     *     "verified": true,
     *     "email": "nelly@discord.com",
     *     "flags": 64,
     *     "premium_type": 1,
     *     "public_flags": 64
     * }
     * @return Response
     */
    protected function _getCurrentUser()
    {
        // todo expiresしている時のトークン更新処理と再送処理
        if ($this->_user === null) {
            $this->_user = $this->http_client->get('/users/@me');
        }
        return $this->_user;
    }

    /**
     * @return ?string
     */
    public function getId(): ?string
    {
        return $this->_getCurrentUser()['id'];
    }

    /**
     * @return string
     */
    public function getUserName(): ?string
    {
        return $this->_getCurrentUser()['username'];
    }

    protected function refreshToken(DiscordOAuthFunction $func)
    {
        $response = $func->refreshAccessToken($this->token->refresh_token);
        $this->token->access_token = $response['access_token'];
        $this->token->refresh_token = $response['refresh_token'];
        $this->token->expires_in = $response['expires_in'];
        $this->token->save();
    }
}
