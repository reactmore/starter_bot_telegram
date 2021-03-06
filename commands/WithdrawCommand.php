<?php

/**
 * This file is part of the PHP Telegram Support Bot.
 *
 * (c) PHP Telegram Bot Team (https://github.com/php-telegram-bot)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use TelegramBot\SupportBot\Helpers;
use TelegramBot\SupportBot\Models\User;
use Longman\TelegramBot\Request;

/**
 * Display user and chat information.
 */
class WithdrawCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'withdraw';

    /**
     * @var string
     */
    protected $description = 'withdraw balance on Bot';

    /**
     * @var string
     */
    protected $usage = '/withdraw';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @return ServerResponse
     * @throws TelegramException
     */
    public function preExecute(): ServerResponse
    {
        $this->isPrivateOnly() && $this->removeNonPrivateMessage();
        $user = User::getUserData($this->getMessage()->getChat()->getId());

        // Make sure we only reply to messages.
        if (!$this->getMessage()) {
            return Request::emptyResponse();
        }

        if (empty($user['wallet'])) {
            $text = 'Kamu Belum Menambahkan Wallet di Profile silahkan masuk ke menu profil dan masukan info wallet disana';
            return $this->replyToUser($text, ['parse_mode' => 'markdown']);
        }

        return $this->execute();
    }

    /**
     * Execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $user_info = '👤 *User Info*' . PHP_EOL . $this->getUserInfo();
        $chat_info = '🗣 *Chat Info*' . PHP_EOL . $this->getChatInfo();

        return $this->replyToUser($user_info . PHP_EOL . PHP_EOL . $chat_info, ['parse_mode' => 'markdown']);
    }

    /**
     * Get the information of the user.
     *
     * @return string
     */
    protected function getUserInfo(): string
    {
        $user = $this->getMessage()->getFrom();

        return implode(PHP_EOL, [
            "User Id: `{$user->getId()}`",
            'First Name: ' . (($first_name = $user->getFirstName()) ? "`{$first_name}`" : '_n/a_'),
            'Last Name: ' . (($last_name = $user->getLastName()) ? "`{$last_name}`" : '_n/a_'),
            'Username: ' . (($username = $user->getUsername()) ? "`{$username}`" : '_n/a_'),
            'Language Code: ' . (($language_code = $user->getLanguageCode()) ? "`{$language_code}`" : '_n/a_'),
        ]);
    }

    /**
     * Get the information of the chat.
     *
     * @return string
     */
    protected function getChatInfo(): string
    {
        $message = $this->getMessage();
        $chat    = $message->getForwardFromChat() ?? $message->getChat();

        if (!$chat || $chat->isPrivateChat()) {
            return '`Private chat`';
        }

        return implode(PHP_EOL, [
            "Type: `{$chat->getType()}`",
            "Chat Id: `{$chat->getId()}`",
            'Title: ' . (($title = $chat->getTitle()) ? "`{$title}`" : '_n/a_'),
            'First Name: ' . (($first_name = $chat->getFirstName()) ? "`{$first_name}`" : '_n/a_'),
            'Last Name: ' . (($last_name = $chat->getLastName()) ? "`{$last_name}`" : '_n/a_'),
            'Username: ' . (($username = $chat->getUsername()) ? "`{$username}`" : '_n/a_'),
        ]);
    }
}
