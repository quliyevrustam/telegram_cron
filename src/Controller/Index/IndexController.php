<?php

namespace Controller\Index;

use Controller\MainController;
use Model\Channel\Channel;
use Model\Channel\ChannelFound;
use Model\Channel\Message;
use Utilities\Helper;
use Utilities\Pagination;

class IndexController extends MainController
{
    public function index()
    {
        // Daily Top 5
        $filter = [
            'begin_date'   => date('Y-m-d'),
            'end_date'     => date('Y-m-d'),
            'channel_type' => Channel::TYPE_ALL,
            'channel_id'   => Channel::TYPE_ALL,
        ];

        $messageRecords = (new Message())->getMessageList((new Pagination(0, 5, 'err', 'desc')), $filter);

        $mainPage['daily_top_five_table'] = $messageRecords['records'];

        // Last 5 Message
        $filter = [
            'begin_date'   => date('Y-m-d'),
            'end_date'     => date('Y-m-d'),
            'channel_type' => Channel::TYPE_ALL,
            'channel_id'   => Channel::TYPE_ALL,
        ];

        $messageRecords = (new Message())->getMessageList((new Pagination(0, 5, 'created_at', 'desc')), $filter);

        $mainPage['last_five_message_table'] = $messageRecords['records'];

        // Weekly Not News Top 5
        $filter = [
            'begin_date'   => date('Y-m-d', strtotime('sunday -1 week')),
            'end_date'     => date('Y-m-d'),
            'channel_type' => Channel::TYPE_NOT_NEWS,
            'channel_id'   => Channel::TYPE_ALL,
        ];

        $messageRecords = (new Message())->getMessageList((new Pagination(0, 5, 'err', 'desc')), $filter);

        $mainPage['weekly_top_five_not_news_table'] = $messageRecords['records'];

        // Found Channel
        $filter = [
            'condition'   => ChannelFound::CONDITION_NOT_CHECKED,
        ];

        $foundChannels = (new ChannelFound())->getChannelFoundList((new Pagination(0, 5, 'follower_count', 'desc')), $filter);

        $mainPage['found_channel_table'] = $foundChannels['records'];

        // Last Found Channel
        $filter = [
            'condition'   => ChannelFound::CONDITION_NOT_CHECKED,
        ];

        $foundChannels = (new ChannelFound())->getChannelFoundList((new Pagination(0, 5, 'created_at', 'desc')), $filter);

        $mainPage['last_found_channel_table'] = $foundChannels['records'];

        return $this->tmp()->render('Index/start.html', ['main_page' => $mainPage]);
    }

    public function showName($name, $id)
    {
        return $this->tmp()->render('Index/start.html', ['name' => '#'.$id.' '.$name]);
    }

    public function postIndex()
    {
        $name = $this->http()->request->get('name');
        return $this->tmp()->render('Index/start.html', ['name' => $name]);
    }

    public function viewLoginPage()
    {
        return $this->tmp()->render('Index/login.html');
    }

    public function logoutUser()
    {
        $this->auth()->logoutUser();
    }
}